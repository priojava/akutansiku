<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Asset;
use App\Models\AssetDepreciationLog;
use App\Models\AssetType;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssetDepreciationService
{
    public function __construct(
        protected JournalEntryService $journalService
    ) {}

    /**
     * Dapatkan daftar aset yang memenuhi syarat disusutkan pada periode YYYY-MM tertentu
     */
    public function getEligibleAssets(int $companyId, string $period): array
    {
        $periodDate = Carbon::createFromFormat('Y-m', $period)->endOfMonth();

        $assets = Asset::with(['assetAccount', 'expenseAccount', 'accumulatedAccount', 'assetType'])
            ->where('company_id', $companyId)
            ->where('is_depreciated', true)
            ->where('depreciation_status', 'active')
            ->where(function ($q) use ($periodDate) {
                $q->where(function ($sub) use ($periodDate) {
                    $sub->whereNotNull('usage_date')
                        ->where('usage_date', '<=', $periodDate->toDateString());
                })->orWhere(function ($sub) use ($periodDate) {
                    $sub->whereNull('usage_date')
                        ->where('acquisition_date', '<=', $periodDate->toDateString());
                });
            })
            ->get();

        $alreadyLoggedAssetIds = AssetDepreciationLog::where('company_id', $companyId)
            ->where('period', $period)
            ->pluck('asset_id')
            ->toArray();

        $eligible = [];
        foreach ($assets as $asset) {
            $isAlreadyProcessed = in_array($asset->id, $alreadyLoggedAssetIds);
            $maxDepreciable = max(0, $asset->acquisition_cost - $asset->salvage_value);
            $remainingToDepreciate = max(0, $maxDepreciable - $asset->accumulated_depreciation_amount);

            if ($remainingToDepreciate <= 0) {
                continue; // Sudah habis masa manfaatnya
            }

            $monthlyAmount = $asset->monthly_depreciation;
            $depreciationAmount = min($monthlyAmount, $remainingToDepreciate);

            $eligible[] = [
                'asset' => $asset,
                'monthly_amount' => $depreciationAmount,
                'remaining_amount' => $remainingToDepreciate,
                'is_already_processed' => $isAlreadyProcessed,
                'has_valid_accounts' => !empty($asset->expense_account_id) && !empty($asset->accumulated_depreciation_account_id),
            ];
        }

        return $eligible;
    }

    /**
     * Eksekusi pencatatan jurnal penyusutan bulanan untuk seluruh aset yang memenuhi syarat
     */
    public function executePeriodDepreciation(int $companyId, string $period, ?int $userId = null): array
    {
        $eligibleList = $this->getEligibleAssets($companyId, $period);
        $periodEnd = Carbon::createFromFormat('Y-m', $period)->endOfMonth();
        $dateStr = $periodEnd->toDateString();

        $processedCount = 0;
        $totalDepreciated = 0;
        $skippedCount = 0;
        $errors = [];

        DB::transaction(function () use ($companyId, $period, $dateStr, $userId, $eligibleList, &$processedCount, &$totalDepreciated, &$skippedCount, &$errors) {
            foreach ($eligibleList as $item) {
                /** @var Asset $asset */
                $asset = $item['asset'];
                $amount = $item['monthly_amount'];

                if ($item['is_already_processed']) {
                    $skippedCount++;
                    continue;
                }

                if (!$item['has_valid_accounts'] || $amount <= 0) {
                    $skippedCount++;
                    $errors[] = "Aset '{$asset->name}' dilewati karena belum memiliki akun beban atau akun akumulasi penyusutan.";
                    continue;
                }

                $bookValueBefore = $asset->book_value;
                $newAccumulated = $asset->accumulated_depreciation_amount + $amount;
                $bookValueAfter = max(0, $asset->acquisition_cost - $newAccumulated);

                // 1. Buat Transaksi & Jurnal Akuntansi via JournalEntryService
                $trx = $this->journalService->recordTransaction($companyId, [
                    'date' => $dateStr,
                    'time' => '23:59:59',
                    'type' => 'expense',
                    'debit_account_id' => $asset->expense_account_id,
                    'credit_account_id' => $asset->accumulated_depreciation_account_id,
                    'amount' => $amount,
                    'notes' => "Penyusutan Aset Bulanan ({$period}): {$asset->name} [{$asset->code}]",
                ], $userId);

                $journal = $trx->journalEntry;

                // 2. Update Nilai Akumulasi & Status Aset
                $isCompleted = ($newAccumulated >= ($asset->acquisition_cost - $asset->salvage_value));
                $asset->update([
                    'accumulated_depreciation_amount' => $newAccumulated,
                    'depreciation_status' => $isCompleted ? 'completed' : 'active',
                ]);

                // 3. Catat Log Penyusutan
                AssetDepreciationLog::create([
                    'company_id' => $companyId,
                    'asset_id' => $asset->id,
                    'journal_entry_id' => $journal?->id,
                    'period' => $period,
                    'depreciation_date' => $dateStr,
                    'depreciation_amount' => $amount,
                    'book_value_before' => $bookValueBefore,
                    'book_value_after' => $bookValueAfter,
                    'notes' => "Depresiasi metode garis lurus untuk periode {$period}",
                    'created_by' => $userId,
                ]);

                $processedCount++;
                $totalDepreciated += $amount;
            }
        });

        return [
            'success' => true,
            'period' => $period,
            'processed_count' => $processedCount,
            'total_depreciated' => $totalDepreciated,
            'skipped_count' => $skippedCount,
            'errors' => $errors,
        ];
    }

    /**
     * Cari seluruh periode bulan yang belum diposting penyusutannya (Tertunggak)
     */
    public function getPendingPeriods(int $companyId): array
    {
        $earliestDate = Asset::where('company_id', $companyId)
            ->where('is_depreciated', true)
            ->where('depreciation_status', 'active')
            ->selectRaw('MIN(COALESCE(usage_date, acquisition_date)) as min_date')
            ->value('min_date');

        if (!$earliestDate) {
            return [];
        }

        $startDate = Carbon::parse($earliestDate)->startOfMonth();
        $endDate = Carbon::now()->startOfMonth();

        if ($startDate->gt($endDate)) {
            $startDate = $endDate->copy();
        }

        $pending = [];
        $cursor = $startDate->copy();

        while ($cursor->lte($endDate)) {
            $period = $cursor->format('Y-m');
            $eligible = $this->getEligibleAssets($companyId, $period);

            // Filter yang belum diposting dan punya akun valid
            $unprocessed = array_filter($eligible, fn($item) => !$item['is_already_processed'] && $item['has_valid_accounts'] && $item['monthly_amount'] > 0);

            if (!empty($unprocessed)) {
                $totalAmount = array_sum(array_column($unprocessed, 'monthly_amount'));
                $pending[] = [
                    'period' => $period,
                    'period_label' => $cursor->translatedFormat('F Y'),
                    'pending_count' => count($unprocessed),
                    'total_amount' => $totalAmount,
                    'items' => array_values($unprocessed),
                ];
            }

            $cursor->addMonth();
        }

        return $pending;
    }

    /**
     * Eksekusi seluruh periode tertunda sekaligus secara berurutan
     */
    public function executeBulkPendingDepreciation(int $companyId, ?int $userId = null): array
    {
        $pendingPeriods = $this->getPendingPeriods($companyId);
        $totalProcessed = 0;
        $totalDepreciated = 0;
        $processedPeriods = [];

        foreach ($pendingPeriods as $p) {
            $res = $this->executePeriodDepreciation($companyId, $p['period'], $userId);
            if ($res['processed_count'] > 0) {
                $totalProcessed += $res['processed_count'];
                $totalDepreciated += $res['total_depreciated'];
                $processedPeriods[] = $p['period'];
            }
        }

        return [
            'success' => true,
            'total_periods' => count($processedPeriods),
            'processed_periods' => $processedPeriods,
            'total_processed_assets' => $totalProcessed,
            'total_depreciated' => $totalDepreciated,
        ];
    }

    /**
     * Batalkan (Rollback) satu log penyusutan tertentu
     */
    public function rollbackLog(int $logId, int $companyId): bool
    {
        $log = AssetDepreciationLog::with('asset')->where('company_id', $companyId)->find($logId);
        if (!$log) return false;

        DB::transaction(function () use ($log) {
            $asset = $log->asset;

            // Kurangi akumulasi depresiasi pada aset
            if ($asset) {
                $newAccumulated = max(0, $asset->accumulated_depreciation_amount - $log->depreciation_amount);
                $asset->update([
                    'accumulated_depreciation_amount' => $newAccumulated,
                    'depreciation_status' => 'active',
                ]);
            }

            // Hapus Jurnal & Transaksi terkait
            if ($log->journal_entry_id) {
                $journal = JournalEntry::find($log->journal_entry_id);
                if ($journal) {
                    $trxId = $journal->transaction_id;
                    JournalItem::where('journal_entry_id', $journal->id)->delete();
                    $journal->delete();

                    if ($trxId) {
                        Transaction::where('id', $trxId)->delete();
                    }
                }
            }

            $log->delete();
        });

        return true;
    }

    /**
     * Seed atau inisialisasi master tipe aset bawaan untuk perusahaan
     */
    public function seedDefaultAssetTypes(int $companyId): void
    {
        $existingCount = AssetType::where('company_id', $companyId)->count();
        if ($existingCount > 0) return;

        // Ambil ID akun yang relevan
        $findAcc = fn($code) => Account::where('company_id', $companyId)->where('code', $code)->value('id');

        $types = [
            [
                'name' => 'Kendaraan',
                'code' => 'KND',
                'useful_life_years' => 4,
                'asset_account_id' => $findAcc('1-10703'),
                'expense_account_id' => $findAcc('6-60502'),
                'accumulated_account_id' => $findAcc('1-10753'),
                'is_depreciated' => true,
                'description' => 'Mobil operasional, motor, truk armada distribusi',
            ],
            [
                'name' => 'Bangunan & Gedung',
                'code' => 'BGN',
                'useful_life_years' => 20,
                'asset_account_id' => $findAcc('1-10701'),
                'expense_account_id' => $findAcc('6-60500'),
                'accumulated_account_id' => $findAcc('1-10751'),
                'is_depreciated' => true,
                'description' => 'Gedung kantor, ruko, gudang penyimpanan',
            ],
            [
                'name' => 'Mesin & Peralatan Pabrik',
                'code' => 'MSN',
                'useful_life_years' => 8,
                'asset_account_id' => $findAcc('1-10704'),
                'expense_account_id' => $findAcc('6-60503'),
                'accumulated_account_id' => $findAcc('1-10754'),
                'is_depreciated' => true,
                'description' => 'Mesin produksi, alat berat, generator, instalasi',
            ],
            [
                'name' => 'Peralatan & Inventaris Kantor',
                'code' => 'PLT',
                'useful_life_years' => 4,
                'asset_account_id' => $findAcc('1-10705'),
                'expense_account_id' => $findAcc('6-60504'),
                'accumulated_account_id' => $findAcc('1-10755'),
                'is_depreciated' => true,
                'description' => 'Komputer, laptop, printer, AC, meja kursi kantor',
            ],
            [
                'name' => 'Tanah',
                'code' => 'TNH',
                'useful_life_years' => 0,
                'asset_account_id' => $findAcc('1-10700'),
                'expense_account_id' => null,
                'accumulated_account_id' => null,
                'is_depreciated' => false,
                'description' => 'Lahan tanah usaha (tidak mengalami penyusutan nilai)',
            ],
        ];

        foreach ($types as $t) {
            AssetType::create(array_merge(['company_id' => $companyId], $t));
        }
    }
}
