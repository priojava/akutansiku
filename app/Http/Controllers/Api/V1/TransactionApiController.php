<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Transaction;
use App\Services\JournalEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class TransactionApiController extends Controller
{
    public function __construct(
        protected JournalEntryService $journalService
    ) {}

    /**
     * List transaksi dengan pagination dan filter
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id') ?: $request->input('company_id', 1);

        $query = Transaction::with(['contact', 'debitAccount', 'creditAccount', 'journalEntry.items.account'])
            ->where('company_id', $companyId);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderByDesc('date')->orderByDesc('time')->paginate($request->input('per_page', 25));

        return response()->json([
            'status' => 'success',
            'data' => $transactions,
        ]);
    }

    /**
     * Catat transaksi baru via REST API
     */
    public function store(Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id') ?: $request->input('company_id', 1);

        $validated = $request->validate([
            'date' => 'required|date',
            'time' => 'nullable|string',
            'type' => 'required|in:income,expense,transfer,journal',
            'debit_account_id' => 'required|exists:accounts,id',
            'credit_account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string',
            'contact_id' => 'nullable|exists:contacts,id',
            'tag_id' => 'nullable|exists:tags,id',
            'tax_id' => 'nullable|exists:taxes,id',
        ]);

        try {
            $transaction = $this->journalService->recordTransaction($companyId, $validated, $request->user()?->id);
            $transaction->load(['debitAccount', 'creditAccount', 'contact', 'journalEntry.items']);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil disimpan dan dijurnal otomatis.',
                'data' => $transaction,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Detail transaksi
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id') ?: $request->input('company_id', 1);

        $transaction = Transaction::with(['contact', 'debitAccount', 'creditAccount', 'journalEntry.items.account'])
            ->where('company_id', $companyId)
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $transaction,
        ]);
    }
}
