<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Transaction;
use Database\Seeders\AccountingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiCompoundTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccountingSeeder::class);
        $this->company = Company::first();
    }

    public function test_can_get_coa_accounts_via_api(): void
    {
        $response = $this->getJson('/api/v1/accounts?company_id=' . $this->company->id);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                '*' => ['id', 'company_id', 'code', 'name', 'category', 'type', 'is_active']
            ]
        ]);
    }

    public function test_can_get_and_create_master_data_via_api(): void
    {
        // 1. Create Department
        $deptResponse = $this->postJson('/api/v1/departments?company_id=' . $this->company->id, [
            'name' => 'Divisi Rekrutmen & Talent',
            'code' => 'REC',
            'description' => 'Departemen khusus penempatan dan jasa kandidat'
        ]);
        $deptResponse->assertStatus(201);
        $deptId = $deptResponse->json('data.id');

        // 2. Create Project
        $projResponse = $this->postJson('/api/v1/projects?company_id=' . $this->company->id, [
            'name' => 'Project Headhunting PT Maju Jaya',
            'code' => 'PRJ-MJ-2026',
            'department_id' => $deptId,
            'contract_amount' => 50000000,
            'status' => 'active'
        ]);
        $projResponse->assertStatus(201);
        $projId = $projResponse->json('data.id');

        // 3. Create Tag
        $tagResponse = $this->postJson('/api/v1/tags?company_id=' . $this->company->id, [
            'name' => 'Jasa Kandidat',
            'color' => '#10b981'
        ]);
        $tagResponse->assertStatus(201);
        $tagId = $tagResponse->json('data.id');

        // 4. Create Vendor (Kandidat / Agency Partner)
        $vendorResponse = $this->postJson('/api/v1/contacts?company_id=' . $this->company->id, [
            'name' => 'Budi Santoso (Kandidat Senior Engineer)',
            'type' => 'vendor',
            'phone' => '08123456789',
            'email' => 'budi.engineer@gmail.com'
        ]);
        $vendorResponse->assertStatus(201);
        $vendorId = $vendorResponse->json('data.id');

        // 5. Create Customer (Klien Perusahaan Pemesan)
        $clientResponse = $this->postJson('/api/v1/contacts?company_id=' . $this->company->id, [
            'name' => 'PT Maju Jaya Solusindo (Klien)',
            'type' => 'customer',
            'phone' => '021-99887766',
            'email' => 'hrd@majujaya.com'
        ]);
        $clientResponse->assertStatus(201);
        $clientId = $clientResponse->json('data.id');

        // 6. Test GET endpoints
        $this->getJson('/api/v1/departments?company_id=' . $this->company->id)->assertStatus(200);
        $this->getJson('/api/v1/projects?company_id=' . $this->company->id)->assertStatus(200);
        $this->getJson('/api/v1/tags?company_id=' . $this->company->id)->assertStatus(200);
        $this->getJson('/api/v1/vendors?company_id=' . $this->company->id)->assertStatus(200)->assertJsonFragment(['name' => 'Budi Santoso (Kandidat Senior Engineer)']);
        $this->getJson('/api/v1/customers?company_id=' . $this->company->id)->assertStatus(200)->assertJsonFragment(['name' => 'PT Maju Jaya Solusindo (Klien)']);

        // 7. Test Master Bundle
        $bundleResponse = $this->getJson('/api/v1/master-bundle?company_id=' . $this->company->id);
        $bundleResponse->assertStatus(200);
        $bundleResponse->assertJsonStructure([
            'status',
            'data' => [
                'accounts',
                'vendors',
                'customers',
                'departments',
                'projects',
                'tags',
                'payment_methods',
                'taxes',
            ]
        ]);
    }

    public function test_can_post_compound_transaction_for_candidate_service(): void
    {
        // Siapkan Akun
        $accReceivable = Account::firstOrCreate(
            ['company_id' => $this->company->id, 'code' => '1-10200'],
            ['name' => 'Piutang Usaha', 'category' => 'Akun Piutang', 'type' => 'Debit', 'is_active' => true]
        );

        $accRevenue = Account::firstOrCreate(
            ['company_id' => $this->company->id, 'code' => '4-40100'],
            ['name' => 'Pendapatan Jasa Penempatan Kandidat', 'category' => 'Pendapatan', 'type' => 'Credit', 'is_active' => true]
        );

        $accTaxPayable = Account::firstOrCreate(
            ['company_id' => $this->company->id, 'code' => '2-20500'],
            ['name' => 'PPN Keluaran', 'category' => 'Akun Hutang', 'type' => 'Credit', 'is_active' => true]
        );

        $client = Contact::create([
            'company_id' => $this->company->id,
            'name' => 'PT Tech Ventura (Klien)',
            'type' => 'customer'
        ]);

        $dept = Department::create([
            'company_id' => $this->company->id,
            'name' => 'Divisi Recruitment Headhunter',
            'code' => 'REC'
        ]);

        $proj = Project::create([
            'company_id' => $this->company->id,
            'department_id' => $dept->id,
            'name' => 'Placement Senior DevOps Specialist',
            'contract_amount' => 22200000,
            'status' => 'active'
        ]);

        $tag = Tag::create([
            'company_id' => $this->company->id,
            'name' => 'Jasa Kandidat IT',
            'color' => '#6366f1'
        ]);

        // POST Transaksi Majemuk:
        // Tagihan Jasa Kandidat Rp 20.000.000 + PPN 11% Rp 2.200.000 = Piutang Rp 22.200.000
        $payload = [
            'date' => '2026-09-23',
            'notes' => 'Invoice Fee Penempatan Kandidat Senior DevOps PT Tech Ventura',
            'contact_id' => $client->id,
            'department_id' => $dept->id,
            'project_id' => $proj->id,
            'tag_id' => $tag->id,
            'items' => [
                [
                    'account_code' => '1-10200', // Piutang Usaha
                    'debit' => 22200000,
                    'credit' => 0,
                    'memo' => 'Piutang tagihan PT Tech Ventura'
                ],
                [
                    'account_code' => '4-40100', // Pendapatan Jasa Kandidat
                    'debit' => 0,
                    'credit' => 20000000,
                    'memo' => 'Fee rekrutmen kandidat 1 org'
                ],
                [
                    'account_code' => '2-20500', // PPN Keluaran
                    'debit' => 0,
                    'credit' => 2200000,
                    'memo' => 'PPN 11%'
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/transactions?company_id=' . $this->company->id, $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Transaksi majemuk berhasil disimpan dan dijurnal otomatis.',
        ]);

        $trxId = $response->json('data.id');
        $this->assertDatabaseHas('transactions', [
            'id' => $trxId,
            'company_id' => $this->company->id,
            'contact_id' => $client->id,
            'department_id' => $dept->id,
            'project_id' => $proj->id,
            'tag_id' => $tag->id,
            'amount' => 22200000,
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'transaction_id' => $trxId,
            'company_id' => $this->company->id,
        ]);

        $this->assertEquals(3, \App\Models\JournalItem::where('journal_entry_id', $response->json('data.journal_entry.id'))->count());

        // Verifikasi endpoint GET transactions
        $getResponse = $this->getJson('/api/v1/transactions/' . $trxId . '?company_id=' . $this->company->id);
        $getResponse->assertStatus(200);
        $getResponse->assertJsonPath('data.department.name', 'Divisi Recruitment Headhunter');
        $getResponse->assertJsonPath('data.project.name', 'Placement Senior DevOps Specialist');
        $getResponse->assertJsonPath('data.contact.name', 'PT Tech Ventura (Klien)');
        $getResponse->assertJsonPath('data.tag.name', 'Jasa Kandidat IT');
    }

    public function test_can_post_transaction_with_auto_create_names_on_the_fly(): void
    {
        // Test zero-setup on-the-fly creation: hanya kirim nama teks tanpa ID sebelumnya
        $payload = [
            'date' => '2026-09-23',
            'notes' => 'Pembayaran Tahap 1 DP - Kandidat Baru On The Fly',
            'customer_name' => 'Ahmad Fauzi (Kandidat Baru)',
            'contact_phone' => '081987654321',
            'contact_email' => 'ahmad.fauzi@gmail.com',
            'department_name' => 'Daya Talenta Global (DTG)',
            'project_name' => 'Barista Kapal Pesiar',
            'tag_name' => 'Agensi DTG - IDMI Coffee',
            'items' => [
                [
                    'account_code' => '1-10002', // Bank BCA
                    'debit' => 5000000,
                    'credit' => 0,
                    'memo' => 'DP Tahap 1 Masuk Rekening BCA'
                ],
                [
                    'account_code' => '4-40100', // Pendapatan Jasa
                    'debit' => 0,
                    'credit' => 5000000,
                    'memo' => 'Pendapatan DP Jasa Penempatan'
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/transactions?company_id=' . $this->company->id, $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('data.contact.name', 'Ahmad Fauzi (Kandidat Baru)');
        $response->assertJsonPath('data.department.name', 'Daya Talenta Global (DTG)');
        $response->assertJsonPath('data.project.name', 'Barista Kapal Pesiar');
        $response->assertJsonPath('data.tag.name', 'Agensi DTG - IDMI Coffee');

        $this->assertDatabaseHas('contacts', ['name' => 'Ahmad Fauzi (Kandidat Baru)', 'type' => 'customer']);
        $this->assertDatabaseHas('departments', ['name' => 'Daya Talenta Global (DTG)']);
        $this->assertDatabaseHas('projects', ['name' => 'Barista Kapal Pesiar']);
        $this->assertDatabaseHas('tags', ['name' => 'Agensi DTG - IDMI Coffee']);
    }

    public function test_rejects_unbalanced_compound_transaction(): void
    {
        $accCash = Account::where('company_id', $this->company->id)->first();

        $payload = [
            'date' => '2026-09-23',
            'notes' => 'Jurnal Majemuk Tidak Balance',
            'items' => [
                [
                    'account_id' => $accCash->id,
                    'debit' => 500000,
                    'credit' => 0
                ],
                [
                    'account_id' => $accCash->id,
                    'debit' => 0,
                    'credit' => 450000 // Selisih 50.000
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/transactions?company_id=' . $this->company->id, $payload);
        $response->assertStatus(422);
        $response->assertJsonFragment(['status' => 'error']);
    }
}
