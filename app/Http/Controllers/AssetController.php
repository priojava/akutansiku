<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Asset;
use App\Models\AssetDepreciationLog;
use App\Models\AssetType;
use App\Models\Company;
use App\Services\AssetDepreciationService;
use App\Services\JournalEntryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    public function __construct(
        protected JournalEntryService $journalService,
        protected AssetDepreciationService $depreciationService
    ) {}

    public function index(Request $request)
    {
        $company = $this->getActiveCompany();

        // Otomatis seed tipe aset default jika belum ada
        $this->depreciationService->seedDefaultAssetTypes($company->id);

        $query = Asset::with(['assetType', 'assetAccount', 'creditedAccount', 'expenseAccount', 'accumulatedAccount'])
            ->where('company_id', $company->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('asset_type_id')) {
            $query->where('asset_type_id', $request->asset_type_id);
        }

        if ($request->filled('status')) {
            $query->where('depreciation_status', $request->status);
        }

        $assets = $query->orderBy('acquisition_date', 'desc')->get();
        $assetTypes = AssetType::where('company_id', $company->id)->orderBy('name')->get();

        $totalAcquisitionCost = $assets->sum('acquisition_cost');
        $totalAccumulatedDepreciation = $assets->sum('accumulated_depreciation_amount');
        $totalBookValue = $assets->sum(fn($a) => $a->book_value);
        $totalMonthlyDepreciation = $assets->where('depreciation_status', 'active')->sum(fn($a) => $a->monthly_depreciation);

        // Ambil riwayat log penyusutan terbaru
        $recentLogs = AssetDepreciationLog::with(['asset', 'journalEntry', 'creator'])
            ->where('company_id', $company->id)
            ->orderBy('period', 'desc')
            ->orderBy('id', 'desc')
            ->take(15)
            ->get();

        // Periode bulan aktif default untuk form proses depresiasi
        $currentPeriod = Carbon::now()->format('Y-m');

        // Deteksi seluruh periode tertunda (yang lupa diposting)
        $pendingPeriods = $this->depreciationService->getPendingPeriods($company->id);

        return view('assets.index', compact(
            'company',
            'assets',
            'assetTypes',
            'totalAcquisitionCost',
            'totalAccumulatedDepreciation',
            'totalBookValue',
            'totalMonthlyDepreciation',
            'recentLogs',
            'currentPeriod',
            'pendingPeriods'
        ));
    }

    public function create()
    {
        $company = $this->getActiveCompany();

        // Otomatis seed tipe aset default jika belum ada
        $this->depreciationService->seedDefaultAssetTypes($company->id);

        $assetTypes = AssetType::where('company_id', $company->id)->orderBy('name')->get();

        // 1. Akun Asset Tetap (Kategori: Harta Tetap)
        $assetAccounts = Account::where('company_id', $company->id)
            ->whereIn('category', ['Harta Tetap', 'Harta Lainnya', 'Persediaan'])
            ->orderBy('code')
            ->get();
        if ($assetAccounts->isEmpty()) {
            $assetAccounts = Account::where('company_id', $company->id)->where('type', 'Debit')->orderBy('code')->get();
        }

        // 2. Akun Pajak (Opsional)
        $taxAccounts = Account::where('company_id', $company->id)
            ->where(function($q) {
                $q->where('name', 'like', '%pajak%')
                  ->orWhere('name', 'like', '%ppn%')
                  ->orWhere('category', 'like', '%pajak%')
                  ->orWhere('category', 'Kewajiban Lancar Lainnya');
            })
            ->orderBy('code')
            ->get();

        // 3. Akun Dikreditkan (Kas, Bank, atau Hutang)
        $creditedAccounts = Account::where('company_id', $company->id)
            ->whereIn('category', ['Kas & Bank', 'Akun Hutang', 'Kewajiban Lancar Lainnya', 'Modal'])
            ->orderBy('code')
            ->get();

        // 4. Akun Beban Penyusutan (Beban)
        $expenseAccounts = Account::where('company_id', $company->id)
            ->whereIn('category', ['Beban', 'Harga Pokok Penjualan', 'Beban Lainnya'])
            ->orderBy('code')
            ->get();

        // 5. Akun Akumulasi Penyusutan (Depresiasi & Amortisasi)
        $accumulatedAccounts = Account::where('company_id', $company->id)
            ->whereIn('category', ['Depresiasi & Amortisasi', 'Harta Tetap'])
            ->orderBy('code')
            ->get();

        $today = Carbon::now()->toDateString();

        return view('assets.create', compact(
            'company',
            'assetTypes',
            'assetAccounts',
            'taxAccounts',
            'creditedAccounts',
            'expenseAccounts',
            'accumulatedAccounts',
            'today'
        ));
    }

    public function store(Request $request)
    {
        $company = $this->getActiveCompany();

        $validated = $request->validate([
            'asset_type_id' => 'nullable|exists:asset_types,id',
            'acquisition_date' => 'required|date',
            'usage_date' => 'nullable|date',
            'code' => 'required|string|max:100|unique:assets,code,NULL,id,company_id,' . $company->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'asset_account_id' => 'required|exists:accounts,id',
            'acquisition_cost' => 'required|string',
            'tax_account_id' => 'nullable|exists:accounts,id',
            'tax_amount' => 'nullable|string',
            'credited_account_id' => 'required|exists:accounts,id',
            'photo' => 'nullable|image|max:2048',
            'is_depreciated' => 'nullable|boolean',
            'depreciation_method' => 'nullable|string|in:straight_line,declining_balance',
            'useful_life_years' => 'nullable|integer|min:0|max:50',
            'useful_life_months' => 'nullable|integer|min:0|max:11',
            'salvage_value' => 'nullable|string',
            'expense_account_id' => 'nullable|exists:accounts,id',
            'accumulated_depreciation_account_id' => 'nullable|exists:accounts,id',
            'depreciation_end_date' => 'nullable|date',
        ]);

        // Bersihkan format nominal rupiah
        $cost = floatval(str_replace(['.', ','], '', $validated['acquisition_cost']));
        $taxAmount = floatval(str_replace(['.', ','], '', $validated['tax_amount'] ?? 0));
        $salvageValue = floatval(str_replace(['.', ','], '', $validated['salvage_value'] ?? 0));

        // Format kode aset tanpa spasi
        $code = Str::slug(str_replace(' ', '', strtolower($validated['code'])), '');

        // Upload Foto jika ada
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('assets', 'public');
        }

        $isDepreciated = $request->has('is_depreciated') || $request->input('is_depreciated') == 1;
        $usefulYears = intval($validated['useful_life_years'] ?? 0);
        $usefulInputMonths = intval($request->input('useful_life_months', 0));
        $totalUsefulMonths = ($usefulYears * 12) + $usefulInputMonths;

        // Tanggal Mulai Pakai (default ke acquisition_date bila tidak diisi)
        $usageDate = !empty($validated['usage_date']) ? $validated['usage_date'] : ($validated['acquisition_date'] ?? Carbon::now()->toDateString());

        $depEndDate = $validated['depreciation_end_date'] ?? null;
        if ($isDepreciated && !$depEndDate && $totalUsefulMonths > 0) {
            $depEndDate = Carbon::parse($usageDate)->addMonths($totalUsefulMonths)->toDateString();
        }

        $userId = auth()->id() ?? $request->user()?->id;

        $asset = Asset::create([
            'company_id' => $company->id,
            'asset_type_id' => $validated['asset_type_id'] ?? null,
            'code' => $code,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'acquisition_date' => $validated['acquisition_date'],
            'usage_date' => $usageDate,
            'acquisition_cost' => $cost,
            'asset_account_id' => $validated['asset_account_id'],
            'tax_account_id' => $validated['tax_account_id'] ?? null,
            'tax_amount' => $taxAmount,
            'credited_account_id' => $validated['credited_account_id'],
            'photo_path' => $photoPath,
            'is_depreciated' => $isDepreciated,
            'depreciation_method' => $isDepreciated ? ($validated['depreciation_method'] ?? 'straight_line') : null,
            'useful_life_years' => $isDepreciated ? $usefulYears : null,
            'useful_life_months' => $isDepreciated ? $totalUsefulMonths : null,
            'salvage_value' => $isDepreciated ? $salvageValue : 0,
            'expense_account_id' => $isDepreciated ? ($validated['expense_account_id'] ?? null) : null,
            'accumulated_depreciation_account_id' => $isDepreciated ? ($validated['accumulated_depreciation_account_id'] ?? null) : null,
            'accumulated_depreciation_amount' => 0,
            'depreciation_end_date' => $depEndDate,
            'depreciation_status' => $isDepreciated ? 'active' : 'stopped',
            'created_by' => $userId,
        ]);

        // Catat transaksi perolehan aset ke jurnal akuntansi secara otomatis
        if ($cost > 0) {
            try {
                $this->journalService->recordTransaction($company->id, [
                    'date' => $validated['acquisition_date'],
                    'time' => Carbon::now()->format('H:i:s'),
                    'type' => 'expense',
                    'debit_account_id' => $validated['asset_account_id'],
                    'credit_account_id' => $validated['credited_account_id'],
                    'amount' => $cost + $taxAmount,
                    'notes' => "Perolehan Aset Tetap: {$validated['name']} ({$code})",
                ], $userId);
            } catch (\Exception $e) {
                // Abaikan jika jurnal terlewati
            }
        }

        return redirect()->route('assets.index')->with('success', "Aset '{$asset->name}' ({$asset->code}) berhasil disimpan dan dijurnal.");
    }

    /**
     * Preview perhitungan depresiasi bulanan sebelum eksekusi (JSON)
     */
    public function previewDepreciation(Request $request)
    {
        $company = $this->getActiveCompany();
        $period = $request->input('period', Carbon::now()->format('Y-m'));

        $eligible = $this->depreciationService->getEligibleAssets($company->id, $period);

        $items = array_map(function($item) {
            /** @var Asset $a */
            $a = $item['asset'];
            return [
                'id' => $a->id,
                'code' => $a->code,
                'name' => $a->name,
                'type' => $a->assetType?->name ?? 'Umum',
                'acquisition_cost' => $a->acquisition_cost,
                'accumulated_before' => $a->accumulated_depreciation_amount,
                'book_value_before' => $a->book_value,
                'monthly_amount' => $item['monthly_amount'],
                'book_value_after' => max(0, $a->book_value - $item['monthly_amount']),
                'expense_account' => $a->expenseAccount?->name ?? 'Belum Diatur',
                'accumulated_account' => $a->accumulatedAccount?->name ?? 'Belum Diatur',
                'is_already_processed' => $item['is_already_processed'],
                'has_valid_accounts' => $item['has_valid_accounts'],
            ];
        }, $eligible);

        $totalDepreciated = array_sum(array_map(fn($i) => (!$i['is_already_processed'] && $i['has_valid_accounts']) ? $i['monthly_amount'] : 0, $items));

        return response()->json([
            'success' => true,
            'period' => $period,
            'items' => $items,
            'total_amount' => $totalDepreciated,
            'count' => count($items),
        ]);
    }

    /**
     * Eksekusi pencatatan jurnal penyusutan bulanan (Proses Akhir Bulan)
     */
    public function runDepreciation(Request $request)
    {
        $company = $this->getActiveCompany();
        $request->validate([
            'period' => 'required|date_format:Y-m',
        ]);

        $period = $request->input('period');
        $userId = auth()->id() ?? $request->user()?->id;

        $result = $this->depreciationService->executePeriodDepreciation($company->id, $period, $userId);

        if ($result['processed_count'] === 0 && empty($result['errors'])) {
            return back()->with('info', "Semua aset untuk periode {$period} sudah disusutkan sebelumnya atau tidak ada aset yang memenuhi syarat.");
        }

        $msg = "Berhasil memproses depresiasi untuk {$result['processed_count']} aset pada periode {$period} dengan total Rp " . number_format($result['total_depreciated'], 0, ',', '.') . ". Jurnal akuntansi telah diposting otomatis.";
        return back()->with('success', $msg);
    }

    /**
     * Eksekusi seluruh periode tertunda sekaligus (Catch-up Bulk Depreciation)
     */
    public function runBulkDepreciation(Request $request)
    {
        $company = $this->getActiveCompany();
        $userId = auth()->id() ?? $request->user()?->id;

        $result = $this->depreciationService->executeBulkPendingDepreciation($company->id, $userId);

        if ($result['total_periods'] === 0) {
            return back()->with('info', "Tidak ada periode penyusutan tertunda yang perlu diposting.");
        }

        $periodsStr = implode(', ', $result['processed_periods']);
        $msg = "⚡ Berhasil memproses {$result['total_periods']} periode tertunggak ({$periodsStr}) untuk {$result['total_processed_assets']} aset dengan total Rp " . number_format($result['total_depreciated'], 0, ',', '.') . ". Jurnal akuntansi masing-masing akhir bulan telah diposting otomatis.";
        return back()->with('success', $msg);
    }

    /**
     * Batalkan (Rollback) log penyusutan tertentu
     */
    public function rollbackDepreciation(int $logId)
    {
        $company = $this->getActiveCompany();
        $success = $this->depreciationService->rollbackLog($logId, $company->id);

        if ($success) {
            return back()->with('success', "Jurnal penyusutan dan log depresiasi berhasil dibatalkan.");
        }
        return back()->with('error', "Gagal membatalkan penyusutan. Log tidak ditemukan.");
    }

    public function toggleDepreciation(int $id)
    {
        $asset = Asset::findOrFail($id);
        $newStatus = $asset->depreciation_status === 'active' ? 'stopped' : 'active';
        $asset->update(['depreciation_status' => $newStatus]);

        $statusLabel = $newStatus === 'active' ? 'diaktifkan kembali' : 'dihentikan sementara';
        return back()->with('success', "Status depresiasi untuk aset '{$asset->name}' berhasil {$statusLabel}.");
    }

    public function exportExcel()
    {
        $company = $this->getActiveCompany();
        $assets = Asset::with(['assetType', 'assetAccount', 'creditedAccount', 'expenseAccount', 'accumulatedAccount'])
            ->where('company_id', $company->id)
            ->orderBy('code')
            ->get();

        $csv = "NO,KODE,TIPE ASET,NAMA,AKUN ASET TETAP,DESKRIPSI,TGL PEROLEHAN,TGL PAKAI,BIAYA AKUISISI,BEBAN DEPRESIASI/BLN,NILAI BUKU,AKUN DIKREDITKAN,ASET DEPRESIASI,METODE,MASA MANFAAT,AKUN BEBAN PENYUSUTAN,AKUMULASI AKUN PENYUSUTAN,AKUMULASI PENYUSUTAN,BULAN AKHIR PENYUSUTAN,STATUS DEPRESIASI\n";

        $no = 1;
        foreach ($assets as $a) {
            $isDep = $a->is_depreciated ? 'Ya' : 'Tidak';
            $metode = $a->depreciation_method === 'declining_balance' ? 'Saldo Menurun' : ($a->is_depreciated ? 'Garis Lurus' : '-');
            if ($a->useful_life_months) {
                $thn = floor($a->useful_life_months / 12);
                $bln = $a->useful_life_months % 12;
                $masa = trim(($thn > 0 ? "{$thn} Tahun " : '') . ($bln > 0 ? "{$bln} Bulan" : ''));
            } elseif ($a->useful_life_years) {
                $masa = "{$a->useful_life_years} Tahun";
            } else {
                $masa = '-';
            }
            $endDate = $a->depreciation_end_date ? $a->depreciation_end_date->format('M Y') : '-';
            $usageDateStr = $a->usage_date ? $a->usage_date->format('d/m/Y') : $a->acquisition_date->format('d/m/Y');

            $csv .= sprintf(
                "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $no++,
                $a->code,
                str_replace('"', '""', $a->assetType?->name ?? 'Umum'),
                str_replace('"', '""', $a->name),
                str_replace('"', '""', $a->assetAccount?->name ?? '-'),
                str_replace('"', '""', $a->description ?? '-'),
                $a->acquisition_date->format('d/m/Y'),
                $usageDateStr,
                $a->acquisition_cost,
                $a->monthly_depreciation,
                $a->book_value,
                str_replace('"', '""', $a->creditedAccount?->name ?? '-'),
                $isDep,
                $metode,
                $masa,
                str_replace('"', '""', $a->expenseAccount?->name ?? '-'),
                str_replace('"', '""', $a->accumulatedAccount?->name ?? '-'),
                $a->accumulated_depreciation_amount,
                $endDate,
                ucfirst($a->depreciation_status)
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="daftar_aset_' . date('Ymd_His') . '.csv"',
        ]);
    }

    public function destroy(int $id)
    {
        $company = $this->getActiveCompany();
        $asset = Asset::where('company_id', $company->id)->findOrFail($id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($company, $asset) {
            // Hapus log depresiasi terkait
            AssetDepreciationLog::where('asset_id', $asset->id)->delete();

            // Hapus transaksi & jurnal perolehan aset jika ada
            $transactions = \App\Models\Transaction::where('company_id', $company->id)
                ->where('notes', 'like', "%({$asset->code})%")
                ->get();

            foreach ($transactions as $trx) {
                $journalEntries = \App\Models\JournalEntry::where('transaction_id', $trx->id)->get();
                foreach ($journalEntries as $entry) {
                    \App\Models\JournalItem::where('journal_entry_id', $entry->id)->delete();
                    $entry->delete();
                }
                $trx->delete();
            }

            // Hapus jurnal penyusutan aset jika pernah dijalankan
            $deprEntries = \App\Models\JournalEntry::where('company_id', $company->id)
                ->where('notes', 'like', "%Penyusutan Aset%: {$asset->name}%")
                ->get();
            foreach ($deprEntries as $entry) {
                \App\Models\JournalItem::where('journal_entry_id', $entry->id)->delete();
                $entry->delete();
            }

            $asset->delete();
        });

        return back()->with('success', "Aset '{$asset->name}' ({$asset->code}) beserta catatan jurnalnya berhasil dihapus.");
    }
}
