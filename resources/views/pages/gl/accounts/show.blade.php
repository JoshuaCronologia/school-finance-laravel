@extends('layouts.app')
@section('title', $account->account_code . ' - ' . $account->account_name)

@section('content')
<x-page-header :title="$account->account_code . ' — ' . $account->account_name" subtitle="Account Detail">
    <x-slot name="actions">
        <a href="{{ route('reports.general-ledger', ['account_id' => $account->id]) }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
            Full Ledger
        </a>
        <a href="{{ route('gl.accounts.index') }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
            Back to COA
        </a>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Account Info --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title">Account Information</h3></div>
        <div class="card-body space-y-3">
            <div>
                <span class="text-xs text-secondary-500 uppercase">Account Code</span>
                <p class="font-semibold font-mono text-lg">{{ $account->account_code }}</p>
            </div>
            <div>
                <span class="text-xs text-secondary-500 uppercase">Account Name</span>
                <p class="font-medium">{{ $account->account_name }}</p>
            </div>
            <div class="flex gap-6">
                <div>
                    <span class="text-xs text-secondary-500 uppercase">Type</span>
                    @php
                        $_map = ['asset' => 'badge-info', 'liability' => 'badge-warning', 'equity' => 'badge-success', 'revenue' => 'badge-success', 'expense' => 'badge-danger'];
                        $typeBadge = $_map[$account->account_type ?? ''] ?? 'badge-neutral';
                    @endphp
                    <p><span class="badge {{ $typeBadge }}">{{ ucfirst($account->account_type) }}</span></p>
                </div>
                <div>
                    <span class="text-xs text-secondary-500 uppercase">Normal Balance</span>
                    <p class="font-medium">{{ ucfirst($account->normal_balance) }}</p>
                </div>
            </div>
            @if($account->parent)
            <div>
                <span class="text-xs text-secondary-500 uppercase">Parent Account</span>
                <p><a href="{{ route('gl.accounts.show', $account->parent) }}" class="text-primary-600 hover:underline">{{ $account->parent->account_code }} - {{ $account->parent->account_name }}</a></p>
            </div>
            @endif
            @if($account->children->count() > 0)
            <div>
                <span class="text-xs text-secondary-500 uppercase">Sub-Accounts</span>
                <p class="text-sm text-secondary-600">{{ $account->children->count() }} sub-accounts</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Balance Summary --}}
    <div class="card lg:col-span-2">
        <div class="card-header"><h3 class="card-title">Balance Summary</h3></div>
        <div class="card-body">
            <div class="text-center mb-6">
                <span class="text-xs text-secondary-500 uppercase">Current Balance</span>
                <p class="font-bold text-3xl {{ $balance > 0 ? 'text-green-600' : ($balance < 0 ? 'text-red-600' : 'text-secondary-400') }}">
                    {{ '₱' . number_format(abs($balance), 2) }}
                </p>
            </div>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="p-3 bg-blue-50 rounded-lg">
                    <span class="text-xs text-blue-600 uppercase">Total Debits</span>
                    <p class="font-semibold text-blue-700">{{ '₱' . number_format($totalDebit, 2) }}</p>
                </div>
                <div class="p-3 bg-amber-50 rounded-lg">
                    <span class="text-xs text-amber-600 uppercase">Total Credits</span>
                    <p class="font-semibold text-amber-700">{{ '₱' . number_format($totalCredit, 2) }}</p>
                </div>
                @if($feeBalance > 0)
                <div class="p-3 bg-green-50 rounded-lg">
                    <span class="text-xs text-green-600 uppercase">Finance Collections</span>
                    <p class="font-semibold text-green-700">{{ '₱' . number_format($feeBalance, 2) }}</p>
                </div>
                @else
                <div class="p-3 bg-gray-50 rounded-lg">
                    <span class="text-xs text-secondary-500 uppercase">Net (Debit - Credit)</span>
                    <p class="font-semibold text-secondary-700">{{ '₱' . number_format(abs($totalDebit - $totalCredit), 2) }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Recent JE Transactions --}}
<div class="card mb-6">
    <div class="card-header">
        <h3 class="card-title">Recent Journal Entry Transactions</h3>
        <a href="{{ route('reports.general-ledger', ['account_id' => $account->id]) }}" class="text-sm text-primary-600 hover:underline">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Entry #</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions as $txn)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($txn->posting_date)->format('M d, Y') }}</td>
                    <td class="font-mono text-sm">{{ $txn->entry_number }}</td>
                    <td class="text-sm text-secondary-500">{{ $txn->reference_number ?? '-' }}</td>
                    <td>{{ $txn->description ?: $txn->je_description ?: '-' }}</td>
                    <td class="text-right font-mono">{{ $txn->debit > 0 ? '₱' . number_format($txn->debit, 2) : '' }}</td>
                    <td class="text-right font-mono">{{ $txn->credit > 0 ? '₱' . number_format($txn->credit, 2) : '' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-secondary-400 py-6">No journal entry transactions.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Finance Fee Transactions (if mapped) --}}
@if($feeDateFrom !== null)
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Finance Fee Collections</h3>
        <span class="text-xs text-secondary-400">{{ $syLabel }}</span>
    </div>
    <form method="GET" action="{{ route('gl.accounts.show', $account) }}" class="px-4 py-3 border-b border-gray-200 flex items-center gap-3 flex-wrap">
        <label class="form-label mb-0">Date Range</label>
        <input type="date" name="fee_date_from" value="{{ $feeDateFrom }}" class="form-input" onchange="this.form.submit()">
        <span class="text-secondary-400">—</span>
        <input type="date" name="fee_date_to" value="{{ $feeDateTo }}" class="form-input" onchange="this.form.submit()">
        <button type="submit" class="btn-primary text-sm">Filter</button>
    </form>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Fee Name</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feeTransactions as $fee)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($fee->posting_date)->format('M d, Y') }}</td>
                    <td class="font-mono text-sm">{{ $fee->entry_number }}</td>
                    <td>{{ $fee->description }}</td>
                    <td class="text-right font-mono text-green-600">{{ '₱' . number_format($fee->credit, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-secondary-400 py-6">No fee collections for this date range.</td></tr>
                @endforelse
            </tbody>
            @if($feeTransactions->count() > 0)
            <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-400">
                <tr>
                    <td colspan="3" class="text-right">Total:</td>
                    <td class="text-right font-mono text-green-700">{{ '₱' . number_format($feeTransactions->sum('credit'), 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endif
@endsection
