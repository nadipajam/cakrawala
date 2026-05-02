<?php

namespace App\Http\Middleware;

use App\Support\UserRole;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            return redirect()->route('login');
        }

        $normalizedRole = UserRole::normalize($request->user()->role);
        $normalizedAllowedRoles = collect($roles)
            ->map(fn (string $role) => UserRole::normalize($role))
            ->unique()
            ->values()
            ->all();

        if ($normalizedAllowedRoles !== [] && ! in_array($normalizedRole, $normalizedAllowedRoles, true)) {
            if (! ($request->expectsJson() || $request->is('api/*'))) {
                if (in_array($normalizedRole, UserRole::backofficeValues(), true)) {
                    return redirect()->route('admin.dashboard');
                }

                return redirect()->route('dashboard');
            }

            throw new AuthorizationException('Unauthorized');
        }

        return $next($request);
    }
}
