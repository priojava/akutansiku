<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\AccountingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitLossMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccountingSeeder::class);
    }

    public function test_comparative_profit_loss_by_department_loads(): void
    {
        $user = User::first();
        $company = Company::first();

        $dept1 = Department::create(['company_id' => $company->id, 'code' => 'D01', 'name' => 'Operasional']);
        $dept2 = Department::create(['company_id' => $company->id, 'code' => 'D02', 'name' => 'Pemasaran']);

        $response = $this->actingAs($user)->get('/reports/profit-loss?view_mode=by_project&group_by=department&selected_ids[]=' . $dept1->id);

        $response->assertStatus(200);
        $response->assertSee('Operasional');
    }

    public function test_comparative_profit_loss_multi_period_loads(): void
    {
        $user = User::first();

        $response = $this->actingAs($user)->get('/reports/profit-loss?view_mode=by_project&group_by=month&start_date=2026-07-01&end_date=2026-09-30');

        $response->assertStatus(200);
        $response->assertSee('Total');
    }
}
