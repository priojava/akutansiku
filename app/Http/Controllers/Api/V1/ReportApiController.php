<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\FinancialReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportApiController extends Controller
{
    public function __construct(
        protected FinancialReportService $reportService
    ) {}

    public function dashboardSummary(Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id') ?: $request->input('company_id', 1);
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $data = $this->reportService->getDashboardSummary($companyId, $startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function profitAndLoss(Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id') ?: $request->input('company_id', 1);
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $data = $this->reportService->getProfitAndLoss($companyId, $startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function trialBalance(Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id') ?: $request->input('company_id', 1);
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $data = $this->reportService->getTrialBalance($companyId, $startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function generalLedger(Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id') ?: $request->input('company_id', 1);
        $accountId = $request->input('account_id') ? intval($request->input('account_id')) : null;
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $data = $this->reportService->getGeneralLedger($companyId, $accountId, $startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function cashFlow(Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id') ?: $request->input('company_id', 1);
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $data = $this->reportService->getCashFlow($companyId, $startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function balanceSheet(Request $request): JsonResponse
    {
        $companyId = $request->header('X-Company-Id') ?: $request->input('company_id', 1);
        $asOfDate = $request->input('as_of_date', $request->input('end_date', Carbon::now()->toDateString()));

        $data = $this->reportService->getBalanceSheet($companyId, $asOfDate);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
}
