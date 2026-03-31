@extends('layouts.app')
@section('title', 'Access Rights')

@section('content')
<x-page-header title="Access Rights" subtitle="Manage which SIS users can access the Accounting system">
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif

{{-- Add New User --}}
<div class="card mb-6" x-data="{ open: false }">
    <div class="card-header flex items-center justify-between">
        <h3 class="text-sm font-semibold text-secondary-700">Grant Access</h3>
        <button @click="open = !open" class="btn-primary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add User
        </button>
    </div>
    <div x-show="open" x-collapse class="p-4 border-t border-gray-100">
        <form method="POST" action="{{ route('access-rights.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="form-label">Parent ID (SIS ID) <span class="text-danger-500">*</span></label>
                    <input type="text" name="parent_id" class="form-input" placeholder="e.g., EMP-001" required>
                </div>
                <div>
                    <label class="form-label">User Type <span class="text-danger-500">*</span></label>
                    <select name="parent_type" class="form-input" required>
                        <option value="employee">Employee</option>
                        <option value="student">Student</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Branch Code <span class="text-danger-500">*</span></label>
                    <select name="branch_code" class="form-input" required>
                        @foreach($branchCodes as $code)
                            <option value="{{ $code }}">{{ ucfirst($code) }}</option>
                        @endforeach
                        @if(empty($branchCodes))
                            <option value="main">Main</option>
                        @endif
                    </select>
                </div>
                <div>
                    <label class="form-label">Full Name <span class="text-danger-500">*</span></label>
                    <input type="text" name="name" class="form-input" placeholder="Juan Dela Cruz" required>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" placeholder="juan@school.edu.ph">
                </div>
            </div>
            <div class="mb-4">
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
            <button type="submit" class="btn-primary text-sm">Grant Access</button>
        </form>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-6">
    <form method="GET" class="p-4 flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[200px]">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Name, email, or ID..." value="{{ request('search') }}">
        </div>
        <div>
            <label class="form-label">Branch</label>
            <select name="branch_code" class="form-input">
                <option value="">All Branches</option>
                @foreach($branchCodes as $code)
                    <option value="{{ $code }}" {{ request('branch_code') === $code ? 'selected' : '' }}>{{ ucfirst($code) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Type</label>
            <select name="parent_type" class="form-input">
                <option value="">All Types</option>
                <option value="employee" {{ request('parent_type') === 'employee' ? 'selected' : '' }}>Employee</option>
                <option value="student" {{ request('parent_type') === 'student' ? 'selected' : '' }}>Student</option>
            </select>
        </div>
        <button type="submit" class="btn-primary">Filter</button>
        @if(request()->hasAny(['search', 'branch_code', 'parent_type']))
            <a href="{{ route('access-rights.index') }}" class="btn-secondary">Clear</a>
        @endif
    </form>
</div>

{{-- Users Table --}}
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
                    <td>
                        <x-badge :status="$bu->is_active ? 'active' : 'inactive'" />
                    </td>
                    <td>
                        <div class="flex items-center gap-1">
                            <button @click="$dispatch('open-modal', 'edit-branch-user-{{ $bu->id }}')" class="btn-icon text-secondary-500 hover:text-secondary-700" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                            </button>
                            <form method="POST" action="{{ route('access-rights.destroy', $bu) }}" onsubmit="return confirm('Remove access for {{ $bu->name }}?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon text-danger-500 hover:text-danger-700" title="Remove">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-secondary-400 py-8">No branch users found. Add users above to grant SIS access.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($branchUsers->hasPages())
    <div class="card-footer">
        {{ $branchUsers->links() }}
    </div>
    @endif
</div>

{{-- Edit Modals --}}
@foreach($branchUsers as $bu)
<x-modal name="edit-branch-user-{{ $bu->id }}" title="Edit Access — {{ $bu->name }}">
    <form method="POST" action="{{ route('access-rights.update', $bu) }}">
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
@endsection
