<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Libraries\Branch;
use App\Libraries\SharedFunctions;
use App\Models\Student;
use App\Models\Employee;
use App\Models\CollegeEmployee;
use App\Models\CollegeUser;
use App\Models\SchoolInfo;
use App\Models\CollegeSchoolImage;
use App\Services\Users\BranchUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\AuditService;
use Spatie\Permission\Models\Permission;

class LoginController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect(url('/'));
        }
        $branch = Branch::get("", false, false, true);
        try {
            $school_info = SchoolInfo::on($branch[0])->first();
            $data['school_img'] = $school_info['file_url'] . '/' . $school_info['active_disk'] . '/School_Files/' . '1/' . $school_info['folder_name'] . '/school_profile/' . $school_info['header_src'];
        } catch (\Exception $e) {
            $data['school_img'] = null;
        }
        $data['css'] = ['login'];
        $data['js'] = [];
        return view('auth.login', $data);
    }

    public function branch_login($user_type, $branch_code, $user_id)
    {
        Auth::logout();
        Session::flush();

        $permissions = [];
        $branch = Branch::get($branch_code, false, false, false);
        try {
            $school_info = SchoolInfo::on($branch[0]->platforms[0]['id'])->first();
            $school_image = $school_info['file_url'] . '/' . $school_info['active_disk'] . '/School_Files/' . '1/' . $school_info['folder_name'] . '/school_profile/' . $school_info['header_src'];
        } catch (\Exception $e) {
            try {
                $school_info = CollegeSchoolImage::on($branch[0]->platforms[1]['id'])->where('image_type', 2)->first();
                $school_image = $school_info['file_url'] . '/' . $school_info['file_name'];
            } catch (\Exception $e2) {
                $school_image = null;
            }
        }

        $rs = SharedFunctions::prompt_msg('User not found!');

        if ($user_type == 'employee') {
            try {
                $employee = Employee::on($branch[0]->platforms[0]['id']);
                $employee_user = $employee->where(DB::raw('md5(id)'), $user_id)->first();
            } catch (\Exception $e) {
                goto b;
            }

            if ($employee_user) {
                $branch_account = BranchUser::where('parent_id', $employee_user->id)
                    ->where('parent_type', \App\Models\Employee::class)
                    ->where('branch_code', $branch[0]->id)
                    ->first();
                if ($branch_account) {
                    $model_permissions = DB::table('model_has_permissions')
                        ->where('model_id', $branch_account->id)
                        ->get();
                    foreach ($model_permissions as $m_permissions) {
                        $permissions[] = Permission::where('id', $m_permissions->permission_id)
                            ->pluck('name')->first();
                    }
                } else {
                    $branch_account = BranchUser::create([
                        'parent_id' => $employee_user->id,
                        'parent_type' => \App\Models\Employee::class,
                        'branch_code' => $branch[0]->id
                    ]);
                }
                Session::put('user_info', SharedFunctions::get_auth($employee_user->id, $branch[0]->id, \App\Models\Employee::class));
                Session::put('branch_code', $branch[0]->id);
                Session::put('permissions', $permissions);
                Session::put('user_id', $employee_user->id);
                Session::put('platform', 'Kto12');
                Session::put('is_sso', true);
                Session::put('school_img', $school_image);
                $rs = SharedFunctions::success_msg('Logged in successfully!');
                $rs['redirect'] = url('/');
            } else {
                b:
                if (isset($branch[0]->platforms[1])) {
                    $employee = CollegeEmployee::on($branch[0]->platforms[1]['id'])->select('id as key_id');
                    $employee_user = $employee->where('id', $user_id)->first();
                    if ($employee_user) {
                        $branch_account = BranchUser::where('parent_id', $employee_user->key_id)
                            ->where('parent_type', \App\Models\Employee::class)
                            ->where('branch_code', $branch[0]->id)
                            ->first();
                        if ($branch_account) {
                            $model_permissions = DB::table('model_has_permissions')
                                ->where('model_id', $branch_account->id)
                                ->get();
                            foreach ($model_permissions as $m_permissions) {
                                $permissions[] = Permission::where('id', $m_permissions->permission_id)
                                    ->pluck('name')->first();
                            }
                        } else {
                            $branch_account = BranchUser::create([
                                'parent_id' => $employee_user->key_id,
                                'parent_type' => \App\Models\Employee::class,
                                'branch_code' => $branch[0]->id
                            ]);
                        }

                        Session::put('user_info', SharedFunctions::get_auth($employee_user->key_id, $branch[0]->id, \App\Models\Employee::class));
                        Session::put('branch_code', $branch[0]->id);
                        Session::put('permissions', $permissions);
                        Session::put('user_id', $employee_user->key_id);
                        Session::put('platform', 'College');
                        Session::put('is_sso', true);
                        Session::put('school_img', $school_image);
                        $rs = SharedFunctions::success_msg('Logged in successfully!');
                        $rs['redirect'] = url('/');
                    }
                }
            }
        } else if ($user_type == 'student') {
            $student = Student::on($branch[0]->platforms[0]['id']);
            $student_user = $student->where(DB::raw('md5(id)'), $user_id)->first();
            if ($student_user) {
                $branch_account = BranchUser::where('parent_id', $student_user->id)
                    ->where('parent_type', \App\Models\Student::class)
                    ->where('branch_code', $branch[0]->id)
                    ->first();
                if ($branch_account) {
                    $model_permissions = DB::table('model_has_permissions')
                        ->where('model_id', $branch_account->id)
                        ->get();
                    foreach ($model_permissions as $m_permissions) {
                        $permissions[] = Permission::where('id', $m_permissions->permission_id)
                            ->pluck('name')->first();
                    }
                } else {
                    $branch_account = BranchUser::create([
                        'parent_id' => $student_user->id,
                        'parent_type' => \App\Models\Student::class,
                        'branch_code' => $branch[0]->id
                    ]);
                }
                Session::put('user_info', SharedFunctions::get_auth($student_user->id, $branch[0]->id, \App\Models\Student::class));
                Session::put('branch_code', $branch[0]->id);
                Session::put('permissions', $permissions);
                Session::put('user_id', $student_user->id);
                Session::put('platform', 'Kto12');
                Session::put('is_sso', true);
                $rs = SharedFunctions::success_msg('Logged in successfully!');
                $rs['redirect'] = url('/');
            }
        }

        if (isset($rs['redirect'])) {
            return redirect($rs['redirect']);
        }
        return redirect(url('/login'))->withErrors(['email' => $rs['text']]);
    }

    public function multi_login(Request $request)
    {
        $rs = SharedFunctions::prompt_msg('Invalid credentials!');

        // Reset default connection before Auth::attempt()
        DB::setDefaultConnection('mysql');

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $permissions = Auth::user()
                ->getAllPermissions()
                ->pluck('name')
                ->toArray();

            Session::put('user_info', SharedFunctions::get_auth(Auth::user()->id, null, \App\Services\Users\User::class));
            Session::put('branch_code', null);
            Session::put('permissions', $permissions);
            Session::put('user_id', Auth::user()->id);
            Session::put('referer', $request->server('HTTP_REFERER'));
            Session::put('school_img', null);

            $branches = Branch::get("", false, false, false);
            foreach ($branches as $branch) {
                try {
                    $school_info = SchoolInfo::on($branch->platforms[0]['id'])->first();
                    $school_image = $school_info['file_url'] . '/' . $school_info['active_disk'] . '/School_Files/' . '1/' . $school_info['folder_name'] . '/school_profile/' . $school_info['header_src'];
                } catch (\Exception $e) {
                    $school_image = null;
                }
            }
            Session::put('school_img', $school_image ?? null);

            (new AuditService)->logActivity('login', 'auth', 'Admin login: ' . Auth::user()->email);

            $rs = SharedFunctions::success_msg('Logged in successfully!');
            $rs['redirect'] = url('/');
        } else {
            $permissions = [];
            $branches = Branch::get("", false, false, false);
            $found = false;

            foreach ($branches as $branch) {
                if ($found) break;

                try {
                    $school_info = SchoolInfo::on($branch->platforms[0]['id'])->first();
                    $school_image = $school_info['file_url'] . '/' . $school_info['active_disk'] . '/School_Files/' . '1/' . $school_info['folder_name'] . '/school_profile/' . $school_info['header_src'];
                } catch (\Exception $e) {
                    $school_image = null;
                }

                // --- Search K-12 Employee ---
                try {
                    $employee_user = Employee::on($branch->platforms[0]['id'])
                        ->where(function ($query) use ($request) {
                            $query->where('email', $request->email)
                                ->orWhere('employee_id', $request->email);
                        })
                        ->where('password', md5($request->password))
                        ->first();

                    if (!$employee_user) {
                        $employee_user = DB::connection($branch->platforms[0]['id'])->table('employee_db as a')
                            ->leftJoin('accounts_db as b', 'a.id', '=', 'b.username')
                            ->select('a.id', 'a.firstname', 'a.lastname', 'a.middlename', 'a.mobile_number', 'a.email', 'b.id as subadmin_id')
                            ->where(function ($query) use ($request) {
                                $query->where('a.email', $request->email)
                                    ->orWhere('employee_id', $request->email);
                            })
                            ->where('b.password', md5($request->password))
                            ->first();
                        if ($employee_user) {
                            $employee_user = Employee::on($branch->platforms[0]['id'])->where('id', $employee_user->id)->first();
                        }
                    }
                } catch (\Exception $e) {
                    $employee_user = null;
                }

                if ($employee_user) {
                    $branch_account = BranchUser::where('parent_id', $employee_user->id)
                        ->where('parent_type', \App\Models\Employee::class)
                        ->where('branch_code', $branch->id)
                        ->first();

                    if ($branch_account) {
                        $model_permissions = DB::table('model_has_permissions')
                            ->where('model_id', $branch_account->id)
                            ->get();
                        foreach ($model_permissions as $m_permissions) {
                            $permissions[] = Permission::where('id', $m_permissions->permission_id)
                                ->pluck('name')->first();
                        }
                    } else {
                        $branch_account = BranchUser::create([
                            'parent_id' => $employee_user->id,
                            'parent_type' => \App\Models\Employee::class,
                            'branch_code' => $branch->id
                        ]);
                    }

                    Session::put('user_info', SharedFunctions::get_auth($employee_user->id, $branch->id, \App\Models\Employee::class));
                    Session::put('branch_code', $branch->id);
                    Session::put('permissions', $permissions);
                    Session::put('user_id', $employee_user->id);
                    Session::put('platform', 'Kto12');
                    Session::put('is_sso', true);
                    Session::put('referer', $request->server('HTTP_REFERER'));
                    Session::put('school_img', $school_image);

                    (new AuditService)->logActivity('login', 'auth', 'K-12 Employee login: ' . ($employee_user->email ?: $employee_user->employee_id));

                    $rs = SharedFunctions::success_msg('Logged in successfully!');
                    $rs['redirect'] = url('/');
                    $found = true;
                    continue;
                }

                // --- Search K-12 Student ---
                try {
                    $student_user = Student::on($branch->platforms[0]['id'])
                        ->where(function ($query) use ($request) {
                            $query->where('email', $request->email)
                                ->orWhere('student_id', $request->email)
                                ->orWhere('control_id', $request->email);
                        })
                        ->where('password', md5($request->password))
                        ->where('isdeleted', 0)
                        ->first();
                } catch (\Exception $e) {
                    $student_user = null;
                }

                if ($student_user) {
                    $branch_account = BranchUser::where('parent_id', $student_user->id)
                        ->where('parent_type', \App\Models\Student::class)
                        ->where('branch_code', $branch->id)
                        ->first();

                    if ($branch_account) {
                        $model_permissions = DB::table('model_has_permissions')
                            ->where('model_id', $branch_account->id)
                            ->get();
                        foreach ($model_permissions as $m_permissions) {
                            $permissions[] = Permission::where('id', $m_permissions->permission_id)
                                ->pluck('name')->first();
                        }
                    } else {
                        $branch_account = BranchUser::create([
                            'parent_id' => $student_user->id,
                            'parent_type' => \App\Models\Student::class,
                            'branch_code' => $branch->id
                        ]);
                    }

                    Session::put('user_info', SharedFunctions::get_auth($student_user->id, $branch->id, \App\Models\Student::class));
                    Session::put('branch_code', $branch->id);
                    Session::put('permissions', $permissions);
                    Session::put('user_id', $student_user->id);
                    Session::put('platform', 'Kto12');
                    Session::put('is_sso', true);
                    Session::put('referer', $request->server('HTTP_REFERER'));
                    Session::put('school_img', $school_image);

                    (new AuditService)->logActivity('login', 'auth', 'K-12 Student login: ' . ($student_user->email ?: $student_user->student_id));

                    $rs = SharedFunctions::success_msg('Logged in successfully!');
                    $rs['redirect'] = url('/');
                    $found = true;
                    continue;
                }

                // --- Search College Employee ---
                if (isset($branch->platforms[1])) {
                    try {
                        $college_users = CollegeUser::on($branch->platforms[1]['id'])->where('email', $request->email)->get();

                        foreach ($college_users as $user) {
                            if (Hash::check($request->password, $user->password)) {
                                $employee = CollegeEmployee::on($branch->platforms[1]['id'])->select('id as key_id');
                                $employee_user = $employee->where('user_id', $user->id)->first();

                                if (!$employee_user) continue;

                                $branch_account = BranchUser::where('parent_id', $employee_user->key_id)
                                    ->where('parent_type', \App\Models\Employee::class)
                                    ->where('branch_code', $branch->id)
                                    ->first();

                                if ($branch_account) {
                                    $model_permissions = DB::table('model_has_permissions')
                                        ->where('model_id', $branch_account->id)
                                        ->get();
                                    foreach ($model_permissions as $m_permissions) {
                                        $permissions[] = Permission::where('id', $m_permissions->permission_id)
                                            ->pluck('name')->first();
                                    }
                                } else {
                                    $branch_account = BranchUser::create([
                                        'parent_id' => $employee_user->key_id,
                                        'parent_type' => \App\Models\Employee::class,
                                        'branch_code' => $branch->id
                                    ]);
                                }

                                Session::put('user_info', SharedFunctions::get_auth($employee_user->key_id, $branch->id, \App\Models\Employee::class));
                                Session::put('branch_code', $branch->id);
                                Session::put('permissions', $permissions);
                                Session::put('user_id', $employee_user->key_id);
                                Session::put('platform', 'College');
                                Session::put('is_sso', true);
                                Session::put('referer', $request->server('HTTP_REFERER'));
                                Session::put('school_img', $school_image);

                                (new AuditService)->logActivity('login', 'auth', 'College Employee login: ' . $request->email);

                                $rs = SharedFunctions::success_msg('Logged in successfully!');
                                $rs['redirect'] = url('/');
                                $found = true;
                                break;
                            }
                        }
                    } catch (\Exception $e) {
                        // College connection failed, skip
                    }
                }
            }
        }
        return response()->json($rs);
    }

    public function login(Request $request)
    {
        $rs = SharedFunctions::default_msg();
        $this->validate($request, [
            'email' => 'required|email|max:100',
            'password' => 'required|max:255'
        ]);

        // Reset default connection before Auth::attempt()
        DB::setDefaultConnection('mysql');

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $permissions = Auth::user()->getAllPermissions()
                ->pluck('name')->toArray();
            Session::put('user_info', SharedFunctions::get_auth(Auth::user()->id, null, \App\Services\Users\User::class));
            Session::put('branch_code', null);
            Session::put('permissions', $permissions);
            Session::put('user_id', Auth::user()->id);
            Session::put('referer', $request->server('HTTP_REFERER'));

            $rs = SharedFunctions::success_msg();
            $rs['redirect'] = url('/');
            $rs['text'] = "Logged in successfully!";
        }
        return response()->json($rs);
    }

    public function logout()
    {
        (new AuditService)->logActivity('logout', 'auth', 'User logged out');

        $redirect = env('HOME_URL', url('/login'));
        Auth::logout();
        Session::flush();
        return redirect($redirect);
    }
}
