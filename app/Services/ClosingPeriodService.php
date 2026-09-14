<?php

namespace App\Services;

use App\Models\Account;
use App\Models\ClosingPeriod;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class ClosingPeriodService
{
    /**
     * Kertas Kerja Neraca Lajur (Worksheet Matrix 6 Kolom)
     */
    public function getWorksheet(int $companyId, string $closingDate): array
    {
        $accounts = Account::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $rows = [];
        $totalNeracaSaldoDebit = 0;
        $totalNeracaSaldoCredit = 0;
        $totalLabaRugiDebit = 0;
        $totalLabaRugiCredit = 0;
        $totalNeracaDebit = 0;
        $totalNeracaCredit = 0;

        $nominalCategories = ['Pendapatan', 'Harga Pokok Penjualan', 'Beban', 'Pendapatan Lainnya', 'Beban Lainnya'];
        $revenueCategories = ['Pendapatan', 'Pendapatan Lainnya'];
        $expenseCategories = ['Harga Pokok Penjualan', 'Beban', 'Beban Lainnya'];

        foreach ($accounts as $acc) {
            // Hitung mutasi sampai closing date
            $mutation = JournalItem::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($closingDate) {
                    $q->where('date', '<=', $closingDate);
                })
                ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
                ->first();

            $debitSum = ($acc->initial_debit ?? 0) + ($mutation->total_debit ?? 0);
            $creditSum = ($acc->initial_credit ?? 0) + ($mutation->total_credit ?? 0);

            $netBalance = $acc->type === 'Debit' ? ($debitSum - $creditSum) : ($creditSum - $debitSum);

            if ($debitSum > 0 || $creditSum > 0 || $acc->initial_debit > 0 || $acc->initial_credit > 0) {
                // Neraca Saldo Kolom
                $nsDebit = 0;
                $nsCredit = 0;
                if ($acc->type === 'Debit') {
                    if ($netBalance >= 0) {
                        $nsDebit = $netBalance;
                    } else {
                        $nsCredit = abs($netBalance);
                    }
                } else {
                    if ($netBalance >= 0) {
                        $nsCredit = $netBalance;
                    } else {
                        $nsDebit = abs($netBalance);
                    }
                }

                $totalNeracaSaldoDebit += $nsDebit;
                $totalNeracaSaldoCredit += $nsCredit;

                // Laba Rugi Kolom (Akun Nominal)
                $lrDebit = 0;
                $lrCredit = 0;
                if (in_array($acc->category, $nominalCategories)) {
                    $lrDebit = $nsDebit;
                    $lrCredit = $nsCredit;
                    $totalLabaRugiDebit += $lrDebit;
                    $totalLabaRugiCredit += $lrCredit;
                }

                // Neraca Kolom (Akun Riil)
                $nrcDebit = 0;
                $nrcCredit = 0;
                if (!in_array($acc->category, $nominalCategories)) {
                    $nrcDebit = $nsDebit;
                    $nrcCredit = $nsCredit;
                    $totalNeracaDebit += $nrcDebit;
                    $totalNeracaCredit += $nrcCredit;
                }

                $rows[] = [
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'category' => $acc->category,
                    'type' => $acc->type,
                    'ns_debit' => $nsDebit,
                    'ns_credit' => $nsCredit,
                    'lr_debit' => $lrDebit,
                    'lr_credit' => $lrCredit,
                    'nrc_debit' => $nrcDebit,
                    'nrc_credit' => $nrcCredit,
                ];
            }
        }

        // Laba Bersih Sebelum Pajak = Kredit Laba Rugi (Pendapatan) - Debit Laba Rugi (Beban)
        $labaRugiBersih = $totalLabaRugiCredit - $totalLabaRugiDebit;

        return [
            'closing_date' => $closingDate,
            'rows' => $rows,
            'totals' => [
                'ns_debit' => $totalNeracaSaldoDebit,
                'ns_credit' => $totalNeracaSaldoCredit,
                'lr_debit' => $totalLabaRugiDebit,
                'lr_credit' => $totalLabaRugiCredit,
                'nrc_debit' => $totalNeracaDebit,
                'nrc_credit' => $totalNeracaCredit,
                'laba_rugi' => $labaRugiBersih,
            ],
            'laba_rugi_bersih' => $labaRugiBersih,
        ];
    }

    /**
     * Eksekusi Simpan Tutup Buku & Buat Jurnal Penutup Otomatis
     */
    public function executeClosing(Company|int $company, array $data, int $userId): ClosingPeriod
    {
        $companyId = is_numeric($company) ? $company : $company->id;

        return DB::transaction(function () use ($companyId, $data, $userId) {
            $closingDate = $data['closing_date'] ?? Carbon::now()->toDateString();
            $notes = $data['notes'] ?? 'Tutup Buku Periode ' . Carbon::parse($closingDate)->translatedFormat('F Y');
            $taxExpenseAccountId = $data['tax_expense_account_id'] ?? null;
            $taxAmount = floatval($data['tax_amount'] ?? 0);
            $taxPayableAccountId = $data['tax_payable_account_id'] ?? null;
            $retainedEarningsAccountId = $data['retained_earnings_account_id'] ?? null;

            if (!$retainedEarningsAccountId) {
                throw new Exception("Akun Laba Ditahan / Ekuitas wajib dipilih.");
            }

            // Dapatkan Worksheet
            $worksheet = $this->getWorksheet($companyId, $closingDate);
            $labaSebelumPajak = $worksheet['laba_rugi_bersih'];
            $labaSetelahPajak = $labaSebelumPajak - $taxAmount;

            // Generate Ayat Jurnal Penutup (Closing Entries)
            $period = Carbon::parse($closingDate)->format('Ym');
            $journalCount = JournalEntry::where('company_id', $companyId)->count() + 1;
            $jrnNumber = "CLO/{$period}/" . str_pad($journalCount, 4, '0', STR_PAD_LEFT);

            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'transaction_id' => null,
                'entry_number' => $jrnNumber,
                'date' => $closingDate,
                'time' => '23:59:59',
                'reference_number' => "TUTUP-BUKU-{$period}",
                'description' => "Jurnal Penutup - {$notes}",
                'created_by' => $userId,
            ]);

            // 1. Tutup Akun Pendapatan (Debit Akun Pendapatan, Kredit Laba Ditahan)
            foreach ($worksheet['rows'] as $row) {
                if ($row['lr_credit'] > 0) {
                    // Akun Pendapatan didebit sebesar saldonya agar menjadi 0
                    JournalItem::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $row['id'],
                        'debit' => $row['lr_credit'],
                        'credit' => 0,
                        'memo' => "Penutupan Pendapatan {$row['name']}",
                    ]);
                }
            }

            // 2. Tutup Akun Beban & HPP (Kredit Akun Beban, Debit Laba Ditahan)
            foreach ($worksheet['rows'] as $row) {
                if ($row['lr_debit'] > 0) {
                    // Akun Beban dikredit sebesar saldonya agar menjadi 0
                    JournalItem::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $row['id'],
                        'debit' => 0,
                        'credit' => $row['lr_debit'],
                        'memo' => "Penutupan Beban {$row['name']}",
                    ]);
                }
            }

            // 3. Catat Jurnal Pajak jika ada
            if ($taxAmount > 0 && $taxExpenseAccountId && $taxPayableAccountId) {
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $taxExpenseAccountId,
                    'debit' => $taxAmount,
                    'credit' => 0,
                    'memo' => "Beban Pajak Penghasilan Periode",
                ]);
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $taxPayableAccountId,
                    'debit' => 0,
                    'credit' => $taxAmount,
                    'memo' => "Hutang Pajak Penghasilan",
                ]);
            }

            // 4. Tutup Laba / Rugi Bersih Setelah Pajak ke Akun Laba Ditahan
            if ($labaSetelahPajak > 0) {
                // Laba Bersih: Kredit ke Akun Laba Ditahan
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $retainedEarningsAccountId,
                    'debit' => 0,
                    'credit' => $labaSetelahPajak,
                    'memo' => "Transfer Laba Bersih ke Laba Ditahan",
                ]);
            } elseif ($labaSetelahPajak < 0) {
                // Rugi Bersih: Debit ke Akun Laba Ditahan
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $retainedEarningsAccountId,
                    'debit' => abs($labaSetelahPajak),
                    'credit' => 0,
                    'memo' => "Transfer Rugi Bersih ke Laba Ditahan",
                ]);
            }

            // Simpan Record Tutup Buku
            $closingPeriod = ClosingPeriod::create([
                'company_id' => $companyId,
                'closing_date' => $closingDate . ' 23:59:59',
                'period_name' => $data['period_name'] ?? Carbon::parse($closingDate)->locale('id')->translatedFormat('F Y'),
                'notes' => $notes,
                'total_revenue' => $worksheet['totals']['lr_credit'],
                'total_expense' => $worksheet['totals']['lr_debit'],
                'net_profit_before_tax' => $labaSebelumPajak,
                'tax_expense_account_id' => $taxExpenseAccountId,
                'tax_amount' => $taxAmount,
                'tax_payable_account_id' => $taxPayableAccountId,
                'retained_earnings_account_id' => $retainedEarningsAccountId,
                'net_profit_after_tax' => $labaSetelahPajak,
                'worksheet_data' => $worksheet,
                'journal_entry_id' => $journalEntry->id,
                'created_by' => $userId,
            ]);

            return $closingPeriod;
        });
    }
}
