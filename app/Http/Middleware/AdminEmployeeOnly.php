<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Allows User (admin) or Employee.
 */
class AdminEmployeeOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            return $next($request);
        }

        $userType = Session::get('user_info.user_type');
        if (in_array($userType, [User::class, Employee::class])) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Admin or Employee access only.'], 401);
        }

        abort(401, 'Admin or Employee access only.');
    }
}
