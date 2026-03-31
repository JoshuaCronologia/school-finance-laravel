<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Allows Employee or User (admin counts as employee-level).
 */
class EmployeeOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            return $next($request);
        }

        $userType = Session::get('user_info.user_type');
        if (in_array($userType, [Employee::class, User::class])) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Employee access only.'], 401);
        }

        abort(401, 'Employee access only.');
    }
}
