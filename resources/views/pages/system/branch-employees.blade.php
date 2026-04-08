@extends('layouts.app')
@section('title', $platform . ' Employees')

@section('content')
<div x-data="{
    showModal: false,
    selectedEmp: { id: '', name: '', email: '', branch: '' }
}">

<x-page-header :title="$platform . ' Employees'" subtitle="Grant access to the Finance system">
    <x-slot name="actions">
        <a href="{{ url('/user-access?tab=branch') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
            Back to User Access
        </a>
    </x-slot>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Platform Tabs --}}
<div class="flex gap-1 mb-4 border-b border-gray-200">
    <a href="{{ url('/user-access/kto12') }}"
       class="px-4 py-2.5 text-sm font-medium border-b-2 transition {{ $platform === 'K-12' ? 'border-primary-600 text-primary-700' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
        K-12 Employees
    </a>
    <a href="{{ url('/user-access/college') }}"
       class="px-4 py-2.5 text-sm font-medium border-b-2 transition {{ $platform === 'College' ? 'border-primary-600 text-primary-700' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
        College Employees
    </a>
</div>

{{-- Search --}}
<div class="card mb-4">
    <form method="GET" class="p-4 flex flex-wrap items-end gap-3">
        @if($branchCodes && count($branchCodes) > 1)
        <div class="min-w-[180px]">
            <label class="form-label">Branch</label>
            <select name="branch" class="form-input" onchange="this.form.submit()">
                @foreach($branchCodes as $code)
                    <option value="{{ $code }}" {{ $branchCode === $code ? 'selected' : '' }}>
                        {{ strtoupper($code) }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="flex-1 min-w-[200px]">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-input" placeholder="Name, email, or employee ID..." value="{{ $search }}">
        </div>
        <button type="submit" class="btn-primary">Search</button>
        @if($search || request('branch'))
            <a href="{{ url('/user-access/' . $platformSlug) }}" class="btn-secondary">Clear</a>
        @endif
    </form>
</div>

{{-- Employee List --}}
<div class="card">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    @if($platform === 'K-12')
                        <th>Employee ID</th>
                    @endif
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th class="w-32">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                @php
                    $empId = (string) $emp->id;
                    $empName = $platform === 'K-12'
                        ? trim(($emp->firstname ?? '') . ' ' . ($emp->middlename ?? '') . ' ' . ($emp->lastname ?? ''))
                        : trim(($emp->fname ?? '') . ' ' . ($emp->mname ?? '') . ' ' . ($emp->lname ?? ''));
                    $empEmail = $emp->email ?? '';
                    $hasAccess = in_array($empId, $existingIds);
                @endphp
                <tr class="{{ $hasAccess ? 'bg-green-50' : '' }}">
                    @if($platform === 'K-12')
                        <td class="font-mono text-sm">{{ $emp->employee_id ?? '-' }}</td>
                    @endif
                    <td class="font-medium">{{ $empName }}</td>
                    <td class="text-sm text-secondary-500">{{ $empEmail ?: '-' }}</td>
                    <td>
                        @if($hasAccess)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Has Access</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">No Access</span>
                        @endif
                    </td>
                    <td>
                        @if(!$hasAccess)
                            <button @click="selectedEmp = { id: '{{ $empId }}', name: '{{ addslashes($empName) }}', email: '{{ addslashes($empEmail) }}', branch: '{{ $branchCode }}' }; showModal = true" class="btn-primary text-xs px-3 py-1">
                                Grant Access
                            </button>
                        @else
                            <span class="text-xs text-green-600">Active</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $platform === 'K-12' ? 5 : 4 }}" class="text-center text-secondary-400 py-8">
                        No employees found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employees instanceof \Illuminate\Pagination\LengthAwarePaginator && $employees->hasPages())
    <div class="card-footer">{{ $employees->links() }}</div>
    @endif
</div>

{{-- Single Grant Access Modal --}}
<div x-show="showModal" x-transition class="modal-overlay" style="display: none;" @keydown.escape.window="showModal = false">
    <div class="absolute inset-0 bg-black/50" @click="showModal = false"></div>
    <div class="modal-content max-w-2xl relative" @click.away="showModal = false">
        <div class="modal-header">
            <h3 class="text-lg font-semibold text-secondary-900">Grant Access</h3>
            <button @click="showModal = false" class="btn-icon">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ route('user-access.grant-access') }}">
                @csrf
                <input type="hidden" name="parent_id" :value="selectedEmp.id">
                <input type="hidden" name="branch_code" :value="selectedEmp.branch">
                <input type="hidden" name="name" :value="selectedEmp.name">
                <input type="hidden" name="email" :value="selectedEmp.email">

                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="font-semibold text-secondary-900" x-text="selectedEmp.name"></div>
                    <div class="text-sm text-secondary-500" x-text="selectedEmp.email || 'No email'"></div>
                    <div class="text-xs text-secondary-400 mt-1">{{ $platform }} &middot; Branch: {{ strtoupper($branchCode) }}</div>
                </div>

                <div class="mb-4">
                    <label class="form-label mb-2">Set Login Password</label>
                    <input type="password" name="new_password" class="form-input" placeholder="Leave empty to keep current password">
                    <p class="text-xs text-secondary-400 mt-1">Optional. Set a new password for this employee to login.</p>
                </div>

                <div>
                    <label class="form-label mb-2">Assign Permissions</label>
                    <div class="flex flex-wrap gap-3">
                        @foreach($ssoPermissions as $perm)
                        <label class="inline-flex items-center gap-2 text-sm border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="permissions[]" value="{{ $perm }}" class="form-checkbox">
                            {{ ucfirst($perm) }}
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 mt-4 border-t border-gray-100">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Grant Access</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>
@endsection
