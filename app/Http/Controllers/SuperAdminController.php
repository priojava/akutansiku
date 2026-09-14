<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        $query = Company::with(['users', 'subscriptionInvoices']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
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
            'extend_days' => 'nullable|integer',
        ]);

        $company->subscription_plan = $validated['subscription_plan'];
        $company->plan_type = $validated['subscription_plan'];
        $company->subscription_status = $validated['subscription_status'];

        if ($request->filled('extend_days') && (int)$request->extend_days > 0) {
            $currentExpiry = ($company->subscription_expires_at && $company->subscription_expires_at->isFuture())
                ? $company->subscription_expires_at
                : Carbon::now();
            $company->subscription_expires_at = (clone $currentExpiry)->addDays((int)$request->extend_days);
        }

        $company->save();

        return back()->with('success', 'Data status langganan perusahaan ' . $company->name . ' berhasil diperbarui!');
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
            'plan_type' => $invoice->plan_name,
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
}

