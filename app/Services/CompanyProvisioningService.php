<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\PaymentMethod;
use App\Models\SubscriptionInvoice;
use App\Models\Tax;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CompanyProvisioningService
{
    /**
     * Inisialisasi Perusahaan Baru beserta 120 COA, Pengaturan, Pajak DJP, dan Invoice Trial
     */
    public function provisionCompany(array $data, ?User $owner = null): Company
    {
        return DB::transaction(function () use ($data, $owner) {
            // Ambil data paket dari perusahaan utama pemilik jika sudah ada
            $parentCompany = null;
            if ($owner) {
                $parentCompany = Company::where('owner_id', $owner->id)
                    ->orWhere('id', $owner->default_company_id)
                    ->first();
            }

            $plan = $parentCompany ? $parentCompany->subscription_plan : ($data['plan_type'] ?? 'premium');
            $planType = $parentCompany ? $parentCompany->plan_type : ($plan === 'standard' ? 'free' : 'premium');
            $status = $parentCompany ? $parentCompany->subscription_status : 'trial';
            $expiresAt = $parentCompany ? $parentCompany->subscription_expires_at : Carbon::now()->addDays(14);
            $maxCompanies = $parentCompany ? ($parentCompany->max_companies ?? 3) : ($plan === 'premium' ? 3 : 1);

            // 1. Simpan Record Perusahaan
            $company = Company::create([
                'name' => $data['name'],
                'city' => $data['city'] ?? 'Bekasi',
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'plan_type' => $planType,
                'subscription_plan' => $plan,
                'subscription_status' => $status,
                'subscription_expires_at' => $expiresAt,
                'max_companies' => $maxCompanies,
                'conversion_date' => $data['conversion_date'] ?? Carbon::now()->startOfMonth()->toDateString(),
                'is_initial_balance_locked' => false,
                'owner_id' => $owner?->id,
            ]);

            // 2. Buat Pengaturan Default (CompanySetting)
            CompanySetting::create([
                'company_id' => $company->id,
                'preview_transaksi' => true,
                'number_format' => '1,000,000.00',
                'decimal_places' => 0,
                'cache_reports' => false,
                'cache_ar_ap' => false,
                'gemini_api_key' => config('services.gemini.api_key') ?: 'AQ.Ab8RN6LoDJ7glHsP2wlfOaR38B9JaMfatPa3C7CCJLkBOYdksQ',
            ]);

            // 3. Hubungkan User sebagai Admin / Owner
            if ($owner) {
                DB::table('company_user')->updateOrInsert(
                    ['company_id' => $company->id, 'user_id' => $owner->id],
                    ['role' => 'admin', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
                );
            }

            // 4. Inisialisasi Standar 120 Bagan Akun (Chart of Accounts / COA)
            $this->seedStandardCoa($company->id);

            // 5. Inisialisasi Metode Pembayaran Standar
            $kasAccount = Account::where('company_id', $company->id)->where('code', '1-10001')->first();
            $bankAccount = Account::where('company_id', $company->id)->where('code', '1-10007')->first() ?? $kasAccount;

            PaymentMethod::create([
                'company_id' => $company->id,
                'name' => 'Kas Tunai',
                'account_id' => $kasAccount?->id,
            ]);

            PaymentMethod::create([
                'company_id' => $company->id,
                'name' => 'Transfer Bank BCA',
                'account_id' => $bankAccount?->id,
            ]);

            PaymentMethod::create([
                'company_id' => $company->id,
                'name' => 'QRIS Instant',
                'account_id' => $kasAccount?->id,
            ]);

            // 6. Inisialisasi Pajak Standar (PPN & PPh)
            $ppnMasukan = Account::where('company_id', $company->id)->where('code', '1-10500')->first();
            $ppnKeluaran = Account::where('company_id', $company->id)->where('code', '2-20500')->first()
                ?? Account::where('company_id', $company->id)->where('category', 'Akun Hutang')->first();

            Tax::create([
                'company_id' => $company->id,
                'name' => 'PPN 11%',
                'rate' => 11.00,
                'sales_account_id' => $ppnKeluaran?->id,
                'purchase_account_id' => $ppnMasukan?->id,
                'is_withholding' => false,
            ]);

            Tax::create([
                'company_id' => $company->id,
                'name' => 'PPh 23 (Jasa) 2%',
                'rate' => 2.00,
                'sales_account_id' => $ppnKeluaran?->id,
                'purchase_account_id' => $ppnMasukan?->id,
                'is_withholding' => true,
            ]);

            // 7. Catat Invoice Pertama (Trial Aktivasi Rp 0)
            SubscriptionInvoice::create([
                'company_id' => $company->id,
                'invoice_number' => 'INV' . Carbon::now()->format('YmdHis') . rand(10, 99),
                'plan_name' => $company->subscription_plan ?: 'premium',
                'duration_months' => 1,
                'amount' => 0,
                'status' => 'paid',
                'description' => 'Trial fitur premium 14 hari',
                'payment_method' => 'Trial Onboarding',
                'start_date' => Carbon::now(),
                'end_date' => $company->subscription_expires_at,
                'paid_at' => Carbon::now(),
                'created_by' => $owner?->id,
            ]);

            return $company;
        });
    }

    /**
     * 120 Bagan Akun Standar SAK EMKM Indonesia
     */
    public function seedStandardCoa(int $companyId): void
    {
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
            ['2-20202', 'Hutang Dividen', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20203', 'Hutang Bunga', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20204', 'Hutang Komisi', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20205', 'Hutang Biaya', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20206', 'Pendapatan Diterima Dimuka', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20207', 'Uang Muka Penjualan', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20208', 'Deposit Pelanggan', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20209', 'Klaim Biaya Karyawan (Reimbursement)', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20300', 'Hutang Jangka Pendek', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20500', 'PPN Keluaran', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20501', 'Hutang Pajak Penghasilan - PPh 21', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20502', 'Hutang Pajak Penghasilan - PPh 23', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20503', 'Hutang Pajak Penghasilan - PPh 25', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20504', 'Hutang Pajak Penghasilan - PPh 29', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20505', 'Hutang Pajak Penghasilan - PPh Final', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20506', 'Hutang PPh 4 ayat 2', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20600', 'Hutang Pemegang Saham', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20700', 'Hutang Jangka Panjang', 'Kewajiban Jangka Panjang', 'Credit'],
            ['2-20701', 'Hutang Bank', 'Kewajiban Jangka Panjang', 'Credit'],
            ['2-20702', 'Hutang Hipotik', 'Kewajiban Jangka Panjang', 'Credit'],
            ['2-20703', 'Hutang Sewa Pembiayaan', 'Kewajiban Jangka Panjang', 'Credit'],
            ['3-30000', 'Modal Saham', 'Modal', 'Credit'],
            ['3-30001', 'Modal Disetor', 'Modal', 'Credit'],
            ['3-30002', 'Tambahan Modal Disetor', 'Modal', 'Credit'],
            ['3-30100', 'Laba Ditahan', 'Modal', 'Credit'],
            ['3-30200', 'Laba Periode Berjalan', 'Modal', 'Credit'],
            ['3-30300', 'Dividen', 'Modal', 'Debit'],
            ['3-30999', 'Saldo Awal', 'Modal', 'Credit'],
            ['4-40000', 'Pendapatan Penjualan', 'Pendapatan', 'Credit'],
            ['4-40001', 'Pendapatan Jasa', 'Pendapatan', 'Credit'],
            ['4-40002', 'Pendapatan Catering / Masakan', 'Pendapatan', 'Credit'],
            ['4-40100', 'Diskon Penjualan', 'Pendapatan', 'Debit'],
            ['4-40200', 'Retur Penjualan', 'Pendapatan', 'Debit'],
            ['4-40300', 'Pendapatan Pengiriman', 'Pendapatan', 'Credit'],
            ['5-50000', 'Harga Pokok Penjualan (HPP)', 'HPP', 'Debit'],
            ['5-50001', 'Biaya Bahan Baku Makanan', 'HPP', 'Debit'],
            ['5-50002', 'Biaya Tenaga Kerja Langsung', 'HPP', 'Debit'],
            ['5-50003', 'Biaya Overhead Produksi', 'HPP', 'Debit'],
            ['5-50100', 'Diskon Pembelian', 'HPP', 'Credit'],
            ['5-50200', 'Retur Pembelian', 'HPP', 'Credit'],
            ['5-50300', 'Biaya Pengiriman Pembelian', 'HPP', 'Debit'],
            ['5-50400', 'Biaya Import', 'HPP', 'Debit'],
            ['5-50500', 'Biaya Pengemasan & Packaging', 'HPP', 'Debit'],
            ['6-60000', 'Beban Gaji Karyawan', 'Beban', 'Debit'],
            ['6-60001', 'Beban Tunjangan & Bonus', 'Beban', 'Debit'],
            ['6-60002', 'Beban Lembur', 'Beban', 'Debit'],
            ['6-60100', 'Beban Sewa Tempat & Gedung', 'Beban', 'Debit'],
            ['6-60101', 'Beban BPJS Ketenagakerjaan', 'Beban', 'Debit'],
            ['6-60102', 'Beban BPJS Kesehatan', 'Beban', 'Debit'],
            ['6-60103', 'Beban THR & Bonus', 'Beban', 'Debit'],
            ['6-60200', 'Beban Listrik, Air & Gas', 'Beban', 'Debit'],
            ['6-60201', 'Beban Internet & Telepon', 'Beban', 'Debit'],
            ['6-60202', 'Beban Jamuan & Entertainment', 'Beban', 'Debit'],
            ['6-60203', 'Beban Perjalanan Dinas', 'Beban', 'Debit'],
            ['6-60204', 'Beban Asuransi Operasional', 'Beban', 'Debit'],
            ['6-60205', 'Beban Kebersihan & Keamanan', 'Beban', 'Debit'],
            ['6-60206', 'Beban Legal & Perizinan', 'Beban', 'Debit'],
            ['6-60207', 'Beban Konsultan & Profesional', 'Beban', 'Debit'],
            ['6-60300', 'Beban Pemasaran & Iklan', 'Beban', 'Debit'],
            ['6-60301', 'Beban Promosi & Diskon Promo', 'Beban', 'Debit'],
            ['6-60400', 'Beban Perlengkapan & ATK', 'Beban', 'Debit'],
            ['6-60401', 'Beban Piutang Tak Tertagih', 'Beban', 'Debit'],
            ['6-60500', 'Beban Perbaikan & Pemeliharaan', 'Beban', 'Debit'],
            ['6-60600', 'Beban Transportasi & Bensin', 'Beban', 'Debit'],
            ['6-60700', 'Beban Penyusutan Aset', 'Beban', 'Debit'],
            ['6-60800', 'Beban Konsumsi & Dapur Staf', 'Beban', 'Debit'],
            ['6-60900', 'Beban Operasional Lainnya', 'Beban', 'Debit'],
            ['7-70000', 'Pendapatan Bunga Bank', 'Pendapatan Lainnya', 'Credit'],
            ['7-70100', 'Keuntungan Penjualan Aset', 'Pendapatan Lainnya', 'Credit'],
            ['7-70200', 'Pendapatan Lain-lain', 'Pendapatan Lainnya', 'Credit'],
            ['8-80000', 'Beban Administrasi Bank', 'Beban Lainnya', 'Debit'],
            ['8-80100', 'Beban Bunga Pinjaman', 'Beban Lainnya', 'Debit'],
            ['8-80200', 'Beban Pajak Penghasilan Perusahaan', 'Beban Lainnya', 'Debit'],
            ['8-80300', 'Beban Denda & Penalti', 'Beban Lainnya', 'Debit'],
            ['8-80900', 'Beban Non-Operasional Lainnya', 'Beban Lainnya', 'Debit'],
        ];

        $insertData = [];
        $now = Carbon::now();
        foreach ($coaList as $coa) {
            $insertData[] = [
                'company_id' => $companyId,
                'code' => $coa[0],
                'name' => $coa[1],
                'category' => $coa[2],
                'type' => $coa[3],
                'initial_debit' => 0,
                'initial_credit' => 0,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Account::insert($insertData);
    }
}
