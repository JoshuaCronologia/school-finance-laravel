@extends('layouts.app')
@section('title', 'Balance Sheet')

@section('content')
@php
    $asOfDate = $asOfDate ?? now()->format('F d, Y');
    $assets = $assets ?? collect();
    $liabilities = $liabilities ?? collect();
    $equity = $equity ?? collect();
    $totalAssets = $totalAssets ?? 0;
    $totalLiabilities = $totalLiabilities ?? 0;
    $totalEquity = $totalEquity ?? 0;
    $previousAssets = $previousAssets ?? 0;
    $previousLiabilities = $previousLiabilities ?? 0;
    $previousEquity = $previousEquity ?? 0;
    $isBalanced = abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01;
@endphp

<x-page-header title="Balance Sheet" :subtitle="'Statement of Financial Position as of ' . $asOfDate">
    <x-slot:actions>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Excel
        </a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
            PDF
        </a>
    </x-slot:actions>
</x-page-header>

{{-- Date Filter --}}
<div class="card mb-6">
    <form class="card-body">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">As of Date</label>
                <input type="date" name="as_of_date" value="{{ request('as_of_date', now()->format('Y-m-d')) }}" class="form-input w-48">
            </div>
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                Generate
            </button>
            <a href="{{ request()->url() }}" class="btn-secondary">Clear</a>
        </div>
    </form>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <x-stat-card label="Total Assets" :value="'₱' . number_format($totalAssets, 2)" color="blue"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21\' /></svg>'" />
    <x-stat-card label="Total Liabilities" :value="'₱' . number_format($totalLiabilities, 2)" color="red"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z\' /></svg>'" />
    <x-stat-card label="Total Equity" :value="'₱' . number_format($totalEquity, 2)" color="green"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21\' /></svg>'" />
</div>

{{-- Assets Section --}}
<div class="card mb-6">
    <div class="card-header bg-blue-50">
        <h3 class="text-sm font-semibold text-blue-800">Assets</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Account Name</th>
                    <th>Code</th>
                    <th class="text-right">Current Period</th>
                    <th class="text-right">Previous Period</th>
                    <th class="text-right">Variance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $account)
                    <tr>
                        <td>{{ $account->account_name }}</td>
                        <td class="font-mono text-sm">{{ $account->account_code }}</td>
                        <td class="text-right font-mono">₱{{ number_format($account->current_balance ?? 0, 2) }}</td>
                        <td class="text-right font-mono">₱{{ number_format($account->previous_balance ?? 0, 2) }}</td>
                        @php $variance = ($account->current_balance ?? 0) - ($account->previous_balance ?? 0); @endphp
                        <td class="text-right font-mono {{ $variance >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                            {{ $variance >= 0 ? '+' : '' }}₱{{ number_format($variance, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-secondary-400 py-4">No asset accounts found</td></tr>
                @endforelse
            </tbody>
            <tfoot class="bg-blue-50 font-semibold">
                <tr>
                    <td colspan="2" class="text-right">Total Assets</td>
                    <td class="text-right font-mono">₱{{ number_format($totalAssets, 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($previousAssets, 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($totalAssets - $previousAssets, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Liabilities Section --}}
<div class="card mb-6">
    <div class="card-header bg-red-50">
        <h3 class="text-sm font-semibold text-red-800">Liabilities</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Account Name</th>
                    <th>Code</th>
                    <th class="text-right">Current Period</th>
                    <th class="text-right">Previous Period</th>
                    <th class="text-right">Variance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($liabilities as $account)
                    <tr>
                        <td>{{ $account->account_name }}</td>
                        <td class="font-mono text-sm">{{ $account->account_code }}</td>
                        <td class="text-right font-mono">₱{{ number_format($account->current_balance ?? 0, 2) }}</td>
                        <td class="text-right font-mono">₱{{ number_format($account->previous_balance ?? 0, 2) }}</td>
                        @php $variance = ($account->current_balance ?? 0) - ($account->previous_balance ?? 0); @endphp
                        <td class="text-right font-mono {{ $variance >= 0 ? 'text-danger-600' : 'text-success-600' }}">
                            {{ $variance >= 0 ? '+' : '' }}₱{{ number_format($variance, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-secondary-400 py-4">No liability accounts found</td></tr>
                @endforelse
            </tbody>
            <tfoot class="bg-red-50 font-semibold">
                <tr>
                    <td colspan="2" class="text-right">Total Liabilities</td>
                    <td class="text-right font-mono">₱{{ number_format($totalLiabilities, 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($previousLiabilities, 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($totalLiabilities - $previousLiabilities, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Equity Section --}}
<div class="card mb-6">
    <div class="card-header bg-green-50">
        <h3 class="text-sm font-semibold text-green-800">Equity</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Account Name</th>
                    <th>Code</th>
                    <th class="text-right">Current Period</th>
                    <th class="text-right">Previous Period</th>
                    <th class="text-right">Variance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($equity as $account)
                    <tr>
                        <td>{{ $account->account_name }}</td>
                        <td class="font-mono text-sm">{{ $account->account_code }}</td>
                        <td class="text-right font-mono">₱{{ number_format($account->current_balance ?? 0, 2) }}</td>
                        <td class="text-right font-mono">₱{{ number_format($account->previous_balance ?? 0, 2) }}</td>
                        @php $variance = ($account->current_balance ?? 0) - ($account->previous_balance ?? 0); @endphp
                        <td class="text-right font-mono {{ $variance >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                            {{ $variance >= 0 ? '+' : '' }}₱{{ number_format($variance, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-secondary-400 py-4">No equity accounts found</td></tr>
                @endforelse
            </tbody>
            <tfoot class="bg-green-50 font-semibold">
                <tr>
                    <td colspan="2" class="text-right">Total Equity</td>
                    <td class="text-right font-mono">₱{{ number_format($totalEquity, 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($previousEquity, 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($totalEquity - $previousEquity, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Balance Verification --}}
<div class="card">
    <div class="card-body">
        <div class="flex items-center justify-between p-4 rounded-lg {{ $isBalanced ? 'bg-success-50' : 'bg-danger-50' }}">
            <div>
                <p class="text-sm font-medium {{ $isBalanced ? 'text-success-800' : 'text-danger-800' }}">
                    Accounting Equation Verification: Assets = Liabilities + Equity
                </p>
                <p class="text-sm {{ $isBalanced ? 'text-success-600' : 'text-danger-600' }} mt-1">
                    ₱{{ number_format($totalAssets, 2) }} = ₱{{ number_format($totalLiabilities, 2) }} + ₱{{ number_format($totalEquity, 2) }} = ₱{{ number_format($totalLiabilities + $totalEquity, 2) }}
                </p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $isBalanced ? 'bg-success-100 text-success-700' : 'bg-danger-100 text-danger-700' }}">
                {{ $isBalanced ? 'BALANCED' : 'UNBALANCED' }}
            </span>
        </div>
    </div>
</div>
@endsection
