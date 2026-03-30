@extends('layouts.app')
@section('title', 'User Access')

@section('content')
<x-page-header title="User Access" subtitle="Manage users and their system permissions">
    <x-slot:actions>
        <button @click="$dispatch('open-modal', 'add-user')" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add User
        </button>
    </x-slot:actions>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Users Table --}}
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
                    <td>
                        @php
                            $rolePerms = $role ? $role->permissions->pluck('name') : collect();
                            $directPerms = $user->getDirectPermissions()->pluck('name');
                            $allPerms = $user->getAllPermissions()->pluck('name');
                        @endphp
                        <span class="text-sm text-secondary-500">{{ $allPerms->count() }} permissions</span>
                    </td>
                    <td class="text-sm text-secondary-500">{{ $user->created_at?->format('M d, Y') }}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <button @click="$dispatch('open-modal', 'edit-user-{{ $user->id }}')" class="text-primary-600 hover:text-primary-700 text-sm font-medium">Edit</button>
                            <button @click="$dispatch('open-modal', 'perms-user-{{ $user->id }}')" class="text-secondary-600 hover:text-secondary-700 text-sm font-medium">Permissions</button>
                            @if($user->id !== auth()->id())
                            <form method="POST" data-turbo="false" action="{{ route('user-access.delete', $user) }}" onsubmit="return confirm('Delete this user?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-danger-500 hover:text-danger-700 text-sm font-medium">Delete</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-secondary-400 py-8">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Roles & Permissions Overview --}}
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

{{-- Add User Modal --}}
@php
    $menuAccess = [
        'Budget Management' => [
            'budget.view' => 'View Budgets',
            'budget.create' => 'Create Budget',
            'budget.edit' => 'Edit Budget',
            'budget.approve' => 'Approve Budget',
        ],
        'Accounts Payable' => [
            'bill.view' => 'View Bills',
            'bill.create' => 'Create Bill',
            'bill.approve' => 'Approve Bill',
            'bill.post' => 'Post Bill',
            'disbursement.view' => 'View Disbursements',
            'disbursement.create' => 'Create Disbursement',
            'disbursement.approve' => 'Approve Disbursement',
            'disbursement.pay' => 'Process Payment',
        ],
        'Accounts Receivable' => [
            'invoice.view' => 'View Invoices',
            'invoice.create' => 'Create Invoice',
            'collection.view' => 'View Collections',
            'collection.create' => 'Create Collection',
        ],
        'General Ledger' => [
            'je.view' => 'View Journal Entries',
            'je.create' => 'Create JE',
            'je.post' => 'Post JE',
            'je.reverse' => 'Reverse JE',
            'period.close' => 'Close Period',
        ],
        'Reports & System' => [
            'report.view' => 'View Reports',
            'report.export' => 'Export Reports',
            'audit.view' => 'Audit Trail',
            'settings.manage' => 'System Settings & User Access',
        ],
    ];
@endphp
<x-modal name="add-user" title="Add User" maxWidth="4xl">
    <form action="{{ route('user-access.store') }}" method="POST" data-turbo="false" x-data="{
        role: '',
        rolePermissions: @js($roles->mapWithKeys(fn($r) => [$r->name => $r->permissions->pluck('name')])),
        get currentPerms() { return this.rolePermissions[this.role] || []; },
        isChecked(perm) { return this.currentPerms.includes(perm); }
    }">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Left: Account Info --}}
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

            {{-- Right: Menu Access --}}
            <div>
                <label class="form-label mb-2">Menu Access</label>
                <div class="border border-gray-200 rounded-lg max-h-80 overflow-y-auto">
                    @foreach($menuAccess as $section => $perms)
                    <div class="border-b border-gray-100 last:border-0">
                        <div class="px-3 py-2 bg-gray-50 text-xs font-bold text-secondary-600 uppercase">{{ $section }}</div>
                        <div class="px-3 py-2 space-y-1.5">
                            @foreach($perms as $permKey => $permLabel)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $permKey }}"
                                       :checked="isChecked('{{ $permKey }}')"
                                       class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-secondary-700">{{ $permLabel }}</span>
                                <span x-show="isChecked('{{ $permKey }}')" class="text-[10px] text-secondary-400 ml-auto">(from role)</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                <p class="text-xs text-secondary-400 mt-1">Checkboxes auto-fill based on role. You can customize further.</p>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'add-user')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Create User</button>
        </div>
    </form>
</x-modal>

{{-- Edit User Modals --}}
@foreach($users as $user)
<x-modal name="edit-user-{{ $user->id }}" title="Edit: {{ $user->name }}" maxWidth="lg">
    <form action="{{ route('user-access.update', $user) }}" method="POST" data-turbo="false">
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
                <input type="password" name="password" class="form-input" minlength="8" placeholder="Enter new password">
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

{{-- Permissions Modal --}}
<x-modal name="perms-user-{{ $user->id }}" title="Permissions: {{ $user->name }}" maxWidth="4xl">
    <form action="{{ route('user-access.permissions', $user) }}" method="POST" data-turbo="false">
        @csrf @method('PUT')

        @php $userPerms = $user->getAllPermissions()->pluck('name'); @endphp

        <p class="text-sm text-secondary-500 mb-4">
            Role: <strong>{{ ucwords(str_replace('_', ' ', $user->roles->first()->name ?? 'None')) }}</strong> —
            Check/uncheck to customize this user's access. Role permissions are included by default.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
            @foreach($permissionGroups as $module => $perms)
            <div class="border border-gray-200 rounded-lg p-3">
                <h4 class="text-xs font-bold text-secondary-700 uppercase mb-2">{{ ucfirst($module) }}</h4>
                <div class="space-y-1.5">
                    @foreach($perms as $perm)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                               {{ $userPerms->contains($perm->name) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
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
