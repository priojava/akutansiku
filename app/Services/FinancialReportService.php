<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    /**
     * Data Summary Widget Dashboard
     */
    public function getDashboardSummary(int $companyId, ?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?: Carbon::now()->startOfMonth()->toDateString();
        $endDate = $endDate ?: Carbon::now()->endOfMonth()->toDateString();

        // 1. Kas & Bank (Akun kategori 'Kas & Bank')
        $kasAccounts = Account::where('company_id', $companyId)
            ->where('category', 'Kas & Bank')
            ->pluck('id');

        $kasBalance = $this->calculateAccountsBalance($companyId, $kasAccounts, $endDate);

        // 2. Piutang (Akun Piutang)
        $piutangAccounts = Account::where('company_id', $companyId)
            ->where('category', 'Akun Piutang')
            ->pluck('id');
        $piutangBalance = $this->calculateAccountsBalance($companyId, $piutangAccounts, $endDate);

        // 3. Hutang (Akun Hutang)
        $hutangAccounts = Account::where('company_id', $companyId)
            ->where('category', 'Akun Hutang')
            ->pluck('id');
        $hutangBalance = $this->calculateAccountsBalance($companyId, $hutangAccounts, $endDate, 'Credit');

        // 4. Laba Rugi dalam rentang tanggal
        $pl = $this->getProfitAndLoss($companyId, $startDate, $endDate);

        // 5. Beban Operasional dalam rentang tanggal
        $operatingExpenses = $this->getOperatingExpenses($companyId, $startDate, $endDate);

        // 6. Arus Kas dalam rentang tanggal
        $cashFlow = $this->getCashFlow($companyId, $startDate, $endDate);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'formatted' => Carbon::parse($startDate)->isoFormat('D MMM Y') . ' - ' . Carbon::parse($endDate)->isoFormat('D MMM Y')
            ],
            'kas_bank' => [
                'total' => $kasBalance,
                'formatted' => $this->formatRupiah($kasBalance),
            ],
            'piutang' => [
                'total' => $piutangBalance,
                'formatted' => $this->formatRupiah($piutangBalance),
            ],
            'hutang' => [
                'total' => $hutangBalance,
                'formatted' => $this->formatRupiah($hutangBalance),
            ],
            'laba_rugi' => [
                'total_pendapatan' => $pl['total_pendapatan'],
                'total_beban' => $pl['total_hpp'] + $pl['total_beban_operasional'] + $pl['total_beban_lainnya'],
                'laba_bersih' => $pl['laba_bersih'],
                'formatted' => $this->formatRupiah($pl['laba_bersih']),
            ],
            'beban_operasional' => [
                'total' => $operatingExpenses['total_beban_operasional'],
                'formatted' => $this->formatRupiah($operatingExpenses['total_beban_operasional']),
            ],
            'arus_kas' => [
                'kas_masuk' => $cashFlow['kas_masuk'],
                'kas_keluar' => $cashFlow['kas_keluar'],
                'kas_bersih' => $cashFlow['perubahan_kas'],
                'formatted' => $this->formatRupiah($cashFlow['perubahan_kas']),
            ],
            'charts' => $this->getDashboardCharts($companyId, $startDate, $endDate),
        ];
    }

    /**
     * Data Time-Series Dinamis untuk Grafik Dashboard (Kas & Bank, Laba Rugi, Arus Kas, Beban)
     */
    public function getDashboardCharts(int $companyId, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $diffDays = max(1, $start->diffInDays($end));
        $pointsCount = min(8, max(4, $diffDays));
        $step = max(1, (int) floor($diffDays / ($pointsCount - 1)));
        
        $dates = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $dates[] = $current->toDateString();
            $current->addDays($step);
            if (count($dates) >= $pointsCount - 1) {
                break;
            }
        }
        if (!in_array($end->toDateString(), $dates)) {
            $dates[] = $end->toDateString();
        }
        
        $labels = [];
        $kasBankSeries = [];
        $piutangSeries = [];
        $hutangSeries = [];
        $pemasukanSeries = [];
        $biayaSeries = [];
        $labaBersihSeries = [];
        $kasMasukSeries = [];
        $kasKeluarSeries = [];
        $kasBersihSeries = [];
        
        $kasAccountIds = Account::where('company_id', $companyId)->where('category', 'Kas & Bank')->pluck('id')->toArray();
        $piutangAccountIds = Account::where('company_id', $companyId)->where('category', 'Akun Piutang')->pluck('id')->toArray();
        $hutangAccountIds = Account::where('company_id', $companyId)->where('category', 'Akun Hutang')->pluck('id')->toArray();
        $pendapatanAccountIds = Account::where('company_id', $companyId)->whereIn('category', ['Pendapatan', 'Pendapatan Lainnya'])->pluck('id')->toArray();
        $bebanAccountIds = Account::where('company_id', $companyId)->whereIn('category', ['Harga Pokok Penjualan', 'Beban', 'Beban Lainnya'])->pluck('id')->toArray();

        $prevDate = null;
        foreach ($dates as $dateStr) {
            $labels[] = Carbon::parse($dateStr)->format('d M');
            
            // Rentang per interval: dari awal rentang interval ke $dateStr
            $rangeStart = $prevDate ? Carbon::parse($prevDate)->addDay()->toDateString() : $start->toDateString();
            $rangeEnd = $dateStr;
            $prevDate = $dateStr;
            
            // 1. Kas & Bank Saldo Kumulatif sampai $dateStr
            $kasBankSeries[] = (float) $this->calculateAccountsBalance($companyId, $kasAccountIds, $dateStr);
            
            // 2. Piutang Saldo Kumulatif sampai $dateStr
            $piutangSeries[] = (float) $this->calculateAccountsBalance($companyId, $piutangAccountIds, $dateStr, 'Debit');
            
            // 3. Hutang Saldo Kumulatif sampai $dateStr
            $hutangSeries[] = (float) $this->calculateAccountsBalance($companyId, $hutangAccountIds, $dateStr, 'Credit');
            
            // 4. Pemasukan interval (Credit pada akun pendapatan)
            $pemasukan = (float) JournalItem::whereIn('account_id', $pendapatanAccountIds)
                ->whereHas('journalEntry', function($q) use ($rangeStart, $rangeEnd) {
                    $q->whereBetween('date', [$rangeStart, $rangeEnd]);
                })->sum('credit');
            $pemasukanSeries[] = $pemasukan;
            
            // 5. Biaya interval (Debit pada akun beban & HPP)
            $biaya = (float) JournalItem::whereIn('account_id', $bebanAccountIds)
                ->whereHas('journalEntry', function($q) use ($rangeStart, $rangeEnd) {
                    $q->whereBetween('date', [$rangeStart, $rangeEnd]);
                })->sum('debit');
            $biayaSeries[] = $biaya;
            
            // Laba Bersih = Pemasukan - Biaya
            $labaBersihSeries[] = $pemasukan - $biaya;
            
            // 6. Arus Kas Masuk (Debit pada Kas & Bank)
            $kasMasuk = (float) JournalItem::whereIn('account_id', $kasAccountIds)
                ->whereHas('journalEntry', function($q) use ($rangeStart, $rangeEnd) {
                    $q->whereBetween('date', [$rangeStart, $rangeEnd]);
                })->sum('debit');
            $kasMasukSeries[] = $kasMasuk;
            
            // 7. Arus Kas Keluar (Credit pada Kas & Bank)
            $kasKeluar = (float) JournalItem::whereIn('account_id', $kasAccountIds)
                ->whereHas('journalEntry', function($q) use ($rangeStart, $rangeEnd) {
                    $q->whereBetween('date', [$rangeStart, $rangeEnd]);
                })->sum('credit');
            $kasKeluarSeries[] = $kasKeluar;
            
            // Kas Bersih = Kas Masuk - Kas Keluar
            $kasBersihSeries[] = $kasMasuk - $kasKeluar;
        }

        // Beban breakdown untuk Doughnut Chart
        $bebanMutations = $this->getCategoryMutations($companyId, ['Harga Pokok Penjualan', 'Beban', 'Beban Lainnya'], $startDate, $endDate, 'Debit');
        $bebanLabels = [];
        $bebanData = [];
        foreach ($bebanMutations as $bm) {
            if ($bm['total'] > 0) {
                $bebanLabels[] = $bm['name'];
                $bebanData[] = (float) $bm['total'];
            }
        }
        
        return [
            'labels' => $labels,
            'kas_bank_series' => $kasBankSeries,
            'piutang_series' => $piutangSeries,
            'hutang_series' => $hutangSeries,
            'pemasukan_series' => $pemasukanSeries,
            'biaya_series' => $biayaSeries,
            'laba_bersih_series' => $labaBersihSeries,
            'kas_masuk_series' => $kasMasukSeries,
            'kas_keluar_series' => $kasKeluarSeries,
            'kas_bersih_series' => $kasBersihSeries,
            'beban_labels' => $bebanLabels,
            'beban_data' => $bebanData,
        ];
    }

    /**
     * Laporan Laba Rugi (Profit and Loss Statement)
     */
    public function getProfitAndLoss(int $companyId, string $startDate, string $endDate, ?int $tagId = null): array
    {
        // 1. Pendapatan Penjualan (Kategori: Pendapatan)
        $pendapatanAccounts = $this->getCategoryMutations($companyId, ['Pendapatan'], $startDate, $endDate, 'Credit', $tagId);
        $totalPendapatan = array_sum(array_column($pendapatanAccounts, 'total'));

        // 2. Harga Pokok Penjualan (HPP)
        $hppAccounts = $this->getCategoryMutations($companyId, ['Harga Pokok Penjualan', 'HPP'], $startDate, $endDate, 'Debit', $tagId);
        $totalHpp = array_sum(array_column($hppAccounts, 'total'));

        // Laba Kotor = Total Pendapatan - Total HPP
        $labaKotor = $totalPendapatan - $totalHpp;

        // 3. Beban Operasional (Beban)
        $bebanAccounts = $this->getCategoryMutations($companyId, ['Beban'], $startDate, $endDate, 'Debit', $tagId);
        $totalBebanOperasional = array_sum(array_column($bebanAccounts, 'total'));

        // Laba Bersih Operasional / Pendapatan Operasional = Laba Kotor - Beban Operasional
        $labaBersihOperasional = $labaKotor - $totalBebanOperasional;

        // 4. Pendapatan Lainnya (Non Operasional)
        $pendapatanLainnya = $this->getCategoryMutations($companyId, ['Pendapatan Lainnya'], $startDate, $endDate, 'Credit', $tagId);
        $totalPendapatanLainnya = array_sum(array_column($pendapatanLainnya, 'total'));

        // 5. Beban Lainnya & Pajak Penghasilan (Non Operasional)
        $allBebanLainnya = $this->getCategoryMutations($companyId, ['Beban Lainnya'], $startDate, $endDate, 'Debit', $tagId);
        $bebanLainnya = [];
        $pajakPenghasilanList = [];

        foreach ($allBebanLainnya as $item) {
            $isTax = stripos($item['name'], 'Pajak Penghasilan') !== false 
                  || in_array($item['code'], ['8-80200', '9-90000', '9-90001']);
            if ($isTax) {
                $pajakPenghasilanList[] = $item;
            } else {
                $bebanLainnya[] = $item;
            }
        }

        $totalBebanLainnya = array_sum(array_column($bebanLainnya, 'total'));
        $totalPajak = array_sum(array_column($pajakPenghasilanList, 'total'));

        // Jumlah Pendapatan dan Beban Non Operasional (Net)
        $totalNonOperasionalNet = $totalPendapatanLainnya - $totalBebanLainnya;

        // LABA BERSIH (Sebelum Pajak)
        $labaSebelumPajak = $labaBersihOperasional + $totalNonOperasionalNet;

        // LABA BERSIH (Setelah Pajak)
        $labaSetelahPajak = $labaSebelumPajak - $totalPajak;
        $labaBersih = $labaSetelahPajak;

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'pendapatan_list' => $pendapatanAccounts,
            'total_pendapatan' => $totalPendapatan,
            'hpp_list' => $hppAccounts,
            'total_hpp' => $totalHpp,
            'laba_kotor' => $labaKotor,
            'beban_operasional_list' => $bebanAccounts,
            'total_beban_operasional' => $totalBebanOperasional,
            'laba_bersih_operasional' => $labaBersihOperasional,
            'pendapatan_lainnya_list' => $pendapatanLainnya,
            'total_pendapatan_lainnya' => $totalPendapatanLainnya,
            'beban_lainnya_list' => $bebanLainnya,
            'total_beban_lainnya' => $totalBebanLainnya,
            'total_non_operasional_net' => $totalNonOperasionalNet,
            'laba_sebelum_pajak' => $labaSebelumPajak,
            'pajak_penghasilan_list' => $pajakPenghasilanList,
            'total_pajak' => $totalPajak,
            'laba_setelah_pajak' => $labaSetelahPajak,
            'laba_bersih' => $labaBersih,
        ];
    }

    /**
     * Laporan Neraca Saldo (Trial Balance)
     */
    public function getTrialBalance(int $companyId, string $startDate, string $endDate): array
    {
        $accounts = Account::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $acc) {
            // Hitung mutasi dalam periode
            $mutation = JournalItem::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate]);
                })
                ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
                ->first();

            $debitSum = ($acc->initial_debit ?? 0) + ($mutation->total_debit ?? 0);
            $creditSum = ($acc->initial_credit ?? 0) + ($mutation->total_credit ?? 0);

            $netBalance = $acc->type === 'Debit' ? ($debitSum - $creditSum) : ($creditSum - $debitSum);

            if ($debitSum > 0 || $creditSum > 0 || $acc->initial_debit > 0 || $acc->initial_credit > 0) {
                $saldoDebit = $acc->type === 'Debit' ? max(0, $netBalance) : 0;
                $saldoCredit = $acc->type === 'Credit' ? max(0, $netBalance) : 0;

                // Jika saldo negatif pada tipe normal, tempatkan pada sisi sebaliknya
                if ($acc->type === 'Debit' && $netBalance < 0) {
                    $saldoCredit = abs($netBalance);
                } elseif ($acc->type === 'Credit' && $netBalance < 0) {
                    $saldoDebit = abs($netBalance);
                }

                $totalDebit += $saldoDebit;
                $totalCredit += $saldoCredit;

                $rows[] = [
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'category' => $acc->category,
                    'saldo_debit' => $saldoDebit,
                    'saldo_credit' => $saldoCredit,
                ];
            }
        }

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'rows' => $rows,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
        ];
    }

    /**
     * Laporan Buku Besar (General Ledger)
     */
    public function getGeneralLedger(int $companyId, ?int $accountId, string $startDate, string $endDate): array
    {
        $query = Account::where('company_id', $companyId)->where('is_active', true);
        if ($accountId) {
            $query->where('id', $accountId);
        }
        $accounts = $query->orderBy('code')->get();

        $ledgerData = [];

        foreach ($accounts as $acc) {
            // Saldo awal sebelum startDate
            $priorMutation = JournalItem::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($startDate) {
                    $q->where('date', '<', $startDate);
                })
                ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
                ->first();

            $initialDebit = ($acc->initial_debit ?? 0) + ($priorMutation->total_debit ?? 0);
            $initialCredit = ($acc->initial_credit ?? 0) + ($priorMutation->total_credit ?? 0);
            $runningBalance = $acc->type === 'Debit' ? ($initialDebit - $initialCredit) : ($initialCredit - $initialDebit);

            // Transaksi dalam periode
            $items = JournalItem::with(['journalEntry.transaction', 'journalEntry.creator', 'journalEntry.items.account'])
                ->where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate]);
                })
                ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
                ->orderBy('journal_entries.date')
                ->orderBy('journal_entries.time')
                ->select('journal_items.*')
                ->get();

            $entries = [];
            $totalDebitPeriod = 0;
            $totalCreditPeriod = 0;

            foreach ($items as $item) {
                $debit = floatval($item->debit);
                $credit = floatval($item->credit);
                $totalDebitPeriod += $debit;
                $totalCreditPeriod += $credit;

                if ($acc->type === 'Debit') {
                    $runningBalance += ($debit - $credit);
                } else {
                    $runningBalance += ($credit - $debit);
                }

                $allJournalItems = [];
                if ($item->journalEntry && $item->journalEntry->items) {
                    foreach ($item->journalEntry->items as $ji) {
                        $allJournalItems[] = [
                            'account_code' => $ji->account?->code,
                            'account_name' => $ji->account?->name,
                            'debit' => (float) $ji->debit,
                            'credit' => (float) $ji->credit,
                            'memo' => $ji->memo,
                        ];
                    }
                }

                $entries[] = [
                    'id' => $item->id,
                    'date' => $item->journalEntry->date->format('Y-m-d') . ' ' . $item->journalEntry->time,
                    'entry_number' => $item->journalEntry->entry_number,
                    'reference_number' => $item->journalEntry->reference_number ?: $item->journalEntry->entry_number,
                    'transaction_type' => $item->journalEntry->transaction?->type ? ucfirst($item->journalEntry->transaction->type) : 'Jurnal',
                    'notes' => $item->memo ?: $item->journalEntry->description,
                    'creator' => $item->journalEntry->creator?->name ?? 'Admin / System',
                    'debit' => $debit,
                    'credit' => $credit,
                    'saldo' => $runningBalance,
                    'journal_items' => $allJournalItems,
                ];
            }

            if (count($entries) > 0 || $runningBalance != 0) {
                $ledgerData[] = [
                    'account' => [
                        'id' => $acc->id,
                        'code' => $acc->code,
                        'name' => $acc->name,
                        'type' => $acc->type,
                    ],
                    'initial_balance' => $acc->type === 'Debit' ? ($initialDebit - $initialCredit) : ($initialCredit - $initialDebit),
                    'entries' => $entries,
                    'total_debit' => $totalDebitPeriod,
                    'total_credit' => $totalCreditPeriod,
                    'ending_balance' => $runningBalance,
                ];
            }
        }

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'accounts' => $ledgerData,
        ];
    }

    /**
     * Laporan Arus Kas (Cash Flow Statement - Direct Method)
     */
    public function getCashFlow(int $companyId, string $startDate, string $endDate): array
    {
        $kasAccountIds = Account::where('company_id', $companyId)
            ->where('category', 'Kas & Bank')
            ->pluck('id');

        // Total kas masuk ke kas & bank
        $kasMasuk = JournalItem::whereIn('account_id', $kasAccountIds)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            })
            ->sum('debit');

        // Total kas keluar dari kas & bank
        $kasKeluar = JournalItem::whereIn('account_id', $kasAccountIds)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            })
            ->sum('credit');

        $perubahanKas = $kasMasuk - $kasKeluar;

        // Posisi Kas awal & akhir
        $posisiKasAwal = $this->calculateAccountsBalance($companyId, $kasAccountIds, Carbon::parse($startDate)->subDay()->toDateString());
        $posisiKasAkhir = $posisiKasAwal + $perubahanKas;

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'aktivitas_operasional' => [
                'penerimaan_pelanggan' => $kasMasuk,
                'pembayaran_pemasok' => $kasKeluar,
                'total' => $perubahanKas,
            ],
            'aktivitas_investasi' => [
                'perolehan_aset' => 0,
                'total' => 0,
            ],
            'aktivitas_keuangan' => [
                'pinjaman' => 0,
                'modal' => 0,
                'total' => 0,
            ],
            'kas_masuk' => $kasMasuk,
            'kas_keluar' => $kasKeluar,
            'perubahan_kas' => $perubahanKas,
            'posisi_kas_awal' => $posisiKasAwal,
            'posisi_kas_akhir' => $posisiKasAkhir,
        ];
    }

    /**
     * Laporan Beban Operasional
     */
    public function getOperatingExpenses(int $companyId, string $startDate, string $endDate, ?int $tagId = null): array
    {
        $bebanList = $this->getCategoryMutations($companyId, ['Beban'], $startDate, $endDate, 'Debit', $tagId);
        $totalBeban = array_sum(array_column($bebanList, 'total'));

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'beban_list' => $bebanList,
            'total_beban_operasional' => $totalBeban,
        ];
    }

    /**
     * Laporan Jurnal Umum (General Journal Report)
     */
    public function getJournalReport(int $companyId, string $startDate, string $endDate, ?string $type = null, ?int $accountId = null, ?string $search = null, ?int $tagId = null): array
    {
        $query = JournalEntry::with(['items.account', 'creator', 'transaction.contact', 'transaction.tag'])
            ->where('company_id', $companyId)
            ->whereBetween('date', [$startDate, $endDate]);

        if ($type) {
            if ($type === 'closing') {
                $query->where('entry_number', 'like', 'CLO/%');
            } elseif ($type === 'manual') {
                $query->whereNull('transaction_id')->where('entry_number', 'not like', 'CLO/%');
            } else {
                $query->whereHas('transaction', function ($q) use ($type) {
                    $q->where('type', $type);
                });
            }
        }

        if ($accountId) {
            $query->whereHas('items', function ($q) use ($accountId) {
                $q->where('account_id', $accountId);
            });
        }

        if ($tagId) {
            $query->whereHas('transaction', function ($q) use ($tagId) {
                $q->where('tag_id', $tagId);
            });
        }

        if (!empty($search)) {
            $search = trim($search);
            $query->where(function ($q) use ($search) {
                $q->where('entry_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('transaction.contact', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('items.account', function ($aq) use ($search) {
                      $aq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        $entries = $query->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $totalDebit = 0;
        $totalCredit = 0;
        $formattedEntries = [];

        foreach ($entries as $entry) {
            $entryDebit = 0;
            $entryCredit = 0;
            $items = [];

            foreach ($entry->items as $item) {
                $d = (float) $item->debit;
                $c = (float) $item->credit;
                $entryDebit += $d;
                $entryCredit += $c;

                $items[] = [
                    'id' => $item->id,
                    'account_code' => $item->account?->code ?? '-',
                    'account_name' => $item->account?->name ?? '-',
                    'debit' => $d,
                    'credit' => $c,
                    'memo' => $item->memo,
                ];
            }

            $totalDebit += $entryDebit;
            $totalCredit += $entryCredit;

            $trxType = 'Jurnal Umum';
            if ($entry->transaction) {
                $t = strtolower($entry->transaction->type);
                $trxType = match ($t) {
                    'income', 'pemasukan' => 'Pemasukan',
                    'expense', 'pengeluaran' => 'Pengeluaran',
                    'transfer' => 'Transfer Kas',
                    'journal', 'jurnal' => 'Jurnal Umum',
                    default => ucfirst($entry->transaction->type)
                };
            } elseif (str_starts_with($entry->entry_number, 'CLO/')) {
                $trxType = 'Jurnal Penutup';
            }

            $formattedEntries[] = [
                'id' => $entry->id,
                'entry_number' => $entry->entry_number,
                'reference_number' => $entry->reference_number ?: $entry->entry_number,
                'date' => $entry->date->format('d/m/Y'),
                'time' => $entry->time,
                'type' => $trxType,
                'description' => $entry->description,
                'creator' => $entry->creator?->name ?? 'Admin / System',
                'contact_name' => $entry->transaction?->contact?->name,
                'tag_name' => $entry->transaction?->tag?->name,
                'tag_color' => $entry->transaction?->tag?->color,
                'total_debit' => $entryDebit,
                'total_credit' => $entryCredit,
                'is_balanced' => round($entryDebit, 2) === round($entryCredit, 2),
                'items' => $items,
            ];
        }

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'total_entries' => count($formattedEntries),
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => round($totalDebit, 2) === round($totalCredit, 2),
            'entries' => $formattedEntries,
        ];
    }

    private function getCategoryMutations(int $companyId, array $categories, string $startDate, string $endDate, string $type = 'Debit', ?int $tagId = null): array
    {
        $accounts = Account::where('company_id', $companyId)
            ->whereIn('category', $categories)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $result = [];
        foreach ($accounts as $acc) {
            $sum = JournalItem::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($startDate, $endDate, $tagId) {
                    $q->whereBetween('date', [$startDate, $endDate]);
                    if ($tagId) {
                        $q->whereHas('transaction', function ($t) use ($tagId) {
                            $t->where('tag_id', $tagId);
                        });
                    }
                })
                ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
                ->first();

            $val = $type === 'Debit'
                ? (($sum->total_debit ?? 0) - ($sum->total_credit ?? 0))
                : (($sum->total_credit ?? 0) - ($sum->total_debit ?? 0));

            if ($val != 0) {
                $result[] = [
                    'id' => $acc->id,
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'total' => $val,
                ];
            }
        }

        return $result;
    }

    private function calculateAccountsBalance(int $companyId, $accountIds, string $untilDate, string $normalType = 'Debit'): float
    {
        $initial = Account::whereIn('id', $accountIds)->sum($normalType === 'Debit' ? 'initial_debit' : 'initial_credit');

        $mutation = JournalItem::whereIn('account_id', $accountIds)
            ->whereHas('journalEntry', function ($q) use ($untilDate) {
                $q->where('date', '<=', $untilDate);
            })
            ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
            ->first();

        $debit = $mutation->total_debit ?? 0;
        $credit = $mutation->total_credit ?? 0;

        return $normalType === 'Debit' ? ($initial + $debit - $credit) : ($initial + $credit - $debit);
    }

    private function formatRupiah(float $amount): string
    {
        $isNegative = $amount < 0;
        $abs = abs($amount);
        $formatted = 'Rp ' . number_format($abs, 0, ',', '.');
        return $isNegative ? "({$formatted})" : $formatted;
    }

    /**
     * Laporan Neraca Keuangan (Balance Sheet - Bentuk Skontro / T-Account & Stafel)
     */
    public function getBalanceSheet(int $companyId, string $asOfDate): array
    {
        $accounts = Account::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $asetLancar = [];
        $asetTetap = [];
        $akumulasiPenyusutan = [];
        $asetLainnya = [];

        $kewajibanJangkaPendek = [];
        $kewajibanJangkaPanjang = [];

        $modal = [];
        $labaDitahan = [];

        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($accounts as $acc) {
            $mutation = JournalItem::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($asOfDate) {
                    $q->where('date', '<=', $asOfDate);
                })
                ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
                ->first();

            $debitSum = ($acc->initial_debit ?? 0) + ($mutation->total_debit ?? 0);
            $creditSum = ($acc->initial_credit ?? 0) + ($mutation->total_credit ?? 0);

            $balanceDebit = $debitSum - $creditSum;
            $balanceCredit = $creditSum - $debitSum;

            // Akun Nominal untuk Laba (Rugi) Periode Berjalan
            if (in_array($acc->category, ['Pendapatan', 'Pendapatan Lainnya'])) {
                $totalRevenue += $balanceCredit;
            } elseif (in_array($acc->category, ['Harga Pokok Penjualan', 'Beban', 'Beban Lainnya'])) {
                $totalExpense += $balanceDebit;
            }

            // 1. ASET LANCAR
            if (in_array($acc->category, ['Kas & Bank', 'Akun Piutang', 'Persediaan', 'Harta Lancar Lainnya'])) {
                if ($debitSum > 0 || $creditSum > 0 || ($acc->initial_debit ?? 0) > 0 || ($acc->initial_credit ?? 0) > 0) {
                    $asetLancar[] = [
                        'id' => $acc->id,
                        'code' => $acc->code,
                        'name' => $acc->name,
                        'category' => $acc->category,
                        'amount' => $balanceDebit,
                    ];
                }
            }
            // 2. ASET TIDAK LANCAR (Harta Tetap)
            elseif ($acc->category === 'Harta Tetap') {
                if ($debitSum > 0 || $creditSum > 0 || ($acc->initial_debit ?? 0) > 0 || ($acc->initial_credit ?? 0) > 0) {
                    $asetTetap[] = [
                        'id' => $acc->id,
                        'code' => $acc->code,
                        'name' => $acc->name,
                        'category' => $acc->category,
                        'amount' => $balanceDebit,
                    ];
                }
            }
            // 3. AKUMULASI PENYUSUTAN (Contra-Asset pengurang Aset Tetap)
            elseif ($acc->category === 'Depresiasi & Amortisasi' || str_starts_with($acc->code, '1-1075')) {
                if ($debitSum > 0 || $creditSum > 0 || ($acc->initial_debit ?? 0) > 0 || ($acc->initial_credit ?? 0) > 0) {
                    $akumulasiPenyusutan[] = [
                        'id' => $acc->id,
                        'code' => $acc->code,
                        'name' => $acc->name,
                        'category' => $acc->category,
                        'amount' => $balanceCredit, // Nilai positif untuk pengurang
                    ];
                }
            }
            // 4. ASET LAINNYA
            elseif ($acc->category === 'Harta Lainnya') {
                if ($debitSum > 0 || $creditSum > 0 || ($acc->initial_debit ?? 0) > 0 || ($acc->initial_credit ?? 0) > 0) {
                    $asetLainnya[] = [
                        'id' => $acc->id,
                        'code' => $acc->code,
                        'name' => $acc->name,
                        'category' => $acc->category,
                        'amount' => $balanceDebit,
                    ];
                }
            }
            // 5. KEWAJIBAN JANGKA PENDEK
            elseif (in_array($acc->category, ['Akun Hutang', 'Kewajiban Lancar Lainnya'])) {
                if ($debitSum > 0 || $creditSum > 0 || ($acc->initial_debit ?? 0) > 0 || ($acc->initial_credit ?? 0) > 0) {
                    $kewajibanJangkaPendek[] = [
                        'id' => $acc->id,
                        'code' => $acc->code,
                        'name' => $acc->name,
                        'category' => $acc->category,
                        'amount' => $balanceCredit,
                    ];
                }
            }
            // 6. KEWAJIBAN JANGKA PANJANG
            elseif ($acc->category === 'Kewajiban Jangka Panjang') {
                if ($debitSum > 0 || $creditSum > 0 || ($acc->initial_debit ?? 0) > 0 || ($acc->initial_credit ?? 0) > 0) {
                    $kewajibanJangkaPanjang[] = [
                        'id' => $acc->id,
                        'code' => $acc->code,
                        'name' => $acc->name,
                        'category' => $acc->category,
                        'amount' => $balanceCredit,
                    ];
                }
            }
            // 7. EKUITAS
            elseif ($acc->category === 'Modal') {
                if ($debitSum > 0 || $creditSum > 0 || ($acc->initial_debit ?? 0) > 0 || ($acc->initial_credit ?? 0) > 0) {
                    if (str_contains(strtolower($acc->name), 'laba ditahan')) {
                        $labaDitahan[] = [
                            'id' => $acc->id,
                            'code' => $acc->code,
                            'name' => $acc->name,
                            'amount' => $balanceCredit,
                        ];
                    } else {
                        $modal[] = [
                            'id' => $acc->id,
                            'code' => $acc->code,
                            'name' => $acc->name,
                            'amount' => $balanceCredit,
                        ];
                    }
                }
            }
        }

        // Subtotal Perhitungan
        $totalAsetLancar = array_sum(array_column($asetLancar, 'amount'));
        $totalAsetTetap = array_sum(array_column($asetTetap, 'amount'));
        $totalAkumulasiPenyusutan = array_sum(array_column($akumulasiPenyusutan, 'amount'));
        $totalAsetLainnya = array_sum(array_column($asetLainnya, 'amount'));

        $totalAsetTidakLancar = $totalAsetTetap - $totalAkumulasiPenyusutan + $totalAsetLainnya;
        $totalAset = $totalAsetLancar + $totalAsetTidakLancar;

        $totalKewajibanPendek = array_sum(array_column($kewajibanJangkaPendek, 'amount'));
        $totalKewajibanPanjang = array_sum(array_column($kewajibanJangkaPanjang, 'amount'));
        $totalKewajiban = $totalKewajibanPendek + $totalKewajibanPanjang;

        $totalModalDisetor = array_sum(array_column($modal, 'amount'));
        $totalLabaDitahan = array_sum(array_column($labaDitahan, 'amount'));
        $labaPeriodeBerjalan = $totalRevenue - $totalExpense;
        $totalEkuitas = $totalModalDisetor + $totalLabaDitahan + $labaPeriodeBerjalan;

        $totalKewajibanDanEkuitas = $totalKewajiban + $totalEkuitas;

        return [
            'as_of_date' => $asOfDate,
            'as_of_date_formatted' => Carbon::parse($asOfDate)->isoFormat('D MMMM Y'),
            'aset' => [
                'lancar' => [
                    'items' => $asetLancar,
                    'total' => $totalAsetLancar,
                ],
                'tidak_lancar' => [
                    'tetap' => $asetTetap,
                    'total_tetap' => $totalAsetTetap,
                    'akumulasi' => $akumulasiPenyusutan,
                    'total_akumulasi' => $totalAkumulasiPenyusutan,
                    'lainnya' => $asetLainnya,
                    'total_lainnya' => $totalAsetLainnya,
                    'total' => $totalAsetTidakLancar,
                ],
                'total' => $totalAset,
            ],
            'kewajiban' => [
                'jangka_pendek' => [
                    'items' => $kewajibanJangkaPendek,
                    'total' => $totalKewajibanPendek,
                ],
                'jangka_panjang' => [
                    'items' => $kewajibanJangkaPanjang,
                    'total' => $totalKewajibanPanjang,
                ],
                'total' => $totalKewajiban,
            ],
            'ekuitas' => [
                'modal' => [
                    'items' => $modal,
                    'total' => $totalModalDisetor,
                ],
                'laba_ditahan' => [
                    'items' => $labaDitahan,
                    'total' => $totalLabaDitahan,
                ],
                'laba_berjalan' => $labaPeriodeBerjalan,
                'total' => $totalEkuitas,
            ],
            'total_kewajiban_dan_ekuitas' => $totalKewajibanDanEkuitas,
            'is_balanced' => round($totalAset, 2) === round($totalKewajibanDanEkuitas, 2),
            'diff' => round($totalAset - $totalKewajibanDanEkuitas, 2),
        ];
    }
}
