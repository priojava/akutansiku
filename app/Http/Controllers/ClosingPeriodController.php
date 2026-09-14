<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ClosingPeriod;
use App\Models\Company;
use App\Services\ClosingPeriodService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClosingPeriodController extends Controller
{
    protected ClosingPeriodService $closingService;

    public function __construct(ClosingPeriodService $closingService)
    {
        $this->closingService = $closingService;
    }

    /**
     * Halaman Daftar Tutup Buku (Screenshot 2)
     */
    public function index(Request $request)
    {
        $company = $this->getActiveCompany();
        $closingPeriods = ClosingPeriod::with(['creator', 'retainedEarningsAccount', 'journalEntry.items.account'])
            ->where('company_id', $company->id)
            ->latest('closing_date')
            ->get();

        return view('closing.index', compact('company', 'closingPeriods'));
    }

    /**
     * Halaman Form Buat Tutup Buku Baru (Screenshot 1)
     */
    public function create(Request $request)
    {
        $company = $this->getActiveCompany();
        
        // Tanggal tutup buku (default akhir bulan sebelumnya atau hari ini)
        $closingDate = $request->get('closing_date', Carbon::now()->endOfMonth()->toDateString());
        
        // Ambil data kertas kerja / worksheet matriks 6 kolom
        $worksheet = $this->closingService->getWorksheet($company->id, $closingDate);

        // Akun Beban Pajak (Hanya akun beban / pengeluaran)
        $taxExpenseAccounts = Account::where('company_id', $company->id)
            ->where(function($q) {
                $q->whereIn('category', ['Beban', 'Beban Lainnya'])
                  ->orWhere('code', 'like', '6-%')
                  ->orWhere('code', 'like', '8-%');
            })
            ->where('code', 'not like', '1-%')
            ->where('code', 'not like', '2-%')
            ->orderBy('code')
            ->get();

        // Akun Hutang Pajak (Hanya akun kewajiban / hutang)
        $taxPayableAccounts = Account::where('company_id', $company->id)
            ->where(function($q) {
                $q->whereIn('category', ['Akun Hutang', 'Liabilitas Jangka Pendek Lainnya', 'Kewajiban Lancar Lainnya'])
                  ->orWhere('code', 'like', '2-%');
            })
            ->where('code', 'like', '2-%')
            ->orderBy('code')
            ->get();

        // Akun Ekuitas / Laba Ditahan
        $equityAccounts = Account::where('company_id', $company->id)
            ->where(function($q) {
                $q->whereIn('category', ['Modal', 'Ekuitas'])->orWhere('code', 'like', '3-%');
            })
            ->orderBy('code')
            ->get();

        // Default selections
        $defaultTaxExpense = $taxExpenseAccounts->firstWhere('code', '8-80200')
            ?? $taxExpenseAccounts->first(fn($a) => str_contains(strtolower($a->name), 'pajak'))
            ?? $taxExpenseAccounts->first();

        $defaultTaxPayable = $taxPayableAccounts->firstWhere('code', '2-20505')
            ?? $taxPayableAccounts->firstWhere('code', '2-20504')
            ?? $taxPayableAccounts->first(fn($a) => str_contains(strtolower($a->name), 'pajak'))
            ?? $taxPayableAccounts->first();

        $defaultEquity = $equityAccounts->firstWhere('code', '3-30100')
            ?? $equityAccounts->firstWhere('name', 'Laba Ditahan') 
            ?? $equityAccounts->first();

        return view('closing.create', compact(
            'company',
            'closingDate',
            'worksheet',
            'taxExpenseAccounts',
            'taxPayableAccounts',
            'equityAccounts',
            'defaultTaxExpense',
            'defaultTaxPayable',
            'defaultEquity'
        ));
    }

    /**
     * Simpan & Eksekusi Tutup Buku
     */
    public function store(Request $request)
    {
        $company = $this->getActiveCompany();

        $request->validate([
            'closing_date' => 'required|date',
            'retained_earnings_account_id' => 'required|exists:accounts,id',
            'tax_expense_account_id' => 'nullable|exists:accounts,id',
            'tax_payable_account_id' => 'nullable|exists:accounts,id',
        ]);

        $taxAmountRaw = (string) $request->input('tax_amount', 0);
        $taxAmountClean = str_replace(['.', ','], '', $taxAmountRaw);
        $taxAmount = floatval($taxAmountClean);

        $closingDate = $request->input('closing_date');
        $carbonDate = Carbon::parse($closingDate)->locale('id');
        $monthName = $carbonDate->translatedFormat('F Y');

        $data = [
            'closing_date' => $closingDate,
            'period_name' => $monthName,
            'notes' => $request->input('notes') ?: 'Tutup Buku Periode ' . $monthName,
            'tax_expense_account_id' => $request->input('tax_expense_account_id'),
            'tax_amount' => $taxAmount,
            'tax_payable_account_id' => $request->input('tax_payable_account_id'),
            'retained_earnings_account_id' => $request->input('retained_earnings_account_id'),
        ];

        try {
            $closing = $this->closingService->executeClosing($company, $data, Auth::id() ?? 1);
            return redirect()->route('closing.index')->with('success', 'Tutup buku periode ' . $closing->period_name . ' berhasil dieksekusi dan jurnal penutup telah dibuat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses tutup buku: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Detail Tutup Buku
     */
    public function show(int $id)
    {
        $company = $this->getActiveCompany();
        $closingPeriod = ClosingPeriod::with(['creator', 'taxExpenseAccount', 'taxPayableAccount', 'retainedEarningsAccount', 'journalEntry.items.account'])
            ->where('company_id', $company->id)
            ->findOrFail($id);

        return view('closing.show', compact('company', 'closingPeriod'));
    }

    /**
     * Batalkan / Hapus Tutup Buku
     */
    public function destroy(int $id)
    {
        $company = $this->getActiveCompany();
        $closingPeriod = ClosingPeriod::where('company_id', $company->id)->findOrFail($id);

        if ($closingPeriod->journal_entry_id) {
            \App\Models\JournalItem::where('journal_entry_id', $closingPeriod->journal_entry_id)->delete();
            \App\Models\JournalEntry::where('id', $closingPeriod->journal_entry_id)->delete();
        }

        $closingPeriod->delete();

        return redirect()->route('closing.index')->with('success', 'Data tutup buku dan jurnal penutup berhasil dibatalkan/dihapus.');
    }
}
