@extends('layouts.app')
@section('title', 'Finance Dashboard')

@section('content')
{{-- Page Header --}}
<x-page-header title="Finance Dashboard" subtitle="School Year 2025-2026 Overview" />

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <x-stat-card label="Total Annual Budget" value="{{ '₱' . number_format($totalBudget, 2) }}" color="blue">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75" /></svg></x-slot>
    </x-stat-card>

    <x-stat-card label="Committed Budget" value="{{ '₱' . number_format($committed, 2) }}" color="yellow">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot>
    </x-stat-card>

    <x-stat-card label="Actual Spending" value="{{ '₱' . number_format($actual, 2) }}" color="red">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg></x-slot>
    </x-stat-card>

    <x-stat-card label="Remaining Budget" value="{{ '₱' . number_format($remaining, 2) }}" color="green">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot>
    </x-stat-card>
</div>

{{-- Charts Row --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Budget vs Actual by Department --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Budget vs Actual by Department</h3>
        </div>
        <div class="card-body">
            <canvas id="deptChart" height="320"></canvas>
        </div>
    </div>

    {{-- Monthly Expense Trend --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Monthly Expense Trend</h3>
        </div>
        <div class="card-body">
            <canvas id="expenseChart" height="320"></canvas>
        </div>
    </div>
</div>

{{-- Spending by Category --}}
<div class="card mb-6">
    <div class="card-header">
        <h3 class="card-title">Spending by Category</h3>
    </div>
    <div class="card-body flex justify-center">
        <div style="max-width: 400px; width: 100%;">
            <canvas id="categoryChart" height="300"></canvas>
        </div>
    </div>
</div>

{{-- Fee Collections Summary (from Finance DB) --}}
@if($feeCollections)
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
    <x-stat-card label="Total Fee Collections (SY {{ date('Y') }}-{{ date('Y') + 1 }})" value="{{ '₱' . number_format($feeCollections->total_collected, 2) }}" color="green">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75" /></svg></x-slot>
    </x-stat-card>
    <x-stat-card label="Total Transactions" value="{{ number_format($feeCollections->txn_count) }}" color="blue">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg></x-slot>
    </x-stat-card>
</div>

<div class="card mb-6">
    <div class="card-header">
        <h3 class="card-title">Top Fee Collections (SY {{ date('Y') }}-{{ date('Y') + 1 }})</h3>
        <a href="{{ route('reports.fee-collections') }}" class="text-sm text-primary-600 hover:text-primary-700">View Full Report</a>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fee Name</th>
                    <th class="text-right">Transactions</th>
                    <th class="text-right">Total Collected</th>
                </tr>
            </thead>
            <tbody>
                @foreach($feeCollections->top_fees as $fee)
                <tr>
                    <td class="font-medium">{{ $fee->fee_name }}</td>
                    <td class="text-right">{{ number_format($fee->txn_count) }}</td>
                    <td class="text-right font-medium text-green-600">{{ '₱' . number_format($fee->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Recent Disbursement Requests --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Disbursement Requests</h3>
        <a href="{{ route('ap.disbursements.index') }}" class="text-sm text-primary-600 hover:text-primary-700">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Request #</th>
                    <th>Description</th>
                    <th>Department</th>
                    <th class="text-right">Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentDisbursements as $dr)
                <tr>
                    <td class="font-medium">
                        <a href="{{ url('/ap/disbursements/' . $dr->id) }}" class="text-primary-600 hover:underline">{{ $dr->request_number }}</a>
                    </td>
                    <td class="max-w-xs truncate">{{ $dr->description ?? $dr->payee_name }}</td>
                    <td>{{ $dr->department->name ?? '-' }}</td>
                    <td class="text-right font-medium">{{ '₱' . number_format($dr->amount, 2) }}</td>
                    <td><x-badge :status="$dr->status" /></td>
                    <td>{{ $dr->request_date->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-secondary-400 py-8">
                        <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9.75m3 0v3.375m0-3.375h3.375M6.75 3h3.375" /></svg>
                        No recent disbursement requests found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var deptLabels = @json($departmentLabels);
    var deptDatasets = @json($departmentDatasets);
    var monthlyLabels = @json($monthlyLabels);
    var monthlyDatasets = @json($monthlyDatasets);
    var catLabels = @json($categoryLabels);
    var catValues = @json($categoryValues);

    var barColors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'];
    var doughnutColors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#ec4899', '#06b6d4'];

    function loadChartJs(cb) {
        if (typeof Chart !== 'undefined') { cb(); return; }
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
        s.onload = cb;
        document.head.appendChild(s);
    }

    function currencyTick(v) {
        return '\u20B1' + (v >= 1000000 ? (v/1000000).toFixed(1)+'M' : (v/1000).toFixed(0)+'K');
    }

    function currencyTooltip(ctx) {
        return ctx.dataset.label + ': \u20B1' + ctx.parsed.y.toLocaleString('en-PH', {minimumFractionDigits:2});
    }

    function initChart(id, cfg) {
        var canvas = document.getElementById(id);
        if (!canvas) return;
        var existing = Chart.getChart(canvas);
        if (existing) existing.destroy();
        new Chart(canvas.getContext('2d'), cfg);
    }

    function renderAll() {
        loadChartJs(function() {
            // Budget vs Actual by Department (Bar)
            initChart('deptChart', {
                type: 'bar',
                data: {
                    labels: deptLabels,
                    datasets: deptDatasets.map(function(ds, i) {
                        return {
                            label: ds.label,
                            data: ds.data,
                            backgroundColor: barColors[i % barColors.length],
                            borderRadius: 4
                        };
                    })
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: currencyTooltip } } },
                    scales: { y: { beginAtZero: true, ticks: { callback: currencyTick } }, x: { grid: { display: false } } }
                }
            });

            // Monthly Expense Trend (Line)
            initChart('expenseChart', {
                type: 'line',
                data: {
                    labels: monthlyLabels,
                    datasets: monthlyDatasets.map(function(ds, i) {
                        return {
                            label: ds.label,
                            data: ds.data,
                            borderColor: barColors[i % barColors.length],
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true, tension: 0.3
                        };
                    })
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: currencyTooltip } } },
                    scales: { y: { beginAtZero: true, ticks: { callback: currencyTick } }, x: { grid: { display: false } } }
                }
            });

            // Spending by Category (Doughnut)
            initChart('categoryChart', {
                type: 'doughnut',
                data: {
                    labels: catLabels,
                    datasets: [{ data: catValues, backgroundColor: doughnutColors.slice(0, catLabels.length) }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.label + ': \u20B1' + ctx.parsed.toLocaleString('en-PH', {minimumFractionDigits:2});
                                }
                            }
                        }
                    }
                }
            });
        });
    }

    renderAll();
})();
</script>
@endpush
