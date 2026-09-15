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

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Ambil role user pada company aktif
        $companyId = $request->header('X-Company-Id') ?: (session('active_company_id') ?: ($user->default_company_id ?: 1));
        $userCompany = $user->companies()->where('companies.id', $companyId)->first();
        $userRole = $userCompany ? $userCompany->pivot->role : ($user->isAdmin($companyId) ? 'admin' : 'staff');

        // Normalisasi alias cashier & staff
        $normalizedUserRole = in_array($userRole, ['cashier', 'staff']) ? 'staff' : $userRole;
        $normalizedAllowedRoles = array_map(fn($r) => in_array($r, ['cashier', 'staff']) ? 'staff' : $r, $roles);

        if (!empty($normalizedAllowedRoles) && !in_array($normalizedUserRole, $normalizedAllowedRoles) && $userRole !== 'admin') {
            $message = 'Akses ditolak: Level pengguna Anda (' . ucfirst($userRole) . ') tidak memiliki izin untuk fitur ini.';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 403);
            }

            return redirect()->route('dashboard')->with('error', $message);
        }

        return $next($request);
    }
}
