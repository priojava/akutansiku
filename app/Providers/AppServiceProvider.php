<?php

namespace App\Providers;

use App\Models\Company;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (!$view->offsetExists('company')) {
                $activeCompanyId = session('active_company_id') ?? auth()->user()?->default_company_id ?? 1;
                $activeCompany = Company::with('settings')->find($activeCompanyId) ?? Company::first();
                $view->with('company', $activeCompany);
            }
        });
    }
}
