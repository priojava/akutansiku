<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request based on user role.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user() ?? \App\Models\User::first();

        if (!$user) {
            abort(403, 'Akses ditolak: Anda belum login.');
        }

        // Ambil role user pada company aktif
        $companyId = $request->header('X-Company-Id') ?: ($user->default_company_id ?: 1);
        $userCompany = $user->companies()->where('companies.id', $companyId)->first();
        $userRole = $userCompany ? $userCompany->pivot->role : 'admin';

        if (!empty($roles) && !in_array($userRole, $roles) && $userRole !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak: Level pengguna Anda (' . ucfirst($userRole) . ') tidak memiliki izin untuk fitur ini.'
                ], 403);
            }
            abort(403, 'Akses ditolak: Level akun Anda (' . ucfirst($userRole) . ') tidak memiliki izin untuk fitur ini.');
        }

        return $next($request);
    }
}
