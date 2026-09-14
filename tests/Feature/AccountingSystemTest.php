<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Asset;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\AccountingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccountingSeeder::class);
    }

    public function test_dashboard_page_loads_successfully(): void
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dapur Gemoy');
        $response->assertSee('Kas & Bank', false);
        $response->assertSee('Laba Rugi');
    }

    public function test_transaction_create_page_loads(): void
    {
        $response = $this->get('/transactions/create');
        $response->assertStatus(200);
        $response->assertSee('Tambah Transaksi');
        $response->assertSee('minta bantuan AI untuk penjurnalan');
    }

    public function test_master_accounts_loads_with_120_coa(): void
    {
        $response = $this->get('/master/accounts');
        $response->assertStatus(200);
        $response->assertSee('Tanggal Konversi Saldo Awal');
        $response->assertSee('1-10001');
        $response->assertSee('Beban Pokok Pendapatan');
    }

    public function test_profit_and_loss_report_calculates_correctly(): void
    {
        $response = $this->get('/reports/profit-loss');
        $response->assertStatus(200);
        $response->assertSee('Harga Pokok Penjualan');
        $response->assertSee('Laba Bersih');
    }

    public function test_trial_balance_report_loads(): void
    {
        $response = $this->get('/reports/trial-balance');
        $response->assertStatus(200);
        $response->assertSee('SALDO DEBIT');
        $response->assertSee('SALDO KREDIT');
    }

    public function test_journal_report_loads(): void
    {
        $response = $this->get('/reports/journal');
        $response->assertStatus(200);
        $response->assertSee('Laporan Jurnal Umum');
        $response->assertSee('Total Ayat Jurnal');
        $response->assertSee('Total Mutasi Debit');
    }

    public function test_can_create_new_coa_account(): void
    {
        $response = $this->post('/master/accounts', [
            'name' => 'Bank Mandiri Syariah',
            'category' => 'Kas & Bank',
            'code_prefix' => '1-10',
            'code_number' => '102',
            'description' => 'Rekening Operasional Tambahan'
        ]);

        $response->assertRedirect(route('master.accounts'));
        $this->assertDatabaseHas('accounts', [
            'code' => '1-10102',
            'name' => 'Bank Mandiri Syariah',
            'type' => 'Debit'
        ]);
    }

    public function test_can_create_contact(): void
    {
        $response = $this->get('/master/contacts');
        $response->assertStatus(200);

        $storeResponse = $this->post('/master/contacts', [
            'name' => 'PT Pangan Nusantara',
            'type' => 'vendor',
            'phone' => '08123456789',
            'email' => 'vendor@pangan.com',
            'address' => 'Jl. Industri No 45',
        ]);

        $storeResponse->assertRedirect(route('master.contacts'));
        $this->assertDatabaseHas('contacts', [
            'name' => 'PT Pangan Nusantara',
            'type' => 'vendor',
        ]);
    }

    public function test_can_toggle_lock_initial_balances(): void
    {
        $response = $this->post('/master/accounts/toggle-lock');
        $response->assertRedirect(route('master.accounts'));

        $company = Company::first();
        $this->assertTrue($company->is_initial_balance_locked);
    }

    public function test_employee_and_role_management(): void
    {
        $response = $this->get('/settings/employees');
        $response->assertStatus(200);
        $response->assertSee('Karyawan & Level Akses (RBAC)', false);
        $response->assertSee('Matriks Wewenang');

        $storeResponse = $this->post('/settings/employees', [
            'name' => 'Budi Staff',
            'email' => 'budi@example.com',
            'phone' => '0812345678',
            'role' => 'cashier',
        ]);

        $storeResponse->assertRedirect(route('settings.employees'));
        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
    }

    public function test_can_login_and_logout(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Akuntansiku');

        // Test login
        $loginResponse = $this->post('/login', [
            'email' => 'admin@dapurgemoy.com',
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        // Test quick login as accountant
        $quickResponse = $this->get('/login/quick/accountant');
        $quickResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        // Test quick login as cashier
        $cashierResponse = $this->get('/login/quick/cashier');
        $cashierResponse->assertRedirect(route('dashboard'));

        // Test logout
        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_can_record_transaction_and_balanced_journal(): void
    {
        $company = Company::first();
        $kas = Account::where('company_id', $company->id)->where('code', '1-10001')->first();
        $pendapatan = Account::where('company_id', $company->id)->where('code', '4-40000')->first();

        $response = $this->post('/transactions/store', [
            'date' => '2026-09-09',
            'time' => '10:00',
            'type' => 'income',
            'debit_account_id' => $kas->id,
            'credit_account_id' => $pendapatan->id,
            'amount' => 500000,
            'notes' => 'Penjualan Catering Harian',
        ]);

        $response->assertRedirect(route('transactions.history'));

        $this->assertDatabaseHas('transactions', [
            'notes' => 'Penjualan Catering Harian',
            'amount' => 500000,
        ]);
    }

    public function test_api_v1_dashboard_summary(): void
    {
        $response = $this->getJson('/api/v1/dashboard/summary');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'kas_bank',
                'piutang',
                'hutang',
                'laba_rugi',
                'beban_operasional',
                'arus_kas'
            ]
        ]);
    }

    public function test_api_v1_ai_journal_parser(): void
    {
        $response = $this->postJson('/api/v1/ai/parse', [
            'prompt' => 'Beli bensin 50rb pakai kas'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'confidence' => 'high'
        ]);
    }

    public function test_owner_can_reset_testing_data(): void
    {
        $admin = User::first();
        $this->actingAs($admin);

        // Test reset transactions
        $response = $this->post('/settings/reset-data', [
            'reset_type' => 'transactions_only',
            'confirmation_text' => 'RESET',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertEquals(0, Transaction::count());
        $this->assertEquals(0, JournalEntry::count());
    }

    public function test_reset_data_works_with_default_user(): void
    {
        // Without actingAs, should fall back to default user (Admin)
        $response = $this->post('/settings/reset-data', [
            'reset_type' => 'transactions_only',
            'confirmation_text' => 'RESET',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
    }

    public function test_non_admin_cannot_reset_data(): void
    {
        $cashier = User::where('email', 'kasir@dapurgemoy.com')->first();
        if ($cashier) {
            $response = $this->actingAs($cashier)->post('/settings/reset-data', [
                'reset_type' => 'transactions_only',
                'confirmation_text' => 'RESET',
            ]);

            $response->assertSessionHas('error');
        }
    }

    public function test_asset_management_flow(): void
    {
        $company = Company::first();
        $admin = User::first();
        $this->actingAs($admin);

        // 1. Index page loads
        $response = $this->get('/assets');
        $response->assertStatus(200);
        $response->assertSee('Daftar Aset (Aktiva Tetap)');

        // 2. Create page loads
        $createResponse = $this->get('/assets/create');
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Tambah Aset');

        // 3. Store new asset
        $kendaraan = Account::where('company_id', $company->id)->where('code', '1-10703')->first()
            ?? Account::where('company_id', $company->id)->where('category', 'Harta Tetap')->first();
        $kas = Account::where('company_id', $company->id)->where('code', '1-10001')->first();
        $bebanPenyusutan = Account::where('company_id', $company->id)->where('category', 'Beban')->first();
        $akumulasi = Account::where('company_id', $company->id)->where('category', 'Depresiasi & Amortisasi')->first();

        $storeResponse = $this->post('/assets/store', [
            'acquisition_date' => '2026-09-09',
            'code' => 'toyotainnova',
            'name' => 'Mobil Innova Toyota',
            'description' => 'Kendaraan operasional kantor',
            'asset_account_id' => $kendaraan->id,
            'acquisition_cost' => '150.000.000',
            'tax_amount' => '0',
            'credited_account_id' => $kas->id,
            'is_depreciated' => '1',
            'depreciation_method' => 'straight_line',
            'useful_life_years' => '4',
            'salvage_value' => '0',
            'expense_account_id' => $bebanPenyusutan?->id,
            'accumulated_depreciation_account_id' => $akumulasi?->id,
        ]);

        $storeResponse->assertRedirect(route('assets.index'));
        $this->assertDatabaseHas('assets', [
            'code' => 'toyotainnova',
            'name' => 'Mobil Innova Toyota',
            'acquisition_cost' => 150000000,
        ]);

        // 4. Export CSV works
        $exportResponse = $this->get('/assets/export');
        $exportResponse->assertStatus(200);
        $this->assertTrue(str_contains($exportResponse->getContent(), 'toyotainnova'));
    }

    public function test_master_tutup_buku_flow(): void
    {
        $company = Company::first();
        $admin = User::first();
        $this->actingAs($admin);

        // 1. Index loads
        $indexResponse = $this->get('/closing');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Master Tutup Buku');
        $indexResponse->assertSee('Buat Tutup Buku Baru');

        // 2. Create form loads with worksheet
        $createResponse = $this->get('/closing/create?closing_date=2026-08-31');
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Periode tutup buku');
        $createResponse->assertSee('NERACA SALDO');
        $createResponse->assertSee('LABA RUGI');
        $createResponse->assertSee('NERACA');

        // 3. Store closing period
        $retainedEarnings = Account::where('company_id', $company->id)
            ->where(function($q) {
                $q->whereIn('category', ['Modal', 'Ekuitas'])->orWhere('code', 'like', '3-%');
            })->first();
        $taxExpense = Account::where('company_id', $company->id)->where('category', 'Beban')->first();
        $taxPayable = Account::where('company_id', $company->id)->whereIn('category', ['Akun Hutang', 'Kewajiban'])->first()
            ?? Account::where('company_id', $company->id)->where('code', 'like', '2-%')->first();

        $storeResponse = $this->post('/closing/store', [
            'closing_date' => '2026-08-31',
            'notes' => 'Tutup Buku Periode Agustus 2026',
            'tax_expense_account_id' => $taxExpense?->id,
            'tax_amount' => '100.000',
            'tax_payable_account_id' => $taxPayable?->id,
            'retained_earnings_account_id' => $retainedEarnings->id,
        ]);

        $storeResponse->assertRedirect(route('closing.index'));
        $this->assertDatabaseHas('closing_periods', [
            'period_name' => 'Agustus 2026',
            'tax_amount' => 100000,
        ]);

        // 4. Show detail page loads
        $closing = \App\Models\ClosingPeriod::latest()->first();
        $showResponse = $this->get('/closing/' . $closing->id);
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Detail Tutup Buku');
        $showResponse->assertSee('Ayat Jurnal Penutup');
    }

    public function test_master_pajak_flow(): void
    {
        $company = Company::first();
        $admin = User::first();
        $this->actingAs($admin);

        // 1. Taxes index loads
        $indexResponse = $this->get('/master/taxes');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Tambah Pajak');

        // 2. Store new tax
        $salesAcc = Account::where('company_id', $company->id)->where('code', '2-20500')->first()
            ?? Account::where('company_id', $company->id)->where('category', 'Akun Hutang')->first();
        $purchaseAcc = Account::where('company_id', $company->id)->where('code', '1-10500')->first()
            ?? Account::where('company_id', $company->id)->where('category', 'Harta Lancar Lainnya')->first();

        $storeResponse = $this->post('/master/taxes', [
            'name' => 'PPN 11%',
            'rate' => 11,
            'is_withholding' => '0',
            'sales_account_id' => $salesAcc?->id,
            'purchase_account_id' => $purchaseAcc?->id,
        ]);

        $storeResponse->assertRedirect(route('master.taxes'));
        $this->assertDatabaseHas('taxes', [
            'name' => 'PPN 11%',
            'rate' => 11,
        ]);

        // 3. Update tax
        $tax = \App\Models\Tax::where('name', 'PPN 11%')->first();
        $updateResponse = $this->put('/master/taxes/' . $tax->id, [
            'name' => 'PPN 12%',
            'rate' => 12,
            'is_withholding' => '0',
            'sales_account_id' => $salesAcc?->id,
            'purchase_account_id' => $purchaseAcc?->id,
        ]);
        $updateResponse->assertRedirect(route('master.taxes'));
        $this->assertDatabaseHas('taxes', [
            'name' => 'PPN 12%',
            'rate' => 12,
        ]);

        // 4. Delete tax
        $deleteResponse = $this->delete('/master/taxes/' . $tax->id);
        $deleteResponse->assertRedirect(route('master.taxes'));
        $this->assertDatabaseMissing('taxes', [
            'id' => $tax->id,
        ]);
    }

    public function test_multi_company_management_flow(): void
    {
        $admin = User::first();
        $this->actingAs($admin);

        // 1. Company index page loads
        $response = $this->get('/company/switch');
        $response->assertStatus(200);
        $response->assertSee('Tambah Perusahaan');

        // 2. Store new company
        $storeResponse = $this->post('/company/store', [
            'name' => 'PT Gemoy Logistik Sejahtera',
            'city' => 'Jakarta Barat',
            'address' => 'Jl. Kebon Jeruk No. 12',
            'phone' => '081122334455',
            'email' => 'finance@gemoylogistik.com',
            'plan_type' => 'premium',
            'conversion_date' => '2026-09-01',
        ]);

        $storeResponse->assertRedirect(route('company.switch'));
        $newCompany = Company::where('name', 'PT Gemoy Logistik Sejahtera')->first();
        $this->assertNotNull($newCompany);

        // Verify COA automatically seeded for the new company
        $this->assertEquals(120, Account::where('company_id', $newCompany->id)->count());

        // 3. Switch company
        $switchResponse = $this->post('/company/switch/' . $newCompany->id);
        $switchResponse->assertRedirect(route('dashboard'));
        $this->assertEquals($newCompany->id, session('active_company_id'));

        // 4. Update company info
        $updateResponse = $this->put('/company/' . $newCompany->id, [
            'name' => 'RUMAH MAKAN PADANG',
            'city' => 'BEKASI KOTA',
            'phone' => '085714680135',
            'email' => 'suprianto2125@gmail.com',
            'address' => 'Jl. Juanda No. 10',
            'plan_type' => 'premium',
        ]);
        $updateResponse->assertRedirect(route('company.switch'));
        $this->assertDatabaseHas('companies', [
            'id' => $newCompany->id,
            'name' => 'RUMAH MAKAN PADANG',
            'phone' => '085714680135',
        ]);
    }

    public function test_cashier_dashboard_and_isolated_transaction_history(): void
    {
        $company = Company::first();

        // 1. Setup Cashier user
        $cashierUser = User::firstOrCreate(
            ['email' => 'kasir@dapurgemoy.com'],
            [
                'name' => 'Siti Kasir',
                'password' => bcrypt('password'),
                'default_company_id' => $company->id,
            ]
        );
        $cashierUser->companies()->syncWithoutDetaching([$company->id => ['role' => 'cashier']]);

        // 2. Setup Admin user
        $adminUser = User::where('email', 'admin@dapurgemoy.com')->first() ?? User::first();

        // 3. Cashier visits dashboard -> sees specialized cashier terminal
        $this->actingAs($cashierUser);
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Terminal Kasir & Shift');
        $dashboardResponse->assertSee('Kas di Laci (Net)');
        $dashboardResponse->assertSee('Pemasukan Hari Ini');
        $dashboardResponse->assertSee('Pintasan Kasir');

        // 4. Create distinct transactions
        $kasAccount = Account::where('company_id', $company->id)->where('code', '1-10001')->first() ?? Account::first();
        $pendapatanAccount = Account::where('company_id', $company->id)->where('code', '4-40000')->first() ?? Account::latest()->first();

        $cashierTrx = Transaction::create([
            'company_id' => $company->id,
            'transaction_number' => 'TRX-KASIR-001',
            'date' => now()->toDateString(),
            'time' => '10:00:00',
            'type' => 'income',
            'debit_account_id' => $kasAccount->id,
            'credit_account_id' => $pendapatanAccount->id,
            'amount' => 125000,
            'notes' => 'Pesanan Meja 5 - Siti',
            'created_by' => $cashierUser->id,
        ]);

        $adminTrx = Transaction::create([
            'company_id' => $company->id,
            'transaction_number' => 'TRX-ADMIN-999',
            'date' => now()->toDateString(),
            'time' => '11:00:00',
            'type' => 'income',
            'debit_account_id' => $kasAccount->id,
            'credit_account_id' => $pendapatanAccount->id,
            'amount' => 950000,
            'notes' => 'Catering Kantor - Budi Admin',
            'created_by' => $adminUser->id,
        ]);

        // 5. Cashier accesses transaction history -> only sees cashierTrx
        $cashierHistoryResponse = $this->actingAs($cashierUser)->get('/transactions/history');
        $cashierHistoryResponse->assertStatus(200);
        $cashierHistoryResponse->assertSee('TRX-KASIR-001');
        $cashierHistoryResponse->assertSee('Pesanan Meja 5 - Siti');
        $cashierHistoryResponse->assertDontSee('TRX-ADMIN-999');
        $cashierHistoryResponse->assertDontSee('Catering Kantor - Budi Admin');
        $cashierHistoryResponse->assertSee('Mode Kasir Terisolasi');

        // 6. Admin accesses transaction history -> sees both transactions
        $adminHistoryResponse = $this->actingAs($adminUser)->get('/transactions/history');
        $adminHistoryResponse->assertStatus(200);
        $adminHistoryResponse->assertSee('TRX-KASIR-001');
        $adminHistoryResponse->assertSee('TRX-ADMIN-999');
    }

    public function test_saas_subscription_and_superadmin_workflow(): void
    {
        $company = Company::first();
        $adminUser = User::first();
        $this->actingAs($adminUser);

        // 1. Tenant visits subscription page
        $subResponse = $this->get('/subscription');
        $subResponse->assertStatus(200);
        $subResponse->assertSee('Status :');
        $subResponse->assertSee('Perpanjang Langganan');
        $subResponse->assertSee('Tagihan Terbaru');

        // 2. Tenant renews subscription for 6 months
        $renewResponse = $this->post('/subscription/renew', [
            'plan_name' => 'premium',
            'duration_months' => 6,
            'payment_method' => 'Transfer Bank BCA',
        ]);
        $renewResponse->assertRedirect(route('subscription.index'));
        $renewResponse->assertSessionHas('success');

        $latestInvoice = \App\Models\SubscriptionInvoice::where('company_id', $company->id)->latest('id')->first();
        $this->assertNotNull($latestInvoice);
        $this->assertEquals(6, $latestInvoice->duration_months);
        $this->assertEquals('paid', $latestInvoice->status);

        // 3. View Invoice Page
        $invoiceResponse = $this->get('/subscription/invoice/' . $latestInvoice->id);
        $invoiceResponse->assertStatus(200);
        $invoiceResponse->assertSee($latestInvoice->invoice_number);
        $invoiceResponse->assertSee('LUNAS');

        // 4. Super Admin visits SaaS Portal
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@dapurgemoy.com'],
            ['name' => 'Super Admin', 'password' => bcrypt('password'), 'is_superadmin' => true]
        );
        $superadmin->update(['is_superadmin' => true]);

        $this->actingAs($superadmin);
        $saasDashboard = $this->get('/superadmin/dashboard');
        $saasDashboard->assertStatus(200);
        $saasDashboard->assertSee('Master SaaS Control Center');

        // 5. Super Admin updates tenant plan and extends days
        $saasTenants = $this->get('/superadmin/tenants');
        $saasTenants->assertStatus(200);
        $saasTenants->assertSee($company->name);

        $updateTenantResponse = $this->post('/superadmin/tenants/' . $company->id . '/update-plan', [
            'subscription_plan' => 'premium',
            'subscription_status' => 'active',
            'extend_days' => 30,
        ]);
        $updateTenantResponse->assertSessionHas('success');

        // 6. Super Admin views all invoices
        $saasInvoices = $this->get('/superadmin/invoices');
        $saasInvoices->assertStatus(200);
        $saasInvoices->assertSee($latestInvoice->invoice_number);
    }

    public function test_public_saas_registration_flow(): void
    {
        // 1. Guest visits register page
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Form Pendaftaran Klien Baru');
        $response->assertSee('TRIAL 14 HARI');

        // 2. Submit new business registration
        $postResponse = $this->post('/register', [
            'name' => 'Pak Taji Owner',
            'email' => 'taji@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'TOKO SEMBAKO TAJI',
            'city' => 'Bekasi',
            'phone' => '081234567890',
        ]);

        $postResponse->assertRedirect(route('dashboard'));
        $postResponse->assertSessionHas('success');
        $this->assertAuthenticated();

        // 3. Verify created company & automated provisioning
        $tajiCompany = Company::where('name', 'TOKO SEMBAKO TAJI')->first();
        $this->assertNotNull($tajiCompany);
        $this->assertEquals('trial', $tajiCompany->subscription_status);
        $this->assertTrue($tajiCompany->remaining_days >= 13);

        // Verify COA automatically seeded
        $this->assertEquals(120, Account::where('company_id', $tajiCompany->id)->count());

        // Verify Trial Invoice
        $trialInvoice = \App\Models\SubscriptionInvoice::where('company_id', $tajiCompany->id)->first();
        $this->assertNotNull($trialInvoice);
        $this->assertEquals(0, (float)$trialInvoice->amount);
        $this->assertEquals('paid', $trialInvoice->status);
    }

    public function test_superadmin_and_owner_can_delete_company(): void
    {
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@dapurgemoy.com'],
            ['name' => 'Super Admin Master', 'password' => bcrypt('password'), 'is_superadmin' => true]
        );
        $superadmin->update(['is_superadmin' => true]);

        // 1. Create a dummy company to delete
        $tempCompany = Company::create([
            'name' => 'PT Mau Dihapus',
            'city' => 'Jakarta',
            'plan_type' => 'free',
            'subscription_status' => 'trial',
        ]);

        $this->actingAs($superadmin);

        // 2. Super Admin deletes the company
        $deleteResponse = $this->delete('/superadmin/tenants/' . $tempCompany->id);
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('companies', ['id' => $tempCompany->id]);

        // 3. Prevent deleting if only 1 company left
        $allCompanies = Company::all();
        if ($allCompanies->count() > 1) {
            foreach ($allCompanies->slice(1) as $c) {
                $c->delete();
            }
        }

        $lastCompany = Company::first();
        $failDeleteResponse = $this->delete('/superadmin/tenants/' . $lastCompany->id);
        $failDeleteResponse->assertSessionHas('error');
        $this->assertDatabaseHas('companies', ['id' => $lastCompany->id]);
    }

    public function test_can_view_and_update_default_account_mappings(): void
    {
        $admin = User::first();
        $this->actingAs($admin);

        $company = Company::first();
        $inventoryAcc = Account::where('company_id', $company->id)->where('code', '1-10200')->first();
        $salesAcc = Account::where('company_id', $company->id)->where('code', '4-40000')->first();
        $cogsAcc = Account::where('company_id', $company->id)->where('code', '5-50000')->first();

        // 1. Can view account mappings page
        $response = $this->get('/settings/account-mappings');
        $response->assertStatus(200);
        $response->assertSeeText('Akun Perkiraan');
        $response->assertSeeText('Default Account Mappings');
        $response->assertSeeText('Persediaan');

        // 2. Can update account mappings
        $updateResponse = $this->post('/settings/account-mappings', [
            'account_inventory_id' => $inventoryAcc?->id,
            'account_sales_id' => $salesAcc?->id,
            'account_cogs_id' => $cogsAcc?->id,
        ]);

        $updateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('company_settings', [
            'company_id' => $company->id,
            'account_inventory_id' => $inventoryAcc?->id,
            'account_sales_id' => $salesAcc?->id,
            'account_cogs_id' => $cogsAcc?->id,
        ]);
    }
}
