@extends('layouts.app')
@section('title', 'Chart of Accounts')

@section('content')
@php
    $accountCount = $accounts instanceof \Illuminate\Pagination\LengthAwarePaginator ? $accounts->total() : count($accounts);
@endphp

<x-page-header title="Chart of Accounts" :subtitle="$accountCount . ' accounts'">
    <x-slot name="actions">
        <button @click="$dispatch('open-modal', 'add-account')" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add Account
        </button>
    </x-slot>
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
        <label class="form-label">Search</label>
        <input type="text" name="search" value="{{ request('search') }}" class="form-input w-52" placeholder="Code or name...">
    </div>
    <div>
        <label class="form-label">Account Type</label>
        <select name="account_type" class="form-input w-44">
            <option value="">All Types</option>
            @foreach(['asset', 'liability', 'equity', 'revenue', 'expense'] as $t)
                <option value="{{ $t }}" {{ request('account_type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
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
<x-data-table :searchable="false">
    <thead>
        <tr>
            <th>Account Code</th>
            <th>Account Name</th>
            <th>Type</th>
            <th class="text-right">Balance</th>
            <th class="w-16">Actions</th>
        </tr>
    </thead>
    <tbody id="coa-tbody">
        @forelse($accounts as $account)
        @php
            $_map = ['asset' => 'badge-info', 'liability' => 'badge-warning', 'equity' => 'badge-success', 'revenue' => 'badge-success', 'expense' => 'badge-danger'];
            $typeBadge = $_map[$account->account_type ?? ''] ?? 'badge-neutral';
            $hasChildren = ($account->child_count ?? 0) > 0;
        @endphp

        @if($hasChildren)
        <tr class="bg-gray-50/50 font-semibold coa-parent-row"
            data-id="{{ $account->id }}"
            data-url="{{ route('gl.accounts.children', $account->id) }}"
            data-open="0"
            onclick="toggleChildren(this)">
            <td>
                <span class="inline-flex items-center gap-1">
                    <svg class="coa-arrow w-3.5 h-3.5 text-secondary-400 transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    <span class="text-secondary-900">{{ $account->account_code }}</span>
                </span>
            </td>
            <td>
                {{ $account->account_name }}
                <span class="text-xs text-secondary-400 font-normal">({{ $account->child_count }})</span>
            </td>
            <td><span class="badge {{ $typeBadge }}">{{ ucfirst($account->account_type ?? '-') }}</span></td>
            <td class="text-right font-medium {{ $account->balance > 0 ? 'text-green-600' : ($account->balance < 0 ? 'text-red-600' : 'text-secondary-400') }}">
                {{ $account->balance != 0 ? '₱' . number_format(abs($account->balance), 2) : '-' }}
            </td>
            <td onclick="event.stopPropagation()">
                <button onclick="openEditModal({{ json_encode(['id'=>$account->id,'account_code'=>$account->account_code,'account_name'=>$account->account_name,'account_type'=>$account->account_type,'normal_balance'=>$account->normal_balance,'parent_id'=>$account->parent_id,'notes'=>$account->notes ?? '']) }})"
                    class="text-primary-600 hover:text-primary-700 text-sm font-medium">Edit</button>
            </td>
        </tr>
        {{-- Loading placeholder row (hidden by default) --}}
        <tr class="coa-loading-{{ $account->id }}" style="display:none">
            <td colspan="5" class="pl-10 py-3 text-sm text-secondary-400">
                <svg class="animate-spin w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Loading sub-accounts…
            </td>
        </tr>

        @else
        {{-- Standalone account --}}
        <tr class="cursor-pointer hover:bg-primary-50/50" onclick="window.location='{{ route('gl.accounts.show', $account) }}'">
            <td class="font-medium text-secondary-900">{{ $account->account_code }}</td>
            <td class="font-medium">{{ $account->account_name }}</td>
            <td><span class="badge {{ $typeBadge }}">{{ ucfirst($account->account_type ?? '-') }}</span></td>
            <td class="text-right font-medium {{ $account->balance > 0 ? 'text-green-600' : ($account->balance < 0 ? 'text-red-600' : 'text-secondary-400') }}">
                {{ $account->balance != 0 ? '₱' . number_format(abs($account->balance), 2) : '-' }}
            </td>
            <td onclick="event.stopPropagation()">
                <button onclick="openEditModal({{ json_encode(['id'=>$account->id,'account_code'=>$account->account_code,'account_name'=>$account->account_name,'account_type'=>$account->account_type,'normal_balance'=>$account->normal_balance,'parent_id'=>$account->parent_id,'notes'=>$account->notes ?? '']) }})"
                    class="text-primary-600 hover:text-primary-700 text-sm font-medium">Edit</button>
            </td>
        </tr>
        @endif

        @empty
        <tr>
            <td colspan="5" class="text-center text-secondary-400 py-8">
                <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21" /></svg>
                No accounts found.
            </td>
        </tr>
        @endforelse
    </tbody>
    @if($accounts instanceof \Illuminate\Pagination\LengthAwarePaginator && $accounts->hasPages())
    <x-slot name="footer">
        {{ $accounts->withQueryString()->links() }}
    </x-slot>
    @endif
</x-data-table>

{{-- Add Account Modal --}}
<x-modal name="add-account" title="Add Account" maxWidth="2xl">
    <form action="{{ route('gl.accounts.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Account Code <span class="text-danger-500">*</span></label>
                <input type="text" name="account_code" class="form-input" required placeholder="e.g., 1010-001">
            </div>
            <div>
                <label class="form-label">Account Name <span class="text-danger-500">*</span></label>
                <input type="text" name="account_name" class="form-input" required placeholder="Account name">
            </div>
            <div>
                <label class="form-label">Account Type <span class="text-danger-500">*</span></label>
                <select name="account_type" class="form-input" required>
                    <option value="">Select Type</option>
                    @foreach($accountTypes as $t)
                        <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Parent Account</label>
                <select name="parent_id" class="form-input">
                    <option value="">None (Top Level)</option>
                    @foreach($parentAccounts as $pa)
                        <option value="{{ $pa->id }}">{{ $pa->account_code }} - {{ $pa->account_name }}</option>
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
                <textarea name="notes" class="form-input" rows="2" placeholder="Optional notes"></textarea>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'add-account')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save Account</button>
        </div>
    </form>
</x-modal>

{{-- Single shared Edit Modal --}}
<x-modal name="edit-account" title="Edit Account" maxWidth="2xl">
    <form id="edit-account-form" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Account Code <span class="text-danger-500">*</span></label>
                <input type="text" name="account_code" id="edit-account_code" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Account Name <span class="text-danger-500">*</span></label>
                <input type="text" name="account_name" id="edit-account_name" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Account Type <span class="text-danger-500">*</span></label>
                <select name="account_type" id="edit-account_type" class="form-input" required>
                    @foreach($accountTypes as $t)
                        <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Parent Account</label>
                <select name="parent_id" id="edit-parent_id" class="form-input">
                    <option value="">None (Top Level)</option>
                    @foreach($parentAccounts as $pa)
                        <option value="{{ $pa->id }}">{{ $pa->account_code }} - {{ $pa->account_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Normal Balance <span class="text-danger-500">*</span></label>
                <select name="normal_balance" id="edit-normal_balance" class="form-input" required>
                    <option value="debit">Debit</option>
                    <option value="credit">Credit</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="form-label">Notes</label>
                <textarea name="notes" id="edit-notes" class="form-input" rows="2"></textarea>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'edit-account')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Update Account</button>
        </div>
    </form>
</x-modal>

<script>
function openEditModal(account) {
    document.getElementById('edit-account-form').action = '{{ url('gl/accounts') }}/' + account.id;
    document.getElementById('edit-account_code').value   = account.account_code || '';
    document.getElementById('edit-account_name').value   = account.account_name || '';
    document.getElementById('edit-account_type').value   = account.account_type || '';
    document.getElementById('edit-normal_balance').value = account.normal_balance || '';
    document.getElementById('edit-parent_id').value      = account.parent_id || '';
    document.getElementById('edit-notes').value          = account.notes || '';
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-account' }));
}

function toggleChildren(row) {
    var id   = row.getAttribute('data-id');
    var url  = row.getAttribute('data-url');
    var open = row.getAttribute('data-open') === '1';
    var arrow = row.querySelector('.coa-arrow');

    if (open) {
        // Collapse: hide all child rows for this parent
        var children = document.querySelectorAll('.coa-child-of-' + id);
        children.forEach(function(el) { el.style.display = 'none'; });
        arrow.style.transform = '';
        row.setAttribute('data-open', '0');
        return;
    }

    // Already loaded — just show
    var existing = document.querySelectorAll('.coa-child-of-' + id);
    if (existing.length > 0) {
        existing.forEach(function(el) { el.style.display = ''; });
        arrow.style.transform = 'rotate(90deg)';
        row.setAttribute('data-open', '1');
        return;
    }

    // First open — fetch via AJAX
    var loadingRow = document.querySelector('.coa-loading-' + id);
    if (loadingRow) loadingRow.style.display = '';

    fetch(url)
        .then(function(r) { return r.text(); })
        .then(function(html) {
            if (loadingRow) loadingRow.style.display = 'none';

            // Parse returned <tr> elements and insert after loading row (or parent row)
            var tmp = document.createElement('tbody');
            tmp.innerHTML = html;
            var insertAfter = loadingRow || row;
            Array.from(tmp.querySelectorAll('tr')).reverse().forEach(function(tr) {
                tr.classList.add('coa-child-of-' + id);
                insertAfter.insertAdjacentElement('afterend', tr);
            });

            arrow.style.transform = 'rotate(90deg)';
            row.setAttribute('data-open', '1');
        })
        .catch(function() {
            if (loadingRow) loadingRow.style.display = 'none';
        });
}
</script>
@endsection
