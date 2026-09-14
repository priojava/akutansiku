<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Company;
use App\Services\FinancialReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected FinancialReportService $reportService
    ) {}

    public function profitAndLoss(Request $request)
    {
        $company = $this->getActiveCompany();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $report = $this->reportService->getProfitAndLoss($company->id, $startDate, $endDate);

        return view('reports.profit_loss', compact('company', 'report', 'startDate', 'endDate'));
    }

    public function trialBalance(Request $request)
    {
        $company = $this->getActiveCompany();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $report = $this->reportService->getTrialBalance($company->id, $startDate, $endDate);

        return view('reports.trial_balance', compact('company', 'report', 'startDate', 'endDate'));
    }

    public function generalLedger(Request $request)
    {
        $company = $this->getActiveCompany();
        $accountId = $request->input('account_id') ? intval($request->input('account_id')) : null;
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $allAccounts = Account::where('company_id', $company->id)->where('is_active', true)->orderBy('code')->get();
        $report = $this->reportService->getGeneralLedger($company->id, $accountId, $startDate, $endDate);

        return view('reports.general_ledger', compact('company', 'allAccounts', 'accountId', 'report', 'startDate', 'endDate'));
    }

    public function cashFlow(Request $request)
    {
        $company = $this->getActiveCompany();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $report = $this->reportService->getCashFlow($company->id, $startDate, $endDate);

        return view('reports.cash_flow', compact('company', 'report', 'startDate', 'endDate'));
    }

    public function operatingExpenses(Request $request)
    {
        $company = $this->getActiveCompany();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $report = $this->reportService->getOperatingExpenses($company->id, $startDate, $endDate);

        return view('reports.operating_expenses', compact('company', 'report', 'startDate', 'endDate'));
    }

    public function balanceSheet(Request $request)
    {
        $company = $this->getActiveCompany();
        $asOfDate = $request->input('as_of_date', $request->input('end_date', Carbon::now()->toDateString()));

        $report = $this->reportService->getBalanceSheet($company->id, $asOfDate);

        return view('reports.balance_sheet', compact('company', 'report', 'asOfDate'));
    }

    public function journal(Request $request)
    {
        $company = $this->getActiveCompany();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $type = $request->input('type');
        $accountId = $request->input('account_id') ? intval($request->input('account_id')) : null;
        $search = $request->input('search');

        $allAccounts = Account::where('company_id', $company->id)->where('is_active', true)->orderBy('code')->get();
        $report = $this->reportService->getJournalReport($company->id, $startDate, $endDate, $type, $accountId, $search);

        return view('reports.journal', compact('company', 'allAccounts', 'accountId', 'type', 'report', 'startDate', 'endDate', 'search'));
    }
}
