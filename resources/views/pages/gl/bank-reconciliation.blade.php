@extends('layouts.app')
@section('title', 'Bank Reconciliation')

@section('content')
<x-page-header title="Bank Reconciliation" subtitle="Manage bank accounts, issued checks, and reconciliation">
    <x-slot name="actions">
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Tabs --}}
@php $tab = $tab ?? 'reconcile'; @endphp
<div class="flex gap-1 mb-6 border-b border-gray-200">
    <a href="{{ route('gl.bank-reconciliation', ['tab' => 'reconcile']) }}" class="px-4 py-2 text-sm font-medium rounded-t-lg {{ $tab === 'reconcile' ? 'bg-white border border-b-white border-gray-200 text-primary-700 -mb-px' : 'text-secondary-500 hover:text-secondary-700' }}">Reconciliation</a>
    <a href="{{ route('gl.bank-reconciliation', ['tab' => 'cib']) }}" class="px-4 py-2 text-sm font-medium rounded-t-lg {{ $tab === 'cib' ? 'bg-white border border-b-white border-gray-200 text-primary-700 -mb-px' : 'text-secondary-500 hover:text-secondary-700' }}">Cash in Bank</a>
    <a href="{{ route('gl.bank-reconciliation', ['tab' => 'statements']) }}" class="px-4 py-2 text-sm font-medium rounded-t-lg {{ $tab === 'statements' ? 'bg-white border border-b-white border-gray-200 text-primary-700 -mb-px' : 'text-secondary-500 hover:text-secondary-700' }}">Bank Statements</a>
    <a href="{{ route('gl.bank-reconciliation', ['tab' => 'accounts']) }}" class="px-4 py-2 text-sm font-medium rounded-t-lg {{ $tab === 'accounts' ? 'bg-white border border-b-white border-gray-200 text-primary-700 -mb-px' : 'text-secondary-500 hover:text-secondary-700' }}">Bank Accounts</a>
    <a href="{{ route('tax.check-writer') }}" class="px-4 py-2 text-sm font-medium rounded-t-lg text-secondary-500 hover:text-secondary-700">Check Writer (IC/CC)</a>
</div>

{{-- ============================================================ --}}
{{-- TAB: RECONCILIATION --}}
{{-- ============================================================ --}}
@if($tab === 'reconcile')
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="tab" value="reconcile">
            <div>
                <label class="form-label">Bank Account <span class="text-danger-500">*</span></label>
                <select name="bank_account_id" class="form-input w-64" required>
                    <option value="">Select Account</option>
                    @foreach($bankAccounts as $ba)
                        <option value="{{ $ba->id }}" {{ ($selectedBankId ?? '') == $ba->id ? 'selected' : '' }}>{{ $ba->full_label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">As of Date</label>
                <input type="date" name="as_of_date" class="form-input w-44" value="{{ $asOfDate ?? now()->toDateString() }}">
            </div>
            <div>
                <label class="form-label">Bank Statement Balance</label>
                <input type="number" name="statement_balance" class="form-input w-48" step="0.01" placeholder="Enter bank balance" value="{{ $statementBalance ?? '' }}">
            </div>
            <button type="submit" class="btn-primary">Reconcile</button>
        </form>
    </div>
</div>

@if(isset($reconData) && $reconData)
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Bank Statement Side --}}
    <div class="card">
        <div class="card-header bg-blue-50"><h3 class="card-title text-blue-800">Per Bank Statement</h3></div>
        <div class="card-body space-y-3">
            <div class="flex justify-between text-sm">
                <span>Bank Statement Balance</span>
                <span class="font-bold text-lg">{{ $reconData->bank_statement_balance !== null ? '₱' . number_format($reconData->bank_statement_balance, 2) : 'Not entered' }}</span>
            </div>
            @if($reconData->bank_statement_balance !== null)
            <div class="border-t border-gray-100 pt-3">
                <p class="text-xs font-semibold text-secondary-500 uppercase mb-2">Add: Deposits in Transit</p>
                @forelse($reconData->deposits_in_transit as $dep)
                <div class="flex justify-between text-sm pl-4 py-0.5">
                    <span class="text-secondary-600">{{ \Carbon\Carbon::parse($dep->posting_date)->format('M d') }} - {{ $dep->entry_number }}</span>
                    <span class="font-mono">₱{{ number_format($dep->debit, 2) }}</span>
                </div>
                @empty
                <p class="text-sm text-secondary-400 pl-4">None</p>
                @endforelse
                <div class="flex justify-between text-sm font-semibold pl-4 pt-1 border-t border-dashed border-gray-200">
                    <span>Total</span><span class="font-mono">₱{{ number_format($reconData->total_deposits_transit, 2) }}</span>
                </div>
            </div>
            <div class="border-t border-gray-100 pt-3">
                <p class="text-xs font-semibold text-secondary-500 uppercase mb-2">Less: Outstanding Checks</p>
                @forelse($reconData->outstanding_checks as $chk)
                <div class="flex justify-between text-sm pl-4 py-0.5">
                    <span class="text-secondary-600">{{ $chk->check_date->format('M d') }} - #{{ $chk->check_number }} - {{ $chk->payee }}</span>
                    <span class="font-mono text-danger-600">(₱{{ number_format($chk->amount, 2) }})</span>
                </div>
                @empty
                <p class="text-sm text-secondary-400 pl-4">None</p>
                @endforelse
                <div class="flex justify-between text-sm font-semibold pl-4 pt-1 border-t border-dashed border-gray-200">
                    <span>Total</span><span class="font-mono text-danger-600">(₱{{ number_format($reconData->total_outstanding_checks, 2) }})</span>
                </div>
            </div>
            <div class="border-t-2 border-gray-300 pt-3">
                <div class="flex justify-between font-bold text-base">
                    <span>Adjusted Bank Balance</span>
                    <span class="font-mono text-blue-800">₱{{ number_format($reconData->adjusted_bank_balance, 2) }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>
    {{-- Book Side --}}
    <div class="card">
        <div class="card-header bg-green-50"><h3 class="card-title text-green-800">Per Books ({{ $reconData->bank_account->chartAccount->account_code }})</h3></div>
        <div class="card-body space-y-3">
            <div class="flex justify-between text-sm">
                <span>Book Balance per GL</span>
                <span class="font-bold text-lg">₱{{ number_format($reconData->book_balance, 2) }}</span>
            </div>
            <div class="border-t-2 border-gray-300 pt-3">
                <div class="flex justify-between font-bold text-base">
                    <span>Adjusted Book Balance</span>
                    <span class="font-mono text-green-800">₱{{ number_format($reconData->book_balance, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@if($reconData->difference !== null)
<div class="card mb-6 {{ abs($reconData->difference) < 0.01 ? 'border-green-300' : 'border-red-300' }} border-2">
    <div class="card-body text-center py-4">
        @if(abs($reconData->difference) < 0.01)
        <div class="text-green-700 text-lg font-bold">RECONCILED - Balances Match</div>
        @else
        <div class="text-red-700 text-lg font-bold">UNRECONCILED - Difference: ₱{{ number_format(abs($reconData->difference), 2) }}</div>
        @endif
    </div>
</div>
@endif
@endif

{{-- ============================================================ --}}
{{-- TAB: ISSUED CHECKS --}}
{{-- ============================================================ --}}
@elseif($tab === 'checks')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="tab" value="checks">
            <div>
                <label class="form-label">Bank Account</label>
                <select name="bank_account_id" class="form-input w-56">
                    <option value="">All Banks</option>
                    @foreach($bankAccounts as $ba)
                        <option value="{{ $ba->id }}" {{ ($selectedBankId ?? '') == $ba->id ? 'selected' : '' }}>{{ $ba->full_label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-input w-40">
                    <option value="">All</option>
                    <option value="outstanding" {{ ($selectedStatus ?? '') === 'outstanding' ? 'selected' : '' }}>Outstanding</option>
                    <option value="cleared" {{ ($selectedStatus ?? '') === 'cleared' ? 'selected' : '' }}>Cleared</option>
                    <option value="voided" {{ ($selectedStatus ?? '') === 'voided' ? 'selected' : '' }}>Voided</option>
                </select>
            </div>
            <button type="submit" class="btn-primary text-sm">Filter</button>
            <button type="button" onclick="document.getElementById('add-check-form').style.display=document.getElementById('add-check-form').style.display==='none'?'':'none'" class="btn-secondary text-sm ml-auto">+ Add Check</button>
        </form>
    </div>
</div>

{{-- Add Check Form (hidden by default) --}}
<div id="add-check-form" class="card mb-4" style="display:none">
    <div class="card-header"><h3 class="card-title">Record New Check</h3></div>
    <div class="card-body">
        <form action="{{ route('gl.bank-reconciliation.store-check') }}" method="POST" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="form-label">Bank Account *</label>
                <select name="bank_account_id" class="form-input" required>
                    @foreach($bankAccounts as $ba)
                        <option value="{{ $ba->id }}">{{ $ba->full_label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Check Date *</label>
                <input type="date" name="check_date" class="form-input" value="{{ date('Y-m-d') }}" required>
            </div>
            <div>
                <label class="form-label">Check Number *</label>
                <input type="text" name="check_number" class="form-input" required placeholder="e.g. 000123">
            </div>
            <div>
                <label class="form-label">Payee *</label>
                <input type="text" name="payee" class="form-input" required placeholder="Supplier name">
            </div>
            <div>
                <label class="form-label">Amount *</label>
                <input type="number" name="amount" class="form-input" step="0.01" required>
            </div>
            <div>
                <label class="form-label">Remarks</label>
                <input type="text" name="remarks" class="form-input" placeholder="Optional">
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-primary">Save Check</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Check #</th>
                    <th>Bank</th>
                    <th>Payee</th>
                    <th class="text-right">Amount</th>
                    <th>Status</th>
                    <th>Cleared Date</th>
                    <th class="w-32">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($checks as $check)
                <tr>
                    <td>{{ $check->check_date->format('M d, Y') }}</td>
                    <td class="font-mono font-medium">{{ $check->check_number }}</td>
                    <td class="text-sm">{{ $check->bankAccount->bank_name }} {{ $check->bankAccount->account_type }}</td>
                    <td>{{ $check->payee }}</td>
                    <td class="text-right font-mono">₱{{ number_format($check->amount, 2) }}</td>
                    <td>
                        @if($check->status === 'cleared')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Cleared</span>
                        @elseif($check->status === 'voided')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Voided</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Outstanding</span>
                        @endif
                    </td>
                    <td>{{ $check->cleared_date ? $check->cleared_date->format('M d, Y') : '-' }}</td>
                    <td>
                        @if($check->status === 'outstanding')
                        <form action="{{ route('gl.bank-reconciliation.clear-check', $check) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="cleared_date" value="{{ date('Y-m-d') }}">
                            <button type="submit" class="text-green-600 hover:text-green-700 text-sm font-medium">Clear</button>
                        </form>
                        <form action="{{ route('gl.bank-reconciliation.void-check', $check) }}" method="POST" class="inline ml-2">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium" onclick="return confirm('Void this check?')">Void</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-secondary-400 py-8">No checks found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($checks->hasPages())
    <div class="card-body border-t">{{ $checks->appends(request()->query())->links() }}</div>
    @endif
</div>

{{-- ============================================================ --}}
{{-- TAB: CASH IN BANK (CIB) --}}
{{-- ============================================================ --}}
@elseif($tab === 'cib')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="tab" value="cib">
            <div>
                <label class="form-label">Bank Account *</label>
                <select name="bank_account_id" class="form-input w-64" required>
                    <option value="">Select Account</option>
                    @foreach($bankAccounts as $ba)
                        <option value="{{ $ba->id }}" {{ ($selectedBankId ?? '') == $ba->id ? 'selected' : '' }}>{{ $ba->full_label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">From</label>
                <input type="date" name="date_from" class="form-input w-40" value="{{ $dateFrom ?? now()->startOfMonth()->toDateString() }}">
            </div>
            <div>
                <label class="form-label">To</label>
                <input type="date" name="date_to" class="form-input w-40" value="{{ $dateTo ?? now()->toDateString() }}">
            </div>
            <button type="submit" class="btn-primary">View</button>
        </form>
    </div>
</div>

@if(isset($cibData) && $cibData)
<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4">
    <div class="card"><div class="card-body text-center">
        <span class="text-xs text-secondary-500 uppercase">Opening Balance</span>
        <p class="font-bold text-lg">₱{{ number_format($cibData->opening_balance, 2) }}</p>
    </div></div>
    <div class="card"><div class="card-body text-center">
        <span class="text-xs text-secondary-500 uppercase">Total Deposits</span>
        <p class="font-bold text-lg text-green-600">₱{{ number_format($cibData->total_debit, 2) }}</p>
    </div></div>
    <div class="card"><div class="card-body text-center">
        <span class="text-xs text-secondary-500 uppercase">Total Withdrawals</span>
        <p class="font-bold text-lg text-red-600">₱{{ number_format($cibData->total_credit, 2) }}</p>
    </div></div>
    <div class="card"><div class="card-body text-center">
        <span class="text-xs text-secondary-500 uppercase">Closing Balance</span>
        <p class="font-bold text-lg text-primary-700">₱{{ number_format($cibData->closing_balance, 2) }}</p>
    </div></div>
</div>

<div class="grid grid-cols-2 gap-4 mb-4">
    <div class="card"><div class="card-body flex justify-between">
        <span class="text-sm text-secondary-600">Cleared Checks</span>
        <span class="font-semibold text-green-600">₱{{ number_format($cibData->cleared_checks, 2) }}</span>
    </div></div>
    <div class="card"><div class="card-body flex justify-between">
        <span class="text-sm text-secondary-600">Outstanding Checks</span>
        <span class="font-semibold text-amber-600">₱{{ number_format($cibData->outstanding_checks, 2) }}</span>
    </div></div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">{{ $cibData->bank_account->full_label }} Transactions</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Entry #</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th class="text-right">Debit (In)</th>
                    <th class="text-right">Credit (Out)</th>
                    <th class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @php $runBalance = $cibData->opening_balance; @endphp
                @forelse($cibData->transactions as $txn)
                @php $runBalance += $txn->debit - $txn->credit; @endphp
                <tr>
                    <td class="text-sm">{{ \Carbon\Carbon::parse($txn->posting_date)->format('M d, Y') }}</td>
                    <td class="font-mono text-sm">{{ $txn->entry_number }}</td>
                    <td class="font-mono text-sm">{{ $txn->reference_number ?? '-' }}</td>
                    <td class="text-sm">{{ $txn->je_description ?? $txn->description ?? '-' }}</td>
                    <td class="text-right font-mono">{{ $txn->debit > 0 ? '₱' . number_format($txn->debit, 2) : '' }}</td>
                    <td class="text-right font-mono">{{ $txn->credit > 0 ? '₱' . number_format($txn->credit, 2) : '' }}</td>
                    <td class="text-right font-mono font-medium">₱{{ number_format($runBalance, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-secondary-400 py-8">No transactions. Select a bank account and date range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ============================================================ --}}
{{-- TAB: BANK STATEMENTS --}}
{{-- ============================================================ --}}
@elseif($tab === 'statements')
<div class="card mb-4">
    <div class="card-body">
        <div class="flex flex-wrap items-end gap-4">
            <form method="GET" class="flex items-end gap-4">
                <input type="hidden" name="tab" value="statements">
                <div>
                    <label class="form-label">Bank Account</label>
                    <select name="bank_account_id" class="form-input w-56" onchange="this.form.submit()">
                        <option value="">All Banks</option>
                        @foreach($bankAccounts as $ba)
                            <option value="{{ $ba->id }}" {{ ($selectedBankId ?? '') == $ba->id ? 'selected' : '' }}>{{ $ba->full_label }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            <button type="button" onclick="document.getElementById('upload-stmt-form').style.display=document.getElementById('upload-stmt-form').style.display==='none'?'':'none'" class="btn-primary text-sm ml-auto">Upload Statement</button>
        </div>
    </div>
</div>

{{-- Upload Form --}}
<div id="upload-stmt-form" class="card mb-4" style="display:none">
    <div class="card-header"><h3 class="card-title">Upload Bank Statement</h3></div>
    <div class="card-body">
        <form action="{{ route('gl.bank-reconciliation.upload-statement') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="form-label">Bank Account *</label>
                <select name="bank_account_id" class="form-input" required>
                    @foreach($bankAccounts as $ba)
                        <option value="{{ $ba->id }}">{{ $ba->full_label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Statement Date *</label>
                <input type="date" name="statement_date" class="form-input" value="{{ date('Y-m-d') }}" required>
            </div>
            <div>
                <label class="form-label">Opening Balance *</label>
                <input type="number" name="opening_balance" class="form-input" step="0.01" required>
            </div>
            <div>
                <label class="form-label">Closing Balance *</label>
                <input type="number" name="closing_balance" class="form-input" step="0.01" required>
            </div>
            <div class="col-span-2">
                <label class="form-label">CSV File * <span class="text-xs text-secondary-400">(date, description, debit, credit, balance, reference)</span></label>
                <input type="file" name="file" class="form-input" accept=".csv,.txt,.xlsx,.xls" required>
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-primary">Upload & Parse</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Bank Account</th>
                    <th>Period</th>
                    <th>File</th>
                    <th class="text-right">Opening</th>
                    <th class="text-right">Closing</th>
                    <th class="text-right">Items</th>
                </tr>
            </thead>
            <tbody>
                @forelse($statements as $stmt)
                <tr>
                    <td>{{ $stmt->statement_date->format('M d, Y') }}</td>
                    <td>{{ $stmt->bankAccount->full_label ?? '-' }}</td>
                    <td>{{ $stmt->period_label }}</td>
                    <td class="text-sm">{{ $stmt->file_name ?? '-' }}</td>
                    <td class="text-right font-mono">₱{{ number_format($stmt->opening_balance, 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($stmt->closing_balance, 2) }}</td>
                    <td class="text-right">{{ $stmt->items()->count() }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-secondary-400 py-8">No bank statements uploaded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ============================================================ --}}
{{-- TAB: BANK ACCOUNTS --}}
{{-- ============================================================ --}}
@elseif($tab === 'accounts')
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Bank Accounts</h3>
        <button type="button" onclick="document.getElementById('add-bank-form').style.display=document.getElementById('add-bank-form').style.display==='none'?'':'none'" class="btn-primary text-sm">+ Add Bank Account</button>
    </div>

    {{-- Add Bank Form --}}
    <div id="add-bank-form" class="card-body border-b" style="display:none">
        <form action="{{ route('gl.bank-reconciliation.store-bank-account') }}" method="POST" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="form-label">Bank Name *</label>
                <input type="text" name="bank_name" class="form-input" required placeholder="e.g. BDO">
            </div>
            <div>
                <label class="form-label">Type *</label>
                <select name="account_type" class="form-input" required>
                    <option value="SA">Savings Account (SA)</option>
                    <option value="CA">Checking Account (CA)</option>
                </select>
            </div>
            <div>
                <label class="form-label">Account Number</label>
                <input type="text" name="account_number" class="form-input" placeholder="Optional">
            </div>
            <div>
                <label class="form-label">Label *</label>
                <input type="text" name="account_label" class="form-input" required placeholder="e.g. BDO Savings Account">
            </div>
            <div>
                <label class="form-label">Link to COA *</label>
                <select name="chart_account_id" class="form-input" required>
                    @foreach($coaAccounts ?? [] as $coa)
                        <option value="{{ $coa->id }}">{{ $coa->account_code }} - {{ $coa->account_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Bank</th>
                    <th>Type</th>
                    <th>Account #</th>
                    <th>Label</th>
                    <th>Linked COA</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allBankAccounts ?? [] as $ba)
                <tr>
                    <td class="font-medium">{{ $ba->bank_name }}</td>
                    <td><span class="badge {{ $ba->account_type === 'CA' ? 'badge-warning' : 'badge-info' }}">{{ $ba->account_type }}</span></td>
                    <td class="font-mono text-sm">{{ $ba->account_number ?? '-' }}</td>
                    <td>{{ $ba->account_label }}</td>
                    <td class="text-sm">{{ $ba->chartAccount->account_code ?? '' }} - {{ $ba->chartAccount->account_name ?? '' }}</td>
                    <td><span class="badge {{ $ba->is_active ? 'badge-success' : 'badge-danger' }}">{{ $ba->is_active ? 'Active' : 'Inactive' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-secondary-400 py-8">No bank accounts.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ============================================================ --}}
{{-- TAB: STATEMENT DETAIL --}}
{{-- ============================================================ --}}
@elseif($tab === 'statement-detail' && isset($statement))
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $statement->bankAccount->full_label }} - {{ $statement->period_label }}</h3>
        <a href="{{ route('gl.bank-reconciliation', ['tab' => 'statements']) }}" class="text-sm text-primary-600">Back to Statements</a>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-4 gap-4 mb-4 text-sm">
            <div><span class="text-secondary-500">Opening:</span> <strong>₱{{ number_format($statement->opening_balance, 2) }}</strong></div>
            <div><span class="text-secondary-500">Closing:</span> <strong>₱{{ number_format($statement->closing_balance, 2) }}</strong></div>
            <div><span class="text-secondary-500">Total Debit:</span> <strong>₱{{ number_format($statement->total_debit, 2) }}</strong></div>
            <div><span class="text-secondary-500">Total Credit:</span> <strong>₱{{ number_format($statement->total_credit, 2) }}</strong></div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Reference</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                    <th class="text-right">Balance</th>
                    <th>Matched</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statement->items as $item)
                <tr>
                    <td>{{ $item->transaction_date->format('M d, Y') }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="font-mono text-sm">{{ $item->reference_number ?? '-' }}</td>
                    <td class="text-right font-mono">{{ $item->debit > 0 ? '₱' . number_format($item->debit, 2) : '' }}</td>
                    <td class="text-right font-mono">{{ $item->credit > 0 ? '₱' . number_format($item->credit, 2) : '' }}</td>
                    <td class="text-right font-mono">₱{{ number_format($item->running_balance, 2) }}</td>
                    <td>
                        @if($item->is_matched)
                            <span class="badge badge-success">Matched</span>
                        @else
                            <span class="badge badge-warning">Unmatched</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
