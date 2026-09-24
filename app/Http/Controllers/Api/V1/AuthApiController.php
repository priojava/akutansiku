<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CompanyProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    public function __construct(
        protected CompanyProvisioningService $provisioningService
    ) {}

    /**
     * 1. Register Akun & Perusahaan Baru via API
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'company_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
        ]);

        // Buat User Owner
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
        ]);

        // Provisioning Perusahaan + 120 COA + Pengaturan Akuntansi
        $company = $this->provisioningService->provisionCompany([
            'name' => $validated['company_name'],
            'city' => $validated['city'] ?? 'Jakarta',
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['city'] ?? null,
        ], $user);

        $user->update(['default_company_id' => $company->id]);

        // Buat Token Sanctum
        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi perusahaan dan pengguna berhasil.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'active_company' => [
                'id' => $company->id,
                'name' => $company->name,
                'city' => $company->city,
                'plan_type' => $company->plan_type ?? 'trial',
                'role' => 'admin',
            ],
            'accessible_companies' => [
                [
                    'id' => $company->id,
                    'name' => $company->name,
                    'role' => 'admin',
                ]
            ],
        ], 201);
    }

    /**
     * 2. Login & Dapatkan Bearer Token
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial email atau password yang Anda masukkan tidak sesuai.'],
            ]);
        }

        // Tentukan Active Company
        $activeCompany = $user->defaultCompany 
            ?? $user->companies()->first() 
            ?? \App\Models\Company::where('owner_id', $user->id)->first()
            ?? \App\Models\Company::first();

        $deviceName = $validated['device_name'] ?? 'api_client';
        $token = $user->createToken($deviceName)->plainTextToken;

        $companies = $user->companies()->get()->map(function ($comp) {
            return [
                'id' => $comp->id,
                'name' => $comp->name,
                'city' => $comp->city,
                'role' => $comp->pivot->role ?? 'member',
            ];
        });

        if ($companies->isEmpty() && $activeCompany) {
            $companies = collect([[
                'id' => $activeCompany->id,
                'name' => $activeCompany->name,
                'city' => $activeCompany->city,
                'role' => 'admin',
            ]]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'is_superadmin' => (bool) $user->is_superadmin,
            ],
            'active_company' => $activeCompany ? [
                'id' => $activeCompany->id,
                'name' => $activeCompany->name,
                'city' => $activeCompany->city,
                'plan_type' => $activeCompany->plan_type ?? 'trial',
            ] : null,
            'accessible_companies' => $companies,
        ]);
    }

    /**
     * 3. Ambil Profil User & Daftar Perusahaan Milik Saya (GET /auth/me)
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $activeCompanyId = $this->getCompanyId($request);
        $activeCompany = \App\Models\Company::find($activeCompanyId);

        $companies = $user->companies()->get()->map(function ($comp) {
            return [
                'id' => $comp->id,
                'name' => $comp->name,
                'city' => $comp->city,
                'role' => $comp->pivot->role ?? 'member',
            ];
        });

        if ($companies->isEmpty() && $activeCompany) {
            $companies = collect([[
                'id' => $activeCompany->id,
                'name' => $activeCompany->name,
                'city' => $activeCompany->city,
                'role' => 'admin',
            ]]);
        }

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'is_superadmin' => (bool) $user->is_superadmin,
            ],
            'active_company' => $activeCompany ? [
                'id' => $activeCompany->id,
                'name' => $activeCompany->name,
                'city' => $activeCompany->city,
                'plan_type' => $activeCompany->plan_type ?? 'trial',
            ] : null,
            'accessible_companies' => $companies,
        ]);
    }

    /**
     * 4. Revoke Token (Logout API)
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()?->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Token berhasil dicabut (Logout berhasil).',
        ]);
    }
}
