<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Contact;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountApiController extends Controller
{
    /**
     * List Chart of Accounts (COA)
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id') ?: $request->input('company_id', 1);

        $query = Account::where('company_id', $companyId)->where('is_active', true);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $accounts = $query->orderBy('code')->get();

        return response()->json([
            'status' => 'success',
            'data' => $accounts,
        ]);
    }

    /**
     * Update Saldo Awal Akun COA
     */
    public function updateInitialBalances(Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id') ?: $request->input('company_id', 1);

        $validated = $request->validate([
            'conversion_date' => 'required|date',
            'balances' => 'required|array',
            'balances.*.id' => 'required|exists:accounts,id',
            'balances.*.initial_debit' => 'nullable|numeric|min:0',
            'balances.*.initial_credit' => 'nullable|numeric|min:0',
        ]);

        foreach ($validated['balances'] as $bal) {
            Account::where('company_id', $companyId)
                ->where('id', $bal['id'])
                ->update([
                    'initial_debit' => floatval($bal['initial_debit'] ?? 0),
                    'initial_credit' => floatval($bal['initial_credit'] ?? 0),
                ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Saldo awal akun berhasil diperbarui.',
        ]);
    }

    /**
     * Master Kontak
     */
    public function contacts(Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id') ?: $request->input('company_id', 1);
        $contacts = Contact::where('company_id', $companyId)->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $contacts,
        ]);
    }

    /**
     * Master Cara Pembayaran
     */
    public function paymentMethods(Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id') ?: $request->input('company_id', 1);
        $methods = PaymentMethod::with('account')->where('company_id', $companyId)->get();

        return response()->json([
            'status' => 'success',
            'data' => $methods,
        ]);
    }
}
