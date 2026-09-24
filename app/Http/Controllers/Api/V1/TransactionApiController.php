<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Department;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Transaction;
use App\Services\JournalEntryService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $companyId = $this->getCompanyId($request);

        $query = Transaction::with([
            'contact',
            'department',
            'project',
            'tag',
            'debitAccount',
            'creditAccount',
            'journalEntry.items.account',
        ])->where('company_id', $companyId);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('contact_id')) {
            $query->where('contact_id', $request->contact_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('tag_id')) {
            $query->where('tag_id', $request->tag_id);
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
     * Catat transaksi baru via REST API (Transaksi Majemuk / Multi-Akun atau Transaksi Cepat)
     * Mendukung pengiriman ID langsung MAUPUN Nama Teks (Auto-Create / Auto-Resolve on-the-fly)
     */
    public function store(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);

        // 1. Auto-Resolve / Auto-Create Contact (Customer / Vendor / Kandidat)
        $contactId = $request->input('contact_id');
        if (!$contactId && ($request->filled('customer_name') || $request->filled('contact_name') || $request->filled('vendor_name'))) {
            $cName = trim((string) ($request->input('customer_name') ?? $request->input('contact_name') ?? $request->input('vendor_name')));
            $cType = $request->filled('vendor_name') ? 'vendor' : ($request->input('contact_type', 'customer'));
            $contact = Contact::firstOrCreate(
                ['company_id' => $companyId, 'name' => $cName],
                [
                    'type' => $cType,
                    'phone' => $request->input('contact_phone') ?? $request->input('phone'),
                    'email' => $request->input('contact_email') ?? $request->input('email'),
                ]
            );
            $contactId = $contact->id;
        }

        // 2. Auto-Resolve / Auto-Create Department (P3MI / Divisi)
        $departmentId = $request->input('department_id');
        if (!$departmentId && $request->filled('department_name')) {
            $dName = trim((string) $request->input('department_name'));
            $dept = Department::firstOrCreate(
                ['company_id' => $companyId, 'name' => $dName],
                ['is_active' => true]
            );
            $departmentId = $dept->id;
        }

        // 3. Auto-Resolve / Auto-Create Project (Lowongan / Pekerjaan)
        $projectId = $request->input('project_id');
        if (!$projectId && $request->filled('project_name')) {
            $pName = trim((string) $request->input('project_name'));
            $proj = Project::firstOrCreate(
                ['company_id' => $companyId, 'name' => $pName],
                ['department_id' => $departmentId, 'status' => 'active']
            );
            $projectId = $proj->id;
        }

        // 4. Auto-Resolve / Auto-Create Tag (Agensi / Label)
        $tagId = $request->input('tag_id');
        if (!$tagId && $request->filled('tag_name')) {
            $tName = trim((string) $request->input('tag_name'));
            $tag = Tag::firstOrCreate(
                ['company_id' => $companyId, 'name' => $tName],
                ['color' => $request->input('tag_color', '#3b82f6')]
            );
            $tagId = $tag->id;
        }

        $request->merge([
            'contact_id' => $contactId,
            'department_id' => $departmentId,
            'project_id' => $projectId,
            'tag_id' => $tagId,
        ]);

        $resolveAccountId = function ($val) use ($companyId) {
            if (!$val) return null;
            if (is_numeric($val) && Account::where('company_id', $companyId)->where('id', $val)->exists()) {
                return (int) $val;
            }
            $acc = Account::where('company_id', $companyId)->where('code', trim((string) $val))->first();
            return $acc ? $acc->id : (is_numeric($val) ? (int) $val : null);
        };

        // Kasus 1: Transaksi Majemuk (Compound / Multi-Akun items)
        if ($request->has('items') && is_array($request->input('items'))) {
            $items = $request->input('items');
            foreach ($items as &$it) {
                $it['account_id'] = $resolveAccountId($it['account_id'] ?? ($it['account_code'] ?? null));
            }
            $request->merge(['items' => $items]);

            $validated = $request->validate([
                'date' => 'required|date',
                'time' => 'nullable|string',
                'notes' => 'nullable|string',
                'contact_id' => 'nullable|exists:contacts,id',
                'department_id' => 'nullable|exists:departments,id',
                'project_id' => 'nullable|exists:projects,id',
                'tag_id' => 'nullable|exists:tags,id',
                'tax_id' => 'nullable|exists:taxes,id',
                'items' => 'required|array|min:2',
                'items.*.account_id' => 'required|exists:accounts,id',
                'items.*.debit' => 'nullable|numeric|min:0',
                'items.*.credit' => 'nullable|numeric|min:0',
                'items.*.memo' => 'nullable|string',
            ]);

            try {
                $cleanedItems = [];
                $totalDebit = 0;
                $totalCredit = 0;
                foreach ($validated['items'] as $item) {
                    $d = floatval($item['debit'] ?? 0);
                    $c = floatval($item['credit'] ?? 0);
                    if ($d > 0 || $c > 0) {
                        $cleanedItems[] = [
                            'account_id' => intval($item['account_id']),
                            'debit' => $d,
                            'credit' => $c,
                            'memo' => $item['memo'] ?? ($validated['notes'] ?? ''),
                        ];
                        $totalDebit += $d;
                        $totalCredit += $c;
                    }
                }

                if (count($cleanedItems) < 2) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Transaksi majemuk minimal membutuhkan 2 baris akun dengan nominal debit / kredit.'
                    ], 422);
                }

                if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Jurnal tidak seimbang! Total Debit (Rp " . number_format($totalDebit, 2) . ") != Total Kredit (Rp " . number_format($totalCredit, 2) . ")"
                    ], 422);
                }

                $trxNumber = $this->journalService->generateTransactionNumber($companyId, $validated['date']);
                $jrnNumber = $this->journalService->generateJournalNumber($companyId, $validated['date']);
                $userId = $request->user()?->id;

                $transaction = DB::transaction(function () use ($companyId, $validated, $cleanedItems, $totalDebit, $userId, $trxNumber, $jrnNumber) {
                    $trx = Transaction::create([
                        'company_id' => $companyId,
                        'transaction_number' => $trxNumber,
                        'date' => $validated['date'],
                        'time' => $validated['time'] ?? Carbon::now()->toTimeString(),
                        'type' => 'journal',
                        'contact_id' => $validated['contact_id'] ?? null,
                        'department_id' => $validated['department_id'] ?? null,
                        'project_id' => $validated['project_id'] ?? null,
                        'tag_id' => $validated['tag_id'] ?? null,
                        'tax_id' => $validated['tax_id'] ?? null,
                        'amount' => $totalDebit,
                        'notes' => $validated['notes'] ?? 'Jurnal Majemuk via API',
                        'created_by' => $userId,
                    ]);

                    $journalEntry = JournalEntry::create([
                        'company_id' => $companyId,
                        'transaction_id' => $trx->id,
                        'entry_number' => $jrnNumber,
                        'date' => $validated['date'],
                        'time' => $validated['time'] ?? Carbon::now()->toTimeString(),
                        'reference_number' => $trxNumber,
                        'description' => "Jurnal Majemuk - " . ($validated['notes'] ?? ''),
                        'created_by' => $userId,
                    ]);

                    foreach ($cleanedItems as $ci) {
                        JournalItem::create([
                            'journal_entry_id' => $journalEntry->id,
                            'account_id' => $ci['account_id'],
                            'debit' => $ci['debit'],
                            'credit' => $ci['credit'],
                            'memo' => $ci['memo'],
                        ]);
                    }

                    return $trx->load([
                        'journalEntry.items.account',
                        'contact',
                        'department',
                        'project',
                        'tag',
                    ]);
                });

                return response()->json([
                    'status' => 'success',
                    'message' => 'Transaksi majemuk berhasil disimpan dan dijurnal otomatis.',
                    'data' => $transaction,
                ], 201);
            } catch (Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
        }

        // Kasus 2: Transaksi Cepat (1 Debit - 1 Kredit)
        $request->merge([
            'debit_account_id' => $resolveAccountId($request->input('debit_account_id') ?? $request->input('debit_account_code')),
            'credit_account_id' => $resolveAccountId($request->input('credit_account_id') ?? $request->input('credit_account_code')),
        ]);

        $validated = $request->validate([
            'date' => 'required|date',
            'time' => 'nullable|string',
            'type' => 'required|in:income,expense,transfer,journal',
            'debit_account_id' => 'required|exists:accounts,id',
            'credit_account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string',
            'contact_id' => 'nullable|exists:contacts,id',
            'department_id' => 'nullable|exists:departments,id',
            'project_id' => 'nullable|exists:projects,id',
            'tag_id' => 'nullable|exists:tags,id',
            'tax_id' => 'nullable|exists:taxes,id',
        ]);

        try {
            $transaction = $this->journalService->recordTransaction($companyId, $validated, $request->user()?->id);
            $transaction->load([
                'debitAccount',
                'creditAccount',
                'contact',
                'department',
                'project',
                'tag',
                'journalEntry.items.account',
            ]);

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
        $companyId = $this->getCompanyId($request);

        $transaction = Transaction::with([
            'contact',
            'department',
            'project',
            'tag',
            'debitAccount',
            'creditAccount',
            'journalEntry.items.account',
        ])
        ->where('company_id', $companyId)
        ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $transaction,
        ]);
    }
}

