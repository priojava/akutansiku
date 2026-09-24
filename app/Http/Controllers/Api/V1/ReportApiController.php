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
        $companyId = $this->getCompanyId($request);
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
        $companyId = $this->getCompanyId($request);
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
        $companyId = $this->getCompanyId($request);
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
        $companyId = $this->getCompanyId($request);
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
        $companyId = $this->getCompanyId($request);
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
        $companyId = $this->getCompanyId($request);
        $asOfDate = $request->input('as_of_date', $request->input('end_date', Carbon::now()->toDateString()));

        $data = $this->reportService->getBalanceSheet($companyId, $asOfDate);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function journal(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $type = $request->input('type');
        $accountId = $request->input('account_id') ? intval($request->input('account_id')) : null;
        $search = $request->input('search');
        $tagId = $request->input('tag_id') ? intval($request->input('tag_id')) : null;

        $data = $this->reportService->getJournalReport($companyId, $startDate, $endDate, $type, $accountId, $search, $tagId);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
}
