<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Asset;
use App\Models\Company;
use App\Services\JournalEntryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    public function __construct(
        protected JournalEntryService $journalService
    ) {}

    public function index(Request $request)
    {
        $company = $this->getActiveCompany();

        $query = Asset::with(['assetAccount', 'creditedAccount', 'expenseAccount', 'accumulatedAccount'])
            ->where('company_id', $company->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $assets = $query->orderBy('acquisition_date', 'desc')->get();

        $totalAcquisitionCost = $assets->sum('acquisition_cost');
        $totalAccumulatedDepreciation = $assets->sum('accumulated_depreciation_amount');
        $totalBookValue = $assets->sum(fn($a) => $a->book_value);

        return view('assets.index', compact(
            'company',
            'assets',
            'totalAcquisitionCost',
            'totalAccumulatedDepreciation',
            'totalBookValue'
        ));
    }

    public function create()
    {
        $company = $this->getActiveCompany();

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
            'acquisition_date' => 'required|date',
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

        // Format kode aset tanpa spasi dan huruf kecil
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

        $depEndDate = $validated['depreciation_end_date'] ?? null;
        if ($isDepreciated && !$depEndDate && $totalUsefulMonths > 0) {
            $depEndDate = Carbon::parse($validated['acquisition_date'])->addMonths($totalUsefulMonths)->toDateString();
        }

        $userId = auth()->id() ?? $request->user()?->id;

        $asset = Asset::create([
            'company_id' => $company->id,
            'code' => $code,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'acquisition_date' => $validated['acquisition_date'],
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
                // Abaikan jika pencatatan jurnal terlewati, aset tetap tersimpan
            }
        }

        return redirect()->route('assets.index')->with('success', "Aset '{$asset->name}' ({$asset->code}) berhasil disimpan dan dijurnal.");
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
        $assets = Asset::with(['assetAccount', 'creditedAccount', 'expenseAccount', 'accumulatedAccount'])
            ->where('company_id', $company->id)
            ->orderBy('code')
            ->get();

        $csv = "NO,KODE,NAMA,AKUN ASET TETAP,DESKRIPSI,TANGGAL AKUISISI,BIAYA AKUISISI,NILAI BUKU,AKUN DIKREDITKAN,ASET DEPRESIASI,METODE,MASA MANFAAT (TAHUN),AKUN PENYUSUTAN,AKUMULASI AKUN PENYUSUTAN,AKUMULASI PENYUSUTAN,BULAN AKHIR AKUMULASI PENYUSUTAN,STATUS DEPRESIASI\n";

        $no = 1;
        foreach ($assets as $a) {
            $isDep = $a->is_depreciated ? 'Ya' : 'Tidak';
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

            $csv .= sprintf(
                "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $no++,
                $a->code,
                str_replace('"', '""', $a->name),
                str_replace('"', '""', $a->assetAccount?->name ?? '-'),
                str_replace('"', '""', $a->description ?? '-'),
                $a->acquisition_date->format('d/m/Y'),
                $a->acquisition_cost,
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
                ->where('notes', 'like', "%Penyusutan Aset: {$asset->name}%")
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

