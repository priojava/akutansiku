<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Company;

class CheckSubscriptionWritable
{
    /**
     * Handle an incoming request.
     * Mencegah aksi transaksi/mutasi data jika langganan perusahaan sudah melewati masa kelonggaran 7 hari (Mode Read-Only).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // 1. Bypass untuk Super Admin
        if ($user && ($user->is_superadmin || $user->email === 'superadmin@dapurgemoy.com')) {
            return $next($request);
        }

        // 2. Dapatkan company aktif
        $activeCompanyId = session('active_company_id') ?? $user?->default_company_id;
        $company = null;
        if ($activeCompanyId) {
            $company = Company::find($activeCompanyId);
        }
        if (!$company && $user) {
            $company = $user->companies()->first() ?? Company::where('owner_id', $user->id)->first();
        }
        if (!$company) {
            $company = Company::first();
        }

        // 3. Cek apakah company dalam status Read-Only (melewati masa kelonggaran 7 hari)
        if ($company && $company->isReadOnly()) {
            $message = 'Akun Terkunci (Mode Read-Only): Masa kelonggaran 7 hari telah berakhir. Anda hanya dapat melihat dan mencetak data. Silakan perpanjang paket langganan untuk mencatat transaksi baru.';

            if ($request->routeIs('transactions.create')) {
                return redirect()->route('subscription.index')->with('warning', $message);
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                    'read_only' => true,
                    'subscription_url' => route('subscription.index'),
                ], 403);
            }

            return redirect()->back()->with('error', $message);
        }

        return $next($request);
    }
}
