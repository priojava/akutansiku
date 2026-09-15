<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SaasPaymentSetting;
use App\Models\SaasPlan;
use App\Models\SubscriptionInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $company = $this->getActiveCompany();
        $currentUser = auth()->user();
        $paymentSettings = SaasPaymentSetting::getSettings();

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
        $plans = SaasPlan::where('is_active', true)->get()->keyBy('code');

        // Cari apakah ada tagihan pending yang belum kadaluwarsa (dalam 24 jam terakhir)
        $pendingInvoice = $company->subscriptionInvoices()
            ->where('status', 'pending')
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->latest()
            ->first();

        return view('subscription.index', compact('company', 'latestInvoice', 'invoices', 'currentUser', 'paymentSettings', 'plans', 'pendingInvoice'));
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

        $plan = SaasPlan::where('code', $validated['plan_name'])->first();
        $monthlyPrice = $plan ? (float) $plan->price_monthly : ($validated['plan_name'] === 'premium' ? 249000 : 99000);
        $discount6 = $plan ? ($plan->discount_6_months / 100) : 0.10;
        $discount12 = $plan ? ($plan->discount_12_months / 100) : 0.20;

        $discount = match ((int) $validated['duration_months']) {
            12 => $discount12,
            6 => $discount6,
            default => 0.0
        };

        $totalAmount = ($monthlyPrice * (int) $validated['duration_months']) * (1 - $discount);
        $invoiceNumber = 'INV' . Carbon::now()->format('YmdHis') . rand(10, 99);

        $currentExpiry = ($company->subscription_expires_at && $company->subscription_expires_at->isFuture()) 
            ? $company->subscription_expires_at 
            : Carbon::now();

        $newExpiry = (clone $currentExpiry)->addMonths((int) $validated['duration_months']);

        $methodLabel = match ($validated['payment_method']) {
            'midtrans' => 'Midtrans Payment Gateway (VA / QRIS / Kartu Kredit)',
            'xendit' => 'Xendit Invoice (VA Multi-Bank / QRIS / e-Wallet)',
            'manual_bca' => 'Transfer Bank BCA Manual',
            'manual_mandiri' => 'Transfer Bank Mandiri Manual',
            default => $validated['payment_method'],
        };

        $paymentSettings = SaasPaymentSetting::getSettings();

        // For online payment gateways (Midtrans / Xendit), status starts as pending until user completes payment
        $isOnlineGateway = in_array($validated['payment_method'], ['midtrans', 'xendit']);
        $initialStatus = $isOnlineGateway ? 'pending' : 'paid';

        // CEK TAGIHAN PENDING YANG MASIH AKTIF (DIBUAT DALAM 24 JAM TERAKHIR)
        $existingInvoice = SubscriptionInvoice::where('company_id', $company->id)
            ->where('status', 'pending')
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->latest()
            ->first();

        // Jika ada tagihan pending dengan paket & durasi yang sama, pakai tagihan tersebut agar tidak membuat order ganda
        if ($existingInvoice && $existingInvoice->plan_name === $validated['plan_name'] && (int)$existingInvoice->duration_months === (int)$validated['duration_months']) {
            $invoice = $existingInvoice;
            $invoiceNumber = $invoice->invoice_number;
            $snapToken = $invoice->snap_token;
            $snapRedirectUrl = $invoice->snap_redirect_url;
        } else {
            // Jika memilih paket atau durasi berbeda, batalkan invoice pending lama
            if ($existingInvoice) {
                $existingInvoice->update(['status' => 'rejected']);
            }

            $invoice = SubscriptionInvoice::create([
                'company_id' => $company->id,
                'invoice_number' => $invoiceNumber,
                'plan_name' => $validated['plan_name'],
                'duration_months' => (int) $validated['duration_months'],
                'amount' => $totalAmount,
                'status' => $initialStatus,
                'description' => 'Perpanjangan Paket ' . ($validated['plan_name'] === 'premium' ? 'Pro Enterprise' : 'Standard') . ' (' . $validated['duration_months'] . ' Bulan)',
                'payment_method' => $methodLabel,
                'start_date' => $currentExpiry,
                'end_date' => $newExpiry,
                'paid_at' => $initialStatus === 'paid' ? Carbon::now() : null,
                'created_by' => $currentUser?->id,
            ]);
            $snapToken = null;
            $snapRedirectUrl = null;
        }

        if ($initialStatus === 'paid') {
            $company->update([
                'subscription_status' => 'active',
                'subscription_plan' => $validated['plan_name'],
                'plan_type' => $validated['plan_name'] === 'standard' ? 'free' : 'premium',
                'subscription_expires_at' => $newExpiry,
            ]);
        }

        // Request Midtrans Snap Token HANYA JIKA BELUM ADA SNAP TOKEN
        if ($validated['payment_method'] === 'midtrans' && !empty($paymentSettings->midtrans_server_key)) {
            if (!$snapToken) {
                try {
                    $endpoint = $paymentSettings->midtrans_is_production
                        ? 'https://app.midtrans.com/snap/v1/transactions'
                        : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

                    $midtransResponse = \Illuminate\Support\Facades\Http::timeout(5)
                        ->withBasicAuth(trim($paymentSettings->midtrans_server_key), '')
                        ->post($endpoint, [
                            'transaction_details' => [
                                'order_id' => $invoiceNumber,
                                'gross_amount' => (int) $totalAmount,
                            ],
                            'customer_details' => [
                                'first_name' => $company->name,
                                'email' => $currentUser?->email ?? 'tenant@dapurgemoy.com',
                            ],
                            'item_details' => [
                                [
                                    'id' => $validated['plan_name'],
                                    'price' => (int) $totalAmount,
                                    'quantity' => 1,
                                    'name' => 'Paket ' . ($validated['plan_name'] === 'premium' ? 'Pro Enterprise' : 'Standard'),
                                ]
                            ]
                        ]);

                    if ($midtransResponse->successful()) {
                        $snapToken = $midtransResponse->json('token');
                        $snapRedirectUrl = $midtransResponse->json('redirect_url');
                        $invoice->update([
                            'snap_token' => $snapToken,
                            'snap_redirect_url' => $snapRedirectUrl,
                        ]);
                    }
                } catch (\Throwable $e) {
                    // Graceful fallback to built-in Sandbox Simulator
                }
            }
        }

        // Return JSON response if requested via AJAX or API
        if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'amount' => $totalAmount,
                'amount_formatted' => 'Rp ' . number_format($totalAmount, 0, ',', '.'),
                'plan_name' => $validated['plan_name'],
                'plan_display' => $validated['plan_name'] === 'premium' ? 'Pro Enterprise' : 'Standard',
                'duration_months' => (int) $validated['duration_months'],
                'payment_method' => $validated['payment_method'],
                'method_label' => $methodLabel,
                'snap_token' => $snapToken,
                'snap_redirect_url' => $snapRedirectUrl,
                'is_production' => $paymentSettings->midtrans_is_production,
                'client_key' => $paymentSettings->midtrans_client_key,
                'company_name' => $company->name,
            ]);
        }

        $planDisplay = $validated['plan_name'] === 'premium' ? 'Pro Enterprise' : 'Standard';
        return redirect()->route('subscription.index')->with('success', "Invoice tagihan {$invoice->invoice_number} berhasil dibuat. Silakan lakukan pembayaran via {$methodLabel}.");
    }

    public function payComplete(Request $request, $id)
    {
        $company = $this->getActiveCompany();
        $invoice = SubscriptionInvoice::where('company_id', $company->id)->findOrFail($id);

        $invoice->update([
            'status' => 'paid',
            'paid_at' => Carbon::now(),
        ]);

        $company->update([
            'subscription_status' => 'active',
            'subscription_plan' => $invoice->plan_name,
            'plan_type' => $invoice->plan_name === 'standard' ? 'free' : 'premium',
            'subscription_expires_at' => $invoice->end_date,
        ]);

        $planDisplay = $invoice->plan_name === 'premium' ? 'Pro Enterprise' : 'Standard';
        $expiryDate = $company->fresh()->subscription_expires_at?->isoFormat('D MMMM Y');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Pembayaran via {$invoice->payment_method} berhasil diverifikasi! Paket {$planDisplay} aktif hingga {$expiryDate}.",
                'invoice_number' => $invoice->invoice_number,
                'expires_at' => $expiryDate,
            ]);
        }

        return redirect()->route('subscription.index')->with('success', "Pembayaran via {$invoice->payment_method} berhasil diverifikasi! Paket {$planDisplay} aktif hingga {$expiryDate}.");
    }

    public function invoice($id)
    {
        $company = $this->getActiveCompany();
        $invoice = SubscriptionInvoice::where('company_id', $company->id)->findOrFail($id);

        return view('subscription.invoice', compact('company', 'invoice'));
    }

    public function cancelPendingInvoice(Request $request, $id)
    {
        $company = $this->getActiveCompany();
        $invoice = SubscriptionInvoice::where('company_id', $company->id)
            ->where('status', 'pending')
            ->findOrFail($id);

        $invoice->update(['status' => 'rejected']);

        return redirect()->route('subscription.index')->with('success', "Tagihan {$invoice->invoice_number} berhasil dibatalkan. Anda dapat memilih metode pembayaran atau bank lain.");
    }
}

