<?php

namespace App\Http\Controllers;

use App\Libraries\Branch;
use App\Models\Employee;
use App\Models\Student;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function validate_login(Request $request)
    {
        $data = [];
        $branch = Branch::get($request->branch_code, false, false, false);

        $employee = Employee::on($branch[0]->platforms[0]['id']);
        $employee_user = $employee->where(function ($query) use ($request) {
            $query->where('email', $request->email)
                ->orWhere('employee_id', $request->email);
        })
            ->where('password', $request->password)
            ->first();

        if ($employee_user) {
            $data = [
                'user_type' => 'employee',
                'branch_code' => $branch[0]->id,
                'user_id' => md5($employee_user->id)
            ];
        } else {
            $student = Student::on($branch[0]->platforms[0]['id']);
            $student_user = $student->where(function ($query) use ($request) {
                $query->where('email', $request->email)
                    ->orWhere('control_id', $request->email)
                    ->orWhere('student_id', $request->email);
            })
                ->where('password', $request->password)
                ->where('isdeleted', 0)
                ->first();

            $data = [
                'user_type' => 'student',
                'branch_code' => $branch[0]->id,
                'user_id' => md5($student_user->id)
            ];
        }
        $url = url('/branch_login/' . $data['user_type'] . '/' . $data['branch_code'] . '/' . $data['user_id']);
        return response()->json(['redirect_url' => $url]);
    }
}
