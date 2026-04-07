@extends('layouts.app')
@section('title', 'Ledger Inquiry')

@section('content')
<x-page-header title="Ledger Inquiry">
    <x-slot name="actions">
        <a href="{{ route('reports.general-ledger') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
            Full GL Report
        </a>
    </x-slot>
</x-page-header>

{{-- Filters --}}
<div class="card mb-6">
    <div class="card-body">
        <form action="{{ route('gl.ledger-inquiry') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[250px]">
                <label class="form-label">Account</label>
                <select name="account_id" class="form-input">
                    <option value="">All Accounts</option>
                    @foreach($accounts ?? [] as $acct)
                        <option value="{{ $acct->id }}" {{ request('account_id') == $acct->id ? 'selected' : '' }}>{{ $acct->account_code }} - {{ $acct->account_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input w-40">
            </div>
            <div>
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input w-40">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    Inquire
                </button>
                <a href="{{ route('gl.ledger-inquiry') }}" class="btn-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

@if(isset($ledgerData) && count($ledgerData) > 0)
    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-stat-card label="Total Debits" :value="'₱' . number_format($totalDebits ?? 0, 2)" color="blue"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg>' />

        <x-stat-card label="Total Credits" :value="'₱' . number_format($totalCredits ?? 0, 2)" color="green"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181" /></svg>' />

        <x-stat-card label="Accounts Shown" :value="count($ledgerData)" color="purple"
            icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21" /></svg>' />
    </div>

    {{-- Per-Account Sections --}}
    @foreach($ledgerData as $accountData)
    <div class="card mb-6">
        <div class="card-header bg-gray-50">
            <div class="flex items-center justify-between w-full">
                <div>
                    <h3 class="text-sm font-semibold text-secondary-900">
                        {{ $accountData->account_code ?? '-' }} &mdash; {{ $accountData->account_name ?? '-' }}
                    </h3>
                </div>
                <div class="text-sm font-semibold">
                    Balance: <span class="{{ ($accountData->balance ?? 0) < 0 ? 'text-danger-500' : 'text-secondary-900' }}">{{ '₱' . number_format($accountData->balance ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Ref #</th>
                        <th>Description</th>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Credit</th>
                        <th class="text-right">Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accountData->transactions ?? [] as $txn)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($txn->date)->format('M d, Y') }}</td>
                        <td class="font-medium text-primary-600">{{ $txn->reference ?? '-' }}</td>
                        <td>{{ $txn->description ?? '-' }}</td>
                        <td class="text-right">{{ ($txn->debit ?? 0) > 0 ? '₱' . number_format($txn->debit, 2) : '-' }}</td>
                        <td class="text-right">{{ ($txn->credit ?? 0) > 0 ? '₱' . number_format($txn->credit, 2) : '-' }}</td>
                        <td class="text-right font-medium">{{ '₱' . number_format($txn->running_balance ?? 0, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-secondary-400 py-4">No transactions found for this account in the selected period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
@else
    <div class="card">
        <div class="card-body text-center py-16">
            <svg class="w-12 h-12 mx-auto mb-4 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
            <h3 class="text-lg font-medium text-secondary-700 mb-1">Ledger Inquiry</h3>
            <p class="text-secondary-400">Select an account and date range to view ledger transactions.</p>
        </div>
    </div>
@endif
@endsection
