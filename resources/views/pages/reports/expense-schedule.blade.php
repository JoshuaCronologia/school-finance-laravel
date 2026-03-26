@extends('layouts.app')
@section('title', 'Expense Schedule')

@section('content')
@php
    $expenses = $expenses ?? collect();
    $totalExpenses = $expenses->sum('amount');
@endphp

<x-page-header title="Expense Schedule" subtitle="Breakdown of expenses by category">
    <x-slot:actions>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm">Excel</a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn-secondary text-sm">PDF</a>
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot:actions>
</x-page-header>

<x-filter-bar />

{{-- Summary Card --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <x-stat-card label="Total Expenses" :value="'₱' . number_format($totalExpenses, 2)" color="red"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181\' /></svg>'" />
    <x-stat-card label="Categories" :value="$expenses->count()" color="blue"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z\' /></svg>'" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Chart --}}
    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-secondary-900">Expense Distribution</h3>
        </div>
        <div class="card-body" x-data="{}" x-init="
            if (typeof Chart !== 'undefined') {
                const ctx = $refs.expenseChart.getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($expenses->pluck('category_name')->toArray()) !!},
                        datasets: [{
                            data: {!! json_encode($expenses->pluck('amount')->toArray()) !!},
                            backgroundColor: ['#3b82f6','#ef4444','#10b981','#f59e0b','#8b5cf6','#ec4899','#06b6d4','#f97316','#84cc16','#6366f1','#14b8a6','#e11d48'],
                            borderWidth: 2,
                            borderColor: '#fff',
                        }]
                    },
                    options: {
                        responsive: true,
                        cutout: '60%',
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true } },
                            tooltip: { callbacks: { label: ctx => ctx.label + ': ₱' + ctx.parsed.toLocaleString() } }
                        }
                    }
                });
            }
        ">
            <canvas x-ref="expenseChart" height="300"></canvas>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-secondary-900">Expense Details</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        @php $pct = $totalExpenses > 0 ? ($expense->amount / $totalExpenses * 100) : 0; @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    <span>{{ $expense->category_name ?? $expense->account_name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="text-right font-mono">₱{{ number_format($expense->amount, 2) }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-primary-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-sm font-mono text-secondary-500">{{ number_format($pct, 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-secondary-400 py-8">No expense data available. Select a date range and click Filter.</td></tr>
                    @endforelse
                </tbody>
                @if($expenses->isNotEmpty())
                <tfoot class="bg-gray-50 font-semibold">
                    <tr>
                        <td>Total</td>
                        <td class="text-right font-mono">₱{{ number_format($totalExpenses, 2) }}</td>
                        <td class="text-right font-mono">100.0%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
