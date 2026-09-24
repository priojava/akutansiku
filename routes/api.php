<?php

use App\Http\Controllers\Api\V1\AccountApiController;
use App\Http\Controllers\Api\V1\AiApiController;
use App\Http\Controllers\Api\V1\AuthApiController;
use App\Http\Controllers\Api\V1\ReportApiController;
use App\Http\Controllers\Api\V1\TransactionApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // 0. API Docs & OpenAPI Schema
    Route::get('/docs', function () {
        return view('docs.api');
    });
    Route::get('/openapi.json', function () {
        return response()->file(public_path('openapi.json'), ['Content-Type' => 'application/json']);
    });

    // 0.1. Autentikasi API & Token Management (Laravel Sanctum)
    Route::post('/auth/register', [AuthApiController::class, 'register']);
    Route::post('/auth/login', [AuthApiController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthApiController::class, 'me']);
        Route::post('/auth/logout', [AuthApiController::class, 'logout']);
    });

    // 1. Master Accounts (COA) & Data Pendukung
    Route::match(['get', 'post'], '/accounts', [AccountApiController::class, 'index']);
    Route::post('/accounts/initial-balances', [AccountApiController::class, 'updateInitialBalances'])->middleware('subscription.writable');
    
    // Master Kontak (Vendor & Customer / Klien)
    Route::get('/contacts', [AccountApiController::class, 'contacts']);
    Route::post('/contacts', [AccountApiController::class, 'storeContact'])->middleware('subscription.writable');
    Route::get('/vendors', [AccountApiController::class, 'vendors']);
    Route::get('/customers', [AccountApiController::class, 'customers']);
    
    // Master Departemen (Divisi)
    Route::get('/departments', [AccountApiController::class, 'departments']);
    Route::post('/departments', [AccountApiController::class, 'storeDepartment'])->middleware('subscription.writable');
    
    // Master Proyek (Project)
    Route::get('/projects', [AccountApiController::class, 'projects']);
    Route::post('/projects', [AccountApiController::class, 'storeProject'])->middleware('subscription.writable');
    
    // Master Tag / Label
    Route::get('/tags', [AccountApiController::class, 'tags']);
    Route::post('/tags', [AccountApiController::class, 'storeTag'])->middleware('subscription.writable');
    
    // Master Cara Pembayaran & Master Bundle Lengkap
    Route::get('/payment-methods', [AccountApiController::class, 'paymentMethods']);
    Route::get('/master-bundle', [AccountApiController::class, 'masterBundle']);

    // 2. Transaksi Majemuk & Transaksi Umum (Jurnal Otomatis)
    Route::get('/transactions', [TransactionApiController::class, 'index']);
    Route::post('/transactions', [TransactionApiController::class, 'store'])->middleware('subscription.writable');
    Route::get('/transactions/{id}', [TransactionApiController::class, 'show']);

    // 3. Reports (Bisa dipanggil via GET maupun POST dengan parameter periode & company_id)
    Route::match(['get', 'post'], '/dashboard/summary', [ReportApiController::class, 'dashboardSummary']);
    Route::match(['get', 'post'], '/reports/journal', [ReportApiController::class, 'journal']);
    Route::match(['get', 'post'], '/reports/profit-loss', [ReportApiController::class, 'profitAndLoss']);
    Route::match(['get', 'post'], '/reports/balance-sheet', [ReportApiController::class, 'balanceSheet']);
    Route::match(['get', 'post'], '/reports/trial-balance', [ReportApiController::class, 'trialBalance']);
    Route::match(['get', 'post'], '/reports/general-ledger', [ReportApiController::class, 'generalLedger']);
    Route::match(['get', 'post'], '/reports/cash-flow', [ReportApiController::class, 'cashFlow']);

    // 4. AI Journaling Helper
    Route::post('/ai/parse', [AiApiController::class, 'parseNaturalLanguage'])->middleware('subscription.writable');
});

