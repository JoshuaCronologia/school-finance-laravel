@extends('layouts.app')
@section('title', 'BIR 1601-E')

@section('content')
@php
    $taxableMonth = $taxableMonth ?? request('month', now()->format('Y-m'));
    $totalTaxWithheld = $totalTaxWithheld ?? 0;
    $taxCredits = $taxCredits ?? 0;
    $netTaxDue = $netTaxDue ?? ($totalTaxWithheld - $taxCredits);
    $penalties = $penalties ?? 0;
    $totalAmountDue = $totalAmountDue ?? ($netTaxDue + $penalties);
    $atcEntries = $atcEntries ?? collect();
    $atcCodesUsed = $atcCodesUsed ?? 0;
    $monthlyTrend = $monthlyTrend ?? collect();
@endphp

<x-page-header title="BIR 1601-E" subtitle="Expanded Withholding Tax Return">
    <x-slot:actions>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm">Excel</a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn-secondary text-sm">PDF</a>
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot:actions>
</x-page-header>

{{-- Filter --}}
<div class="card mb-6">
    <form class="card-body">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">Taxable Month</label>
                <input type="month" name="month" value="{{ $taxableMonth }}" class="form-input w-48">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
            <a href="{{ request()->url() }}" class="btn-secondary">Clear</a>
        </div>
    </form>
</div>

{{-- Tax Computation --}}
<div class="card mb-6">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Tax Computation</h3>
    </div>
    <div class="card-body">
        <div class="space-y-3 max-w-lg">
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-secondary-700">Total Taxes Withheld</span>
                <span class="font-mono font-semibold">₱{{ number_format($totalTaxWithheld, 2) }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-secondary-700">Less: Tax Credits / Payments</span>
                <span class="font-mono text-danger-600">(₱{{ number_format($taxCredits, 2) }})</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-200 bg-blue-50 px-3 rounded">
                <span class="text-sm font-semibold text-blue-800">Net Tax Due</span>
                <span class="font-mono font-bold text-blue-800">₱{{ number_format($netTaxDue, 2) }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-secondary-700">Add: Penalties / Surcharge / Interest</span>
                <span class="font-mono text-danger-600">₱{{ number_format($penalties, 2) }}</span>
            </div>
            <div class="flex items-center justify-between py-3 bg-primary-50 px-3 rounded-lg">
                <span class="text-sm font-bold text-primary-800">Total Amount Due</span>
                <span class="text-lg font-mono font-bold text-primary-800">₱{{ number_format($totalAmountDue, 2) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <x-stat-card label="Total Tax Withheld" :value="'₱' . number_format($totalTaxWithheld, 2)" color="blue" />
    <x-stat-card label="ATC Entries" :value="number_format($atcEntries->count())" color="green" />
    <x-stat-card label="ATC Codes Used" :value="number_format($atcCodesUsed)" color="purple" />
</div>

{{-- Breakdown by ATC Code --}}
<div class="card mb-6">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Breakdown by ATC Code</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ATC</th>
                    <th>Nature of Payment</th>
                    <th class="text-right">Tax Base</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Tax Withheld</th>
                </tr>
            </thead>
            <tbody>
                @forelse($atcEntries as $entry)
                    <tr>
                        <td class="font-mono text-sm font-medium">{{ $entry->atc ?? '' }}</td>
                        <td>{{ $entry->nature ?? '' }}</td>
                        <td class="text-right font-mono">₱{{ number_format($entry->tax_base ?? 0, 2) }}</td>
                        <td class="text-right font-mono">{{ number_format($entry->rate ?? 0, 1) }}%</td>
                        <td class="text-right font-mono font-semibold">₱{{ number_format($entry->tax_withheld ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-8 text-secondary-400">No ATC entries found for this period.</td></tr>
                @endforelse
            </tbody>
            @if($atcEntries->isNotEmpty())
            <tfoot class="bg-gray-50 font-semibold">
                <tr>
                    <td colspan="2" class="text-right">Total</td>
                    <td class="text-right font-mono">₱{{ number_format($atcEntries->sum('tax_base'), 2) }}</td>
                    <td></td>
                    <td class="text-right font-mono">₱{{ number_format($atcEntries->sum('tax_withheld'), 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- Yearly Trend --}}
<div class="card">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Yearly Trend</h3>
    </div>
    <div class="card-body" x-data="{}" x-init="
        if (typeof Chart !== 'undefined') {
            new Chart($refs.trendChart.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                    datasets: [{
                        label: 'Tax Withheld',
                        data: {!! json_encode($monthlyTrend->pluck('amount')->toArray() ?: array_fill(0, 12, 0)) !!},
                        backgroundColor: '#3b82f6',
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => '₱' + ctx.parsed.y.toLocaleString() } } },
                    scales: { y: { beginAtZero: true, ticks: { callback: v => '₱' + (v/1000).toFixed(0) + 'K' } } }
                }
            });
        }
    ">
        <canvas x-ref="trendChart" height="100"></canvas>
    </div>
</div>
@endsection

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
