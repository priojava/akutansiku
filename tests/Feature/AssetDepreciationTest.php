<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Asset;
use App\Models\AssetDepreciationLog;
use App\Models\AssetType;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\AssetDepreciationService;
use Database\Seeders\AccountingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetDepreciationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccountingSeeder::class);

        $this->company = Company::first();
        $this->user = User::first();
    }

    public function test_asset_types_seeded_and_displayed(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['active_company_id' => $this->company->id])
            ->get(route('master.asset_types'));

        $response->assertStatus(200);
        $response->assertSee('Kendaraan');
        $response->assertSee('Bangunan & Gedung');
        $response->assertSee('Peralatan & Inventaris Kantor');
    }

    public function test_can_create_asset_with_type_and_usage_date(): void
    {
        $service = app(AssetDepreciationService::class);
        $service->seedDefaultAssetTypes($this->company->id);

        $kendaraanType = AssetType::where('company_id', $this->company->id)->where('code', 'KND')->first();
        $kasAccount = Account::where('company_id', $this->company->id)->where('code', '1-10001')->first();

        $response = $this->actingAs($this->user)
            ->withSession(['active_company_id' => $this->company->id])
            ->post(route('assets.store'), [
                'asset_type_id' => $kendaraanType->id,
                'code' => 'motorbeat2026',
                'name' => 'Sepeda Motor Honda Beat',
                'acquisition_date' => '2026-08-01',
                'usage_date' => '2026-08-15',
                'acquisition_cost' => '24.000.000',
                'asset_account_id' => $kendaraanType->asset_account_id,
                'credited_account_id' => $kasAccount->id,
                'is_depreciated' => 1,
                'depreciation_method' => 'straight_line',
                'useful_life_years' => 4,
                'useful_life_months' => 0,
                'salvage_value' => '0',
                'expense_account_id' => $kendaraanType->expense_account_id,
                'accumulated_depreciation_account_id' => $kendaraanType->accumulated_account_id,
            ]);

        $response->assertRedirect(route('assets.index'));
        
        $asset = Asset::where('code', 'motorbeat2026')->first();
        $this->assertNotNull($asset);
        $this->assertEquals($this->company->id, $asset->company_id);
        $this->assertEquals('Sepeda Motor Honda Beat', $asset->name);
        $this->assertEquals('2026-08-15', $asset->usage_date->format('Y-m-d'));
        $this->assertEquals(24000000, $asset->acquisition_cost);
        $this->assertTrue($asset->is_depreciated);
        $this->assertEquals(48, $asset->useful_life_months);

        // 24.000.000 / 48 bulan = 500.000 per bulan
        $this->assertEquals(500000, $asset->monthly_depreciation);
    }

    public function test_automatic_monthly_depreciation_execution_and_journal_creation(): void
    {
        $service = app(AssetDepreciationService::class);
        $service->seedDefaultAssetTypes($this->company->id);

        $kendaraanType = AssetType::where('company_id', $this->company->id)->where('code', 'KND')->first();
        $kasAccount = Account::where('company_id', $this->company->id)->where('code', '1-10001')->first();

        $asset = Asset::create([
            'company_id' => $this->company->id,
            'asset_type_id' => $kendaraanType->id,
            'code' => 'motorbeat',
            'name' => 'Motor Beat',
            'acquisition_date' => '2026-08-01',
            'usage_date' => '2026-08-01',
            'acquisition_cost' => 24000000,
            'asset_account_id' => $kendaraanType->asset_account_id,
            'credited_account_id' => $kasAccount->id,
            'is_depreciated' => true,
            'useful_life_years' => 4,
            'useful_life_months' => 48,
            'salvage_value' => 0,
            'expense_account_id' => $kendaraanType->expense_account_id,
            'accumulated_depreciation_account_id' => $kendaraanType->accumulated_account_id,
            'accumulated_depreciation_amount' => 0,
            'depreciation_status' => 'active',
        ]);

        // Eksekusi depresiasi periode Agustus 2026
        $response = $this->actingAs($this->user)
            ->withSession(['active_company_id' => $this->company->id])
            ->post(route('assets.depreciation.run'), [
                'period' => '2026-08',
            ]);

        $response->assertSessionHas('success');

        // Verifikasi Log & Akumulasi Aset terupdate
        $log = AssetDepreciationLog::where('company_id', $this->company->id)
            ->where('asset_id', $asset->id)
            ->where('period', '2026-08')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(500000, $log->depreciation_amount);
        $this->assertEquals('2026-08-31', $log->depreciation_date->format('Y-m-d'));

        $asset->refresh();
        $this->assertEquals(500000, $asset->accumulated_depreciation_amount);
        $this->assertEquals(23500000, $asset->book_value);

        // Verifikasi Jurnal Umum dibuat: Debit Beban Penyusutan, Kredit Akumulasi Penyusutan
        $journal = JournalEntry::where('company_id', $this->company->id)
            ->where('description', 'like', '%Penyusutan Aset Bulanan%')
            ->first();

        $this->assertNotNull($journal);
        $this->assertEquals('2026-08-31', $journal->date->format('Y-m-d'));

        $items = $journal->items;
        $debitItem = $items->firstWhere('account_id', $kendaraanType->expense_account_id);
        $creditItem = $items->firstWhere('account_id', $kendaraanType->accumulated_account_id);

        $this->assertNotNull($debitItem);
        $this->assertEquals(500000, $debitItem->debit);

        $this->assertNotNull($creditItem);
        $this->assertEquals(500000, $creditItem->credit);

        // Eksekusi ulang di periode yang sama tidak boleh menduplikasi
        $responseSecond = $this->actingAs($this->user)
            ->withSession(['active_company_id' => $this->company->id])
            ->post(route('assets.depreciation.run'), [
                'period' => '2026-08',
            ]);

        $responseSecond->assertSessionHas('info');
        $this->assertEquals(1, AssetDepreciationLog::where('company_id', $this->company->id)->where('period', '2026-08')->count());
    }

    public function test_can_rollback_depreciation_log(): void
    {
        $service = app(AssetDepreciationService::class);
        $service->seedDefaultAssetTypes($this->company->id);

        $kendaraanType = AssetType::where('company_id', $this->company->id)->where('code', 'KND')->first();
        $kasAccount = Account::where('company_id', $this->company->id)->where('code', '1-10001')->first();

        $asset = Asset::create([
            'company_id' => $this->company->id,
            'asset_type_id' => $kendaraanType->id,
            'code' => 'motorbeat',
            'name' => 'Motor Beat',
            'acquisition_date' => '2026-08-01',
            'usage_date' => '2026-08-01',
            'acquisition_cost' => 24000000,
            'asset_account_id' => $kendaraanType->asset_account_id,
            'credited_account_id' => $kasAccount->id,
            'is_depreciated' => true,
            'useful_life_years' => 4,
            'useful_life_months' => 48,
            'salvage_value' => 0,
            'expense_account_id' => $kendaraanType->expense_account_id,
            'accumulated_depreciation_account_id' => $kendaraanType->accumulated_account_id,
            'accumulated_depreciation_amount' => 0,
            'depreciation_status' => 'active',
        ]);

        $service->executePeriodDepreciation($this->company->id, '2026-08', $this->user->id);

        $asset->refresh();
        $this->assertEquals(500000, $asset->accumulated_depreciation_amount);

        $log = AssetDepreciationLog::where('company_id', $this->company->id)->where('period', '2026-08')->first();
        $this->assertNotNull($log);

        // Batalkan (Rollback)
        $response = $this->actingAs($this->user)
            ->withSession(['active_company_id' => $this->company->id])
            ->delete(route('assets.depreciation.rollback', $log->id));

        $response->assertSessionHas('success');

        $asset->refresh();
        $this->assertEquals(0, $asset->accumulated_depreciation_amount);
        $this->assertEquals(0, AssetDepreciationLog::where('company_id', $this->company->id)->count());
    }

    public function test_can_bulk_depreciate_multiple_pending_periods(): void
    {
        $service = app(AssetDepreciationService::class);
        $service->seedDefaultAssetTypes($this->company->id);

        $kendaraanType = AssetType::where('company_id', $this->company->id)->where('code', 'KND')->first();
        $kasAccount = Account::where('company_id', $this->company->id)->where('code', '1-10001')->first();

        // Aset mulai dipakai sejak 2 bulan lalu (misal Juni 2026)
        $asset = Asset::create([
            'company_id' => $this->company->id,
            'asset_type_id' => $kendaraanType->id,
            'code' => 'motorbeat',
            'name' => 'Motor Beat',
            'acquisition_date' => '2026-06-01',
            'usage_date' => '2026-06-01',
            'acquisition_cost' => 24000000,
            'asset_account_id' => $kendaraanType->asset_account_id,
            'credited_account_id' => $kasAccount->id,
            'is_depreciated' => true,
            'useful_life_years' => 4,
            'useful_life_months' => 48,
            'salvage_value' => 0,
            'expense_account_id' => $kendaraanType->expense_account_id,
            'accumulated_depreciation_account_id' => $kendaraanType->accumulated_account_id,
            'accumulated_depreciation_amount' => 0,
            'depreciation_status' => 'active',
        ]);

        $pending = $service->getPendingPeriods($this->company->id);
        $this->assertNotEmpty($pending);

        // Eksekusi bulk via POST route
        $response = $this->actingAs($this->user)
            ->withSession(['active_company_id' => $this->company->id])
            ->post(route('assets.depreciation.run_bulk'));

        $response->assertSessionHas('success');

        $asset->refresh();
        $this->assertGreaterThan(0, $asset->accumulated_depreciation_amount);
        $this->assertGreaterThan(0, AssetDepreciationLog::where('company_id', $this->company->id)->count());
    }

    public function test_artisan_command_depreciates_assets(): void
    {
        $service = app(AssetDepreciationService::class);
        $service->seedDefaultAssetTypes($this->company->id);

        $kendaraanType = AssetType::where('company_id', $this->company->id)->where('code', 'KND')->first();
        $kasAccount = Account::where('company_id', $this->company->id)->where('code', '1-10001')->first();

        Asset::create([
            'company_id' => $this->company->id,
            'asset_type_id' => $kendaraanType->id,
            'code' => 'motorbeat',
            'name' => 'Motor Beat',
            'acquisition_date' => '2026-08-01',
            'usage_date' => '2026-08-01',
            'acquisition_cost' => 24000000,
            'asset_account_id' => $kendaraanType->asset_account_id,
            'credited_account_id' => $kasAccount->id,
            'is_depreciated' => true,
            'useful_life_years' => 4,
            'useful_life_months' => 48,
            'salvage_value' => 0,
            'expense_account_id' => $kendaraanType->expense_account_id,
            'accumulated_depreciation_account_id' => $kendaraanType->accumulated_account_id,
            'accumulated_depreciation_amount' => 0,
            'depreciation_status' => 'active',
        ]);

        $this->artisan('assets:depreciate-monthly', [
            '--company_id' => $this->company->id,
            '--period' => '2026-08',
        ])->assertSuccessful();

        $this->assertDatabaseHas('asset_depreciation_logs', [
            'company_id' => $this->company->id,
            'period' => '2026-08',
            'depreciation_amount' => 500000,
        ]);
    }
}
