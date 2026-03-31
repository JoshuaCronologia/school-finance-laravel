<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\BranchUser;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class AccessRightsController extends Controller
{
    public function index(Request $request)
    {
        $query = BranchUser::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('parent_id', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('branch_code')) {
            $query->where('branch_code', $request->branch_code);
        }

        if ($request->filled('parent_type')) {
            $typeMap = [
                'employee' => \App\Models\Employee::class,
                'student'  => \App\Models\Student::class,
            ];
            $query->where('parent_type', $typeMap[$request->parent_type] ?? $request->parent_type);
        }

        $branchUsers = $query->latest()->paginate(20)->withQueryString();
        $ssoPermissions = config('acl.permissions', []);

        // Get branch codes from creds.json
        $branchCodes = $this->getBranchCodes();

        return view('pages.system.access-rights', compact('branchUsers', 'ssoPermissions', 'branchCodes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id'   => 'required|string|max:100',
            'parent_type' => 'required|in:employee,student',
            'branch_code' => 'required|string|max:50',
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
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

        return back()->with('success', "Access granted to {$branchUser->name}.");
    }

    public function update(Request $request, BranchUser $branchUser)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'is_active'   => 'boolean',
            'permissions'  => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $branchUser->update([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'is_active' => $validated['is_active'] ?? $branchUser->is_active,
        ]);

        $branchUser->syncPermissions($validated['permissions'] ?? []);

        return back()->with('success', "Access updated for {$branchUser->name}.");
    }

    public function destroy(BranchUser $branchUser)
    {
        $name = $branchUser->name;
        $branchUser->delete();

        return back()->with('success', "Access removed for {$name}.");
    }

    private function getBranchCodes(): array
    {
        $configKey = env('DB_CONFIG_KEY', '');
        $credsPath = base_path('creds.json');

        if (!$configKey || !file_exists($credsPath)) {
            return [];
        }

        $json = json_decode(file_get_contents($credsPath), true) ?? [];
        $config = $json[$configKey] ?? [];
        $branches = $config['databases']['branches'] ?? [];

        return array_map(fn ($b) => $b['code'], $branches);
    }
}
