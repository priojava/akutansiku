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
        $bebanAccountIds = Account::where('company_id', $companyId)->whereIn('category', ['Harga Pokok Penjualan', 'HPP', 'Beban', 'Beban Lainnya'])->pluck('id')->toArray();

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
        $bebanMutations = $this->getCategoryMutations($companyId, ['Harga Pokok Penjualan', 'HPP', 'Beban', 'Beban Lainnya'], $startDate, $endDate, 'Debit');
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
    public function getProfitAndLoss(int $companyId, string $startDate, string $endDate, ?int $tagId = null, ?int $projectId = null, ?int $departmentId = null): array
    {
        // 1. Pendapatan Penjualan (Kategori: Pendapatan)
        $pendapatanAccounts = $this->getCategoryMutations($companyId, ['Pendapatan'], $startDate, $endDate, 'Credit', $tagId, $projectId, $departmentId);
        $totalPendapatan = array_sum(array_column($pendapatanAccounts, 'total'));

        // 2. Harga Pokok Penjualan (HPP)
        $hppAccounts = $this->getCategoryMutations($companyId, ['Harga Pokok Penjualan', 'HPP'], $startDate, $endDate, 'Debit', $tagId, $projectId, $departmentId);
        $totalHpp = array_sum(array_column($hppAccounts, 'total'));

        // Laba Kotor = Total Pendapatan - Total HPP
        $labaKotor = $totalPendapatan - $totalHpp;

        // 3. Beban Operasional (Beban)
        $bebanAccounts = $this->getCategoryMutations($companyId, ['Beban'], $startDate, $endDate, 'Debit', $tagId, $projectId, $departmentId);
        $totalBebanOperasional = array_sum(array_column($bebanAccounts, 'total'));

        // Laba Bersih Operasional / Pendapatan Operasional = Laba Kotor - Beban Operasional
        $labaBersihOperasional = $labaKotor - $totalBebanOperasional;

        // 4. Pendapatan Lainnya (Non Operasional)
        $pendapatanLainnya = $this->getCategoryMutations($companyId, ['Pendapatan Lainnya'], $startDate, $endDate, 'Credit', $tagId, $projectId, $departmentId);
        $totalPendapatanLainnya = array_sum(array_column($pendapatanLainnya, 'total'));

        // 5. Beban Lainnya & Pajak Penghasilan (Non Operasional)
        $allBebanLainnya = $this->getCategoryMutations($companyId, ['Beban Lainnya'], $startDate, $endDate, 'Debit', $tagId, $projectId, $departmentId);
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
     * Laporan Laba Rugi per Proyek / Tag (Profit & Loss by Project/Tag Multi-Column Matrix)
     */
    /**
     * Laporan Laba Rugi Komparatif Multi-Kolom (Profit & Loss Multi-Column Matrix by Department, Project, Tag, or Month)
     */
    public function getProfitAndLossByProject(int $companyId, string $startDate, string $endDate, string $groupBy = 'department', array $selectedIds = []): array
    {
        $columns = [];
        $isPeriodMode = in_array($groupBy, ['month', 'period']);

        // 1. Tentukan daftar kolom dimensi
        if ($isPeriodMode) {
            $start = Carbon::parse($startDate)->startOfMonth();
            $end = Carbon::parse($endDate)->endOfMonth();
            $current = $start->copy();
            
            while ($current->lte($end)) {
                $monthKey = $current->format('Y-m');
                $columns[$monthKey] = [
                    'id' => $monthKey,
                    'name' => $current->translatedFormat('F Y'),
                    'code' => $current->format('M Y'),
                    'color' => '#3b82f6',
                ];
                $current->addMonth();
            }
            $dimField = "SUBSTR(je.date, 1, 7)";
        } elseif ($groupBy === 'project') {
            $query = \App\Models\Project::where('company_id', $companyId)->orderBy('name');
            if (!empty($selectedIds)) {
                $query->whereIn('id', $selectedIds);
            }
            $dimensionItems = $query->get();
            foreach ($dimensionItems as $item) {
                $columns[$item->id] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code ?? null,
                    'color' => $item->color ?? '#6366f1',
                ];
            }
            $dimField = 'COALESCE(t.project_id, 0)';
        } elseif ($groupBy === 'tag') {
            $query = \App\Models\Tag::where('company_id', $companyId)->orderBy('name');
            if (!empty($selectedIds)) {
                $query->whereIn('id', $selectedIds);
            }
            $dimensionItems = $query->get();
            foreach ($dimensionItems as $item) {
                $columns[$item->id] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code ?? null,
                    'color' => $item->color ?? '#3b82f6',
                ];
            }
            $dimField = 'COALESCE(t.tag_id, 0)';
        } else {
            // Default: department
            $groupBy = 'department';
            $query = \App\Models\Department::where('company_id', $companyId)->orderBy('name');
            if (!empty($selectedIds)) {
                $query->whereIn('id', $selectedIds);
            }
            $dimensionItems = $query->get();
            foreach ($dimensionItems as $item) {
                $columns[$item->id] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code ?? null,
                    'color' => '#0ea5e9',
                ];
            }
            $dimField = 'COALESCE(t.department_id, 0)';
        }

        // 2. Query transaksi periode yang bersangkutan dengan join akun & jurnal
        $journalData = DB::table('journal_items as ji')
            ->join('journal_entries as je', 'je.id', '=', 'ji.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'ji.account_id')
            ->leftJoin('transactions as t', 't.id', '=', 'je.transaction_id')
            ->where('je.company_id', $companyId)
            ->whereBetween('je.date', [$startDate, $endDate])
            ->where('a.is_active', true)
            ->whereIn('a.category', [
                'Pendapatan',
                'Harga Pokok Penjualan',
                'HPP',
                'Beban',
                'Pendapatan Lainnya',
                'Beban Lainnya',
            ])
            ->select(
                'ji.account_id',
                'a.code as account_code',
                'a.name as account_name',
                'a.category as account_category',
                'a.type as account_type',
                DB::raw("{$dimField} as dimension_id"),
                DB::raw('SUM(ji.debit) as total_debit'),
                DB::raw('SUM(ji.credit) as total_credit')
            )
            ->groupBy(
                'ji.account_id',
                'a.code',
                'a.name',
                'a.category',
                'a.type',
                DB::raw($dimField)
            )
            ->orderBy('a.code')
            ->get();

        // Jika tidak ada filter selectedIds dan bukan mode period, periksa apakah ada data unassigned (dimensi = 0)
        if (empty($selectedIds) && !$isPeriodMode) {
            $hasUnassigned = false;
            foreach ($journalData as $row) {
                if ($row->dimension_id == 0 || $row->dimension_id === '0') {
                    $hasUnassigned = true;
                    break;
                }
            }
            if ($hasUnassigned) {
                $columns[0] = [
                    'id' => 0,
                    'name' => 'Umum / Non-Alokasi',
                    'code' => '-',
                    'color' => '#64748b',
                ];
            }
        }

        // Helper inisialisasi saldo per kolom
        $initAmounts = function () use ($columns) {
            $arr = [];
            foreach ($columns as $id => $col) {
                $arr[$id] = 0.0;
            }
            $arr['total'] = 0.0;
            return $arr;
        };

        $totalOperatingRevenue = $initAmounts();
        $totalCogs = $initAmounts();
        $grossProfit = $initAmounts();
        $totalOperatingExpenses = $initAmounts();
        $operatingIncome = $initAmounts();
        $totalOtherIncome = $initAmounts();
        $totalOtherExpenses = $initAmounts();
        $totalOtherNet = $initAmounts();
        $netProfitBeforeTax = $initAmounts();
        $totalTax = $initAmounts();
        $netProfitAfterTax = $initAmounts();

        $accountsGrouped = [];

        foreach ($journalData as $row) {
            $accId = $row->account_id;
            $dimId = $isPeriodMode ? (string)$row->dimension_id : (int)$row->dimension_id;
            $category = $row->account_category;
            $name = $row->account_name;
            $code = $row->account_code;

            // Jika dimensi baris ini tidak ada di daftar kolom yang ditampilkan, lewati
            if (!isset($columns[$dimId])) {
                continue;
            }

            if (in_array($category, ['Pendapatan', 'Pendapatan Lainnya'])) {
                $amount = (float) $row->total_credit - (float) $row->total_debit;
            } else {
                $amount = (float) $row->total_debit - (float) $row->total_credit;
            }

            if (!isset($accountsGrouped[$accId])) {
                $isTax = stripos($name, 'Pajak Penghasilan') !== false || in_array($code, ['8-80200', '9-90000', '9-90001']);
                
                $section = match ($category) {
                    'Pendapatan' => 'operating_revenue',
                    'Harga Pokok Penjualan', 'HPP' => 'cogs',
                    'Beban' => 'operating_expense',
                    'Pendapatan Lainnya' => 'other_income',
                    'Beban Lainnya' => $isTax ? 'tax_expense' : 'other_expense',
                    default => 'operating_expense'
                };

                $accountsGrouped[$accId] = [
                    'id' => $accId,
                    'code' => $code,
                    'name' => $name,
                    'category' => $category,
                    'section' => $section,
                    'amounts' => $initAmounts(),
                ];
            }

            $accountsGrouped[$accId]['amounts'][$dimId] += $amount;
            $accountsGrouped[$accId]['amounts']['total'] += $amount;
        }

        $operatingRevenueAccounts = [];
        $cogsAccounts = [];
        $operatingExpenseAccounts = [];
        $otherIncomeAccounts = [];
        $otherExpenseAccounts = [];
        $taxExpenseAccounts = [];

        foreach ($accountsGrouped as $acc) {
            // Saring hanya akun yang memiliki nominal tidak nol di setidaknya satu kolom
            $hasNonZero = false;
            foreach ($acc['amounts'] as $val) {
                if (abs($val) > 0.0001) {
                    $hasNonZero = true;
                    break;
                }
            }
            if (!$hasNonZero) {
                continue;
            }

            $section = $acc['section'];
            if ($section === 'operating_revenue') {
                $operatingRevenueAccounts[] = $acc;
                foreach ($acc['amounts'] as $key => $val) {
                    $totalOperatingRevenue[$key] += $val;
                }
            } elseif ($section === 'cogs') {
                $cogsAccounts[] = $acc;
                foreach ($acc['amounts'] as $key => $val) {
                    $totalCogs[$key] += $val;
                }
            } elseif ($section === 'operating_expense') {
                $operatingExpenseAccounts[] = $acc;
                foreach ($acc['amounts'] as $key => $val) {
                    $totalOperatingExpenses[$key] += $val;
                }
            } elseif ($section === 'other_income') {
                $otherIncomeAccounts[] = $acc;
                foreach ($acc['amounts'] as $key => $val) {
                    $totalOtherIncome[$key] += $val;
                }
            } elseif ($section === 'other_expense') {
                $otherExpenseAccounts[] = $acc;
                foreach ($acc['amounts'] as $key => $val) {
                    $totalOtherExpenses[$key] += $val;
                }
            } elseif ($section === 'tax_expense') {
                $taxExpenseAccounts[] = $acc;
                foreach ($acc['amounts'] as $key => $val) {
                    $totalTax[$key] += $val;
                }
            }
        }

        foreach (array_keys($totalOperatingRevenue) as $key) {
            $grossProfit[$key] = $totalOperatingRevenue[$key] - $totalCogs[$key];
            $operatingIncome[$key] = $grossProfit[$key] - $totalOperatingExpenses[$key];
            $totalOtherNet[$key] = $totalOtherIncome[$key] - $totalOtherExpenses[$key];
            $netProfitBeforeTax[$key] = $operatingIncome[$key] + $totalOtherNet[$key];
            $netProfitAfterTax[$key] = $netProfitBeforeTax[$key] - $totalTax[$key];
        }

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'columns' => $columns,
            'groupBy' => $groupBy,
            'operating_revenue' => [
                'accounts' => $operatingRevenueAccounts,
                'total' => $totalOperatingRevenue,
            ],
            'cogs' => [
                'accounts' => $cogsAccounts,
                'total' => $totalCogs,
            ],
            'gross_profit' => $grossProfit,
            'operating_expenses' => [
                'accounts' => $operatingExpenseAccounts,
                'total' => $totalOperatingExpenses,
            ],
            'operating_income' => $operatingIncome,
            'other_income' => [
                'accounts' => $otherIncomeAccounts,
                'total' => $totalOtherIncome,
            ],
            'other_expenses' => [
                'accounts' => $otherExpenseAccounts,
                'total' => $totalOtherExpenses,
            ],
            'other_net' => $totalOtherNet,
            'net_profit_before_tax' => $netProfitBeforeTax,
            'tax_expenses' => [
                'accounts' => $taxExpenseAccounts,
                'total' => $totalTax,
            ],
            'net_profit_after_tax' => $netProfitAfterTax,
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

    private function getCategoryMutations(int $companyId, array $categories, string $startDate, string $endDate, string $type = 'Debit', ?int $tagId = null, ?int $projectId = null, ?int $departmentId = null): array
    {
        $accounts = Account::where('company_id', $companyId)
            ->whereIn('category', $categories)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $result = [];
        foreach ($accounts as $acc) {
            $sum = JournalItem::where('account_id', $acc->id)
                ->whereHas('journalEntry', function ($q) use ($startDate, $endDate, $tagId, $projectId, $departmentId) {
                    $q->whereBetween('date', [$startDate, $endDate]);
                    if ($tagId || $projectId || $departmentId) {
                        $q->whereHas('transaction', function ($t) use ($tagId, $projectId, $departmentId) {
                            if ($tagId) {
                                $t->where('tag_id', $tagId);
                            }
                            if ($projectId) {
                                $t->where('project_id', $projectId);
                            }
                            if ($departmentId) {
                                $t->where('department_id', $departmentId);
                            }
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
            } elseif (in_array($acc->category, ['Harga Pokok Penjualan', 'HPP', 'Beban', 'Beban Lainnya'])) {
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

    /**
     * Laporan Laba Rugi Komparatif Berdampingan (Side-by-side by Department / Project / Consolidated)
     * Format formal akuntansi multi-kolom seperti Accurate / Zahir
     */
    public function getComparativeProfitAndLoss(
        int $companyId,
        string $startDate,
        string $endDate,
        string $groupBy = 'department',
        ?int $tagId = null
    ): array {
        // 1. Tentukan Kolom Komparasi
        $columns = [];
        $unassignedKey = 'unassigned';

        if ($groupBy === 'project') {
            $projects = \App\Models\Project::where('company_id', $companyId)->orderBy('code')->orderBy('name')->get();
            foreach ($projects as $proj) {
                $columns[] = [
                    'key' => 'proj_' . $proj->id,
                    'id' => $proj->id,
                    'code' => $proj->code ?: $proj->name,
                    'name' => $proj->name,
                ];
            }
        } elseif ($groupBy === 'department') {
            $departments = \App\Models\Department::where('company_id', $companyId)->where('is_active', true)->orderBy('code')->orderBy('name')->get();
            foreach ($departments as $dept) {
                $columns[] = [
                    'key' => 'dept_' . $dept->id,
                    'id' => $dept->id,
                    'code' => $dept->code ?: $dept->name,
                    'name' => $dept->name,
                ];
            }
        }

        // 2. Query Mutasi Jurnal dengan Agregasi per Akun dan Foreign Key Dimensi
        $groupField = $groupBy === 'project' ? 'transactions.project_id' : 'transactions.department_id';

        $rawMutations = DB::table('journal_items')
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->leftJoin('transactions', 'journal_entries.transaction_id', '=', 'transactions.id')
            ->join('accounts', 'journal_items.account_id', '=', 'accounts.id')
            ->where('journal_entries.company_id', $companyId)
            ->whereBetween('journal_entries.date', [$startDate, $endDate])
            ->whereIn('accounts.category', ['Pendapatan', 'Harga Pokok Penjualan', 'HPP', 'Beban', 'Pendapatan Lainnya', 'Beban Lainnya'])
            ->when($tagId, function ($q) use ($tagId) {
                $q->where('transactions.tag_id', $tagId);
            })
            ->select(
                'accounts.id as account_id',
                'accounts.code as account_code',
                'accounts.name as account_name',
                'accounts.category as account_category',
                "{$groupField} as group_fk",
                DB::raw('COALESCE(SUM(journal_items.debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(journal_items.credit), 0) as total_credit')
            )
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.category', "{$groupField}")
            ->get();

        // 3. Susun Matriks Akun dan Nilai per Kolom
        $accountMatrix = [];
        $hasUnassignedData = false;

        foreach ($rawMutations as $row) {
            $isRevenueType = in_array($row->account_category, ['Pendapatan', 'Pendapatan Lainnya']);
            $val = $isRevenueType
                ? ((float)$row->total_credit - (float)$row->total_debit)
                : ((float)$row->total_debit - (float)$row->total_credit);

            if ($row->group_fk === null) {
                $colKey = $unassignedKey;
                if ($val != 0) {
                    $hasUnassignedData = true;
                }
            } else {
                $colKey = ($groupBy === 'project' ? 'proj_' : 'dept_') . $row->group_fk;
            }

            if (!isset($accountMatrix[$row->account_id])) {
                $accountMatrix[$row->account_id] = [
                    'id' => $row->account_id,
                    'code' => $row->account_code,
                    'name' => $row->account_name,
                    'category' => $row->account_category,
                    'values' => [],
                ];
            }

            $accountMatrix[$row->account_id]['values'][$colKey] = ($accountMatrix[$row->account_id]['values'][$colKey] ?? 0) + $val;
        }

        // Jika ada data unassigned (transaksi tanpa dept/proyek) dan kita dalam mode komparasi, tambahkan kolom Umum
        if ($hasUnassignedData && $groupBy !== 'consolidated') {
            $columns[] = [
                'key' => $unassignedKey,
                'id' => null,
                'code' => 'Umum / Lainnya',
                'name' => 'Umum (Tanpa Alokasi)',
            ];
        }

        // Tambahkan Kolom TOTAL di akhir (atau satu-satunya kolom jika konsolidasi / belum ada departemen)
        $columns[] = [
            'key' => 'total',
            'id' => 'total',
            'code' => 'TOTAL',
            'name' => 'Total Konsolidasi',
        ];

        // Hitung total baris per akun
        foreach ($accountMatrix as $accId => &$acc) {
            $rowTotal = 0;
            foreach ($columns as $c) {
                if ($c['key'] !== 'total') {
                    $rowTotal += ($acc['values'][$c['key']] ?? 0);
                }
            }
            $acc['values']['total'] = $rowTotal;
        }
        unset($acc);

        // 4. Kelompokkan Akun ke Kategori Laba Rugi
        $pendapatanList = [];
        $hppList = [];
        $bebanList = [];
        $pendapatanLainnyaList = [];
        $bebanLainnyaList = [];
        $pajakList = [];

        foreach ($accountMatrix as $acc) {
            // Abaikan jika semua kolom 0
            $hasNonZero = false;
            foreach ($acc['values'] as $v) {
                if (round($v, 2) != 0) {
                    $hasNonZero = true;
                    break;
                }
            }
            if (!$hasNonZero) {
                continue;
            }

            if ($acc['category'] === 'Pendapatan') {
                $pendapatanList[] = $acc;
            } elseif (in_array($acc['category'], ['Harga Pokok Penjualan', 'HPP'])) {
                $hppList[] = $acc;
            } elseif ($acc['category'] === 'Beban') {
                $bebanList[] = $acc;
            } elseif ($acc['category'] === 'Pendapatan Lainnya') {
                $pendapatanLainnyaList[] = $acc;
            } elseif ($acc['category'] === 'Beban Lainnya') {
                $isTax = stripos($acc['name'], 'Pajak Penghasilan') !== false
                    || in_array($acc['code'], ['8-80200', '9-90000', '9-90001']);
                if ($isTax) {
                    $pajakList[] = $acc;
                } else {
                    $bebanLainnyaList[] = $acc;
                }
            }
        }

        // 5. Hitung Subtotal per Kolom
        $totalPendapatan = [];
        $totalHpp = [];
        $labaKotor = [];
        $totalBebanOperasional = [];
        $labaOperasional = [];
        $totalPendapatanLainnya = [];
        $totalBebanLainnya = [];
        $totalNonOperasionalNet = [];
        $labaSebelumPajak = [];
        $totalPajak = [];
        $labaSetelahPajak = [];

        foreach ($columns as $col) {
            $ck = $col['key'];

            $pSum = 0;
            foreach ($pendapatanList as $item) {
                $pSum += ($item['values'][$ck] ?? 0);
            }
            $totalPendapatan[$ck] = $pSum;

            $hSum = 0;
            foreach ($hppList as $item) {
                $hSum += ($item['values'][$ck] ?? 0);
            }
            $totalHpp[$ck] = $hSum;

            $labaKotor[$ck] = $totalPendapatan[$ck] - $totalHpp[$ck];

            $bSum = 0;
            foreach ($bebanList as $item) {
                $bSum += ($item['values'][$ck] ?? 0);
            }
            $totalBebanOperasional[$ck] = $bSum;

            $labaOperasional[$ck] = $labaKotor[$ck] - $totalBebanOperasional[$ck];

            $plSum = 0;
            foreach ($pendapatanLainnyaList as $item) {
                $plSum += ($item['values'][$ck] ?? 0);
            }
            $totalPendapatanLainnya[$ck] = $plSum;

            $blSum = 0;
            foreach ($bebanLainnyaList as $item) {
                $blSum += ($item['values'][$ck] ?? 0);
            }
            $totalBebanLainnya[$ck] = $blSum;

            $totalNonOperasionalNet[$ck] = $totalPendapatanLainnya[$ck] - $totalBebanLainnya[$ck];

            $labaSebelumPajak[$ck] = $labaOperasional[$ck] + $totalNonOperasionalNet[$ck];

            $taxSum = 0;
            foreach ($pajakList as $item) {
                $taxSum += ($item['values'][$ck] ?? 0);
            }
            $totalPajak[$ck] = $taxSum;

            $labaSetelahPajak[$ck] = $labaSebelumPajak[$ck] - $totalPajak[$ck];
        }

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'groupBy' => $groupBy,
            'columns' => $columns,
            'pendapatan_list' => $pendapatanList,
            'total_pendapatan' => $totalPendapatan,
            'hpp_list' => $hppList,
            'total_hpp' => $totalHpp,
            'laba_kotor' => $labaKotor,
            'beban_operasional_list' => $bebanList,
            'total_beban_operasional' => $totalBebanOperasional,
            'laba_bersih_operasional' => $labaOperasional,
            'pendapatan_lainnya_list' => $pendapatanLainnyaList,
            'total_pendapatan_lainnya' => $totalPendapatanLainnya,
            'beban_lainnya_list' => $bebanLainnyaList,
            'total_beban_lainnya' => $totalBebanLainnya,
            'total_non_operasional_net' => $totalNonOperasionalNet,
            'laba_sebelum_pajak' => $labaSebelumPajak,
            'pajak_penghasilan_list' => $pajakList,
            'total_pajak' => $totalPajak,
            'laba_setelah_pajak' => $labaSetelahPajak,
            'laba_bersih' => $labaSetelahPajak,
        ];
    }
}
