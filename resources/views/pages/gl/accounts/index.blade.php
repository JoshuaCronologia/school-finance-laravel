@extends('layouts.app')
@section('title', 'Chart of Accounts')

@section('content')
@php
    $accountCount = $accounts instanceof \Illuminate\Pagination\LengthAwarePaginator ? $accounts->total() : count($accounts);
@endphp

<x-page-header title="Chart of Accounts" :subtitle="$accountCount . ' accounts'">
    <x-slot name="actions">
        <button @click="$dispatch('open-modal', 'manage-types')" class="btn-secondary">
            Manage Types
        </button>
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
            $_clsLabel = $account->account_classification ? ' · ' . ucfirst($account->account_classification) : '';
            $_typeLabel = ucfirst($account->account_type ?? '-') . $_clsLabel;
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
            <td><span class="badge {{ $typeBadge }}">{{ $_typeLabel }}</span></td>
            <td class="text-right font-medium {{ $account->balance > 0 ? 'text-green-600' : ($account->balance < 0 ? 'text-red-600' : 'text-secondary-400') }}">
                {{ $account->balance != 0 ? '₱' . number_format(abs($account->balance), 2) : '-' }}
            </td>
            <td onclick="event.stopPropagation()">
                <button onclick="openEditModal({{ json_encode(['id'=>$account->id,'account_code'=>$account->account_code,'account_name'=>$account->account_name,'account_type'=>$account->account_type,'normal_balance'=>$account->normal_balance,'parent_id'=>$account->parent_id,'account_classification'=>$account->account_classification ?? '','department_id'=>$account->department_id,'notes'=>$account->notes ?? '']) }})"
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
            <td><span class="badge {{ $typeBadge }}">{{ $_typeLabel }}</span></td>
            <td class="text-right font-medium {{ $account->balance > 0 ? 'text-green-600' : ($account->balance < 0 ? 'text-red-600' : 'text-secondary-400') }}">
                {{ $account->balance != 0 ? '₱' . number_format(abs($account->balance), 2) : '-' }}
            </td>
            <td onclick="event.stopPropagation()">
                <button onclick="openEditModal({{ json_encode(['id'=>$account->id,'account_code'=>$account->account_code,'account_name'=>$account->account_name,'account_type'=>$account->account_type,'normal_balance'=>$account->normal_balance,'parent_id'=>$account->parent_id,'account_classification'=>$account->account_classification ?? '','department_id'=>$account->department_id,'notes'=>$account->notes ?? '']) }})"
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
        {{-- Hidden fields set by JS --}}
        <input type="hidden" name="account_type" id="add-account_type">
        <input type="hidden" name="account_classification" id="add-account_classification">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Parent Account</label>
                <select id="add-parent_id" name="parent_id" class="form-input">
                    <option value="">None (Top Level)</option>
                    @foreach($parentAccounts as $pa)
                        <option value="{{ $pa->id }}"
                            data-type="{{ $pa->account_type }}"
                            data-balance="{{ $pa->normal_balance }}"
                            data-code="{{ $pa->account_code }}">{{ $pa->account_code }} — {{ $pa->account_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Account Code <span class="text-danger-500">*</span></label>
                <input type="text" name="account_code" id="add-account_code" class="form-input" required placeholder="e.g., 1010-001">
            </div>
            <div>
                <label class="form-label">Account Name <span class="text-danger-500">*</span></label>
                <input type="text" name="account_name" class="form-input" required placeholder="Account name">
            </div>
            <div>
                <label class="form-label">Account Type <span class="text-danger-500">*</span></label>
                <select id="add-combined_type" class="form-input" required onchange="setCombinedType('add', this.value)">
                    <option value="">Select Type</option>
                    @foreach($coaTypes as $ct)
                        <option value="{{ $ct->base_type }}|{{ $ct->classification }}">{{ $ct->label }}</option>
                    @endforeach
                </select>
            </div>
            <div id="add-dept-row" style="display:none">
                <label class="form-label">Department <span class="text-xs text-secondary-400">(for budget charging)</span></label>
                <select name="department_id" id="add-department_id" class="form-input">
                    <option value="">— None —</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
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
        {{-- Hidden fields set by JS --}}
        <input type="hidden" name="account_type" id="edit-account_type">
        <input type="hidden" name="account_classification" id="edit-account_classification">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Parent Account</label>
                <select name="parent_id" id="edit-parent_id" class="form-input">
                    <option value="">None (Top Level)</option>
                    @foreach($parentAccounts as $pa)
                        <option value="{{ $pa->id }}"
                            data-type="{{ $pa->account_type }}"
                            data-balance="{{ $pa->normal_balance }}"
                            data-code="{{ $pa->account_code }}">{{ $pa->account_code }} — {{ $pa->account_name }}</option>
                    @endforeach
                </select>
            </div>
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
                <select id="edit-combined_type" class="form-input" required onchange="setCombinedType('edit', this.value)">
                    <option value="">Select Type</option>
                    <optgroup label="Assets">
                        <option value="asset|current">Current Asset</option>
                        <option value="asset|non-current">Non-Current Asset</option>
                    </optgroup>
                    <optgroup label="Liabilities">
                        <option value="liability|current">Current Liability</option>
                        <option value="liability|non-current">Non-Current Liability</option>
                    </optgroup>
                    <option value="equity|">Equity</option>
                    <option value="revenue|">Revenue</option>
                    <option value="expense|">Expense</option>
                </select>
            </div>
            <div id="edit-dept-row" style="display:none">
                <label class="form-label">Department <span class="text-xs text-secondary-400">(for budget charging)</span></label>
                <select name="department_id" id="edit-department_id" class="form-input">
                    <option value="">— None —</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
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
var _normalBalanceDefaults = { asset: 'debit', expense: 'debit', liability: 'credit', equity: 'credit', revenue: 'credit' };

function setCombinedType(prefix, val) {
    var parts = val.split('|');
    var type  = parts[0] || '';
    var cls   = parts[1] || '';
    document.getElementById(prefix + '-account_type').value           = type;
    document.getElementById(prefix + '-account_classification').value = cls;
    document.getElementById(prefix + '-normal_balance').value         = _normalBalanceDefaults[type] || '';

    var deptRow = document.getElementById(prefix + '-dept-row');
    if (deptRow) deptRow.style.display = (type === 'revenue' || type === 'expense') ? '' : 'none';
}

function combinedTypeValue(type, cls) {
    if (type === 'asset' || type === 'liability') return type + '|' + (cls || 'current');
    return (type || '') + '|';
}

// Add modal — parent change auto-fills type and suggests code
document.getElementById('add-parent_id').addEventListener('change', function () {
    var opt  = this.options[this.selectedIndex];
    var type = opt.getAttribute('data-type');
    var code = opt.getAttribute('data-code');

    if (type) {
        var combo = document.getElementById('add-combined_type');
        combo.value = combinedTypeValue(type, '');
        combo.disabled = true;
        setCombinedType('add', combo.value);
    } else {
        document.getElementById('add-combined_type').disabled = false;
    }

    var codeInput = document.getElementById('add-account_code');
    if (code && !codeInput.value) {
        codeInput.value = code + '-';
        codeInput.focus();
        codeInput.setSelectionRange(codeInput.value.length, codeInput.value.length);
    }
});

function openEditModal(account) {
    document.getElementById('edit-account-form').action = '{{ url('gl/accounts') }}/' + account.id;
    document.getElementById('edit-account_code').value   = account.account_code || '';
    document.getElementById('edit-account_name').value   = account.account_name || '';
    document.getElementById('edit-parent_id').value      = account.parent_id || '';
    document.getElementById('edit-notes').value          = account.notes || '';
    document.getElementById('edit-department_id').value  = account.department_id || '';
    var cv = combinedTypeValue(account.account_type, account.account_classification);
    document.getElementById('edit-combined_type').value  = cv;
    setCombinedType('edit', cv);
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
{{-- Manage Account Types Modal --}}
<x-modal name="manage-types" title="Manage Account Types" maxWidth="2xl">
    <div class="mb-4 space-y-1">
        @foreach($coaTypes as $ct)
        {{-- View row --}}
        <div id="ct-row-{{ $ct->id }}" class="flex items-center gap-3 py-2 border-b border-gray-100 text-sm">
            <div class="flex-1 font-medium">{{ $ct->label }}</div>
            <div class="w-24 text-secondary-500">{{ ucfirst($ct->base_type) }}</div>
            <div class="w-28 text-secondary-500">{{ $ct->classification ? ucfirst($ct->classification) : '—' }}</div>
            <div class="w-20 text-secondary-500">{{ ucfirst($ct->normal_balance) }}</div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button type="button" onclick="ctEdit({{ $ct->id }})" class="text-primary-600 hover:text-primary-700 text-xs font-medium">Edit</button>
                @if(!$ct->is_system)
                <form method="POST" action="{{ route('gl.account-types.destroy', $ct) }}" onsubmit="return confirm('Delete {{ addslashes($ct->label) }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-danger-500 hover:text-danger-700 text-xs font-medium">Delete</button>
                </form>
                @endif
            </div>
        </div>
        {{-- Inline edit row (hidden by default) --}}
        <div id="ct-edit-{{ $ct->id }}" style="display:none" class="bg-gray-50 rounded-lg p-3 border border-primary-200">
            <form method="POST" action="{{ route('gl.account-types.update', $ct) }}">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="form-label">Label</label>
                        <input type="text" name="label" value="{{ $ct->label }}" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Base Type</label>
                        <select name="base_type" class="form-input" required>
                            @foreach(['asset','liability','equity','revenue','expense'] as $bt)
                            <option value="{{ $bt }}" {{ $ct->base_type === $bt ? 'selected' : '' }}>{{ ucfirst($bt) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Classification</label>
                        <select name="classification" class="form-input">
                            <option value="">None</option>
                            <option value="current" {{ $ct->classification === 'current' ? 'selected' : '' }}>Current</option>
                            <option value="non-current" {{ $ct->classification === 'non-current' ? 'selected' : '' }}>Non-Current</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Normal Balance</label>
                        <select name="normal_balance" class="form-input" required>
                            <option value="debit" {{ $ct->normal_balance === 'debit' ? 'selected' : '' }}>Debit</option>
                            <option value="credit" {{ $ct->normal_balance === 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-3">
                    <button type="button" onclick="ctEdit({{ $ct->id }})" class="btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn-primary text-xs">Save</button>
                </div>
            </form>
        </div>
        @endforeach
    </div>

    {{-- Add new type form --}}
    <div class="border-t border-gray-200 pt-4">
        <p class="text-xs font-semibold text-secondary-600 uppercase tracking-wide mb-3">Add New Type</p>
        <form method="POST" action="{{ route('gl.account-types.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="form-label">Label <span class="text-danger-500">*</span></label>
                    <input type="text" name="label" class="form-input" required placeholder="e.g., Contra Asset, Other Income">
                </div>
                <div>
                    <label class="form-label">Base Type <span class="text-danger-500">*</span></label>
                    <select name="base_type" id="new-base_type" class="form-input" required onchange="document.getElementById('new-normal_balance').value=({'asset':'debit','expense':'debit','liability':'credit','equity':'credit','revenue':'credit'}[this.value]||'debit')">
                        <option value="">Select</option>
                        <option value="asset">Asset</option>
                        <option value="liability">Liability</option>
                        <option value="equity">Equity</option>
                        <option value="revenue">Revenue</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Classification</label>
                    <select name="classification" class="form-input">
                        <option value="">None</option>
                        <option value="current">Current</option>
                        <option value="non-current">Non-Current</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Normal Balance <span class="text-danger-500">*</span></label>
                    <select name="normal_balance" id="new-normal_balance" class="form-input" required>
                        <option value="debit">Debit</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button type="submit" class="btn-primary text-sm">Add Type</button>
            </div>
        </form>
    </div>
</x-modal>

<script>
function ctEdit(id) {
    var row  = document.getElementById('ct-row-' + id);
    var form = document.getElementById('ct-edit-' + id);
    var open = form.style.display !== 'none';
    row.style.display  = open ? '' : 'none';
    form.style.display = open ? 'none' : '';
}
</script>
@endsection
