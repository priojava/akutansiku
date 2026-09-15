<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Services\CompanyProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        protected CompanyProvisioningService $provisioningService
    ) {}

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $company = Company::first();
        return view('auth.login', compact('company'));
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'company_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
        ]);

        // 1. Buat User Pemilik Akun (Owner)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
        ]);

        // 2. Provisioning Perusahaan + 120 COA + Pajak + Trial 14 Hari
        $company = $this->provisioningService->provisionCompany([
            'name' => $validated['company_name'],
            'city' => $validated['city'] ?? 'Bekasi',
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'],
            'plan_type' => 'premium', // Default trial with full premium features
        ], $user);

        // 3. Set default company & login
        $user->update(['default_company_id' => $company->id]);
        session(['active_company_id' => $company->id]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', "Selamat datang di Akuntansi Cloud! Entitas '{$company->name}' berhasil dibuat dengan Trial Premium 14 Hari & 120 Bagan Akun siap pakai.");
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang kembali, ' . Auth::user()->name);
        }

        return back()->withInput($request->only('email'))->with('error', 'Email atau password yang Anda masukkan salah.');
    }

    /**
     * Fitur 1-Click Quick Login untuk Memudahkan Pengujian Role
     */
    public function quickLogin(string $role)
    {
        if ($role === 'superadmin') {
            $user = User::firstOrCreate(
                ['email' => 'superadmin@dapurgemoy.com'],
                [
                    'name' => 'Super Administrator (SaaS Master)',
                    'password' => bcrypt('password'),
                    'is_superadmin' => true,
                    'default_company_id' => 1,
                ]
            );
            $user->update(['is_superadmin' => true]);

            Auth::login($user);
            request()->session()->regenerate();

            return redirect()->route('superadmin.dashboard')->with('success', 'Selamat datang di Master SaaS Portal!');
        }

        $emailMap = [
            'admin' => 'admin@dapurgemoy.com',
            'owner' => 'admin@dapurgemoy.com',
            'accountant' => 'akuntan@dapurgemoy.com',
            'auditor' => 'auditor@dapurgemoy.com',
            'staff' => 'staff@dapurgemoy.com',
            'cashier' => 'kasir@dapurgemoy.com',
        ];

        $email = $emailMap[$role] ?? 'admin@dapurgemoy.com';
        $user = User::where('email', $email)->first();

        if (!$user) {
            $nameMap = [
                'auditor' => 'Hendro Santoso (Auditor)',
                'staff' => 'Budi Pratama (Staff)',
                'cashier' => 'Siti Kasir',
                'accountant' => 'Siti Fatimah (Akuntan)',
                'admin' => 'Budi Santoso (Owner)',
            ];
            $user = User::create([
                'name' => $nameMap[$role] ?? 'Pengguna Demo',
                'email' => $email,
                'password' => bcrypt('password123'),
                'default_company_id' => 1,
            ]);
            $user->companies()->syncWithoutDetaching([1 => ['role' => $role === 'staff' ? 'staff' : $role]]);
        }

        Auth::login($user);
        request()->session()->regenerate();

        $roleLabel = match ($role) {
            'accountant' => 'Akuntan (Finance)',
            'auditor' => 'Auditor (Pemeriksa Laporan)',
            'staff', 'cashier' => 'Staff Operasional',
            default => 'Owner (Administrator)'
        };

        return redirect()->route('dashboard')->with('success', "Anda sekarang masuk sebagai {$roleLabel} ({$user->name}).");
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}

