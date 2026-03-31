<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Checks if the user has a specific permission.
 * Works for both standard auth users (Spatie) and SSO session users.
 *
 * Usage: ->middleware('check_permission:accounting')
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        // Standard auth user — use Spatie
        if (auth()->check() && auth()->user()->can($permission)) {
            return $next($request);
        }

        // SSO user — check session permissions
        $permissions = Session::get('permissions', []);
        if (in_array($permission, $permissions)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden. Missing permission: ' . $permission], 403);
        }

        abort(403, 'You do not have the required permission: ' . $permission);
    }
}
