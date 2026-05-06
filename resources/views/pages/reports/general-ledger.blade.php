@extends('layouts.app')
@section('title', 'General Ledger')

@section('content')
@php
    $accounts = $accounts ?? collect();
    $allAccounts = $allAccounts ?? collect();
@endphp

<x-page-header title="General Ledger Report" subtitle="Detailed transaction history by account (T-Account format)">
    <x-slot name="actions">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Excel
        </a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
            PDF
        </a>
    </x-slot>
</x-page-header>

{{-- Filters --}}
<x-filter-bar action="{{ route('reports.general-ledger') }}">
    <div>
        <label class="form-label">Account</label>
        <select name="account_id" class="form-input w-64">
            <option value="">All Accounts</option>
            @foreach($allAccounts as $acct)
                <option value="{{ $acct->id }}" {{ ($accountId ?? '') == $acct->id ? 'selected' : '' }}>
                    {{ $acct->account_code }} - {{ $acct->account_name }}
                </option>
            @endforeach
        </select>
    </div>
</x-filter-bar>

{{-- Per-Account Ledger Sections (T-Account style) --}}
@php
    $grandTotalDebit = 0;
    $grandTotalCredit = 0;
@endphp

@forelse($accounts as $account)
    @php
        $grandTotalDebit += $account->total_debit;
        $grandTotalCredit += $account->total_credit;
    @endphp
    <div class="card mb-6">
        {{-- Account Header --}}
        <div class="card-header bg-gray-50">
            <div class="flex items-center justify-between w-full">
                <div>
                    <h3 class="text-sm font-bold text-secondary-900">{{ $account->account_code }} - {{ $account->account_name }}</h3>
                    <p class="text-xs text-secondary-500 mt-0.5">{{ ucfirst($account->account_type) }} | Normal Balance: {{ ucfirst($account->normal_balance) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-secondary-500">Ending Balance</p>
                    <p class="text-lg font-mono font-bold {{ $account->ending_balance < 0 ? 'text-danger-600' : 'text-secondary-900' }}">
                        {{ $account->ending_balance < 0 ? '(' . '₱' . number_format(abs($account->ending_balance), 2) . ')' : '₱' . number_format($account->ending_balance, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-28">Date</th>
                        <th class="w-32">Entry #</th>
                        <th>Description</th>
                        <th class="w-28">Reference</th>
                        <th class="text-right w-32">Debit</th>
                        <th class="text-right w-32">Credit</th>
                        <th class="text-right w-36">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Opening Balance Row --}}
                    <tr class="bg-blue-50">
                        <td colspan="4" class="text-sm font-semibold text-blue-800">Opening Balance (as of {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }})</td>
                        <td class="text-right font-mono"></td>
                        <td class="text-right font-mono"></td>
                        <td class="text-right font-mono font-bold text-blue-800">₱{{ number_format($account->opening_balance, 2) }}</td>
                    </tr>

                    {{-- Transaction Rows --}}
                    @php $runningBalance = $account->opening_balance; @endphp
                    @foreach($account->transactions as $txn)
                        @php
                            if ($account->normal_balance === 'debit') {
                                $runningBalance += ($txn->debit ?? 0) - ($txn->credit ?? 0);
                            } else {
                                $runningBalance += ($txn->credit ?? 0) - ($txn->debit ?? 0);
                            }
                        @endphp
                        <tr>
                            <td class="text-sm">{{ \Carbon\Carbon::parse($txn->posting_date ?? $txn->entry_date)->format('M d, Y') }}</td>
                            <td class="font-mono text-sm text-primary-600">{{ $txn->entry_number ?? '' }}</td>
                            <td class="text-sm">{{ $txn->je_description ?? $txn->description ?? '' }}</td>
                            <td class="font-mono text-sm text-secondary-500">{{ $txn->reference_number ?? '' }}</td>
                            <td class="text-right font-mono">{{ ($txn->debit ?? 0) > 0 ? '₱' . number_format($txn->debit, 2) : '' }}</td>
                            <td class="text-right font-mono">{{ ($txn->credit ?? 0) > 0 ? '₱' . number_format($txn->credit, 2) : '' }}</td>
                            <td class="text-right font-mono font-semibold">₱{{ number_format($runningBalance, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>

                {{-- Account Subtotals (T-Account style) --}}
                <tfoot>
                    <tr class="bg-gray-100 font-bold border-t-2 border-gray-400">
                        <td colspan="4" class="text-right text-sm text-secondary-700">Account Totals:</td>
                        <td class="text-right font-mono text-secondary-900">₱{{ number_format($account->total_debit, 2) }}</td>
                        <td class="text-right font-mono text-secondary-900">₱{{ number_format($account->total_credit, 2) }}</td>
                        <td class="text-right font-mono text-secondary-900">₱{{ number_format($account->ending_balance, 2) }}</td>
                    </tr>
                    <tr class="bg-gray-50 text-xs text-secondary-500">
                        <td colspan="4" class="text-right">Net Movement:</td>
                        <td colspan="2" class="text-center font-mono font-semibold {{ ($account->total_debit - $account->total_credit) >= 0 ? 'text-secondary-700' : 'text-danger-600' }}">
                            @php $netMovement = $account->total_debit - $account->total_credit; @endphp
                            {{ $netMovement >= 0 ? 'Dr' : 'Cr' }} ₱{{ number_format(abs($netMovement), 2) }}
                        </td>
                        <td></td>
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
            <p class="text-secondary-400">Select an account and date range, then click Filter to view the general ledger.</p>
        </div>
    </div>
@endforelse

{{-- Grand Total (all accounts) --}}
@if($accounts->count() > 1)
<div class="card">
    <div class="card-header bg-primary-50">
        <h3 class="card-title text-primary-800">Grand Total - All Accounts</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr class="bg-primary-100">
                    <th class="text-right" colspan="4">{{ $accounts->count() }} Accounts</th>
                    <th class="text-right w-32">Total Debit</th>
                    <th class="text-right w-32">Total Credit</th>
                    <th class="text-right w-36">Difference</th>
                </tr>
            </thead>
            <tbody>
                <tr class="font-bold text-lg">
                    <td colspan="4" class="text-right text-secondary-700">Grand Total:</td>
                    <td class="text-right font-mono">₱{{ number_format($grandTotalDebit, 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($grandTotalCredit, 2) }}</td>
                    <td class="text-right font-mono {{ round($grandTotalDebit - $grandTotalCredit, 2) == 0 ? 'text-success-600' : 'text-danger-600' }}">
                        @if(round($grandTotalDebit - $grandTotalCredit, 2) == 0)
                            ₱0.00 (Balanced)
                        @else
                            ₱{{ number_format(abs($grandTotalDebit - $grandTotalCredit), 2) }}
                            ({{ $grandTotalDebit > $grandTotalCredit ? 'Dr' : 'Cr' }})
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
