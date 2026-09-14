<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Transaction;
use App\Models\User;
use App\Services\FinancialReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected FinancialReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $company = $this->getActiveCompany();
        $currentUser = auth()->user() ?? User::first();
        $currentRole = $currentUser ? $currentUser->getRoleInCompany($company->id) : 'admin';

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        if ($currentRole === 'cashier') {
            $today = Carbon::today()->toDateString();
            $userId = $currentUser?->id;

            $todayIncome = (float) Transaction::where('company_id', $company->id)
                ->where('created_by', $userId)
                ->whereDate('date', $today)
                ->where('type', 'income')
                ->sum('amount');

            $todayExpense = (float) Transaction::where('company_id', $company->id)
                ->where('created_by', $userId)
                ->whereDate('date', $today)
                ->where('type', 'expense')
                ->sum('amount');

            $todayCount = Transaction::where('company_id', $company->id)
                ->where('created_by', $userId)
                ->whereDate('date', $today)
                ->count();

            $monthIncome = (float) Transaction::where('company_id', $company->id)
                ->where('created_by', $userId)
                ->whereMonth('date', Carbon::now()->month)
                ->whereYear('date', Carbon::now()->year)
                ->where('type', 'income')
                ->sum('amount');

            $monthCount = Transaction::where('company_id', $company->id)
                ->where('created_by', $userId)
                ->whereMonth('date', Carbon::now()->month)
                ->whereYear('date', Carbon::now()->year)
                ->count();

            // Saldo Kas di Laci / Posisi Kas Kasir Hari Ini
            $drawerCash = $todayIncome - $todayExpense;

            // 7 Hari Tren Transaksi Kasir Ini
            $chartDays = [];
            $chartIncomes = [];
            $chartExpenses = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = Carbon::today()->subDays($i);
                $chartDays[] = $d->isoFormat('D MMM');
                $chartIncomes[] = (float) Transaction::where('company_id', $company->id)
                    ->where('created_by', $userId)
                    ->whereDate('date', $d->toDateString())
                    ->where('type', 'income')
                    ->sum('amount');
                $chartExpenses[] = (float) Transaction::where('company_id', $company->id)
                    ->where('created_by', $userId)
                    ->whereDate('date', $d->toDateString())
                    ->where('type', 'expense')
                    ->sum('amount');
            }

            // 10 Transaksi Terakhir yang Diinput Kasir Ini
            $recentTransactions = Transaction::with(['debitAccount', 'creditAccount', 'contact'])
                ->where('company_id', $company->id)
                ->where('created_by', $userId)
                ->orderByDesc('date')
                ->orderByDesc('time')
                ->take(10)
                ->get();

            $cashierData = [
                'today_income' => $todayIncome,
                'today_income_fmt' => 'Rp ' . number_format($todayIncome, 0, ',', '.'),
                'today_expense' => $todayExpense,
                'today_expense_fmt' => 'Rp ' . number_format($todayExpense, 0, ',', '.'),
                'drawer_cash' => $drawerCash,
                'drawer_cash_fmt' => 'Rp ' . number_format($drawerCash, 0, ',', '.'),
                'today_count' => $todayCount,
                'month_income' => $monthIncome,
                'month_income_fmt' => 'Rp ' . number_format($monthIncome, 0, ',', '.'),
                'month_count' => $monthCount,
                'chart_days' => $chartDays,
                'chart_incomes' => $chartIncomes,
                'chart_expenses' => $chartExpenses,
                'recent_transactions' => $recentTransactions,
            ];

            return view('dashboard.index', compact('company', 'currentRole', 'currentUser', 'cashierData', 'startDate', 'endDate'));
        }

        $summary = $this->reportService->getDashboardSummary($company->id, $startDate, $endDate);

        return view('dashboard.index', compact('company', 'summary', 'startDate', 'endDate', 'currentRole', 'currentUser'));
    }

    public function switchCompany(Request $request)
    {
        $companies = Company::all();
        return view('company.index', compact('companies'));
    }
}

