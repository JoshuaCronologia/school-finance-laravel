@extends('layouts.app')
@section('title', 'Monthly Variance')

@section('content')
@php
    $monthlyData = $monthlyData ?? collect();
    $months = ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
@endphp

<x-page-header title="Monthly Variance Analysis" subtitle="Compare budget versus actual spending by month">
    <x-slot:actions>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm">Excel</a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn-secondary text-sm">PDF</a>
    </x-slot:actions>
</x-page-header>

<x-filter-bar>
    <div>
        <label class="form-label">School Year</label>
        <select name="school_year" class="form-input w-40">
            <option value="2025-2026" {{ request('school_year', '2025-2026') == '2025-2026' ? 'selected' : '' }}>2025-2026</option>
            <option value="2024-2025" {{ request('school_year') == '2024-2025' ? 'selected' : '' }}>2024-2025</option>
        </select>
    </div>
</x-filter-bar>

{{-- Trend Chart --}}
<div class="card mb-6">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Monthly Trend</h3>
    </div>
    <div class="card-body" x-data="{}" x-init="
        if (typeof Chart !== 'undefined') {
            const ctx = $refs.trendChart.getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($months) !!},
                    datasets: [
                        {
                            label: 'Budget',
                            data: {!! json_encode($monthlyData->pluck('budget')->toArray() ?: array_fill(0, 12, 0)) !!},
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true,
                            tension: 0.3,
                        },
                        {
                            label: 'Actual',
                            data: {!! json_encode($monthlyData->pluck('actual')->toArray() ?: array_fill(0, 12, 0)) !!},
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.3,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': ₱' + ctx.parsed.y.toLocaleString() } }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: v => '₱' + (v/1000).toFixed(0) + 'K' } }
                    }
                }
            });
        }
    ">
        <canvas x-ref="trendChart" height="100"></canvas>
    </div>
</div>

{{-- Monthly Table --}}
<div class="card">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Monthly Details</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th class="text-right">Budget</th>
                    <th class="text-right">Actual</th>
                    <th class="text-right">Variance</th>
                    <th class="text-right">Variance %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monthlyData as $index => $month)
                    @php
                        $variance = ($month->budget ?? 0) - ($month->actual ?? 0);
                        $variancePct = ($month->budget ?? 0) > 0 ? ($variance / $month->budget * 100) : 0;
                        $isOver = $variance < 0;
                    @endphp
                    <tr>
                        <td class="font-medium">{{ $months[$index] ?? $month->month_name ?? '' }}</td>
                        <td class="text-right font-mono">₱{{ number_format($month->budget ?? 0, 2) }}</td>
                        <td class="text-right font-mono">₱{{ number_format($month->actual ?? 0, 2) }}</td>
                        <td class="text-right font-mono font-semibold {{ $isOver ? 'text-danger-600' : 'text-success-600' }}">
                            {{ $isOver ? '-' : '' }}₱{{ number_format(abs($variance), 2) }}
                        </td>
                        <td class="text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $isOver ? 'bg-danger-50 text-danger-700' : 'bg-success-50 text-success-700' }}">
                                {{ $isOver ? '' : '+' }}{{ number_format($variancePct, 1) }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    @foreach($months as $monthName)
                        <tr>
                            <td class="font-medium">{{ $monthName }}</td>
                            <td class="text-right font-mono text-secondary-300">₱0.00</td>
                            <td class="text-right font-mono text-secondary-300">₱0.00</td>
                            <td class="text-right font-mono text-secondary-300">₱0.00</td>
                            <td class="text-right"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-50 text-secondary-400">0.0%</span></td>
                        </tr>
                    @endforeach
                @endforelse
            </tbody>
            @if($monthlyData->isNotEmpty())
            <tfoot class="bg-gray-50 font-semibold">
                @php
                    $totBudget = $monthlyData->sum('budget');
                    $totActual = $monthlyData->sum('actual');
                    $totVariance = $totBudget - $totActual;
                @endphp
                <tr>
                    <td>Total</td>
                    <td class="text-right font-mono">₱{{ number_format($totBudget, 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($totActual, 2) }}</td>
                    <td class="text-right font-mono {{ $totVariance < 0 ? 'text-danger-600' : 'text-success-600' }}">₱{{ number_format(abs($totVariance), 2) }}</td>
                    <td class="text-right font-mono">{{ $totBudget > 0 ? number_format($totVariance / $totBudget * 100, 1) : '0.0' }}%</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
