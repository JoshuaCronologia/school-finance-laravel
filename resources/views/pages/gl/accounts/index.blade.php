@extends('layouts.app')
@section('title', 'Chart of Accounts')

@section('content')
@php
    $accountCount = $accounts instanceof \Illuminate\Pagination\LengthAwarePaginator ? $accounts->total() : count($accounts);
@endphp

<x-page-header title="Chart of Accounts" :subtitle="$accountCount . ' accounts'">
    <x-slot:actions>
        <button @click="$dispatch('open-modal', 'add-account')" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add Account
        </button>
    </x-slot:actions>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Filters --}}
<x-filter-bar action="{{ route('gl.accounts.index') }}" method="GET">
    <div>
        <label class="form-label">Account Type</label>
        <select name="type" class="form-input w-44">
            <option value="">All Types</option>
            @foreach(['asset', 'liability', 'equity', 'revenue', 'expense'] as $t)
                <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-input w-36">
            <option value="">All</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
</x-filter-bar>

{{-- Accounts Table --}}
<x-data-table search-placeholder="Search accounts...">
    <thead>
        <tr>
            <th>Account Code</th>
            <th>Account Name</th>
            <th>Type</th>
            <th>Normal Balance</th>
            <th>Children</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($accounts as $account)
        <tr>
            <td class="font-medium text-secondary-900">{{ $account->code }}</td>
            <td class="font-medium">
                @if($account->parent_id)
                    <span class="text-secondary-300 mr-1">&mdash;</span>
                @endif
                {{ $account->name }}
            </td>
            <td>
                @php
                    $typeBadge = match($account->type ?? '') {
                        'asset' => 'badge-info',
                        'liability' => 'badge-warning',
                        'equity' => 'badge-success',
                        'revenue' => 'badge-success',
                        'expense' => 'badge-danger',
                        default => 'badge-neutral',
                    };
                @endphp
                <span class="badge {{ $typeBadge }}">{{ ucfirst($account->type ?? '-') }}</span>
            </td>
            <td>{{ ucfirst($account->normal_balance ?? '-') }}</td>
            <td>
                @if(($account->children_count ?? 0) > 0)
                    <span class="text-sm text-secondary-500">{{ $account->children_count }} sub-accounts</span>
                @else
                    <span class="text-secondary-300">-</span>
                @endif
            </td>
            <td><x-badge :status="$account->status ?? 'active'" /></td>
            <td>
                <button @click="$dispatch('open-modal', 'edit-account-{{ $account->id }}')" class="text-primary-600 hover:text-primary-700 text-sm font-medium">Edit</button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center text-secondary-400 py-8">
                <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21" /></svg>
                No accounts found. Click "+ Add Account" to create one.
            </td>
        </tr>
        @endforelse
    </tbody>
    @if($accounts instanceof \Illuminate\Pagination\LengthAwarePaginator && $accounts->hasPages())
    <x-slot:footer>
        {{ $accounts->withQueryString()->links() }}
    </x-slot:footer>
    @endif
</x-data-table>

{{-- Add Account Modal --}}
<x-modal name="add-account" title="Add Account" maxWidth="2xl">
    <form action="{{ route('gl.accounts.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Account Code <span class="text-danger-500">*</span></label>
                <input type="text" name="code" class="form-input" required placeholder="e.g., 1010-001">
            </div>
            <div>
                <label class="form-label">Account Name <span class="text-danger-500">*</span></label>
                <input type="text" name="name" class="form-input" required placeholder="Account name">
            </div>
            <div>
                <label class="form-label">Account Type <span class="text-danger-500">*</span></label>
                <select name="type" class="form-input" required>
                    <option value="">Select Type</option>
                    <option value="asset">Asset</option>
                    <option value="liability">Liability</option>
                    <option value="equity">Equity</option>
                    <option value="revenue">Revenue</option>
                    <option value="expense">Expense</option>
                </select>
            </div>
            <div>
                <label class="form-label">Parent Account</label>
                <select name="parent_id" class="form-input">
                    <option value="">None (Top Level)</option>
                    @foreach($parentAccounts ?? $accounts as $pa)
                        <option value="{{ $pa->id }}">{{ $pa->code }} - {{ $pa->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Normal Balance <span class="text-danger-500">*</span></label>
                <select name="normal_balance" class="form-input" required>
                    <option value="">Select</option>
                    <option value="debit">Debit</option>
                    <option value="credit">Credit</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-input" rows="2" placeholder="Optional notes about this account"></textarea>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'add-account')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save Account</button>
        </div>
    </form>
</x-modal>

{{-- Edit Account Modals --}}
@foreach($accounts as $account)
<x-modal name="edit-account-{{ $account->id }}" title="Edit Account" maxWidth="2xl">
    <form action="{{ route('gl.accounts.update', $account) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Account Code <span class="text-danger-500">*</span></label>
                <input type="text" name="code" class="form-input" value="{{ $account->code }}" required>
            </div>
            <div>
                <label class="form-label">Account Name <span class="text-danger-500">*</span></label>
                <input type="text" name="name" class="form-input" value="{{ $account->name }}" required>
            </div>
            <div>
                <label class="form-label">Account Type <span class="text-danger-500">*</span></label>
                <select name="type" class="form-input" required>
                    @foreach(['asset', 'liability', 'equity', 'revenue', 'expense'] as $t)
                        <option value="{{ $t }}" {{ ($account->type ?? '') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Parent Account</label>
                <select name="parent_id" class="form-input">
                    <option value="">None (Top Level)</option>
                    @foreach($parentAccounts ?? $accounts as $pa)
                        @if($pa->id !== $account->id)
                            <option value="{{ $pa->id }}" {{ ($account->parent_id ?? '') == $pa->id ? 'selected' : '' }}>{{ $pa->code }} - {{ $pa->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Normal Balance <span class="text-danger-500">*</span></label>
                <select name="normal_balance" class="form-input" required>
                    <option value="debit" {{ ($account->normal_balance ?? '') == 'debit' ? 'selected' : '' }}>Debit</option>
                    <option value="credit" {{ ($account->normal_balance ?? '') == 'credit' ? 'selected' : '' }}>Credit</option>
                </select>
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    <option value="active" {{ ($account->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ ($account->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-input" rows="2">{{ $account->notes ?? '' }}</textarea>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'edit-account-{{ $account->id }}')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Update Account</button>
        </div>
    </form>
</x-modal>
@endforeach
@endsection
