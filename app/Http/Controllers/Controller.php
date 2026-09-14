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
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->default_company_id ?? 1;
        
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
