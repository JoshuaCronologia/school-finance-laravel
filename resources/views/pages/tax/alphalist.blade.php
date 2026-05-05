@extends('layouts.app')
@section('title', 'Alphalist of Payees')

@section('content')
@php
    $quarter = $quarter ?? request('quarter', 'Q1');
    $year = $year ?? request('year', date('Y'));
    $qapEntries = $qapEntries ?? collect();
    $sawtEntries = $sawtEntries ?? collect();
    $totalPayees = $totalPayees ?? $qapEntries->count();
    $totalIncome = $totalIncome ?? $qapEntries->sum('income_payment');
    $totalTax = $totalTax ?? $qapEntries->sum('tax_withheld');
    $monthNames = $monthNames ?? ['Month 1', 'Month 2', 'Month 3'];
    $sawtTotalBase = $sawtTotalBase ?? $sawtEntries->sum('tax_base');
    $sawtTotalTax = $sawtTotalTax ?? $sawtEntries->sum('tax_withheld');
@endphp

<x-page-header title="Alphalist of Payees (QAP) & SAWT" subtitle="Quarterly reporting for BIR compliance" />

{{-- Filters --}}
<div class="card mb-6">
    <form class="card-body">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">Quarter</label>
                <select name="quarter" class="form-input w-28">
                    @foreach(['Q1','Q2','Q3','Q4'] as $q)
                        <option value="{{ $q }}" {{ $quarter == $q ? 'selected' : '' }}>{{ $q }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Year</label>
                <input type="number" name="year" value="{{ $year }}" class="form-input w-28" min="2020" max="2030">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
            <a href="{{ request()->url() }}" class="btn-secondary">Clear</a>
        </div>
    </form>
</div>

{{-- Tabs --}}
<div x-data="{ activeTab: 'qap' }" class="space-y-6">
    <div class="flex border-b border-gray-200">
        <button @click="activeTab = 'qap'" :class="activeTab === 'qap' ? 'border-primary-500 text-primary-600' : 'border-transparent text-secondary-500 hover:text-secondary-700'" class="px-4 py-2 text-sm font-medium border-b-2 transition-colors">
            QAP - Alphalist of Payees
        </button>
        <button @click="activeTab = 'sawt'" :class="activeTab === 'sawt' ? 'border-primary-500 text-primary-600' : 'border-transparent text-secondary-500 hover:text-secondary-700'" class="px-4 py-2 text-sm font-medium border-b-2 transition-colors">
            SAWT - Summary
        </button>
    </div>

    {{-- QAP Tab --}}
    <div x-show="activeTab === 'qap'" x-transition>
        {{-- QAP Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-stat-card label="Total Payees" :value="number_format($totalPayees)" color="blue" />
            <x-stat-card label="Total Income" :value="'₱' . number_format($totalIncome, 2)" color="green" />
            <x-stat-card label="Total Tax" :value="'₱' . number_format($totalTax, 2)" color="red" />
        </div>

        {{-- Export Buttons --}}
        <div class="flex items-center gap-2 mb-4">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel', 'type' => 'qap']) }}" class="btn-secondary text-sm">Export Excel</a>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv', 'type' => 'qap']) }}" class="btn-secondary text-sm">Export CSV</a>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'dat', 'type' => 'qap']) }}" class="btn-secondary text-sm">Export DAT</a>
        </div>

        {{-- QAP Table --}}
        <div class="card">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-100">
                            <th rowspan="2" class="border border-gray-200 px-2 py-2 text-left w-10">Seq #</th>
                            <th rowspan="2" class="border border-gray-200 px-2 py-2 text-left">TIN</th>
                            <th rowspan="2" class="border border-gray-200 px-2 py-2 text-left">Registered Name</th>
                            <th rowspan="2" class="border border-gray-200 px-2 py-2 text-center w-20">ATC</th>
                            <th colspan="3" class="border border-gray-200 px-2 py-1 text-center bg-blue-50">Month</th>
                            <th rowspan="2" class="border border-gray-200 px-2 py-2 text-right">Total Income</th>
                            <th rowspan="2" class="border border-gray-200 px-2 py-2 text-right">Tax Withheld</th>
                        </tr>
                        <tr class="bg-blue-50 text-center">
                            <th class="border border-gray-200 px-2 py-1">{{ $monthNames[0] }}</th>
                            <th class="border border-gray-200 px-2 py-1">{{ $monthNames[1] }}</th>
                            <th class="border border-gray-200 px-2 py-1">{{ $monthNames[2] }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($qapEntries as $index => $entry)
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-200 px-2 py-1 font-mono text-center">{{ $index + 1 }}</td>
                                <td class="border border-gray-200 px-2 py-1 font-mono">{{ $entry->tin ?? 'N/A' }}</td>
                                <td class="border border-gray-200 px-2 py-1">{{ $entry->registered_name ?? '' }}</td>
                                <td class="border border-gray-200 px-2 py-1 text-center font-mono font-semibold">{{ $entry->atc ?? '' }}</td>
                                <td class="border border-gray-200 px-2 py-1 text-right font-mono">{{ ($entry->m1_income ?? 0) > 0 ? number_format($entry->m1_income, 2) : '' }}</td>
                                <td class="border border-gray-200 px-2 py-1 text-right font-mono">{{ ($entry->m2_income ?? 0) > 0 ? number_format($entry->m2_income, 2) : '' }}</td>
                                <td class="border border-gray-200 px-2 py-1 text-right font-mono">{{ ($entry->m3_income ?? 0) > 0 ? number_format($entry->m3_income, 2) : '' }}</td>
                                <td class="border border-gray-200 px-2 py-1 text-right font-mono font-semibold">{{ number_format($entry->income_payment ?? 0, 2) }}</td>
                                <td class="border border-gray-200 px-2 py-1 text-right font-mono font-semibold text-danger-600">{{ number_format($entry->tax_withheld ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="border border-gray-200 text-center py-8 text-secondary-400">No QAP entries found for the selected period.</td></tr>
                        @endforelse
                    </tbody>
                    @if($qapEntries->isNotEmpty())
                    <tfoot class="bg-gray-50 font-semibold">
                        <tr>
                            <td colspan="7" class="border border-gray-200 px-2 py-2 text-right">Totals</td>
                            <td class="border border-gray-200 px-2 py-2 text-right font-mono">{{ number_format($totalIncome, 2) }}</td>
                            <td class="border border-gray-200 px-2 py-2 text-right font-mono text-danger-600">{{ number_format($totalTax, 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- SAWT Tab --}}
    <div x-show="activeTab === 'sawt'" x-transition x-cloak>
        {{-- SAWT Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-stat-card label="Entries" :value="number_format($sawtEntries->count())" color="blue" />
            <x-stat-card label="Total Base" :value="'₱' . number_format($sawtTotalBase, 2)" color="green" />
            <x-stat-card label="Total Tax" :value="'₱' . number_format($sawtTotalTax, 2)" color="red" />
        </div>

        {{-- SAWT Table --}}
        <div class="card">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ATC</th>
                            <th>Description</th>
                            <th class="text-right">Tax Base</th>
                            <th class="text-right">Tax Rate</th>
                            <th class="text-right">Tax Withheld</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sawtEntries as $entry)
                            <tr>
                                <td class="font-mono text-sm font-medium">{{ $entry->atc ?? '' }}</td>
                                <td>{{ $entry->description ?? '' }}</td>
                                <td class="text-right font-mono">₱{{ number_format($entry->tax_base ?? 0, 2) }}</td>
                                <td class="text-right font-mono">{{ number_format($entry->tax_rate ?? 0, 1) }}%</td>
                                <td class="text-right font-mono font-semibold">₱{{ number_format($entry->tax_withheld ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-8 text-secondary-400">No SAWT entries found for the selected period.</td></tr>
                        @endforelse
                    </tbody>
                    @if($sawtEntries->isNotEmpty())
                    <tfoot class="bg-gray-50 font-semibold">
                        <tr>
                            <td colspan="2" class="text-right">Totals</td>
                            <td class="text-right font-mono">₱{{ number_format($sawtTotalBase, 2) }}</td>
                            <td></td>
                            <td class="text-right font-mono">₱{{ number_format($sawtTotalTax, 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
