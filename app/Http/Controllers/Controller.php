<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

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

    /**
     * Dapatkan ID perusahaan yang sedang aktif dengan Validasi Hak Akses Tenant (Tenant Guard)
     */
    protected function getCompanyId(Request $request): int
    {
        $user = $request->user() ?? auth()->user();
        $targetCompanyId = null;

        if ($request->hasHeader('X-Company-Id')) {
            $targetCompanyId = (int) $request->header('X-Company-Id');
        } elseif ($request->filled('company_id')) {
            $targetCompanyId = (int) $request->input('company_id');
        }

        // Jika user terautentikasi (via Token Sanctum atau Session Web)
        if ($user && !$user->isSuperAdmin()) {
            if ($targetCompanyId) {
                // Periksa apakah user memiliki hak akses ke company target
                $hasAccess = $user->companies()->where('companies.id', $targetCompanyId)->exists()
                    || ((int) $user->default_company_id === $targetCompanyId)
                    || Company::where('id', $targetCompanyId)->where('owner_id', $user->id)->exists();

                if (!$hasAccess) {
                    abort(response()->json([
                        'status' => 'error',
                        'message' => "Akses Ditolak (403 Forbidden): Akun Anda tidak memiliki hak akses ke entitas Perusahaan ID {$targetCompanyId}."
                    ], 403));
                }

                return $targetCompanyId;
            }

            // Jika klien tidak mengirimkan ID, otomatis gunakan default company user
            if ($user->default_company_id) {
                return (int) $user->default_company_id;
            }

            $userFirstCompany = $user->companies()->first() ?? Company::where('owner_id', $user->id)->first();
            if ($userFirstCompany) {
                return (int) $userFirstCompany->id;
            }
        }

        // Fallback untuk Session Web atau Pengujian
        if ($targetCompanyId) {
            return $targetCompanyId;
        }

        if (session()->has('active_company_id')) {
            return (int) session('active_company_id');
        }

        return (int) ($this->getActiveCompany()->id ?? 1);
    }
}
