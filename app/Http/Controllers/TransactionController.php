<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Company;
use App\Models\Contact;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\PaymentMethod;
use App\Models\Tag;
use App\Models\Tax;
use App\Models\Transaction;
use App\Services\AiJournalingService;
use App\Services\JournalEntryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class TransactionController extends Controller
{
    public function __construct(
        protected JournalEntryService $journalService,
        protected AiJournalingService $aiService
    ) {}

    public function create()
    {
        $company = $this->getActiveCompany();
        $accounts = Account::where('company_id', $company->id)->where('is_active', true)->orderBy('code')->get();
        $contacts = Contact::where('company_id', $company->id)->orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('company_id', $company->id)->get();
        $tags = Tag::where('company_id', $company->id)->get();
        $taxes = Tax::where('company_id', $company->id)->get();

        $today = Carbon::now();

        $accountsJson = $accounts->map(fn($a) => [
            'id' => $a->id,
            'code' => $a->code,
            'name' => $a->name,
            'category' => $a->category,
            'type' => $a->type,
        ])->values()->toJson();

        return view('transactions.create', compact('company', 'accounts', 'accountsJson', 'contacts', 'paymentMethods', 'tags', 'taxes', 'today'));
    }

    public function store(Request $request)
    {
        $company = $this->getActiveCompany();

        // 1. Mode Transaksi Majemuk / Multi-Baris (Contoh Kasus: Paket Barang + Jasa + HPP)
        if ($request->input('entry_mode') === 'multi') {
            $validated = $request->validate([
                'date' => 'required|date',
                'time' => 'required',
                'notes' => 'required|string',
                'items' => 'required|array|min:2',
                'items.*.account_id' => 'required|exists:accounts,id',
                'items.*.debit' => 'nullable',
                'items.*.credit' => 'nullable',
                'items.*.memo' => 'nullable|string',
                'contact_id' => 'nullable|exists:contacts,id',
                'tag_id' => 'nullable|exists:tags,id',
            ]);

            try {
                $cleanedItems = [];
                $totalDebit = 0;
                $totalCredit = 0;

                foreach ($validated['items'] as $item) {
                    $rawDebit = is_string($item['debit'] ?? null) ? floatval(str_replace(['.', ','], '', $item['debit'])) : floatval($item['debit'] ?? 0);
                    $rawCredit = is_string($item['credit'] ?? null) ? floatval(str_replace(['.', ','], '', $item['credit'])) : floatval($item['credit'] ?? 0);

                    if ($rawDebit > 0 || $rawCredit > 0) {
                        $cleanedItems[] = [
                            'account_id' => intval($item['account_id']),
                            'debit' => $rawDebit,
                            'credit' => $rawCredit,
                            'memo' => $item['memo'] ?? $validated['notes'],
                        ];
                        $totalDebit += $rawDebit;
                        $totalCredit += $rawCredit;
                    }
                }

                if (count($cleanedItems) < 2) {
                    return back()->withInput()->with('error', 'Transaksi majemuk harus memiliki minimal 2 baris akun dengan nominal debit/kredit.');
                }

                if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                    return back()->withInput()->with('error', 'Jurnal tidak seimbang! Total Debit (Rp ' . number_format($totalDebit, 0, ',', '.') . ') != Total Kredit (Rp ' . number_format($totalCredit, 0, ',', '.') . ')');
                }

                $userId = auth()->id() ?? $request->user()?->id;
                $trxNumber = $this->journalService->generateTransactionNumber($company->id, $validated['date']);
                $jrnNumber = $this->journalService->generateJournalNumber($company->id, $validated['date']);

                DB::transaction(function () use ($company, $validated, $cleanedItems, $totalDebit, $userId, $trxNumber, $jrnNumber) {
                    $transaction = Transaction::create([
                        'company_id' => $company->id,
                        'transaction_number' => $trxNumber,
                        'date' => $validated['date'],
                        'time' => $validated['time'],
                        'type' => 'journal',
                        'contact_id' => $validated['contact_id'] ?? null,
                        'amount' => $totalDebit,
                        'notes' => $validated['notes'],
                        'tag_id' => $validated['tag_id'] ?? null,
                        'created_by' => $userId,
                    ]);

                    $journalEntry = JournalEntry::create([
                        'company_id' => $company->id,
                        'transaction_id' => $transaction->id,
                        'entry_number' => $jrnNumber,
                        'date' => $validated['date'],
                        'time' => $validated['time'],
                        'reference_number' => $trxNumber,
                        'description' => "Jurnal Majemuk - " . $validated['notes'],
                        'created_by' => $userId,
                    ]);

                    foreach ($cleanedItems as $ci) {
                        JournalItem::create([
                            'journal_entry_id' => $journalEntry->id,
                            'account_id' => $ci['account_id'],
                            'debit' => $ci['debit'],
                            'credit' => $ci['credit'],
                            'memo' => $ci['memo'] ?: $validated['notes'],
                        ]);
                    }
                });

                return redirect()->route('transactions.history')->with('success', 'Transaksi majemuk berhasil disimpan dan dijurnal otomatis.');
            } catch (Exception $e) {
                return back()->withInput()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
            }
        }

        // 2. Mode Catat Cepat Standar (1 Debit & 1 Kredit)
        $rawAmount = $request->input('amount');
        if (is_string($rawAmount)) {
            $cleanedAmount = floatval(str_replace(['.', ','], '', $rawAmount));
            $request->merge(['amount' => $cleanedAmount]);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'time' => 'required',
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
            $userId = auth()->id() ?? $request->user()?->id;
            $this->journalService->recordTransaction($company->id, $validated, $userId);
            return redirect()->route('transactions.history')->with('success', 'Transaksi berhasil disimpan dan jurnal tercatat secara otomatis.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    public function history(Request $request)
    {
        $company = $this->getActiveCompany();
        $currentUser = auth()->user() ?? $request->user();
        $currentRole = $currentUser ? $currentUser->getRoleInCompany($company->id) : 'admin';

        $query = Transaction::with(['contact', 'debitAccount', 'creditAccount', 'creator', 'journalEntry.items', 'tag'])
            ->where('company_id', $company->id);

        if (in_array($currentRole, ['cashier', 'staff']) && $currentUser) {
            $query->where('created_by', $currentUser->id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
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

        $transactions = $query->orderByDesc('date')->orderByDesc('time')->paginate(20);
        $tags = Tag::where('company_id', $company->id)->get();

        return view('transactions.history', compact('company', 'transactions', 'currentRole', 'currentUser', 'tags'));
    }

    public function aiParse(Request $request)
    {
        $company = $this->getActiveCompany();
        $query = $request->input('prompt', '');
        $result = $this->aiService->parseNaturalLanguage($company->id, $query);

        return response()->json($result);
    }

    public function destroy(int $id)
    {
        $company = $this->getActiveCompany();
        $transaction = \App\Models\Transaction::where('company_id', $company->id)->findOrFail($id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($transaction) {
            $journalEntries = \App\Models\JournalEntry::where('transaction_id', $transaction->id)->get();
            foreach ($journalEntries as $entry) {
                \App\Models\JournalItem::where('journal_entry_id', $entry->id)->delete();
                $entry->delete();
            }
            $transaction->delete();
        });

        return back()->with('success', "Transaksi {$transaction->transaction_number} dan catatan jurnalnya berhasil dihapus.");
    }
}
