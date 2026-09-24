<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_login_and_register_pages_display_google_sso_buttons(): void
    {
        $loginResponse = $this->get(route('login'));
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee(route('auth.google'));
        $loginResponse->assertSee('Masuk dengan Akun Google');

        $registerResponse = $this->get(route('register'));
        $registerResponse->assertStatus(200);
        $registerResponse->assertSee(route('auth.google'));
        $registerResponse->assertSee('Daftar Cepat dengan Akun Google');
    }

    public function test_redirect_to_google_shows_warning_when_credentials_not_configured(): void
    {
        config(['services.google.client_id' => '']);
        config(['services.google.client_secret' => '']);

        $response = $this->get(route('auth.google'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
    }

    public function test_redirect_to_google_redirects_when_credentials_present(): void
    {
        config(['services.google.client_id' => 'dummy-client-id']);
        config(['services.google.client_secret' => 'dummy-client-secret']);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($provider);

        $response = $this->get(route('auth.google'));
        $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_google_callback_logs_in_existing_user_and_updates_google_id(): void
    {
        $company = Company::create([
            'name' => 'PT Sumber Rejeki',
            'plan_type' => 'premium',
        ]);

        $existingUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'password' => bcrypt('secret123'),
            'default_company_id' => $company->id,
        ]);
        $existingUser->companies()->attach($company->id, ['role' => 'admin']);

        // Mock Socialite Google User
        $socialiteUser = new SocialiteUser();
        $socialiteUser->id = 'google-123456789';
        $socialiteUser->name = 'Budi Santoso';
        $socialiteUser->email = 'budi.santoso@example.com';
        $socialiteUser->avatar = 'https://lh3.googleusercontent.com/a/avatar.jpg';

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')
            ->once()
            ->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($existingUser);

        $existingUser->refresh();
        $this->assertEquals('google-123456789', $existingUser->google_id);
        $this->assertEquals('https://lh3.googleusercontent.com/a/avatar.jpg', $existingUser->avatar);
    }

    public function test_google_callback_creates_new_user_and_provisions_company(): void
    {
        $socialiteUser = new SocialiteUser();
        $socialiteUser->id = 'google-999888777';
        $socialiteUser->name = 'Andi Baru';
        $socialiteUser->email = 'andi.baru@example.com';
        $socialiteUser->avatar = 'https://lh3.googleusercontent.com/a/new-avatar.jpg';

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')
            ->once()
            ->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard'));

        $newUser = User::where('email', 'andi.baru@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('google-999888777', $newUser->google_id);
        $this->assertAuthenticatedAs($newUser);

        // Check company provisioned
        $this->assertNotNull($newUser->default_company_id);
        $this->assertDatabaseHas('companies', [
            'id' => $newUser->default_company_id,
            'email' => 'andi.baru@example.com',
        ]);
    }
}
