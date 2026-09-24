<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class JournalEntryService
{
    /**
     * Catat transaksi cepat dan otomatis bentuk Jurnal Umum Berpasangan (Double Entry)
     */
    public function recordTransaction(Company|int $company, array $data, ?int $userId = null): Transaction
    {
        $companyId = is_numeric($company) ? $company : $company->id;

        return DB::transaction(function () use ($companyId, $data, $userId) {
            $date = $data['date'] ?? Carbon::now()->toDateString();
            $time = $data['time'] ?? Carbon::now()->toTimeString();
            $amount = floatval($data['amount'] ?? 0);
            $type = $data['type'] ?? 'income';
            $debitAccountId = $data['debit_account_id'] ?? null;
            $creditAccountId = $data['credit_account_id'] ?? null;

            if ($amount <= 0) {
                throw new Exception("Nominal transaksi harus lebih dari 0.");
            }

            if (!$debitAccountId || !$creditAccountId) {
                throw new Exception("Akun Debit dan Kredit wajib dipilih.");
            }

            // Generate nomor transaksi unik
            $trxNumber = $this->generateTransactionNumber($companyId, $date);

            // 1. Simpan Header Transaksi
            $transaction = Transaction::create([
                'company_id' => $companyId,
                'transaction_number' => $trxNumber,
                'date' => $date,
                'time' => $time,
                'type' => $type,
                'contact_id' => $data['contact_id'] ?? null,
                'debit_account_id' => $debitAccountId,
                'credit_account_id' => $creditAccountId,
                'amount' => $amount,
                'notes' => $data['notes'] ?? '',
                'tag_id' => $data['tag_id'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'project_id' => $data['project_id'] ?? null,
                'tax_id' => $data['tax_id'] ?? null,
                'created_by' => $userId,
            ]);

            // 2. Simpan Header Jurnal Umum
            $jrnNumber = $this->generateJournalNumber($companyId, $date);

            $typeName = match ($type) {
                'income' => 'Pemasukan',
                'expense' => 'Pengeluaran',
                'transfer' => 'Transfer Kas',
                default => 'Jurnal Transaksi'
            };

            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'transaction_id' => $transaction->id,
                'entry_number' => $jrnNumber,
                'date' => $date,
                'time' => $time,
                'reference_number' => $trxNumber,
                'description' => "{$typeName} - " . ($data['notes'] ?: 'Tanpa Catatan'),
                'created_by' => $userId,
            ]);

            // 3. Simpan Detail Jurnal (Item Debit)
            JournalItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $debitAccountId,
                'debit' => $amount,
                'credit' => 0,
                'memo' => $data['notes'] ?? '',
            ]);

            // 4. Simpan Detail Jurnal (Item Kredit)
            JournalItem::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $creditAccountId,
                'debit' => 0,
                'credit' => $amount,
                'memo' => $data['notes'] ?? '',
            ]);

            return $transaction;
        });
    }

    /**
     * Catat Jurnal Umum Manual Multi-Line (Custom Debit & Kredit)
     */
    public function recordManualJournal(Company|int $company, array $data, ?int $userId = null): JournalEntry
    {
        $companyId = is_numeric($company) ? $company : $company->id;

        return DB::transaction(function () use ($companyId, $data, $userId) {
            $date = $data['date'] ?? Carbon::now()->toDateString();
            $time = $data['time'] ?? Carbon::now()->toTimeString();
            $items = $data['items'] ?? [];

            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($items as $item) {
                $totalDebit += floatval($item['debit'] ?? 0);
                $totalCredit += floatval($item['credit'] ?? 0);
            }

            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                throw new Exception("Jurnal tidak seimbang! Total Debit (Rp " . number_format($totalDebit) . ") != Total Kredit (Rp " . number_format($totalCredit) . ")");
            }

            $jrnNumber = $this->generateJournalNumber($companyId, $date);

            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'transaction_id' => null,
                'entry_number' => $jrnNumber,
                'date' => $date,
                'time' => $time,
                'reference_number' => $data['reference_number'] ?? $jrnNumber,
                'description' => $data['description'] ?? 'Jurnal Manual',
                'created_by' => $userId,
            ]);

            foreach ($items as $item) {
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $item['account_id'],
                    'debit' => floatval($item['debit'] ?? 0),
                    'credit' => floatval($item['credit'] ?? 0),
                    'memo' => $item['memo'] ?? '',
                ]);
            }

            return $journalEntry;
        });
    }

    /**
     * Generate nomor transaksi berurutan per perusahaan dan periode (TRX/YYYYMM/XXXX)
     */
    public function generateTransactionNumber(int $companyId, string $date): string
    {
        $period = Carbon::parse($date)->format('Ym');
        $prefix = "TRX/{$period}/";

        $lastTrx = Transaction::where('company_id', $companyId)
            ->where('transaction_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('transaction_number');

        $nextSeq = 1;
        if ($lastTrx && preg_match('/TRX\/\d+\/(\d+)/', $lastTrx, $matches)) {
            $nextSeq = intval($matches[1]) + 1;
        }

        do {
            $candidate = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
            $exists = Transaction::where('company_id', $companyId)
                ->where('transaction_number', $candidate)
                ->exists();
            if ($exists) {
                $nextSeq++;
            }
        } while ($exists);

        return $candidate;
    }

    /**
     * Generate nomor jurnal berurutan per perusahaan dan periode (JRN/YYYYMM/XXXX)
     */
    public function generateJournalNumber(int $companyId, string $date): string
    {
        $period = Carbon::parse($date)->format('Ym');
        $prefix = "JRN/{$period}/";

        $lastJrn = JournalEntry::where('company_id', $companyId)
            ->where('entry_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('entry_number');

        $nextSeq = 1;
        if ($lastJrn && preg_match('/JRN\/\d+\/(\d+)/', $lastJrn, $matches)) {
            $nextSeq = intval($matches[1]) + 1;
        }

        do {
            $candidate = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
            $exists = JournalEntry::where('company_id', $companyId)
                ->where('entry_number', $candidate)
                ->exists();
            if ($exists) {
                $nextSeq++;
            }
        } while ($exists);

        return $candidate;
    }
}
