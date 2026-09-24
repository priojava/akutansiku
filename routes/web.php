<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// 0. Autentikasi Login, Register & Logout
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/login/quick/{role}', [AuthController::class, 'quickLogin'])->name('login.quick');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Google OAuth 2.0 Single Sign-On
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');


// Redirect home to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Panduan Presentasi Lengkap
Route::get('/panduan', function () {
    $company = \App\Models\Company::first();
    if (session()->has('active_company_id')) {
        $company = \App\Models\Company::find(session('active_company_id')) ?? $company;
    }
    return view('guide.index', compact('company'));
})->name('panduan');

Route::get('/panduan/cetak', function () {
    return response()->file(public_path('PANDUAN_PRESENTASI_LENGKAP.html'));
})->name('panduan.cetak');

// Dokumentasi REST API Interaktif & Developer Portal
Route::get('/docs/api', function () {
    return view('docs.api');
})->name('docs.api');
Route::get('/api/docs', function () {
    return view('docs.api');
});

// 1. Dashboard & Multi-Company Management
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::middleware('role:admin,accountant')->group(function () {
    Route::get('/company/switch', [CompanyController::class, 'index'])->name('company.switch');
    Route::post('/company/store', [CompanyController::class, 'store'])->name('company.store');
    Route::post('/company/switch/{id}', [CompanyController::class, 'switch'])->name('company.switch.post');
    Route::put('/company/{id}', [CompanyController::class, 'update'])->name('company.update');
    Route::delete('/company/{id}', [CompanyController::class, 'destroy'])->name('company.destroy');
});

// 2. Transaksi
Route::prefix('transactions')->name('transactions.')->group(function () {
    Route::get('/history', [TransactionController::class, 'history'])->name('history');

    Route::middleware('subscription.writable')->group(function () {
        Route::get('/create', [TransactionController::class, 'create'])->name('create');
        Route::post('/store', [TransactionController::class, 'store'])->name('store');
        Route::post('/ai-parse', [TransactionController::class, 'aiParse'])->name('ai_parse');
        Route::delete('/{id}', [TransactionController::class, 'destroy'])->name('destroy');
    });
});

// 3. Master Data
Route::prefix('master')->name('master.')->middleware('role:admin,accountant,auditor')->group(function () {
    Route::get('/accounts', [MasterDataController::class, 'accounts'])->name('accounts');
    Route::post('/accounts', [MasterDataController::class, 'storeAccount'])->name('accounts.store');
    Route::post('/accounts/initial-balances', [MasterDataController::class, 'updateInitialBalances'])->name('accounts.initial_balances');
    Route::post('/accounts/toggle-lock', [MasterDataController::class, 'toggleLockInitialBalances'])->name('accounts.toggle_lock');
    Route::delete('/accounts/{id}', [MasterDataController::class, 'destroyAccount'])->name('accounts.destroy');
    Route::post('/accounts/{id}/toggle-active', [MasterDataController::class, 'toggleActiveAccount'])->name('accounts.toggle_active');
    Route::get('/contacts', [MasterDataController::class, 'contacts'])->name('contacts');
    Route::post('/contacts', [MasterDataController::class, 'storeContact'])->name('contacts.store');
    Route::get('/payment-methods', [MasterDataController::class, 'paymentMethods'])->name('payment_methods');
    Route::post('/payment-methods', [MasterDataController::class, 'storePaymentMethod'])->name('payment_methods.store');
    Route::get('/taxes', [MasterDataController::class, 'taxes'])->name('taxes');
    Route::post('/taxes', [MasterDataController::class, 'storeTax'])->name('taxes.store');
    Route::put('/taxes/{id}', [MasterDataController::class, 'updateTax'])->name('taxes.update');
    Route::delete('/taxes/{id}', [MasterDataController::class, 'destroyTax'])->name('taxes.destroy');
    Route::get('/tags', [MasterDataController::class, 'tags'])->name('tags');
    Route::post('/tags', [MasterDataController::class, 'storeTag'])->name('tags.store');
    Route::put('/tags/{id}', [MasterDataController::class, 'updateTag'])->name('tags.update');
    Route::delete('/tags/{id}', [MasterDataController::class, 'destroyTag'])->name('tags.destroy');

    // Master Departemen (Divisi)
    Route::get('/departments', [MasterDataController::class, 'departments'])->name('departments');
    Route::post('/departments', [MasterDataController::class, 'storeDepartment'])->name('departments.store');
    Route::put('/departments/{id}', [MasterDataController::class, 'updateDepartment'])->name('departments.update');
    Route::delete('/departments/{id}', [MasterDataController::class, 'destroyDepartment'])->name('departments.destroy');

    // Master Proyek (Project)
    Route::get('/projects', [MasterDataController::class, 'projects'])->name('projects');
    Route::post('/projects', [MasterDataController::class, 'storeProject'])->name('projects.store');
    Route::put('/projects/{id}', [MasterDataController::class, 'updateProject'])->name('projects.update');
    Route::delete('/projects/{id}', [MasterDataController::class, 'destroyProject'])->name('projects.destroy');
    Route::get('/api/projects', [MasterDataController::class, 'apiProjects'])->name('api.projects');

    // Master Tipe Aset (Asset Types)
    Route::get('/asset-types', [\App\Http\Controllers\AssetTypeController::class, 'index'])->name('asset_types');
    Route::post('/asset-types', [\App\Http\Controllers\AssetTypeController::class, 'store'])->name('asset_types.store');
    Route::put('/asset-types/{id}', [\App\Http\Controllers\AssetTypeController::class, 'update'])->name('asset_types.update');
    Route::delete('/asset-types/{id}', [\App\Http\Controllers\AssetTypeController::class, 'destroy'])->name('asset_types.destroy');
});

// Alias for taxes.index
Route::get('/master-taxes-alias', [MasterDataController::class, 'taxes'])->name('taxes.index')->middleware('role:admin,accountant,auditor');

// 3.5. Aset (Aktiva Tetap)
Route::prefix('assets')->name('assets.')->middleware('role:admin,accountant,auditor')->group(function () {
    Route::get('/', [\App\Http\Controllers\AssetController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\AssetController::class, 'create'])->name('create');
    Route::post('/store', [\App\Http\Controllers\AssetController::class, 'store'])->name('store');
    Route::get('/export', [\App\Http\Controllers\AssetController::class, 'exportExcel'])->name('export');
    Route::post('/toggle-depreciation/{id}', [\App\Http\Controllers\AssetController::class, 'toggleDepreciation'])->name('toggle_depreciation');
    Route::get('/depreciation/preview', [\App\Http\Controllers\AssetController::class, 'previewDepreciation'])->name('depreciation.preview');
    Route::post('/depreciation/run', [\App\Http\Controllers\AssetController::class, 'runDepreciation'])->name('depreciation.run');
    Route::post('/depreciation/run-bulk', [\App\Http\Controllers\AssetController::class, 'runBulkDepreciation'])->name('depreciation.run_bulk');
    Route::delete('/depreciation/rollback/{id}', [\App\Http\Controllers\AssetController::class, 'rollbackDepreciation'])->name('depreciation.rollback');
    Route::delete('/{id}', [\App\Http\Controllers\AssetController::class, 'destroy'])->name('destroy');
});

// 3.6. Tutup Buku (Period Closing)
Route::prefix('closing')->name('closing.')->middleware('role:admin,accountant')->group(function () {
    Route::get('/', [\App\Http\Controllers\ClosingPeriodController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\ClosingPeriodController::class, 'create'])->name('create');
    Route::post('/store', [\App\Http\Controllers\ClosingPeriodController::class, 'store'])->name('store');
    Route::get('/{id}', [\App\Http\Controllers\ClosingPeriodController::class, 'show'])->name('show');
    Route::delete('/{id}', [\App\Http\Controllers\ClosingPeriodController::class, 'destroy'])->name('destroy');
});

// 4. Laporan
Route::prefix('reports')->name('reports.')->middleware('role:admin,accountant,auditor')->group(function () {
    Route::get('/journal', [ReportController::class, 'journal'])->name('journal');
    Route::get('/profit-loss', [ReportController::class, 'profitAndLoss'])->name('profit_loss');
    Route::get('/balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance_sheet');
    Route::get('/trial-balance', [ReportController::class, 'trialBalance'])->name('trial_balance');
    Route::get('/general-ledger', [ReportController::class, 'generalLedger'])->name('general_ledger');
    Route::get('/cash-flow', [ReportController::class, 'cashFlow'])->name('cash_flow');
    Route::get('/operating-expenses', [ReportController::class, 'operatingExpenses'])->name('operating_expenses');
});

// 5. Pengaturan
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/profile', [SettingController::class, 'profile'])->name('profile');
    Route::post('/profile', [SettingController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [SettingController::class, 'updatePassword'])->name('profile.update_password');
    Route::post('/profile/token/generate', [SettingController::class, 'generateApiToken'])->name('profile.token.generate');
    Route::delete('/profile/token/{id}', [SettingController::class, 'revokeApiToken'])->name('profile.token.revoke');

    Route::middleware('role:admin')->group(function () {
        Route::get('/main', [SettingController::class, 'main'])->name('main');
        Route::post('/main', [SettingController::class, 'updateMain'])->name('main.update');
        Route::post('/main/test-gemini', [SettingController::class, 'testGeminiAi'])->name('main.test_gemini');
        Route::get('/account-mappings', [SettingController::class, 'accountMappings'])->name('account_mappings');
        Route::post('/account-mappings', [SettingController::class, 'updateAccountMappings'])->name('account_mappings.update');
        Route::get('/employees', [SettingController::class, 'employees'])->name('employees');
        Route::post('/employees', [SettingController::class, 'storeEmployee'])->name('employees.store');
        Route::post('/reset-data', [SettingController::class, 'resetData'])->name('reset_data');
    });
});

// 6. Langganan & Tagihan Tenant (Client Billing)
Route::prefix('subscription')->name('subscription.')->middleware('role:admin')->group(function () {
    Route::get('/', [\App\Http\Controllers\SubscriptionController::class, 'index'])->name('index');
    Route::post('/renew', [\App\Http\Controllers\SubscriptionController::class, 'renew'])->name('renew');
    Route::post('/cancel-pending/{id}', [\App\Http\Controllers\SubscriptionController::class, 'cancelPendingInvoice'])->name('cancel_pending');
    Route::post('/pay-complete/{id}', [\App\Http\Controllers\SubscriptionController::class, 'payComplete'])->name('pay_complete');
    Route::get('/invoice/{id}', [\App\Http\Controllers\SubscriptionController::class, 'invoice'])->name('invoice');
});

// 7. Super Admin SaaS Platform (Master Control)
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\SuperAdminController::class, 'index'])->name('dashboard');
    Route::get('/tenants', [\App\Http\Controllers\SuperAdminController::class, 'tenants'])->name('tenants');
    Route::post('/tenants/{id}/update-plan', [\App\Http\Controllers\SuperAdminController::class, 'updateTenant'])->name('tenants.update_plan');
    Route::post('/tenants/{id}/reset-password', [\App\Http\Controllers\SuperAdminController::class, 'resetTenantPassword'])->name('tenants.reset_password');
    Route::delete('/tenants/{id}', [\App\Http\Controllers\SuperAdminController::class, 'destroyTenant'])->name('tenants.destroy');
    Route::get('/invoices', [\App\Http\Controllers\SuperAdminController::class, 'invoices'])->name('invoices');
    Route::post('/invoices/{id}/approve', [\App\Http\Controllers\SuperAdminController::class, 'approveInvoice'])->name('invoices.approve');
    Route::get('/payment-settings', [\App\Http\Controllers\SuperAdminController::class, 'paymentSettings'])->name('payment_settings');
    Route::post('/payment-settings', [\App\Http\Controllers\SuperAdminController::class, 'updatePaymentSettings'])->name('payment_settings.update');
    Route::get('/plans', [\App\Http\Controllers\SuperAdminController::class, 'plans'])->name('plans');
    Route::post('/plans', [\App\Http\Controllers\SuperAdminController::class, 'updatePlans'])->name('plans.update');
});

