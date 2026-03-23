@extends('layouts.app')
@section('title', 'Trial Balance')

@section('content')
@php
    $totals = $totals ?? ['total_debit' => 0, 'total_credit' => 0, 'difference' => 0];
    $accounts = $accounts ?? collect();
    $isBalanced = abs($totals['difference']) < 0.01;
@endphp

<x-page-header title="Trial Balance">
    <x-slot:actions>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $isBalanced ? 'bg-success-50 text-success-700' : 'bg-danger-50 text-danger-700' }}">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                @if($isBalanced)
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                @endif
            </svg>
            {{ $isBalanced ? 'Balanced' : 'Unbalanced' }}
        </span>
    </x-slot:actions>
</x-page-header>

{{-- Filters --}}
<x-filter-bar>
    {{-- Additional export buttons --}}
    <div class="flex items-center gap-2">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Excel
        </a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
            PDF
        </a>
        <button onclick="window.print()" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 12H5.25" /></svg>
            Print
        </button>
    </div>
</x-filter-bar>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <x-stat-card label="Total Debit" :value="'₱' . number_format($totals['total_debit'], 2)" color="blue"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941\' /></svg>'" />
    <x-stat-card label="Total Credit" :value="'₱' . number_format($totals['total_credit'], 2)" color="green"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181\' /></svg>'" />
    <x-stat-card label="Difference" :value="'₱' . number_format(abs($totals['difference']), 2)" :color="$isBalanced ? 'green' : 'red'"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75\' /></svg>'" />
</div>

{{-- Trial Balance Table --}}
<div class="card">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Account Details</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Account Code</th>
                    <th>Account Name</th>
                    <th>Type</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accounts as $account)
                    <tr>
                        <td class="font-mono text-sm">{{ $account->account_code }}</td>
                        <td>{{ $account->account_name }}</td>
                        <td><x-badge :status="$account->account_type" /></td>
                        <td class="text-right font-mono">{{ $account->debit > 0 ? '₱' . number_format($account->debit, 2) : '' }}</td>
                        <td class="text-right font-mono">{{ $account->credit > 0 ? '₱' . number_format($account->credit, 2) : '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-secondary-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9.75m3 0h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008v-.008Zm-3-6h.008v.008H9.75v-.008Zm0 2.25h.008v.008H9.75v-.008Zm0 2.25h.008v.008H9.75v-.008Z" /></svg>
                            <p>No trial balance data available. Select a date range and click Filter.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($accounts->isNotEmpty())
                <tfoot class="bg-gray-50 font-semibold">
                    <tr>
                        <td colspan="3" class="text-right">Totals</td>
                        <td class="text-right font-mono">₱{{ number_format($totals['total_debit'], 2) }}</td>
                        <td class="text-right font-mono">₱{{ number_format($totals['total_credit'], 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
