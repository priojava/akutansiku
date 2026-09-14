<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SubscriptionInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $company = $this->getActiveCompany();
        $currentUser = auth()->user();

        // If company has no expiration set, initialize default trial (14 days from creation)
        if (!$company->subscription_expires_at) {
            $company->update([
                'subscription_status' => 'trial',
                'subscription_plan' => $company->plan_type ?: 'premium',
                'subscription_expires_at' => Carbon::now()->addDays(14),
            ]);
        }

        // Ensure at least 1 initial invoice exists for demonstration / trial record
        if ($company->subscriptionInvoices()->count() === 0) {
            SubscriptionInvoice::create([
                'company_id' => $company->id,
                'invoice_number' => 'INV' . Carbon::now()->format('YmdHis') . rand(10, 99),
                'plan_name' => $company->subscription_plan ?: 'premium',
                'duration_months' => 1,
                'amount' => 0,
                'status' => 'paid',
                'description' => 'Trial fitur premium 14 hari',
                'payment_method' => 'Trial Activation',
                'start_date' => Carbon::now()->subDay(),
                'end_date' => $company->subscription_expires_at,
                'paid_at' => Carbon::now()->subDay(),
                'created_by' => $currentUser?->id,
            ]);
        }

        $latestInvoice = $company->subscriptionInvoices()->latest()->first();
        $invoices = $company->subscriptionInvoices()->get();

        return view('subscription.index', compact('company', 'latestInvoice', 'invoices', 'currentUser'));
    }

    public function renew(Request $request)
    {
        $company = $this->getActiveCompany();
        $currentUser = auth()->user();

        $validated = $request->validate([
            'plan_name' => 'required|in:standard,premium',
            'duration_months' => 'required|integer|in:1,3,6,12',
            'payment_method' => 'required|string',
        ]);

        $monthlyPrice = $validated['plan_name'] === 'premium' ? 249000 : 99000;
        $discount = match ((int) $validated['duration_months']) {
            12 => 0.20, // 20% discount for 1 year
            6 => 0.10,  // 10% discount for 6 months
            default => 0.0
        };

        $totalAmount = ($monthlyPrice * (int) $validated['duration_months']) * (1 - $discount);
        $invoiceNumber = 'INV' . Carbon::now()->format('YmdHis') . rand(10, 99);

        $currentExpiry = ($company->subscription_expires_at && $company->subscription_expires_at->isFuture()) 
            ? $company->subscription_expires_at 
            : Carbon::now();

        $newExpiry = (clone $currentExpiry)->addMonths((int) $validated['duration_months']);

        $invoice = SubscriptionInvoice::create([
            'company_id' => $company->id,
            'invoice_number' => $invoiceNumber,
            'plan_name' => $validated['plan_name'],
            'duration_months' => (int) $validated['duration_months'],
            'amount' => $totalAmount,
            'status' => 'paid', // Auto-activate for seamless demo experience
            'description' => 'Perpanjangan Paket ' . ucfirst($validated['plan_name']) . ' (' . $validated['duration_months'] . ' Bulan)',
            'payment_method' => $validated['payment_method'],
            'start_date' => $currentExpiry,
            'end_date' => $newExpiry,
            'paid_at' => Carbon::now(),
            'created_by' => $currentUser?->id,
        ]);

        // Update company subscription status
        $company->update([
            'subscription_status' => 'active',
            'subscription_plan' => $validated['plan_name'],
            'plan_type' => $validated['plan_name'],
            'subscription_expires_at' => $newExpiry,
        ]);

        return redirect()->route('subscription.index')->with('success', 'Langganan berhasil diperpanjang hingga ' . $newExpiry->isoFormat('D MMMM Y') . '!');
    }

    public function invoice($id)
    {
        $company = $this->getActiveCompany();
        $invoice = SubscriptionInvoice::where('company_id', $company->id)->findOrFail($id);

        return view('subscription.invoice', compact('company', 'invoice'));
    }
}
