<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\BranchUser;
use App\Models\User;
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
        $permissionGroups = $permissions->groupBy(fn ($p) => explode('.', $p->name)[0]);

        // Branch users
        $branchQuery = BranchUser::query();
        if ($request->filled('branch_search')) {
            $s = $request->branch_search;
            $branchQuery->where(fn ($q) => $q->where('name', 'ilike', "%{$s}%")
                ->orWhere('email', 'ilike', "%{$s}%")
                ->orWhere('parent_id', 'ilike', "%{$s}%"));
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

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('user-access')->with('success', "User {$user->name} updated.");
    }

    public function updatePermissions(Request $request, User $user)
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $user->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('user-access')->with('success', "Permissions updated for {$user->name}.");
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('user-access')->with('success', 'User deleted.');
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

        $branchUser->update([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'is_active' => $validated['is_active'] ?? $branchUser->is_active,
        ]);

        $branchUser->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('user-access', ['tab' => 'branch'])->with('success', "Branch access updated for {$branchUser->name}.");
    }

    public function deleteBranchUser(BranchUser $branchUser)
    {
        $name = $branchUser->name;
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

        return !empty($branches) ? array_map(fn ($b) => $b['code'], $branches) : ['main'];
    }
}
