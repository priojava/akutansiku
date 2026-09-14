<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\PaymentMethod;
use App\Models\Tax;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function __construct(
        protected \App\Services\CompanyProvisioningService $provisioningService
    ) {}

    /**
     * Tampilkan daftar seluruh entitas perusahaan milik pengguna
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user && $user->isSuperAdmin()) {
            $companies = Company::withCount(['accounts', 'transactions'])->get();
        } elseif ($user) {
            $companies = $user->companies()->withCount(['accounts', 'transactions'])->get();
            if ($companies->isEmpty()) {
                $companies = Company::where('owner_id', $user->id)
                    ->orWhere('id', $user->default_company_id)
                    ->withCount(['accounts', 'transactions'])
                    ->get();
            }
        } else {
            $companies = Company::withCount(['accounts', 'transactions'])->get();
        }

        $activeCompanyId = session('active_company_id') ?? $user?->default_company_id ?? $companies->first()?->id ?? 1;
        $company = $companies->firstWhere('id', $activeCompanyId) ?? $companies->first() ?? Company::first();

        return view('company.index', compact('companies', 'company', 'activeCompanyId'));
    }

    /**
     * Tambah Perusahaan Baru dan Inisialisasi Otomatis Standar Akuntansi (120 COA, Settings, Taxes)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'plan_type' => 'nullable|in:free,premium',
            'conversion_date' => 'nullable|date',
        ]);

        $currentUser = auth()->user() ?? User::first();

        $newCompany = $this->provisioningService->provisionCompany($validated, $currentUser);

        // Set Perusahaan Baru sebagai Perusahaan Aktif di Session
        session(['active_company_id' => $newCompany->id]);
        if ($currentUser) {
            $currentUser->default_company_id = $newCompany->id;
            $currentUser->save();
        }

        return redirect()->route('company.switch')->with('success', "Perusahaan '{$newCompany->name}' berhasil dibuat dan 120 Bagan Akun (COA) otomatis diinisialisasi!");
    }

    /**
     * Beralih (Switch) ke Perusahaan Lain
     */
    public function switch(int $id)
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin()) {
            $hasAccess = $user->companies()->where('companies.id', $id)->exists()
                || Company::where('id', $id)->where('owner_id', $user->id)->exists();
            if (!$hasAccess) {
                return redirect()->route('company.switch')->with('error', 'Akses ditolak: Anda tidak memiliki akses ke entitas perusahaan ini.');
            }
        }

        $targetCompany = Company::findOrFail($id);
        
        session(['active_company_id' => $targetCompany->id]);

        if ($user) {
            $user->default_company_id = $targetCompany->id;
            $user->save();
        }

        return redirect()->route('dashboard')->with('success', "Berhasil beralih ke pembukuan: {$targetCompany->name}");
    }

    /**
     * Update Informasi Profil Perusahaan
     */
    public function update(Request $request, int $id)
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin()) {
            $hasAccess = $user->companies()->where('companies.id', $id)->exists()
                || Company::where('id', $id)->where('owner_id', $user->id)->exists();
            if (!$hasAccess) {
                return back()->with('error', 'Akses ditolak: Anda tidak berwenang mengubah informasi perusahaan ini.');
            }
        }

        $company = Company::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'plan_type' => 'nullable|in:free,premium',
        ]);

        $company->update($validated);

        return redirect()->route('company.switch')->with('success', "Informasi perusahaan '{$company->name}' berhasil diperbarui.");
    }

    /**
     * Hapus Entitas Perusahaan
     */
    public function destroy(int $id)
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin()) {
            $hasAccess = $user->companies()->where('companies.id', $id)->exists()
                || Company::where('id', $id)->where('owner_id', $user->id)->exists();
            if (!$hasAccess) {
                return back()->with('error', 'Akses ditolak: Anda tidak berwenang menghapus perusahaan ini.');
            }
        }

        $company = Company::findOrFail($id);

        if (Company::count() <= 1) {
            return back()->with('error', 'Gagal: Tidak dapat menghapus satu-satunya perusahaan yang aktif.');
        }

        $companyName = $company->name;

        \Illuminate\Support\Facades\DB::transaction(function () use ($company) {
            \App\Models\Transaction::where('company_id', $company->id)->delete();
            \App\Models\JournalEntry::where('company_id', $company->id)->delete();
            \App\Models\Asset::where('company_id', $company->id)->delete();
            \App\Models\ClosingPeriod::where('company_id', $company->id)->delete();
            \App\Models\Account::where('company_id', $company->id)->delete();
            \App\Models\Contact::where('company_id', $company->id)->delete();
            \App\Models\PaymentMethod::where('company_id', $company->id)->delete();
            \App\Models\Tax::where('company_id', $company->id)->delete();
            \App\Models\CompanySetting::where('company_id', $company->id)->delete();
            \App\Models\SubscriptionInvoice::where('company_id', $company->id)->delete();
            \Illuminate\Support\Facades\DB::table('company_user')->where('company_id', $company->id)->delete();

            $company->delete();
        });

        if (session('active_company_id') == $id) {
            $fallback = $user ? $user->companies()->first() : Company::first();
            session(['active_company_id' => $fallback?->id]);
        }

        return redirect()->route('company.switch')->with('success', "Perusahaan '{$companyName}' berhasil dihapus.");
    }

    /**
     * Inisialisasi Standar 120 Bagan Akun (COA)
     */
    private function seedStandardCoa(int $companyId): void
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
            ['2-20202', 'Hutang Deviden', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20203', 'Klaim Biaya Karyawan (Reimbursement)', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20300', 'Pendapatan Diterima Di Muka', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20400', 'Uang Muka Pelanggan', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20500', 'PPN Keluaran', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20501', 'Hutang PPh 21', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20502', 'Hutang PPh 22', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20503', 'Hutang PPh 23', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20504', 'Hutang PPh 26', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20505', 'Hutang PPh 29', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20506', 'Hutang PPh 4 ayat 2', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20600', 'Pinjaman Bank Jangka Pendek', 'Kewajiban Lancar Lainnya', 'Credit'],
            ['2-20700', 'Pinjaman Bank Jangka Panjang', 'Kewajiban Jangka Panjang', 'Credit'],
            ['2-20800', 'Kewajiban Tidak Lancar Lainnya', 'Kewajiban Jangka Panjang', 'Credit'],
            ['3-30000', 'Modal Saham', 'Modal', 'Credit'],
            ['3-30001', 'Modal Pemilik', 'Modal', 'Credit'],
            ['3-30100', 'Laba Ditahan', 'Modal', 'Credit'],
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
            ['6-60002', 'Komisi & Insentif', 'Beban', 'Debit'],
            ['6-60100', 'Beban Gaji & Upah', 'Beban', 'Debit'],
            ['6-60101', 'Beban Tunjangan Karyawan', 'Beban', 'Debit'],
            ['6-60102', 'Beban BPJS Ketenagakerjaan', 'Beban', 'Debit'],
            ['6-60103', 'Beban BPJS Kesehatan', 'Beban', 'Debit'],
            ['6-60104', 'Beban Bonus & THR', 'Beban', 'Debit'],
            ['6-60200', 'Beban Sewa Gedung / Kantor', 'Beban', 'Debit'],
            ['6-60201', 'Beban Listrik, Air & Gas', 'Beban', 'Debit'],
            ['6-60202', 'Beban Telepon & Internet', 'Beban', 'Debit'],
            ['6-60203', 'Beban Perlengkapan & ATK', 'Beban', 'Debit'],
            ['6-60204', 'Beban Perjalanan Dinas', 'Beban', 'Debit'],
            ['6-60205', 'Beban Jamuan & Entertainment', 'Beban', 'Debit'],
            ['6-60206', 'Beban Pemeliharaan & Perbaikan', 'Beban', 'Debit'],
            ['6-60207', 'Beban Asuransi Operasional', 'Beban', 'Debit'],
            ['6-60208', 'Beban Kebersihan & Keamanan', 'Beban', 'Debit'],
            ['6-60209', 'Beban Legal & Perizinan', 'Beban', 'Debit'],
            ['6-60210', 'Beban Konsultan & Profesional', 'Beban', 'Debit'],
            ['6-60300', 'Beban Penyusutan Bangunan', 'Beban', 'Debit'],
            ['6-60301', 'Beban Penyusutan Kendaraan', 'Beban', 'Debit'],
            ['6-60302', 'Beban Penyusutan Mesin & Peralatan', 'Beban', 'Debit'],
            ['6-60303', 'Beban Penyusutan Peralatan Kantor', 'Beban', 'Debit'],
            ['6-60400', 'Beban Piutang Tak Tertagih', 'Beban', 'Debit'],
            ['6-60500', 'Beban Operasional Lainnya', 'Beban', 'Debit'],
            ['7-70000', 'Pendapatan Bunga Bank', 'Pendapatan Lainnya', 'Credit'],
            ['7-70100', 'Keuntungan Penjualan Aset Tetap', 'Pendapatan Lainnya', 'Credit'],
            ['7-70200', 'Keuntungan Selisih Kurs', 'Pendapatan Lainnya', 'Credit'],
            ['7-70300', 'Pendapatan Lain-Lain', 'Pendapatan Lainnya', 'Credit'],
            ['8-80000', 'Beban Administrasi Bank', 'Beban Lainnya', 'Debit'],
            ['8-80100', 'Beban Bunga Pinjaman', 'Beban Lainnya', 'Debit'],
            ['8-80200', 'Kerugian Penjualan Aset Tetap', 'Beban Lainnya', 'Debit'],
            ['8-80300', 'Kerugian Selisih Kurs', 'Beban Lainnya', 'Debit'],
            ['8-80400', 'Beban Denda & Pajak', 'Beban Lainnya', 'Debit'],
            ['8-80500', 'Beban Lain-Lain', 'Beban Lainnya', 'Debit'],
            ['9-90000', 'Beban Pajak Penghasilan (PPh Badan)', 'Beban Lainnya', 'Debit'],
        ];

        $now = Carbon::now();
        $insertData = [];
        foreach ($coaList as $coa) {
            $insertData[] = [
                'company_id' => $companyId,
                'code' => $coa[0],
                'name' => $coa[1],
                'category' => $coa[2],
                'type' => $coa[3],
                'initial_debit' => 0.00,
                'initial_credit' => 0.00,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('accounts')->insert($insertData);
    }
}
