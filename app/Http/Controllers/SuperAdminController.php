<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SaasPaymentSetting;
use App\Models\SaasPlan;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    public function index()
    {
        $totalCompanies = Company::count();
        $activeCompanies = Company::where('subscription_status', 'active')->count();
        $trialCompanies = Company::where('subscription_status', 'trial')->count();
        $expiredCompanies = Company::where('subscription_status', 'expired')->orWhere('subscription_status', 'suspended')->count();

        $totalRevenue = SubscriptionInvoice::where('status', 'paid')->sum('amount');
        $thisMonthRevenue = SubscriptionInvoice::where('status', 'paid')
            ->whereMonth('paid_at', Carbon::now()->month)
            ->whereYear('paid_at', Carbon::now()->year)
            ->sum('amount');

        $pendingInvoicesCount = SubscriptionInvoice::where('status', 'pending')->count();

        $recentCompanies = Company::with(['users'])->latest()->take(6)->get();
        $recentInvoices = SubscriptionInvoice::with('company')->latest()->take(8)->get();

        return view('superadmin.dashboard', compact(
            'totalCompanies',
            'activeCompanies',
            'trialCompanies',
            'expiredCompanies',
            'totalRevenue',
            'thisMonthRevenue',
            'pendingInvoicesCount',
            'recentCompanies',
            'recentInvoices'
        ));
    }

    public function tenants(Request $request)
    {
        $query = Company::with(['owner', 'users', 'subscriptionInvoices']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('users', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('subscription_status', $request->status);
        }

        if ($request->filled('plan')) {
            $query->where('subscription_plan', $request->plan);
        }

        $companies = $query->latest()->paginate(15);

        return view('superadmin.tenants', compact('companies'));
    }

    public function invoices(Request $request)
    {
        $query = SubscriptionInvoice::with('company');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('company', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->paginate(20);

        return view('superadmin.invoices', compact('invoices'));
    }

    public function updateTenant(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $validated = $request->validate([
            'subscription_plan' => 'required|in:standard,premium',
            'subscription_status' => 'required|in:trial,active,expired,suspended',
            'max_companies' => 'nullable|integer|min:1',
            'set_days' => 'nullable|integer|min:0',
            'expires_at' => 'nullable|date',
            'extend_days' => 'nullable|integer',
        ]);

        $company->subscription_plan = $validated['subscription_plan'];
        $company->plan_type = $validated['subscription_plan'] === 'standard' ? 'free' : 'premium';
        $company->subscription_status = $validated['subscription_status'];

        if ($request->filled('max_companies')) {
            $company->max_companies = (int) $validated['max_companies'];
            // Jika memiliki owner, update juga kuota seluruh anak entitas milik owner tersebut
            if ($company->owner_id) {
                Company::where('owner_id', $company->owner_id)->update(['max_companies' => (int)$validated['max_companies']]);
            }
        }

        // 1. Opsi Set Tepat X Hari dari Sekarang (misal: 30 hari trial)
        if ($request->filled('set_days') && (int)$request->set_days > 0) {
            $company->subscription_expires_at = Carbon::now()->addDays((int)$request->set_days);
        }
        // 2. Opsi Pilih Tanggal Kadaluwarsa Langsung (Kalender)
        elseif ($request->filled('expires_at')) {
            $company->subscription_expires_at = Carbon::parse($request->expires_at)->endOfDay();
        }
        // 3. Opsi Tambah Hari (+X Hari dari masa berlaku saat ini)
        elseif ($request->filled('extend_days') && (int)$request->extend_days > 0) {
            $currentExpiry = ($company->subscription_expires_at && $company->subscription_expires_at->isFuture())
                ? $company->subscription_expires_at
                : Carbon::now();
            $company->subscription_expires_at = (clone $currentExpiry)->addDays((int)$request->extend_days);
        }

        $company->save();

        $expText = $company->subscription_expires_at ? $company->subscription_expires_at->format('d/m/Y') : '-';
        return back()->with('success', "Data langganan perusahaan '{$company->name}' berhasil diperbarui! Status: " . ucfirst($company->subscription_status) . ", Paket: " . strtoupper($company->subscription_plan) . ", Kuota: {$company->max_companies} entitas, Masa Aktif: {$company->remaining_days} hari lagi ({$expText}).");
    }

    public function approveInvoice($id)
    {
        $invoice = SubscriptionInvoice::with('company')->findOrFail($id);
        $company = $invoice->company;

        $invoice->update([
            'status' => 'paid',
            'paid_at' => Carbon::now(),
            'approved_by' => auth()->id(),
        ]);

        $currentExpiry = ($company->subscription_expires_at && $company->subscription_expires_at->isFuture())
            ? $company->subscription_expires_at
            : Carbon::now();
        
        $newExpiry = (clone $currentExpiry)->addMonths($invoice->duration_months ?: 1);

        $company->update([
            'subscription_status' => 'active',
            'subscription_plan' => $invoice->plan_name,
            'plan_type' => $invoice->plan_name === 'standard' ? 'free' : 'premium',
            'subscription_expires_at' => $newExpiry,
        ]);

        return back()->with('success', 'Invoice ' . $invoice->invoice_number . ' disetujui & masa aktif ' . $company->name . ' diperpanjang!');
    }

    public function destroyTenant($id)
    {
        $company = Company::findOrFail($id);

        if (Company::count() <= 1) {
            return back()->with('error', 'Gagal: Tidak dapat menghapus satu-satunya perusahaan yang tersisa di sistem.');
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
            $fallback = Company::first();
            session(['active_company_id' => $fallback?->id]);
        }

        return back()->with('success', "Perusahaan '{$companyName}' dan seluruh data transaksinya berhasil dihapus permanen oleh Super Admin.");
    }

    public function resetTenantPassword($id)
    {
        $company = Company::with(['owner', 'users'])->findOrFail($id);
        $ownerUser = $company->getOwnerUser();

        if (!$ownerUser) {
            if ($company->email) {
                $ownerUser = User::firstOrCreate(
                    ['email' => $company->email],
                    [
                        'name' => $company->name . ' (Owner)',
                        'password' => Hash::make('password123'),
                        'default_company_id' => $company->id,
                    ]
                );
                $company->users()->syncWithoutDetaching([$ownerUser->id => ['role' => 'admin']]);
            } else {
                return back()->with('error', 'Gagal: Akun pemilik untuk tenant ini tidak ditemukan dan email tidak terdaftar.');
            }
        }

        $ownerUser->update([
            'password' => Hash::make('password123'),
        ]);

        return back()->with('success', 'Password akun pemilik (' . $ownerUser->email . ') berhasil di-reset menjadi default: password123');
    }

    public function paymentSettings()
    {
        $settings = SaasPaymentSetting::getSettings();
        return view('superadmin.payment_settings', compact('settings'));
    }

    public function updatePaymentSettings(Request $request)
    {
        $settings = SaasPaymentSetting::getSettings();

        $validated = $request->validate([
            'midtrans_server_key' => 'nullable|string',
            'midtrans_client_key' => 'nullable|string',
            'midtrans_merchant_id' => 'nullable|string',
            'xendit_secret_key' => 'nullable|string',
            'xendit_public_key' => 'nullable|string',
            'xendit_webhook_token' => 'nullable|string',
            'bank_accounts_info' => 'nullable|string',
        ]);

        $settings->update([
            'midtrans_enabled' => $request->has('midtrans_enabled'),
            'midtrans_server_key' => !empty($validated['midtrans_server_key'] ?? null) ? trim($validated['midtrans_server_key']) : null,
            'midtrans_client_key' => !empty($validated['midtrans_client_key'] ?? null) ? trim($validated['midtrans_client_key']) : null,
            'midtrans_merchant_id' => !empty($validated['midtrans_merchant_id'] ?? null) ? trim($validated['midtrans_merchant_id']) : null,
            'midtrans_is_production' => $request->input('midtrans_environment') === 'production',

            'xendit_enabled' => $request->has('xendit_enabled'),
            'xendit_secret_key' => !empty($validated['xendit_secret_key'] ?? null) ? trim($validated['xendit_secret_key']) : null,
            'xendit_public_key' => !empty($validated['xendit_public_key'] ?? null) ? trim($validated['xendit_public_key']) : null,
            'xendit_webhook_token' => !empty($validated['xendit_webhook_token'] ?? null) ? trim($validated['xendit_webhook_token']) : null,
            'xendit_is_production' => $request->input('xendit_environment') === 'production',

            'manual_transfer_enabled' => $request->has('manual_transfer_enabled'),
            'bank_accounts_info' => !empty($validated['bank_accounts_info'] ?? null) ? trim($validated['bank_accounts_info']) : null,
        ]);

        return back()->with('success', 'Pengaturan Payment Gateway Midtrans & Xendit berhasil disimpan!');
    }

    public function plans()
    {
        $standard = SaasPlan::firstOrCreate(['code' => 'standard'], [
            'name' => 'Standard',
            'price_monthly' => 99000,
            'discount_6_months' => 10,
            'discount_12_months' => 20,
            'description' => 'Maks 2 Cabang, Kasir POS & Laporan Dasar',
            'is_active' => true,
        ]);

        $premium = SaasPlan::firstOrCreate(['code' => 'premium'], [
            'name' => 'Pro Enterprise',
            'price_monthly' => 249000,
            'discount_6_months' => 10,
            'discount_12_months' => 20,
            'description' => 'Unlimited Cabang, AI Jurnal Google Gemini & Pajak DJP',
            'is_active' => true,
        ]);

        return view('superadmin.plans', compact('standard', 'premium'));
    }

    public function updatePlans(Request $request)
    {
        $validated = $request->validate([
            'standard_price' => 'required|numeric|min:0',
            'standard_discount_6' => 'required|numeric|min:0|max:100',
            'standard_discount_12' => 'required|numeric|min:0|max:100',
            'standard_description' => 'nullable|string',

            'premium_price' => 'required|numeric|min:0',
            'premium_discount_6' => 'required|numeric|min:0|max:100',
            'premium_discount_12' => 'required|numeric|min:0|max:100',
            'premium_description' => 'nullable|string',
        ]);

        SaasPlan::updateOrCreate(['code' => 'standard'], [
            'name' => 'Standard',
            'price_monthly' => $validated['standard_price'],
            'discount_6_months' => $validated['standard_discount_6'],
            'discount_12_months' => $validated['standard_discount_12'],
            'description' => $validated['standard_description'],
            'is_active' => true,
        ]);

        SaasPlan::updateOrCreate(['code' => 'premium'], [
            'name' => 'Pro Enterprise',
            'price_monthly' => $validated['premium_price'],
            'discount_6_months' => $validated['premium_discount_6'],
            'discount_12_months' => $validated['premium_discount_12'],
            'description' => $validated['premium_description'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Harga dan rincian Paket Layanan SaaS berhasil diperbarui!');
    }
}

