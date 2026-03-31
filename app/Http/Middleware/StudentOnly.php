<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Only allows Student user type.
 */
class StudentOnly
{
    public function handle(Request $request, Closure $next)
    {
        $userType = Session::get('user_info.user_type');
        if ($userType === Student::class) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Student access only.'], 401);
        }

        abort(401, 'Student access only.');
    }
}
