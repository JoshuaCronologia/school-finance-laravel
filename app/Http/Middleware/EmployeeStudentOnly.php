<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use App\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Allows Employee or Student.
 */
class EmployeeStudentOnly
{
    public function handle(Request $request, Closure $next)
    {
        $userType = Session::get('user_info.user_type');
        if (in_array($userType, [Employee::class, Student::class])) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Employee or Student access only.'], 401);
        }

        abort(401, 'Employee or Student access only.');
    }
}
