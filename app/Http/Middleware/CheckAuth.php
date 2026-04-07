<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Checks if user is logged in — supports both standard auth and SSO sessions.
 */
class CheckAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Standard Laravel auth (admin users)
        if (Auth::check()) {
            return $next($request);
        }

        // SSO session (branch users from SIS)
        if (Session::has('user_id') && Session::has('is_sso')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect(url('/login'));
    }
}
