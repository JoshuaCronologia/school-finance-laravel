<?php

namespace App\Http\Middleware;

use App\Services\Users\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Only allows admin users (User model from accounting DB).
 */
class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        // Standard auth admin
        if (auth()->check()) {
            return $next($request);
        }

        // SSO user — check if user_type is User (admin)
        $userType = Session::get('user_info.user_type');
        if ($userType === User::class) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Admin access only.'], 401);
        }

        abort(401, 'Admin access only.');
    }
}
