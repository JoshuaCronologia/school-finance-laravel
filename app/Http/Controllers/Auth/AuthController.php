<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Users\BranchUser;
use App\Models\Employee;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     *
     * Flow:
     * 1. Try admin login (users table in accounting DB)
     * 2. If admin fails, try multi_login (loop all branch DBs for employee/student)
     * 3. If found in branch, check BranchUser whitelist, create SSO session
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // --- 1. Try admin login first ---
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        // --- 2. Multi-login: search all branch databases ---
        $result = $this->multiLogin($credentials['email'], $credentials['password']);

        if ($result) {
            return $this->createSsoSession($request, $result);
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => __('auth.failed'),
            ]);
    }

    /**
     * Search all branch databases for matching employee or student.
     *
     * Returns array with user info if found, null if not.
     */
    private function multiLogin(string $email, string $password): ?array
    {
        $branches = $this->getBranches();

        foreach ($branches as $branch) {
            $code = strtolower($branch['code']);

            // Search K-12 database
            if (in_array('1', $branch['school_types'] ?? [])) {
                $connection = $code . '_kto12';
                if (config("database.connections.{$connection}")) {
                    $found = $this->findUserInBranch($connection, $email, $password, $code, 'Kto12');
                    if ($found) return $found;
                }
            }

            // Search College database
            if (in_array('2', $branch['school_types'] ?? [])) {
                $connection = $code . '_college';
                if (config("database.connections.{$connection}")) {
                    $found = $this->findUserInBranch($connection, $email, $password, $code, 'College');
                    if ($found) return $found;
                }
            }
        }

        return null;
    }

    /**
     * Search employee_db and students_db tables in a specific branch connection.
     */
    private function findUserInBranch(string $connection, string $email, string $password, string $branchCode, string $platform): ?array
    {
        try {
            // Search employees
            $employee = DB::connection($connection)
                ->table('employee_db')
                ->where('email', $email)
                ->first();

            if ($employee && isset($employee->password) && Hash::check($password, $employee->password)) {
                return [
                    'parent_id'   => (string) $employee->id,
                    'parent_type' => Employee::class,
                    'name'        => trim(($employee->fname ?? '') . ' ' . ($employee->lname ?? '')),
                    'email'       => $employee->email,
                    'branch_code' => $branchCode,
                    'platform'    => $platform,
                ];
            }

            // Search students
            $student = DB::connection($connection)
                ->table('students_db')
                ->where('email', $email)
                ->first();

            if ($student && isset($student->password) && Hash::check($password, $student->password)) {
                return [
                    'parent_id'   => (string) $student->id,
                    'parent_type' => Student::class,
                    'name'        => trim(($student->fname ?? '') . ' ' . ($student->lname ?? '')),
                    'email'       => $student->email,
                    'branch_code' => $branchCode,
                    'platform'    => $platform,
                ];
            }
        } catch (\Exception $e) {
            // Connection failed or table doesn't exist — skip this branch
        }

        return null;
    }

    /**
     * Create SSO session after successful multi_login.
     */
    private function createSsoSession(Request $request, array $userData): RedirectResponse
    {
        // Check BranchUser whitelist
        $branchUser = BranchUser::where('parent_id', $userData['parent_id'])
            ->where('parent_type', $userData['parent_type'])
            ->where('branch_code', $userData['branch_code'])
            ->first();

        if (!$branchUser || !$branchUser->is_active) {
            return redirect()->route('no-access')
                ->with('error', 'Your account does not have access to the Finance system. Contact your administrator.');
        }

        $permissions = $branchUser->getPermissionNames()->toArray();

        Session::put('user_id', $userData['parent_id']);
        Session::put('branch_code', $userData['branch_code']);
        Session::put('platform', $userData['platform']);
        Session::put('permissions', $permissions);
        Session::put('is_sso', true);
        Session::put('user_info', [
            'branch_user_id' => $branchUser->id,
            'user_type'      => $userData['parent_type'],
            'name'           => $branchUser->name ?: $userData['name'],
            'email'          => $userData['email'],
        ]);

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    /**
     * Get branch list from creds.json.
     */
    private function getBranches(): array
    {
        $configKey = env('DB_CONFIG_KEY', '');
        $credsPath = base_path('creds.json');

        if (!$configKey || !file_exists($credsPath)) {
            return [];
        }

        $json = json_decode(file_get_contents($credsPath), true) ?? [];
        return $json[$configKey]['databases']['branches'] ?? [];
    }

    /**
     * Log the user out (supports both admin and SSO).
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
