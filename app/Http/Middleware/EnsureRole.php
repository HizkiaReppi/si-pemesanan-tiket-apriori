<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): Response  $next
     */
    /**
     * Role hierarchy: admin > cashier > gate_staff.
     * Admin can access cashier & owner routes.
     * Cashier can access gate_staff routes.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $userRole = $request->user()->role->value;

        // Direct role match
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // Admin can access owner, cashier, and gate_staff routes
        if ($userRole === 'admin' && array_intersect($roles, ['owner', 'cashier', 'gate_staff'])) {
            return $next($request);
        }

        // Cashier can access gate_staff routes
        if ($userRole === 'cashier' && in_array('gate_staff', $roles)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
