<?php

use App\Http\Controllers\Api\V1\AccountApiController;
use App\Http\Controllers\Api\V1\AiApiController;
use App\Http\Controllers\Api\V1\ReportApiController;
use App\Http\Controllers\Api\V1\TransactionApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // 1. Master Accounts & Data
    Route::get('/accounts', [AccountApiController::class, 'index']);
    Route::post('/accounts/initial-balances', [AccountApiController::class, 'updateInitialBalances']);
    Route::get('/contacts', [AccountApiController::class, 'contacts']);
    Route::get('/payment-methods', [AccountApiController::class, 'paymentMethods']);

    // 2. Transactions & Journal
    Route::get('/transactions', [TransactionApiController::class, 'index']);
    Route::post('/transactions', [TransactionApiController::class, 'store']);
    Route::get('/transactions/{id}', [TransactionApiController::class, 'show']);

    // 3. Reports
    Route::get('/dashboard/summary', [ReportApiController::class, 'dashboardSummary']);
    Route::get('/reports/profit-loss', [ReportApiController::class, 'profitAndLoss']);
    Route::get('/reports/balance-sheet', [ReportApiController::class, 'balanceSheet']);
    Route::get('/reports/trial-balance', [ReportApiController::class, 'trialBalance']);
    Route::get('/reports/general-ledger', [ReportApiController::class, 'generalLedger']);
    Route::get('/reports/cash-flow', [ReportApiController::class, 'cashFlow']);

    // 4. AI Journaling Helper
    Route::post('/ai/parse', [AiApiController::class, 'parseNaturalLanguage']);
});
