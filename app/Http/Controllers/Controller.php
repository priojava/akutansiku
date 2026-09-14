<?php

namespace App\Http\Controllers;

use App\Models\Company;

abstract class Controller
{
    /**
     * Dapatkan entitas perusahaan yang sedang aktif dari session / user default
     */
    protected function getActiveCompany(): Company
    {
        $user = auth()->user();
        $activeCompanyId = session('active_company_id') ?? $user?->default_company_id;
        
        if ($user && !$user->isSuperAdmin()) {
            $company = $user->companies()->where('companies.id', $activeCompanyId)->first()
                ?? Company::where('id', $activeCompanyId)->where('owner_id', $user->id)->first();
            
            if (!$company) {
                $company = $user->companies()->first() ?? Company::where('owner_id', $user->id)->first();
                if ($company) {
                    session(['active_company_id' => $company->id]);
                }
            }
            if ($company) {
                return $company->load('settings');
            }
        }

        return Company::with('settings')->find($activeCompanyId) 
            ?? Company::first() 
            ?? Company::create([
                'name' => 'Dapur Gemoy',
                'plan_type' => 'premium',
                'city' => 'Bekasi Kota',
                'is_initial_balance_locked' => false,
            ]);
    }
}
