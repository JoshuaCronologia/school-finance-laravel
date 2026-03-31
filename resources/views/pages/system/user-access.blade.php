@extends('layouts.app')
@section('title', 'User Access')

@section('content')
<x-page-header title="User Access" subtitle="Manage admin users and branch (SIS) access">
    <x-slot:actions>
        @if(($tab ?? 'admin') === 'admin')
            <button @click="$dispatch('open-modal', 'add-user')" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Add Admin User
            </button>
        @else
            <button @click="$dispatch('open-modal', 'add-branch-user')" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Add Branch User
            </button>
        @endif
    </x-slot:actions>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Tabs --}}
<div class="flex gap-1 mb-6 border-b border-gray-200">
    <a href="{{ route('user-access', ['tab' => 'admin']) }}"
       class="px-4 py-2.5 text-sm font-medium border-b-2 transition {{ ($tab ?? 'admin') === 'admin' ? 'border-primary-600 text-primary-700' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
        Admin Users
        <span class="ml-1 text-xs bg-gray-100 text-secondary-600 px-1.5 py-0.5 rounded-full">{{ $users->count() }}</span>
    </a>
    <a href="{{ route('user-access', ['tab' => 'branch']) }}"
       class="px-4 py-2.5 text-sm font-medium border-b-2 transition {{ ($tab ?? 'admin') === 'branch' ? 'border-primary-600 text-primary-700' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
        Branch Users (SSO)
        <span class="ml-1 text-xs bg-gray-100 text-secondary-600 px-1.5 py-0.5 rounded-full">{{ $branchUsers->total() }}</span>
    </a>
</div>

@if(($tab ?? 'admin') === 'admin')
{{-- ═══════════════════════════════════════════════════════════════
     ADMIN USERS TAB
     ═══════════════════════════════════════════════════════════════ --}}
<div class="card">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Permissions</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                @php $role = $user->roles->first(); @endphp
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-semibold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $user->name)[1] ?? '', 0, 1)) }}</span>
                            </div>
                            <span class="font-medium">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="text-sm text-secondary-500">{{ $user->email }}</td>
                    <td>
                        @if($role)
                            <span class="badge badge-info">{{ ucwords(str_replace('_', ' ', $role->name)) }}</span>
                        @else
                            <span class="badge badge-neutral">No Role</span>
                        @endif
                    </td>
                    <td><span class="text-sm text-secondary-500">{{ $user->getAllPermissions()->count() }} permissions</span></td>
                    <td class="text-sm text-secondary-500">{{ $user->created_at?->format('M d, Y') }}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <button @click="$dispatch('open-modal', 'edit-user-{{ $user->id }}')" class="text-primary-600 hover:text-primary-700 text-sm font-medium">Edit</button>
                            <button @click="$dispatch('open-modal', 'perms-user-{{ $user->id }}')" class="text-secondary-600 hover:text-secondary-700 text-sm font-medium">Permissions</button>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('user-access.delete', $user) }}" onsubmit="return confirm('Delete this user?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-danger-500 hover:text-danger-700 text-sm font-medium">Delete</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-secondary-400 py-8">No admin users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Roles Overview --}}
<div class="card mt-6">
    <div class="card-header"><h3 class="card-title">Roles & Default Permissions</h3></div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($roles as $role)
            <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold text-secondary-900 mb-2">{{ ucwords(str_replace('_', ' ', $role->name)) }}</h4>
                <div class="flex flex-wrap gap-1">
                    @foreach($role->permissions->sortBy('name') as $perm)
                    <span class="inline-block px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded">{{ $perm->name }}</span>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@else
{{-- ═══════════════════════════════════════════════════════════════
     BRANCH USERS (SSO) TAB
     ═══════════════════════════════════════════════════════════════ --}}

{{-- Search --}}
<div class="card mb-4">
    <form method="GET" action="{{ route('user-access') }}" class="p-4 flex flex-wrap items-end gap-3">
        <input type="hidden" name="tab" value="branch">
        <div class="flex-1 min-w-[200px]">
            <label class="form-label">Search</label>
            <input type="text" name="branch_search" class="form-input" placeholder="Name, email, or SIS ID..." value="{{ request('branch_search') }}">
        </div>
        <button type="submit" class="btn-primary">Search</button>
        @if(request('branch_search'))
            <a href="{{ route('user-access', ['tab' => 'branch']) }}" class="btn-secondary">Clear</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>SIS ID</th>
                    <th>Type</th>
                    <th>Branch</th>
                    <th>Permissions</th>
                    <th>Status</th>
                    <th class="w-24">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branchUsers as $bu)
                <tr>
                    <td>
                        <div class="font-medium">{{ $bu->name }}</div>
                        <div class="text-xs text-secondary-400">{{ $bu->email ?? '-' }}</div>
                    </td>
                    <td class="font-mono text-sm">{{ $bu->parent_id }}</td>
                    <td>
                        @if($bu->isEmployee())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">Employee</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700">Student</span>
                        @endif
                    </td>
                    <td class="font-medium">{{ ucfirst($bu->branch_code) }}</td>
                    <td>
                        <div class="flex flex-wrap gap-1">
                            @foreach($bu->getPermissionNames() as $perm)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-gray-100 text-gray-600">{{ $perm }}</span>
                            @endforeach
                            @if($bu->getPermissionNames()->isEmpty())
                                <span class="text-xs text-secondary-400">None</span>
                            @endif
                        </div>
                    </td>
                    <td><x-badge :status="$bu->is_active ? 'active' : 'inactive'" /></td>
                    <td>
                        <div class="flex items-center gap-1">
                            <button @click="$dispatch('open-modal', 'edit-branch-user-{{ $bu->id }}')" class="btn-icon text-secondary-500 hover:text-secondary-700" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                            </button>
                            <form method="POST" action="{{ route('user-access.branch.delete', $bu) }}" onsubmit="return confirm('Remove access for {{ $bu->name }}?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon text-danger-500 hover:text-danger-700" title="Remove">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-secondary-400 py-8">No branch users found. Add users to grant SIS access to the Finance system.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($branchUsers->hasPages())
    <div class="card-footer">{{ $branchUsers->links() }}</div>
    @endif
</div>

{{-- Edit Branch User Modals --}}
@foreach($branchUsers as $bu)
<x-modal name="edit-branch-user-{{ $bu->id }}" title="Edit — {{ $bu->name }}">
    <form method="POST" action="{{ route('user-access.branch.update', $bu) }}">
        @csrf @method('PUT')
        <div class="space-y-4">
            <div>
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-input" value="{{ $bu->name }}" required>
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" value="{{ $bu->email }}">
            </div>
            <div>
                <label class="form-label">Active</label>
                <select name="is_active" class="form-input">
                    <option value="1" {{ $bu->is_active ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$bu->is_active ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <label class="form-label">Permissions</label>
                <div class="flex flex-wrap gap-3">
                    @php $currentPerms = $bu->getPermissionNames()->toArray(); @endphp
                    @foreach($ssoPermissions as $perm)
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="permissions[]" value="{{ $perm }}" class="form-checkbox"
                            {{ in_array($perm, $currentPerms) ? 'checked' : '' }}>
                        {{ ucfirst($perm) }}
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-3 pt-4 mt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'edit-branch-user-{{ $bu->id }}')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>
</x-modal>
@endforeach
@endif

{{-- ═══════════════════════════════════════════════════════════════
     MODALS (shared)
     ═══════════════════════════════════════════════════════════════ --}}

{{-- Add Admin User Modal --}}
@php
    $menuAccess = [
        'Budget Management' => ['budget.view' => 'View Budgets', 'budget.create' => 'Create Budget', 'budget.edit' => 'Edit Budget', 'budget.approve' => 'Approve Budget'],
        'Accounts Payable' => ['bill.view' => 'View Bills', 'bill.create' => 'Create Bill', 'bill.approve' => 'Approve Bill', 'bill.post' => 'Post Bill', 'disbursement.view' => 'View Disbursements', 'disbursement.create' => 'Create Disbursement', 'disbursement.approve' => 'Approve Disbursement', 'disbursement.pay' => 'Process Payment'],
        'Accounts Receivable' => ['invoice.view' => 'View Invoices', 'invoice.create' => 'Create Invoice', 'collection.view' => 'View Collections', 'collection.create' => 'Create Collection'],
        'General Ledger' => ['je.view' => 'View Journal Entries', 'je.create' => 'Create JE', 'je.post' => 'Post JE', 'je.reverse' => 'Reverse JE', 'period.close' => 'Close Period'],
        'Reports & System' => ['report.view' => 'View Reports', 'report.export' => 'Export Reports', 'audit.view' => 'Audit Trail', 'settings.manage' => 'System Settings & User Access'],
    ];
@endphp
<x-modal name="add-user" title="Add Admin User" maxWidth="4xl">
    <form action="{{ route('user-access.store') }}" method="POST" x-data="{
        role: '',
        rolePermissions: @js($roles->mapWithKeys(fn($r) => [$r->name => $r->permissions->pluck('name')])),
        get currentPerms() { return this.rolePermissions[this.role] || []; },
        isChecked(perm) { return this.currentPerms.includes(perm); }
    }">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label class="form-label">Full Name <span class="text-danger-500">*</span></label>
                    <input type="text" name="name" class="form-input" required placeholder="Juan Dela Cruz">
                </div>
                <div>
                    <label class="form-label">Email <span class="text-danger-500">*</span></label>
                    <input type="email" name="email" class="form-input" required placeholder="user@orangeapps.edu.ph">
                </div>
                <div>
                    <label class="form-label">Password <span class="text-danger-500">*</span></label>
                    <input type="password" name="password" class="form-input" required minlength="8" placeholder="Min. 8 characters">
                </div>
                <div>
                    <label class="form-label">Role <span class="text-danger-500">*</span></label>
                    <select name="role" class="form-input" required x-model="role">
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucwords(str_replace('_', ' ', $role->name)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="form-label mb-2">Menu Access</label>
                <div class="border border-gray-200 rounded-lg max-h-80 overflow-y-auto">
                    @foreach($menuAccess as $section => $perms)
                    <div class="border-b border-gray-100 last:border-0">
                        <div class="px-3 py-2 bg-gray-50 text-xs font-bold text-secondary-600 uppercase">{{ $section }}</div>
                        <div class="px-3 py-2 space-y-1.5">
                            @foreach($perms as $permKey => $permLabel)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $permKey }}" :checked="isChecked('{{ $permKey }}')" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-secondary-700">{{ $permLabel }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'add-user')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Create User</button>
        </div>
    </form>
</x-modal>

{{-- Add Branch User Modal --}}
<x-modal name="add-branch-user" title="Add Branch User (SSO)" maxWidth="2xl">
    <form method="POST" action="{{ route('user-access.branch.store') }}">
        @csrf
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">SIS ID <span class="text-danger-500">*</span></label>
                    <input type="text" name="parent_id" class="form-input" placeholder="e.g., EMP-001" required>
                </div>
                <div>
                    <label class="form-label">User Type <span class="text-danger-500">*</span></label>
                    <select name="parent_type" class="form-input" required>
                        <option value="employee">Employee</option>
                        <option value="student">Student</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Branch <span class="text-danger-500">*</span></label>
                    <select name="branch_code" class="form-input" required>
                        @foreach($branchCodes as $code)
                            <option value="{{ $code }}">{{ ucfirst($code) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Full Name <span class="text-danger-500">*</span></label>
                    <input type="text" name="name" class="form-input" placeholder="Juan Dela Cruz" required>
                </div>
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" placeholder="juan@school.edu.ph">
            </div>
            <div>
                <label class="form-label">Permissions</label>
                <div class="flex flex-wrap gap-3">
                    @foreach($ssoPermissions as $perm)
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="permissions[]" value="{{ $perm }}" class="form-checkbox">
                        {{ ucfirst($perm) }}
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-3 pt-4 mt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'add-branch-user')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Grant Access</button>
        </div>
    </form>
</x-modal>

{{-- Edit Admin User Modals --}}
@foreach($users as $user)
<x-modal name="edit-user-{{ $user->id }}" title="Edit: {{ $user->name }}" maxWidth="lg">
    <form action="{{ route('user-access.update', $user) }}" method="POST">
        @csrf @method('PUT')
        <div class="space-y-4">
            <div>
                <label class="form-label">Full Name <span class="text-danger-500">*</span></label>
                <input type="text" name="name" class="form-input" value="{{ $user->name }}" required>
            </div>
            <div>
                <label class="form-label">Email <span class="text-danger-500">*</span></label>
                <input type="email" name="email" class="form-input" value="{{ $user->email }}" required>
            </div>
            <div>
                <label class="form-label">New Password <span class="text-secondary-400 text-xs font-normal">(leave blank to keep)</span></label>
                <input type="password" name="password" class="form-input" minlength="8">
            </div>
            <div>
                <label class="form-label">Role <span class="text-danger-500">*</span></label>
                <select name="role" class="form-input" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $role->name)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'edit-user-{{ $user->id }}')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>
</x-modal>

<x-modal name="perms-user-{{ $user->id }}" title="Permissions: {{ $user->name }}" maxWidth="4xl">
    <form action="{{ route('user-access.permissions', $user) }}" method="POST">
        @csrf @method('PUT')
        @php $userPerms = $user->getAllPermissions()->pluck('name'); @endphp
        <p class="text-sm text-secondary-500 mb-4">
            Role: <strong>{{ ucwords(str_replace('_', ' ', $user->roles->first()->name ?? 'None')) }}</strong>
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
            @foreach($permissionGroups as $module => $perms)
            <div class="border border-gray-200 rounded-lg p-3">
                <h4 class="text-xs font-bold text-secondary-700 uppercase mb-2">{{ ucfirst($module) }}</h4>
                <div class="space-y-1.5">
                    @foreach($perms as $perm)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" {{ $userPerms->contains($perm->name) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm text-secondary-700">{{ ucfirst(explode('.', $perm->name)[1] ?? $perm->name) }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'perms-user-{{ $user->id }}')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save Permissions</button>
        </div>
    </form>
</x-modal>
@endforeach
@endsection
