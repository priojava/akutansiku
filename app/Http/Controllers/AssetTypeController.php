<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AssetType;
use App\Services\AssetDepreciationService;
use Illuminate\Http\Request;

class AssetTypeController extends Controller
{
    public function __construct(
        protected AssetDepreciationService $depreciationService
    ) {}

    public function index(Request $request)
    {
        $company = $this->getActiveCompany();

        // Otomatis buat tipe aset default jika belum pernah ada
        $this->depreciationService->seedDefaultAssetTypes($company->id);

        $query = AssetType::with(['assetAccount', 'expenseAccount', 'accumulatedAccount', 'assets'])
            ->where('company_id', $company->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $assetTypes = $query->orderBy('name')->get();

        // Ambil pilihan akun untuk dropdown
        $assetAccounts = Account::where('company_id', $company->id)
            ->whereIn('category', ['Harta Tetap', 'Harta Lainnya', 'Persediaan'])
            ->orderBy('code')
            ->get();
        if ($assetAccounts->isEmpty()) {
            $assetAccounts = Account::where('company_id', $company->id)->where('type', 'Debit')->orderBy('code')->get();
        }

        $expenseAccounts = Account::where('company_id', $company->id)
            ->whereIn('category', ['Beban', 'Harga Pokok Penjualan', 'Beban Lainnya'])
            ->orderBy('code')
            ->get();

        $accumulatedAccounts = Account::where('company_id', $company->id)
            ->whereIn('category', ['Depresiasi & Amortisasi', 'Harta Tetap'])
            ->orderBy('code')
            ->get();

        return view('master.asset_types.index', compact(
            'company',
            'assetTypes',
            'assetAccounts',
            'expenseAccounts',
            'accumulatedAccounts'
        ));
    }

    public function store(Request $request)
    {
        $company = $this->getActiveCompany();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'useful_life_years' => 'nullable|integer|min:0|max:50',
            'asset_account_id' => 'nullable|exists:accounts,id',
            'expense_account_id' => 'nullable|exists:accounts,id',
            'accumulated_account_id' => 'nullable|exists:accounts,id',
            'is_depreciated' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $isDepreciated = $request->has('is_depreciated') || $request->input('is_depreciated') == 1;

        AssetType::create([
            'company_id' => $company->id,
            'name' => $validated['name'],
            'code' => $validated['code'] ? strtoupper(trim($validated['code'])) : null,
            'useful_life_years' => $isDepreciated ? intval($validated['useful_life_years'] ?? 4) : 0,
            'asset_account_id' => $validated['asset_account_id'] ?? null,
            'expense_account_id' => $isDepreciated ? ($validated['expense_account_id'] ?? null) : null,
            'accumulated_account_id' => $isDepreciated ? ($validated['accumulated_account_id'] ?? null) : null,
            'is_depreciated' => $isDepreciated,
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', "Tipe Aset '{$validated['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, int $id)
    {
        $company = $this->getActiveCompany();
        $assetType = AssetType::where('company_id', $company->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'useful_life_years' => 'nullable|integer|min:0|max:50',
            'asset_account_id' => 'nullable|exists:accounts,id',
            'expense_account_id' => 'nullable|exists:accounts,id',
            'accumulated_account_id' => 'nullable|exists:accounts,id',
            'is_depreciated' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $isDepreciated = $request->has('is_depreciated') || $request->input('is_depreciated') == 1;

        $assetType->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ? strtoupper(trim($validated['code'])) : null,
            'useful_life_years' => $isDepreciated ? intval($validated['useful_life_years'] ?? 4) : 0,
            'asset_account_id' => $validated['asset_account_id'] ?? null,
            'expense_account_id' => $isDepreciated ? ($validated['expense_account_id'] ?? null) : null,
            'accumulated_account_id' => $isDepreciated ? ($validated['accumulated_account_id'] ?? null) : null,
            'is_depreciated' => $isDepreciated,
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', "Tipe Aset '{$assetType->name}' berhasil diperbarui.");
    }

    public function destroy(int $id)
    {
        $company = $this->getActiveCompany();
        $assetType = AssetType::where('company_id', $company->id)->findOrFail($id);

        if ($assetType->assets()->exists()) {
            return back()->with('error', "Tipe Aset '{$assetType->name}' tidak dapat dihapus karena sedang digunakan oleh {$assetType->assets()->count()} aset tetap.");
        }

        $assetType->delete();
        return back()->with('success', "Tipe Aset '{$assetType->name}' berhasil dihapus.");
    }
}
