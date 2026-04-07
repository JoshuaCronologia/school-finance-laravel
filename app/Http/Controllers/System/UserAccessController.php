<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Services\Users\BranchUser;
use App\Services\Users\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserAccessController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'admin');

        // Admin users
        $users = User::with('roles', 'permissions')->orderBy('name')->get();
        $roles = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();
        $permissionGroups = $permissions->groupBy(function ($p) { return explode('.', $p->name)[0]; });

        // Branch users
        $branchQuery = BranchUser::query();
        if ($request->filled('branch_search')) {
            $s = $request->branch_search;
            $branchQuery->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('parent_id', 'like', "%{$s}%");
            });
        }
        $branchUsers = $branchQuery->latest()->paginate(20, ['*'], 'branch_page')->withQueryString();
        $ssoPermissions = config('acl.permissions', []);
        $branchCodes = $this->getBranchCodes();

        return view('pages.system.user-access', compact(
            'tab', 'users', 'roles', 'permissions', 'permissionGroups',
            'branchUsers', 'ssoPermissions', 'branchCodes'
        ));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        // Sync custom permissions if provided
        if (!empty($validated['permissions'])) {
            $user->syncPermissions($validated['permissions']);
        }

        (new AuditService)->log('create', 'user_access', $user, null, "Created admin user with role: {$validated['role']}");

        return redirect()->route('user-access')->with('success', "User {$user->name} created.");
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|max:255|unique:users,email,{$user->id}",
            'password' => 'nullable|string|min:8',
            'role' => 'required|string|exists:roles,name',
        ]);

        $oldValues = ['name' => $user->name, 'email' => $user->email, 'role' => optional($user->roles->first())->name];

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
        ]);

        $user->syncRoles([$validated['role']]);

        (new AuditService)->log('update', 'user_access', $user, $oldValues, "Updated admin user, role: {$validated['role']}" . ($validated['password'] ? ', password changed' : ''));

        return redirect()->route('user-access')->with('success', "User {$user->name} updated.");
    }

    public function updatePermissions(Request $request, User $user)
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $oldPerms = $user->getPermissionNames()->toArray();
        $user->syncPermissions($validated['permissions'] ?? []);
        $newPerms = $validated['permissions'] ?? [];

        (new AuditService)->log('update', 'user_access', $user, ['permissions' => $oldPerms], "Permissions changed: " . implode(', ', $newPerms));

        return redirect()->route('user-access')->with('success', "Permissions updated for {$user->name}.");
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        (new AuditService)->log('delete', 'user_access', $user, null, "Deleted admin user: {$user->name} ({$user->email})");
        $user->delete();
        return redirect()->route('user-access')->with('success', 'User deleted.');
    }

    // ─── K-12 & College Employee Pages ─────────────────────────

    public function kto12Employees(Request $request)
    {
        $search = $request->input('search', '');
        $branches = \App\Libraries\Branch::get("", false, false, false);
        $employees = collect();
        $branchCode = '';

        foreach ($branches as $branch) {
            if (!isset($branch->platforms[0])) continue;
            $con = $branch->platforms[0]['id'];
            $branchCode = $branch->id;

            try {
                $query = \Illuminate\Support\Facades\DB::connection($con)
                    ->table('employee_db')
                    ->where('isdeleted', 0)
                    ->select('id', 'employee_id', 'firstname', 'middlename', 'lastname', 'email');

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('firstname', 'like', "%{$search}%")
                          ->orWhere('lastname', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('employee_id', 'like', "%{$search}%");
                    });
                }

                $employees = $query->orderBy('lastname')->paginate(25)->withQueryString();
            } catch (\Exception $e) {
                $employees = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);
            }
            break;
        }

        // Get existing BranchUser IDs for this branch
        $existingIds = BranchUser::where('branch_code', $branchCode)
            ->where('parent_type', \App\Models\Employee::class)
            ->pluck('parent_id')
            ->map(function ($id) { return (string) $id; })
            ->toArray();

        $ssoPermissions = config('acl.permissions', []);

        return view('pages.system.branch-employees', compact(
            'employees', 'search', 'existingIds', 'ssoPermissions', 'branchCode'
        ))->with('platform', 'K-12');
    }

    public function collegeEmployees(Request $request)
    {
        $search = $request->input('search', '');
        $branches = \App\Libraries\Branch::get("", false, false, false);
        $employees = collect();
        $branchCode = '';

        foreach ($branches as $branch) {
            if (!isset($branch->platforms[1])) continue;
            $con = $branch->platforms[1]['id'];
            $branchCode = $branch->id;

            try {
                $query = \Illuminate\Support\Facades\DB::connection($con)
                    ->table('employees')
                    ->where('deleted', 0)
                    ->select('id', 'fname', 'mname', 'lname', 'email');

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('fname', 'like', "%{$search}%")
                          ->orWhere('lname', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
                }

                $employees = $query->orderBy('lname')->paginate(25)->withQueryString();
            } catch (\Exception $e) {
                $employees = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);
            }
            break;
        }

        $existingIds = BranchUser::where('branch_code', $branchCode)
            ->where('parent_type', \App\Models\Employee::class)
            ->pluck('parent_id')
            ->toArray();

        $ssoPermissions = config('acl.permissions', []);

        return view('pages.system.branch-employees', compact(
            'employees', 'search', 'existingIds', 'ssoPermissions', 'branchCode'
        ))->with('platform', 'College');
    }

    public function grantAccess(Request $request)
    {
        $validated = $request->validate([
            'parent_id'    => 'required|string',
            'branch_code'  => 'required|string',
            'name'         => 'required|string',
            'email'        => 'nullable|string',
            'new_password' => 'nullable|string|min:4',
            'permissions'  => 'nullable|array',
        ]);

        $existing = BranchUser::where('parent_id', $validated['parent_id'])
            ->where('parent_type', \App\Models\Employee::class)
            ->where('branch_code', $validated['branch_code'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Employee already has access.');
        }

        $branchUser = BranchUser::create([
            'parent_id'   => $validated['parent_id'],
            'parent_type' => \App\Models\Employee::class,
            'branch_code' => $validated['branch_code'],
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'is_active'   => true,
        ]);

        if (!empty($validated['permissions'])) {
            $branchUser->syncPermissions($validated['permissions']);
        }

        // Update password in branch database if provided
        if (!empty($validated['new_password'])) {
            $this->updateBranchPassword(
                $validated['parent_id'],
                $validated['branch_code'],
                $validated['new_password']
            );
        }

        $perms = !empty($validated['permissions']) ? implode(', ', $validated['permissions']) : 'none';
        (new AuditService)->log('create', 'user_access', $branchUser, null, "Granted branch access to {$validated['name']} ({$validated['branch_code']}), permissions: {$perms}" . (!empty($validated['new_password']) ? ', password set' : ''));

        return back()->with('success', "Access granted to {$validated['name']}.");
    }

    public function searchEmployees(Request $request)
    {
        $query = $request->input('q', '');
        $platform = $request->input('platform', 'kto12'); // kto12 or college
        $results = [];

        if (strlen($query) < 2) {
            return response()->json($results);
        }

        $branches = \App\Libraries\Branch::get("", false, false, false);

        foreach ($branches as $branch) {
            if ($platform === 'kto12' && isset($branch->platforms[0])) {
                $con = $branch->platforms[0]['id'];
                try {
                    $employees = \Illuminate\Support\Facades\DB::connection($con)
                        ->table('employee_db')
                        ->where('isdeleted', 0)
                        ->where(function ($q) use ($query) {
                            $q->where('firstname', 'like', "%{$query}%")
                              ->orWhere('lastname', 'like', "%{$query}%")
                              ->orWhere('email', 'like', "%{$query}%")
                              ->orWhere('employee_id', 'like', "%{$query}%");
                        })
                        ->select('id', 'employee_id', 'firstname', 'middlename', 'lastname', 'email')
                        ->limit(20)
                        ->get();

                    foreach ($employees as $emp) {
                        // Check if already has BranchUser
                        $existing = BranchUser::where('parent_id', $emp->id)
                            ->where('parent_type', \App\Models\Employee::class)
                            ->where('branch_code', $branch->id)
                            ->first();

                        $results[] = [
                            'id' => $emp->id,
                            'employee_id' => $emp->employee_id,
                            'name' => trim($emp->firstname . ' ' . $emp->middlename . ' ' . $emp->lastname),
                            'email' => $emp->email,
                            'branch_code' => $branch->id,
                            'platform' => 'Kto12',
                            'type' => 'employee',
                            'already_added' => $existing ? true : false,
                        ];
                    }
                } catch (\Exception $e) {
                    // Connection failed, skip
                }
            }

            if ($platform === 'college' && isset($branch->platforms[1])) {
                $con = $branch->platforms[1]['id'];
                try {
                    $employees = \Illuminate\Support\Facades\DB::connection($con)
                        ->table('employees')
                        ->where('deleted', 0)
                        ->where(function ($q) use ($query) {
                            $q->where('fname', 'like', "%{$query}%")
                              ->orWhere('lname', 'like', "%{$query}%")
                              ->orWhere('email', 'like', "%{$query}%");
                        })
                        ->select('id', 'fname', 'mname', 'lname', 'email')
                        ->limit(20)
                        ->get();

                    foreach ($employees as $emp) {
                        $existing = BranchUser::where('parent_id', $emp->id)
                            ->where('parent_type', \App\Models\Employee::class)
                            ->where('branch_code', $branch->id)
                            ->first();

                        $results[] = [
                            'id' => $emp->id,
                            'employee_id' => null,
                            'name' => trim($emp->fname . ' ' . $emp->mname . ' ' . $emp->lname),
                            'email' => $emp->email,
                            'branch_code' => $branch->id,
                            'platform' => 'College',
                            'type' => 'employee',
                            'already_added' => $existing ? true : false,
                        ];
                    }
                } catch (\Exception $e) {
                    // Connection failed, skip
                }
            }
        }

        return response()->json($results);
    }

    // ─── Branch User (SSO) Methods ─────────────────────────────

    public function storeBranchUser(Request $request)
    {
        $validated = $request->validate([
            'parent_id'    => 'required|string|max:100',
            'parent_type'  => 'required|in:employee,student',
            'branch_code'  => 'required|string|max:50',
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email|max:255',
            'permissions'  => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $typeMap = [
            'employee' => \App\Models\Employee::class,
            'student'  => \App\Models\Student::class,
        ];

        $branchUser = BranchUser::create([
            'parent_id'   => $validated['parent_id'],
            'parent_type' => $typeMap[$validated['parent_type']],
            'branch_code' => $validated['branch_code'],
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'is_active'   => true,
        ]);

        if (!empty($validated['permissions'])) {
            $branchUser->syncPermissions($validated['permissions']);
        }

        return redirect()->route('user-access', ['tab' => 'branch'])->with('success', "Branch access granted to {$branchUser->name}.");
    }

    public function updateBranchUser(Request $request, BranchUser $branchUser)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email|max:255',
            'is_active'    => 'boolean',
            'permissions'  => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $oldValues = ['name' => $branchUser->name, 'email' => $branchUser->email, 'is_active' => $branchUser->is_active, 'permissions' => $branchUser->getPermissionNames()->toArray()];

        $branchUser->update([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'is_active' => $validated['is_active'] ?? $branchUser->is_active,
        ]);

        $branchUser->syncPermissions($validated['permissions'] ?? []);

        $newPerms = $validated['permissions'] ?? [];
        (new AuditService)->log('update', 'user_access', $branchUser, $oldValues, "Updated branch user: {$validated['name']}, permissions: " . implode(', ', $newPerms));

        return redirect()->route('user-access', ['tab' => 'branch'])->with('success', "Branch access updated for {$branchUser->name}.");
    }

    public function deleteBranchUser(BranchUser $branchUser)
    {
        $name = $branchUser->name;
        (new AuditService)->log('delete', 'user_access', $branchUser, null, "Removed branch access for: {$name} ({$branchUser->branch_code})");
        $branchUser->delete();

        return redirect()->route('user-access', ['tab' => 'branch'])->with('success', "Branch access removed for {$name}.");
    }

    private function getBranchCodes(): array
    {
        $configKey = env('DB_CONFIG_KEY', '');
        $credsPath = base_path('creds.json');

        if (!$configKey || !file_exists($credsPath)) {
            return ['main']; // default
        }

        $json = json_decode(file_get_contents($credsPath), true) ?? [];
        $branches = $json[$configKey]['databases']['branches'] ?? [];

        return !empty($branches) ? array_map(function ($b) { return $b['code']; }, $branches) : ['main'];
    }

    /**
     * Update employee password in the branch database.
     * K-12 uses md5, College uses bcrypt.
     */
    private function updateBranchPassword($parentId, $branchCode, $newPassword)
    {
        $branches = \App\Libraries\Branch::get($branchCode, false, false, false);
        if ($branches->isEmpty()) return;

        $branch = $branches[0];

        // Try K-12 first (integer ID = K-12)
        if (is_numeric($parentId) && isset($branch->platforms[0])) {
            try {
                \Illuminate\Support\Facades\DB::connection($branch->platforms[0]['id'])
                    ->table('employee_db')
                    ->where('id', $parentId)
                    ->update(['password' => md5($newPassword)]);
            } catch (\Exception $e) {
                // skip
            }
        }

        // College (UUID ID)
        if (!is_numeric($parentId) && isset($branch->platforms[1])) {
            try {
                // Find the employee's user_id, then update user password
                $employee = \Illuminate\Support\Facades\DB::connection($branch->platforms[1]['id'])
                    ->table('employees')
                    ->where('id', $parentId)
                    ->first();

                if ($employee && $employee->user_id) {
                    \Illuminate\Support\Facades\DB::connection($branch->platforms[1]['id'])
                        ->table('users')
                        ->where('id', $employee->user_id)
                        ->update(['password' => bcrypt($newPassword)]);
                }
            } catch (\Exception $e) {
                // skip
            }
        }
    }
}
