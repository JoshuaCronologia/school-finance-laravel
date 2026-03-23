@extends('layouts.app')
@section('title', 'General Ledger')

@section('content')
@php
    $accounts = $accounts ?? collect();
    $selectedAccount = $selectedAccount ?? null;
    $allAccounts = $allAccounts ?? collect();
    $ledgerEntries = $ledgerEntries ?? collect();
@endphp

<x-page-header title="General Ledger" subtitle="Detailed transaction history by account">
    <x-slot:actions>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm">Excel</a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn-secondary text-sm">PDF</a>
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot:actions>
</x-page-header>

{{-- Filters --}}
<x-filter-bar>
    <div>
        <label class="form-label">Account</label>
        <select name="account_id" class="form-input w-64">
            <option value="">All Accounts</option>
            @foreach($allAccounts as $acct)
                <option value="{{ $acct->id }}" {{ request('account_id') == $acct->id ? 'selected' : '' }}>
                    {{ $acct->account_code }} - {{ $acct->account_name }}
                </option>
            @endforeach
        </select>
    </div>
</x-filter-bar>

{{-- Ledger Sections --}}
@forelse($accounts as $account)
    <div class="card mb-6">
        <div class="card-header bg-gray-50">
            <div class="flex items-center justify-between w-full">
                <div>
                    <h3 class="text-sm font-semibold text-secondary-900">{{ $account->account_code }} - {{ $account->account_name }}</h3>
                    <p class="text-xs text-secondary-500 mt-0.5">{{ ucfirst($account->account_type) }} | Normal Balance: {{ ucfirst($account->normal_balance) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-secondary-500">Running Balance</p>
                    <p class="text-sm font-mono font-bold text-secondary-900">₱{{ number_format($account->ending_balance ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Entry #</th>
                        <th>Description</th>
                        <th>Reference</th>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Credit</th>
                        <th class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php $runningBalance = $account->opening_balance ?? 0; @endphp
                    @if(isset($account->opening_balance))
                    <tr class="bg-gray-50">
                        <td colspan="4" class="text-sm font-medium text-secondary-600">Opening Balance</td>
                        <td class="text-right"></td>
                        <td class="text-right"></td>
                        <td class="text-right font-mono font-semibold">₱{{ number_format($runningBalance, 2) }}</td>
                    </tr>
                    @endif
                    @forelse($account->transactions ?? collect() as $txn)
                        @php
                            if ($account->normal_balance === 'debit') {
                                $runningBalance += ($txn->debit ?? 0) - ($txn->credit ?? 0);
                            } else {
                                $runningBalance += ($txn->credit ?? 0) - ($txn->debit ?? 0);
                            }
                        @endphp
                        <tr>
                            <td class="text-sm">{{ \Carbon\Carbon::parse($txn->entry_date)->format('M d, Y') }}</td>
                            <td class="font-mono text-sm">{{ $txn->entry_number ?? '' }}</td>
                            <td class="text-sm">{{ $txn->description ?? '' }}</td>
                            <td class="font-mono text-sm">{{ $txn->reference_number ?? '' }}</td>
                            <td class="text-right font-mono">{{ ($txn->debit ?? 0) > 0 ? '₱' . number_format($txn->debit, 2) : '' }}</td>
                            <td class="text-right font-mono">{{ ($txn->credit ?? 0) > 0 ? '₱' . number_format($txn->credit, 2) : '' }}</td>
                            <td class="text-right font-mono font-semibold">₱{{ number_format($runningBalance, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary-400 py-4">No transactions for this account in the selected period</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 font-semibold">
                    <tr>
                        <td colspan="4" class="text-right">Period Totals</td>
                        <td class="text-right font-mono">₱{{ number_format(($account->transactions ?? collect())->sum('debit'), 2) }}</td>
                        <td class="text-right font-mono">₱{{ number_format(($account->transactions ?? collect())->sum('credit'), 2) }}</td>
                        <td class="text-right font-mono">₱{{ number_format($runningBalance, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-body text-center py-12">
            <svg class="w-16 h-16 mx-auto mb-4 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
            <h3 class="text-lg font-semibold text-secondary-600 mb-1">No Ledger Data</h3>
            <p class="text-secondary-400">Select an account and date range to view the general ledger.</p>
        </div>
    </div>
@endforelse
@endsection
