<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Company;
use App\Models\Contact;
use App\Models\PaymentMethod;
use App\Models\Tag;
use App\Models\Tax;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    public function accounts(Request $request)
    {
        $company = $this->getActiveCompany();

        $query = Account::where('company_id', $company->id)->withCount('journalItems');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $accounts = $query->orderBy('code')->get();

        $totalInitialDebit = $accounts->sum('initial_debit');
        $totalInitialCredit = $accounts->sum('initial_credit');

        // Urutan standar pelaporan keuangan
        $order = [
            'ASET LANCAR' => 1,
            'ASET TIDAK LANCAR' => 2,
            'LIABILITAS' => 3,
            'EKUITAS' => 4,
            'PENDAPATAN' => 5,
            'BIAYA' => 6,
            'LAINNYA' => 7,
        ];

        $groupedAccounts = $accounts->groupBy('classification')->sortBy(function ($group, $key) use ($order) {
            return $order[$key] ?? 99;
        });

        // Ringkasan saldo & jumlah akun per kelompok
        $groupSummaries = [];
        foreach ($groupedAccounts as $classification => $accList) {
            $groupSummaries[$classification] = [
                'count' => $accList->count(),
                'debit' => $accList->sum('initial_debit'),
                'credit' => $accList->sum('initial_credit'),
            ];
        }

        return view('master.accounts', compact(
            'company', 
            'accounts', 
            'groupedAccounts', 
            'groupSummaries', 
            'totalInitialDebit', 
            'totalInitialCredit'
        ));
    }

    public function updateInitialBalances(Request $request)
    {
        $company = $this->getActiveCompany();

        if ($company->is_initial_balance_locked) {
            return back()->with('error', 'Saldo awal telah dikunci untuk keamanan data. Silakan buka kunci jika ingin mengubah.');
        }

        $conversionDate = $request->input('conversion_date');
        if ($conversionDate) {
            $company->update(['conversion_date' => $conversionDate]);
        }

        $debits = $request->input('debit', []);
        $credits = $request->input('credit', []);

        foreach ($debits as $accountId => $val) {
            $debitVal = floatval(str_replace(['.', ','], '', $val));
            $creditVal = floatval(str_replace(['.', ','], '', $credits[$accountId] ?? 0));

            Account::where('id', $accountId)
                ->where('company_id', $company->id)
                ->update([
                    'initial_debit' => $debitVal,
                    'initial_credit' => $creditVal,
                ]);
        }

        return redirect()->route('master.accounts')->with('success', 'Saldo Awal Akun COA berhasil diperbarui.');
    }

    public function toggleLockInitialBalances(Request $request)
    {
        $user = $request->user() ?? \App\Models\User::first();
        $company = $this->getActiveCompany();

        if (!$user->isAdmin($company->id)) {
            return back()->with('error', 'Akses ditolak: Hanya Administrator (Owner) yang memiliki wewenang mengunci atau membuka kunci Saldo Awal COA.');
        }

        $isLocked = !$company->is_initial_balance_locked;
        $company->update(['is_initial_balance_locked' => $isLocked]);

        $statusMsg = $isLocked
            ? '🔒 Saldo Awal berhasil DIKUNCI. Form COA sekarang aman dan berstatus Read-Only.'
            : '🔓 Kunci Saldo Awal DIBUKA oleh Administrator. Anda dapat mengedit saldo awal kembali.';

        return redirect()->route('master.accounts')->with('success', $statusMsg);
    }

    public function storeAccount(Request $request)
    {
        $company = $this->getActiveCompany();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'classification' => 'nullable|string',
            'category' => 'required|string',
            'code_prefix' => 'nullable|string',
            'code_number' => 'required|string',
            'type' => 'nullable|in:Debit,Credit',
            'description' => 'nullable|string',
        ]);

        $prefix = $validated['code_prefix'] ?? '';
        $fullCode = $prefix . $validated['code_number'];

        // Tentukan tipe debit/kredit berdasarkan kategori jika tidak diisi eksplisit
        $creditCategories = [
            'Depresiasi & Amortisasi',
            'Akun Hutang',
            'Kewajiban Lancar Lainnya',
            'Kewajiban Jangka Panjang',
            'Modal',
            'Laba Ditahan',
            'Pendapatan',
            'Pendapatan Lainnya'
        ];
        
        $type = !empty($validated['type']) 
            ? $validated['type'] 
            : (in_array($validated['category'], $creditCategories) ? 'Credit' : 'Debit');

        Account::create([
            'company_id' => $company->id,
            'code' => $fullCode,
            'name' => $validated['name'],
            'category' => $validated['category'],
            'type' => $type,
            'initial_debit' => 0,
            'initial_credit' => 0,
            'is_active' => true,
        ]);

        return redirect()->route('master.accounts')->with('success', "Akun COA '{$validated['name']}' ({$fullCode}) berhasil ditambahkan.");
    }

    public function destroyAccount(Request $request, $id)
    {
        $user = $request->user() ?? \App\Models\User::first();
        $company = $this->getActiveCompany();

        if (!$user || !$user->isAdmin($company->id)) {
            return back()->with('error', 'Akses ditolak: Hanya Owner / Administrator yang berwenang menghapus akun COA.');
        }

        $account = Account::where('id', $id)->where('company_id', $company->id)->firstOrFail();

        // 1. Cek akun sistem proteksi
        if ($account->code === '3-30999') {
            return back()->with('error', 'Akun penyeimbang sistem (3-30999) dilindungi dan tidak boleh dihapus.');
        }

        // 2. Cek apakah ada riwayat jurnal mutasi
        $journalCount = \App\Models\JournalItem::where('account_id', $account->id)->count();
        if ($journalCount > 0) {
            return back()->with('error', "Akun '{$account->name}' ({$account->code}) tidak dapat dihapus permanen karena sudah memiliki {$journalCount} riwayat mutasi jurnal transaksi. Demi integritas pembukuan, Anda dapat menonaktifkan akun ini.");
        }

        // 3. Cek transaksi langsung
        $trxCount = \App\Models\Transaction::where('debit_account_id', $account->id)
            ->orWhere('credit_account_id', $account->id)
            ->count();
        if ($trxCount > 0) {
            return back()->with('error', "Akun '{$account->name}' ({$account->code}) masih terhubung dengan {$trxCount} transaksi dan tidak dapat dihapus.");
        }

        // 4. Cek saldo awal
        if (($account->initial_debit ?? 0) > 0 || ($account->initial_credit ?? 0) > 0) {
            return back()->with('error', "Akun '{$account->name}' ({$account->code}) memiliki saldo awal. Silakan nol-kan terlebih dahulu saldo awalnya jika ingin menghapus akun ini.");
        }

        // 5. Cek keterikatan dengan master lain (Metode Bayar, Pajak, Aset Tetap)
        $isPaymentMethod = \App\Models\PaymentMethod::where('account_id', $account->id)->exists();
        if ($isPaymentMethod) {
            return back()->with('error', "Akun '{$account->name}' ({$account->code}) sedang digunakan oleh Metode Pembayaran.");
        }

        $isTax = \App\Models\Tax::where('account_id', $account->id)
            ->orWhere('purchase_account_id', $account->id)
            ->orWhere('sales_account_id', $account->id)
            ->exists();
        if ($isTax) {
            return back()->with('error', "Akun '{$account->name}' ({$account->code}) sedang digunakan dalam pengaturan Pajak.");
        }

        $isAsset = \App\Models\Asset::where('asset_account_id', $account->id)
            ->orWhere('accumulated_depreciation_account_id', $account->id)
            ->orWhere('expense_account_id', $account->id)
            ->exists();
        if ($isAsset) {
            return back()->with('error', "Akun '{$account->name}' ({$account->code}) sedang digunakan dalam Master Data Aset Tetap.");
        }

        $accountName = $account->name;
        $accountCode = $account->code;
        $account->delete();

        return redirect()->route('master.accounts')->with('success', "Akun COA '{$accountName}' ({$accountCode}) berhasil dihapus permanen.");
    }

    public function toggleActiveAccount(Request $request, $id)
    {
        $user = $request->user() ?? \App\Models\User::first();
        $company = $this->getActiveCompany();

        if (!$user || !$user->isAdmin($company->id)) {
            return back()->with('error', 'Akses ditolak: Hanya Owner / Administrator yang berwenang mengubah status akun.');
        }

        $account = Account::where('id', $id)->where('company_id', $company->id)->firstOrFail();
        $account->update(['is_active' => !$account->is_active]);

        $status = $account->is_active ? 'diaktifkan kembali' : 'dinonaktifkan (diarsipkan)';
        return redirect()->route('master.accounts')->with('success', "Akun '{$account->name}' ({$account->code}) berhasil {$status}.");
    }

    public function contacts(Request $request)
    {
        $company = $this->getActiveCompany();
        $contacts = Contact::where('company_id', $company->id)->orderBy('name')->get();
        return view('master.contacts', compact('company', 'contacts'));
    }

    public function storeContact(Request $request)
    {
        $company = $this->getActiveCompany();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:customer,vendor,employee,other',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        Contact::create([
            'company_id' => $company->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        return redirect()->route('master.contacts')->with('success', "Kontak '{$validated['name']}' berhasil ditambahkan.");
    }

    public function paymentMethods(Request $request)
    {
        $company = $this->getActiveCompany();
        $methods = PaymentMethod::with('account')->where('company_id', $company->id)->get();
        $accounts = Account::where('company_id', $company->id)->where('category', 'Kas & Bank')->get();
        return view('master.payment_methods', compact('company', 'methods', 'accounts'));
    }

    public function storePaymentMethod(Request $request)
    {
        $company = $this->getActiveCompany();
        $validated = $request->validate([
            'name' => 'required|string',
            'account_id' => 'nullable|exists:accounts,id',
        ]);

        PaymentMethod::create([
            'company_id' => $company->id,
            'name' => $validated['name'],
            'account_id' => $validated['account_id'] ?? null,
        ]);

        return redirect()->route('master.payment_methods')->with('success', 'Cara Pembayaran berhasil ditambahkan.');
    }

    public function taxes(Request $request)
    {
        $company = $this->getActiveCompany();

        // Pastikan akun PPN standar tersedia
        Account::firstOrCreate(
            ['company_id' => $company->id, 'code' => '2-20500'],
            ['name' => 'PPN Keluaran', 'category' => 'Akun Hutang', 'type' => 'Credit', 'initial_debit' => 0, 'initial_credit' => 0, 'is_active' => true]
        );
        Account::firstOrCreate(
            ['company_id' => $company->id, 'code' => '1-10500'],
            ['name' => 'PPN Masukan', 'category' => 'Harta Lancar Lainnya', 'type' => 'Debit', 'initial_debit' => 0, 'initial_credit' => 0, 'is_active' => true]
        );

        $query = Tax::with(['salesAccount', 'purchaseAccount'])->where('company_id', $company->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $taxes = $query->orderBy('name')->get();

        $accounts = Account::where('company_id', $company->id)->where('is_active', true)->orderBy('code')->get();
        $defaultSalesAccount = $accounts->firstWhere('code', '2-20500') ?? $accounts->firstWhere('category', 'Akun Hutang');
        $defaultPurchaseAccount = $accounts->firstWhere('code', '1-10500') ?? $accounts->firstWhere('category', 'Harta Lancar Lainnya');

        return view('master.taxes', compact('company', 'taxes', 'accounts', 'defaultSalesAccount', 'defaultPurchaseAccount'));
    }

    public function storeTax(Request $request)
    {
        $company = $this->getActiveCompany();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'is_withholding' => 'nullable',
            'sales_account_id' => 'nullable|exists:accounts,id',
            'purchase_account_id' => 'nullable|exists:accounts,id',
        ]);

        Tax::create([
            'company_id' => $company->id,
            'name' => $validated['name'],
            'rate' => $validated['rate'],
            'is_withholding' => $request->has('is_withholding'),
            'sales_account_id' => $validated['sales_account_id'] ?? null,
            'purchase_account_id' => $validated['purchase_account_id'] ?? null,
            'account_id' => $validated['sales_account_id'] ?? null,
        ]);

        return redirect()->route('master.taxes')->with('success', "Pajak '{$validated['name']}' berhasil ditambahkan.");
    }

    public function updateTax(Request $request, int $id)
    {
        $company = $this->getActiveCompany();
        $tax = Tax::where('company_id', $company->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'is_withholding' => 'nullable',
            'sales_account_id' => 'nullable|exists:accounts,id',
            'purchase_account_id' => 'nullable|exists:accounts,id',
        ]);

        $tax->update([
            'name' => $validated['name'],
            'rate' => $validated['rate'],
            'is_withholding' => $request->has('is_withholding'),
            'sales_account_id' => $validated['sales_account_id'] ?? null,
            'purchase_account_id' => $validated['purchase_account_id'] ?? null,
            'account_id' => $validated['sales_account_id'] ?? null,
        ]);

        return redirect()->route('master.taxes')->with('success', "Pajak '{$tax->name}' berhasil diperbarui.");
    }

    public function destroyTax(int $id)
    {
        $company = $this->getActiveCompany();
        $tax = Tax::where('company_id', $company->id)->findOrFail($id);
        $name = $tax->name;
        $tax->delete();

        return redirect()->route('master.taxes')->with('success', "Pajak '{$name}' berhasil dihapus.");
    }

    public function tags(Request $request)
    {
        $company = $this->getActiveCompany();

        $query = Tag::where('company_id', $company->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $tags = $query->orderBy('name')->get();

        return view('master.tags', compact('company', 'tags'));
    }

    public function storeTag(Request $request)
    {
        $company = $this->getActiveCompany();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:20',
        ]);

        Tag::create([
            'company_id' => $company->id,
            'name' => $validated['name'],
            'color' => $validated['color'] ?: '#3b82f6',
        ]);

        return redirect()->route('master.tags')->with('success', "Tag '{$validated['name']}' berhasil ditambahkan.");
    }

    public function updateTag(Request $request, int $id)
    {
        $company = $this->getActiveCompany();
        $tag = Tag::where('company_id', $company->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:20',
        ]);

        $tag->update([
            'name' => $validated['name'],
            'color' => $validated['color'] ?: '#3b82f6',
        ]);

        return redirect()->route('master.tags')->with('success', "Tag '{$tag->name}' berhasil diperbarui.");
    }

    public function destroyTag(int $id)
    {
        $company = $this->getActiveCompany();
        $tag = Tag::where('company_id', $company->id)->findOrFail($id);
        $name = $tag->name;
        $tag->delete();

        return redirect()->route('master.tags')->with('success', "Tag '{$name}' berhasil dihapus.");
    }
}

