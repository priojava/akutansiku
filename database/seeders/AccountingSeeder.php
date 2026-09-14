<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AccountingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat User Administrator
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Priojaval (Admin)',
            'email' => 'admin@dapurgemoy.com',
            'phone' => '+6282220073300',
            'avatar' => null,
            'address' => 'Bekasi, Jawa Barat',
            'password' => Hash::make('password123'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Buat User Akuntan
        $akuntanId = DB::table('users')->insertGetId([
            'name' => 'Siti Fatimah (Akuntan)',
            'email' => 'akuntan@dapurgemoy.com',
            'phone' => '+6281234567891',
            'avatar' => null,
            'address' => 'Jakarta Timur',
            'password' => Hash::make('password123'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Buat User Kasir
        $kasirId = DB::table('users')->insertGetId([
            'name' => 'Budi Santoso (Kasir)',
            'email' => 'kasir@dapurgemoy.com',
            'phone' => '+6281234567892',
            'avatar' => null,
            'address' => 'Bekasi Selatan',
            'password' => Hash::make('password123'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // 2. Buat Perusahaan Awal (Dapur Gemoy)
        $companyId = DB::table('companies')->insertGetId([
            'name' => 'Dapur Gemoy',
            'city' => 'Bekasi Kota',
            'address' => 'Jl. Boulevard Raya No. 88, Bekasi',
            'phone' => '082220073300',
            'email' => 'admin@dapurgemoy.com',
            'plan_type' => 'premium',
            'conversion_date' => Carbon::parse('2026-09-01'),
            'is_initial_balance_locked' => false,
            'owner_id' => $adminId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Update default company pada user
        DB::table('users')->where('id', $adminId)->update(['default_company_id' => $companyId]);
        DB::table('users')->where('id', $akuntanId)->update(['default_company_id' => $companyId]);
        DB::table('users')->where('id', $kasirId)->update(['default_company_id' => $companyId]);

        // Hubungkan user ke company dengan role masing-masing
        DB::table('company_user')->insert([
            ['company_id' => $companyId, 'user_id' => $adminId, 'role' => 'admin', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['company_id' => $companyId, 'user_id' => $akuntanId, 'role' => 'accountant', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['company_id' => $companyId, 'user_id' => $kasirId, 'role' => 'cashier', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // 3. Pengaturan Perusahaan Default
        DB::table('company_settings')->insert([
            'company_id' => $companyId,
            'preview_transaksi' => true,
            'number_format' => '1,000,000.00',
            'decimal_places' => 0,
            'cache_reports' => false,
            'cache_ar_ap' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // 4. Data 120+ COA dari Google Spreadsheet
        $coaList = [
            ['1-10001', 'Kas', 'Kas & Bank', 'Debit'],
            ['1-10002', 'Rekening Bank', 'Kas & Bank', 'Debit'],
            ['1-10003', 'Bank Mandiri', 'Kas & Bank', 'Debit'],
            ['1-10004', 'Bank Negara Indonesia (BNI)', 'Kas & Bank', 'Debit'],
            ['1-10005', 'Bank Rakyat Indonesia (BRI)', 'Kas & Bank', 'Debit'],
            ['1-10006', 'Bank Tabungan Negara (BTN)', 'Kas & Bank', 'Debit'],
            ['1-10007', 'Bank Central Asia (BCA)', 'Kas & Bank', 'Debit'],
            ['1-10008', 'GoPay', 'Kas & Bank', 'Debit'],
            ['1-10009', 'OVO', 'Kas & Bank', 'Debit'],
            ['1-10010', 'Dana', 'Kas & Bank', 'Debit'],
            ['1-10011', 'Link Aja', 'Kas & Bank', 'Debit'],
            ['1-10012', 'Cashlez', 'Kas & Bank', 'Debit'],
            ['1-10100', 'Piutang Usaha', 'Akun Piutang', 'Debit'],
            ['1-10101', 'Piutang Belum Ditagih', 'Akun Piutang', 'Debit'],
            ['1-10200', 'Persediaan Barang', 'Persediaan', 'Debit'],
            ['1-10300', 'Piutang Lainnya', 'Harta Lancar Lainnya', 'Debit'],
            ['1-10301', 'Piutang Karyawan', 'Harta Lancar Lainnya', 'Debit'],
            ['1-10400', 'Dana Belum Disetor', 'Harta Lancar Lainnya', 'Debit'],
            ['1-10401', 'Aset Lancar Lainnya', 'Harta Lancar Lainnya', 'Debit'],
            ['1-10402', 'Biaya Dibayar Di Muka', 'Harta Lancar Lainnya', 'Debit'],
            ['1-10403', 'Uang Muka', 'Harta Lancar Lainnya', 'Debit'],
            ['1-10500', 'PPN Masukan', 'Harta Lancar Lainnya', 'Debit'],
            ['1-10501', 'Pajak Penghasilan Dibayar Di Muka - PPh 22', 'Harta Lancar Lainnya', 'Debit'],
            ['1-10502', 'Pajak Penghasilan Dibayar Di Muka - PPh 23', 'Harta Lancar Lainnya', 'Debit'],
            ['1-10503', 'Pajak Penghasilan Dibayar Di Muka - PPh 25', 'Harta Lancar Lainnya', 'Debit'],
            ['1-10700', 'Aktiva Tetap - Tanah', 'Harta Tetap', 'Debit'],
            ['1-10701', 'Aset Tetap - Bangunan', 'Harta Tetap', 'Debit'],
            ['1-10702', 'Aset Tetap - Pengembangan Bangunan', 'Harta Tetap', 'Debit'],
            ['1-10703', 'Aset Tetap - Kendaraan', 'Harta Tetap', 'Debit'],
            ['1-10704', 'Aset Tetap - Mesin & Peralatan', 'Harta Tetap', 'Debit'],
            ['1-10705', 'Aset Tetap - Peralatan Kantor', 'Harta Tetap', 'Debit'],
            ['1-10706', 'Aset Tetap - Aset Sewaan', 'Harta Tetap', 'Debit'],
            ['1-10707', 'Aset Tidak Berwujud', 'Harta Tetap', 'Debit'],
            ['1-10751', 'Akumulasi Penyusutan - Bangunan', 'Depresiasi & Amortisasi', 'Credit'],
            ['1-10752', 'Akumulasi Penyusutan - Pengembangan Bangunan', 'Depresiasi & Amortisasi', 'Credit'],
            ['1-10753', 'Akumulasi Penyusutan - Kendaraan', 'Depresiasi & Amortisasi', 'Credit'],
            ['1-10754', 'Akumulasi Penyusutan - Mesin & Peralatan', 'Depresiasi & Amortisasi', 'Credit'],
            ['1-10755', 'Akumulasi Penyusutan - Peralatan Kantor', 'Depresiasi & Amortisasi', 'Credit'],
            ['1-10756', 'Akumulasi Penyusutan - Aset Sewaan', 'Depresiasi & Amortisasi', 'Credit'],
            ['1-10757', 'Akumulasi Armotisasi', 'Depresiasi & Amortisasi', 'Credit'],
            ['1-10800', 'Investasi', 'Harta Lainnya', 'Debit'],
            ['2-20100', 'Hutang Usaha', 'Akun Hutang', 'Credit'],
            ['2-20101', 'Hutang Belum Ditagih', 'Akun Hutang', 'Credit'],
            ['2-20200', 'Hutang Lainnya', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20201', 'Hutang Gaji', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20202', 'Hutang Deviden', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['3-30200', 'Deviden', 'Modal', 'Credit'],
            ['3-30300', 'Pendapatan Komprehensif Lainnya', 'Modal', 'Credit'],
            ['3-30999', 'Saldo Awal', 'Modal', 'Credit'],
            ['4-40000', 'Pendapatan', 'Pendapatan', 'Credit'],
            ['4-40100', 'Diskon Penjualan', 'Pendapatan', 'Credit'],
            ['4-40200', 'Pengembalian Penjualan', 'Pendapatan', 'Credit'],
            ['5-50000', 'Beban Pokok Pendapatan', 'Harga Pokok Penjualan', 'Debit'],
            ['5-50100', 'Diskon Pembelian', 'Harga Pokok Penjualan', 'Debit'],
            ['5-50200', 'Pengembalian Pembelian', 'Harga Pokok Penjualan', 'Debit'],
            ['5-50300', 'Pengiriman / Pengangkutan', 'Harga Pokok Penjualan', 'Debit'],
            ['5-50400', 'Biaya Import', 'Harga Pokok Penjualan', 'Debit'],
            ['5-50500', 'Biaya Produksi', 'Harga Pokok Penjualan', 'Debit'],
            ['6-60000', 'Biaya Penjualan', 'Beban', 'Debit'],
            ['6-60001', 'Iklan & Promosi', 'Beban', 'Debit'],
            ['6-60002', 'Komisi & Fee', 'Beban', 'Debit'],
            ['6-60003', 'Bensin - Toll - dan Parkir - Penjualan', 'Beban', 'Debit'],
            ['6-60004', 'Perjalanan (Travelling) - Penjualan', 'Beban', 'Debit'],
            ['6-60005', 'Komunikasi - Penjualan', 'Beban', 'Debit'],
            ['6-60006', 'Pemasaran Lainnya', 'Beban', 'Debit'],
            ['6-60100', 'Biaya Umum & Administratif', 'Beban', 'Debit'],
            ['6-60101', 'Gaji', 'Beban', 'Debit'],
            ['6-60102', 'Upah', 'Beban', 'Debit'],
            ['6-60103', 'Konsumsi & Transport', 'Beban', 'Debit'],
            ['6-60104', 'Lembur', 'Beban', 'Debit'],
            ['6-60105', 'Kesehatan', 'Beban', 'Debit'],
            ['6-60106', 'THR dan Bonus', 'Beban', 'Debit'],
            ['6-60107', 'Jamsostek', 'Beban', 'Debit'],
            ['6-60108', 'Insentif', 'Beban', 'Debit'],
            ['6-60109', 'Pesangon', 'Beban', 'Debit'],
            ['6-60110', 'Tunjangan Lainnya', 'Beban', 'Debit'],
            ['6-60200', 'Donasi', 'Beban', 'Debit'],
            ['6-60201', 'Hiburan', 'Beban', 'Debit'],
            ['6-60202', 'Bensin - Toll - dan Parkir - Umum', 'Beban', 'Debit'],
            ['6-60203', 'Perbaikan dan Perawatan', 'Beban', 'Debit'],
            ['6-60204', 'Perjalanan (Travelling) - Umum', 'Beban', 'Debit'],
            ['6-60205', 'Konsumsi', 'Beban', 'Debit'],
            ['6-60206', 'Komunikasi - Umum', 'Beban', 'Debit'],
            ['6-60207', 'Iuran & Berlangganan', 'Beban', 'Debit'],
            ['6-60208', 'Asuransi', 'Beban', 'Debit'],
            ['6-60209', 'Biaya Hukum & Professional', 'Beban', 'Debit'],
            ['6-60210', 'Beban Tunjangan Karyawan', 'Beban', 'Debit'],
            ['6-60211', 'Sarana Kantor', 'Beban', 'Debit'],
            ['6-60212', 'Pelatihan & Pengembangan', 'Beban', 'Debit'],
            ['6-60213', 'Beban Hutang Buruk', 'Beban', 'Debit'],
            ['6-60214', 'Pajak & Lisensi', 'Beban', 'Debit'],
            ['6-60215', 'Denda', 'Beban', 'Debit'],
            ['6-60216', 'Pengeluaran Barang Rusak', 'Beban', 'Debit'],
            ['6-60300', 'Beban Kantor', 'Beban', 'Debit'],
            ['6-60301', 'ATK & Print', 'Beban', 'Debit'],
            ['6-60302', 'Materai', 'Beban', 'Debit'],
            ['6-60303', 'Keamanan & Kebersihan', 'Beban', 'Debit'],
            ['6-60304', 'Persediaan Material', 'Beban', 'Debit'],
            ['6-60305', 'Sub Kontraktor', 'Beban', 'Debit'],
            ['6-60400', 'Beban Sewa - Bangunan', 'Beban', 'Debit'],
            ['6-60401', 'Beban Sewa - Kendaraan', 'Beban', 'Debit'],
            ['6-60402', 'Beban Sewa - Sewa Operasional', 'Beban', 'Debit'],
            ['6-60403', 'Beban Sewa - Lainnya', 'Beban', 'Debit'],
            ['6-60500', 'Depresiasi - Bangunan', 'Beban', 'Debit'],
            ['6-60501', 'Depresiasi - Pengembangan Bangunan', 'Beban', 'Debit'],
            ['6-60502', 'Depresiasi - Kendaraan', 'Beban', 'Debit'],
            ['6-60503', 'Depresiasi - Mesin & Peralatan', 'Beban', 'Debit'],
            ['6-60504', 'Depresiasi - Peralatan Kantor', 'Beban', 'Debit'],
            ['6-60599', 'Depresiasi - Aset Sewaan', 'Beban', 'Debit'],
            ['7-70000', 'Pendapatan Bunga - Bank', 'Pendapatan Lainnya', 'Credit'],
            ['7-70001', 'Pendapatan Bunga - Waktu Deposit', 'Pendapatan Lainnya', 'Credit'],
            ['7-70099', 'Pendapatan Lainnya', 'Pendapatan Lainnya', 'Credit'],
            ['8-80000', 'Beban Bunga', 'Beban Lainnya', 'Debit'],
            ['8-80001', 'Persediaan', 'Beban Lainnya', 'Debit'],
            ['8-80002', '(Keuntungan) / Kerugian Pembuangan Aset Tetap', 'Beban Lainnya', 'Debit'],
            ['8-80100', 'Penyesuaian Persediaan', 'Beban Lainnya', 'Debit'],
            ['8-80999', 'Biaya Lainnya', 'Beban Lainnya', 'Debit'],
            ['9-90000', 'Pajak Penghasilan - Saat Ini', 'Beban Lainnya', 'Debit'],
            ['9-90001', 'Pajak Penghasilan - Ditangguhkan', 'Beban Lainnya', 'Debit'],
        ];

        $accountMap = [];
        foreach ($coaList as $coa) {
            $accId = DB::table('accounts')->insertGetId([
                'company_id' => $companyId,
                'code' => $coa[0],
                'name' => $coa[1],
                'category' => $coa[2],
                'type' => $coa[3],
                'initial_debit' => 0,
                'initial_credit' => 0,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $accountMap[$coa[0]] = $accId;
        }

        // 5. Cara Pembayaran Default
        DB::table('payment_methods')->insert([
            [
                'company_id' => $companyId,
                'name' => 'Kas Tunai',
                'account_id' => $accountMap['1-10001'] ?? null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'company_id' => $companyId,
                'name' => 'Transfer Bank BCA',
                'account_id' => $accountMap['1-10007'] ?? null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'company_id' => $companyId,
                'name' => 'GoPay',
                'account_id' => $accountMap['1-10008'] ?? null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // 6. Kontak Contoh
        $contactId = DB::table('contacts')->insertGetId([
            'company_id' => $companyId,
            'name' => 'Pelanggan Umum',
            'type' => 'customer',
            'phone' => '081234567890',
            'email' => 'customer@example.com',
            'address' => 'Jakarta',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // 7. Transaksi Contoh (Sesuai Screenshot: Bayar Listrik / Pengeluaran Rp 1.000.000)
        $kasAccId = $accountMap['1-10001'];
        $hppAccId = $accountMap['5-50000']; // Beban Pokok Pendapatan

        $trxId = DB::table('transactions')->insertGetId([
            'company_id' => $companyId,
            'transaction_number' => 'TRX/202609/0001',
            'date' => '2026-09-08',
            'time' => '16:21:44',
            'type' => 'expense',
            'contact_id' => $contactId,
            'debit_account_id' => $hppAccId, // Simpan ke Beban
            'credit_account_id' => $kasAccId, // Diterima dari Kas
            'amount' => 1000000,
            'notes' => 'BAYAR LISTRIK',
            'created_by' => $adminId,
            'created_at' => Carbon::parse('2026-09-08 16:21:44'),
            'updated_at' => Carbon::parse('2026-09-08 16:21:44'),
        ]);

        // Buat Jurnal Umum Entry
        $journalEntryId = DB::table('journal_entries')->insertGetId([
            'company_id' => $companyId,
            'transaction_id' => $trxId,
            'entry_number' => 'JRN/202609/0001',
            'date' => '2026-09-08',
            'time' => '16:21:44',
            'reference_number' => 'TRX/202609/0001',
            'description' => 'Pengeluaran - BAYAR LISTRIK',
            'created_by' => $adminId,
            'created_at' => Carbon::parse('2026-09-08 16:21:44'),
            'updated_at' => Carbon::parse('2026-09-08 16:21:44'),
        ]);

        // Jurnal Items: Debit Beban Rp 1.000.000, Kredit Kas Rp 1.000.000
        DB::table('journal_items')->insert([
            [
                'journal_entry_id' => $journalEntryId,
                'account_id' => $hppAccId,
                'debit' => 1000000,
                'credit' => 0,
                'memo' => 'BAYAR LISTRIK',
                'created_at' => Carbon::parse('2026-09-08 16:21:44'),
                'updated_at' => Carbon::parse('2026-09-08 16:21:44'),
            ],
            [
                'journal_entry_id' => $journalEntryId,
                'account_id' => $kasAccId,
                'debit' => 0,
                'credit' => 1000000,
                'memo' => 'BAYAR LISTRIK',
                'created_at' => Carbon::parse('2026-09-08 16:21:44'),
                'updated_at' => Carbon::parse('2026-09-08 16:21:44'),
            ],
        ]);
    }
}
