@extends('layouts.app')
@section('title', 'Special Journals')

@section('content')
@php
    $cashReceipts = $cashReceipts ?? collect();
    $cashDisbursements = $cashDisbursements ?? collect();
    $salesJournal = $salesJournal ?? collect();
    $purchasesJournal = $purchasesJournal ?? collect();
@endphp

<x-page-header title="Special Journals" subtitle="BIR Books of Accounts" />

{{-- Date Range Filter --}}
<x-filter-bar />

{{-- Tabs --}}
<div x-data="{ activeTab: 'receipts' }" class="space-y-6">
    <div class="flex flex-wrap border-b border-gray-200">
        <button @click="activeTab = 'receipts'" :class="activeTab === 'receipts' ? 'border-primary-500 text-primary-600' : 'border-transparent text-secondary-500 hover:text-secondary-700'" class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
            Cash Receipts Journal
        </button>
        <button @click="activeTab = 'disbursements'" :class="activeTab === 'disbursements' ? 'border-primary-500 text-primary-600' : 'border-transparent text-secondary-500 hover:text-secondary-700'" class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
            Cash Disbursements Journal
        </button>
        <button @click="activeTab = 'sales'" :class="activeTab === 'sales' ? 'border-primary-500 text-primary-600' : 'border-transparent text-secondary-500 hover:text-secondary-700'" class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
            Sales Journal
        </button>
        <button @click="activeTab = 'purchases'" :class="activeTab === 'purchases' ? 'border-primary-500 text-primary-600' : 'border-transparent text-secondary-500 hover:text-secondary-700'" class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
            Purchases Journal
        </button>
    </div>

    {{-- Cash Receipts Journal --}}
    <div x-show="activeTab === 'receipts'" x-transition>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <x-stat-card label="Total Amount" :value="'₱' . number_format($cashReceipts->sum('amount'), 2)" color="green" />
            <x-stat-card label="Entry Count" :value="number_format($cashReceipts->count())" color="blue" />
        </div>
        <div class="flex items-center gap-2 mb-4">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel', 'journal' => 'receipts']) }}" class="btn-secondary text-sm">Export</a>
            <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
        </div>
        <div class="card">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>OR/Reference #</th>
                            <th>Received From</th>
                            <th>Description</th>
                            <th>Account</th>
                            <th class="text-right">Amount (Dr. Cash)</th>
                            <th class="text-right">Running Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $runningTotal = 0; @endphp
                        @forelse($cashReceipts as $entry)
                            @php $runningTotal += $entry->amount ?? 0; @endphp
                            <tr>
                                <td class="text-sm">{{ isset($entry->date) ? \Carbon\Carbon::parse($entry->date)->format('M d, Y') : '' }}</td>
                                <td class="font-mono text-sm">{{ $entry->reference ?? '' }}</td>
                                <td>{{ $entry->received_from ?? '' }}</td>
                                <td class="text-sm">{{ $entry->description ?? '' }}</td>
                                <td class="text-sm">{{ $entry->account ?? '' }}</td>
                                <td class="text-right font-mono">₱{{ number_format($entry->amount ?? 0, 2) }}</td>
                                <td class="text-right font-mono font-semibold">₱{{ number_format($runningTotal, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-8 text-secondary-400">No cash receipt entries found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Cash Disbursements Journal --}}
    <div x-show="activeTab === 'disbursements'" x-transition x-cloak>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <x-stat-card label="Total Amount" :value="'₱' . number_format($cashDisbursements->sum('amount'), 2)" color="red" />
            <x-stat-card label="Entry Count" :value="number_format($cashDisbursements->count())" color="blue" />
        </div>
        <div class="flex items-center gap-2 mb-4">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel', 'journal' => 'disbursements']) }}" class="btn-secondary text-sm">Export</a>
            <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
        </div>
        <div class="card">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>CV/Check #</th>
                            <th>Paid To</th>
                            <th>Description</th>
                            <th>Account</th>
                            <th>Check No.</th>
                            <th class="text-right">Amount (Cr. Cash)</th>
                            <th class="text-right">Running Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $runningTotal = 0; @endphp
                        @forelse($cashDisbursements as $entry)
                            @php $runningTotal += $entry->amount ?? 0; @endphp
                            <tr>
                                <td class="text-sm">{{ isset($entry->date) ? \Carbon\Carbon::parse($entry->date)->format('M d, Y') : '' }}</td>
                                <td class="font-mono text-sm">{{ $entry->cv_number ?? '' }}</td>
                                <td>{{ $entry->paid_to ?? '' }}</td>
                                <td class="text-sm">{{ $entry->description ?? '' }}</td>
                                <td class="text-sm">{{ $entry->account ?? '' }}</td>
                                <td class="font-mono text-sm">{{ $entry->check_number ?? '' }}</td>
                                <td class="text-right font-mono">₱{{ number_format($entry->amount ?? 0, 2) }}</td>
                                <td class="text-right font-mono font-semibold">₱{{ number_format($runningTotal, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-8 text-secondary-400">No cash disbursement entries found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Sales Journal --}}
    <div x-show="activeTab === 'sales'" x-transition x-cloak>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <x-stat-card label="Total Amount" :value="'₱' . number_format($salesJournal->sum('amount'), 2)" color="green" />
            <x-stat-card label="Entry Count" :value="number_format($salesJournal->count())" color="blue" />
        </div>
        <div class="flex items-center gap-2 mb-4">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel', 'journal' => 'sales']) }}" class="btn-secondary text-sm">Export</a>
            <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
        </div>
        <div class="card">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Description</th>
                            <th>Account</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesJournal as $entry)
                            <tr>
                                <td class="text-sm">{{ isset($entry->date) ? \Carbon\Carbon::parse($entry->date)->format('M d, Y') : '' }}</td>
                                <td class="font-mono text-sm">{{ $entry->invoice_number ?? '' }}</td>
                                <td>{{ $entry->customer ?? '' }}</td>
                                <td class="text-sm">{{ $entry->description ?? '' }}</td>
                                <td class="text-sm">{{ $entry->account ?? '' }}</td>
                                <td class="text-right font-mono">₱{{ number_format($entry->amount ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-8 text-secondary-400">No sales journal entries found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Purchases Journal --}}
    <div x-show="activeTab === 'purchases'" x-transition x-cloak>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <x-stat-card label="Total Amount" :value="'₱' . number_format($purchasesJournal->sum('amount'), 2)" color="red" />
            <x-stat-card label="Entry Count" :value="number_format($purchasesJournal->count())" color="blue" />
        </div>
        <div class="flex items-center gap-2 mb-4">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel', 'journal' => 'purchases']) }}" class="btn-secondary text-sm">Export</a>
            <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
        </div>
        <div class="card">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Bill #</th>
                            <th>Vendor</th>
                            <th>Description</th>
                            <th>Account</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchasesJournal as $entry)
                            <tr>
                                <td class="text-sm">{{ isset($entry->date) ? \Carbon\Carbon::parse($entry->date)->format('M d, Y') : '' }}</td>
                                <td class="font-mono text-sm">{{ $entry->bill_number ?? '' }}</td>
                                <td>{{ $entry->vendor ?? '' }}</td>
                                <td class="text-sm">{{ $entry->description ?? '' }}</td>
                                <td class="text-sm">{{ $entry->account ?? '' }}</td>
                                <td class="text-right font-mono">₱{{ number_format($entry->amount ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-8 text-secondary-400">No purchase journal entries found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
