@extends('layouts.app')
@section('title', 'Budget vs Actual')

@section('content')
@php
    $budgets = $budgets ?? collect();
    $totalBudget = $budgets->sum('annual_budget');
    $totalActual = $budgets->sum('actual');
    $totalCommitted = $budgets->sum('committed');
@endphp

<x-page-header title="Budget vs Actual" subtitle="Compare planned budgets against actual spending">
    <x-slot:actions>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm">Excel</a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn-secondary text-sm">PDF</a>
    </x-slot:actions>
</x-page-header>

<x-filter-bar>
    <div>
        <label class="form-label">School Year</label>
        <select name="school_year" class="form-input w-40">
            <option value="">All Years</option>
            <option value="2025-2026" {{ request('school_year') == '2025-2026' ? 'selected' : '' }}>2025-2026</option>
            <option value="2024-2025" {{ request('school_year') == '2024-2025' ? 'selected' : '' }}>2024-2025</option>
        </select>
    </div>
    <div>
        <label class="form-label">Department</label>
        <select name="department_id" class="form-input w-48">
            <option value="">All Departments</option>
            @foreach($departments ?? collect() as $dept)
                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>
</x-filter-bar>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <x-stat-card label="Total Budget" :value="'₱' . number_format($totalBudget, 2)" color="blue" />
    <x-stat-card label="Total Actual" :value="'₱' . number_format($totalActual, 2)" color="green" />
    <x-stat-card label="Overall Utilization" :value="($totalBudget > 0 ? number_format($totalActual / $totalBudget * 100, 1) : '0.0') . '%'" color="purple" />
</div>

{{-- Budget vs Actual Table --}}
<div class="card">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Budget Comparison</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Budget Name</th>
                    <th>Department</th>
                    <th class="text-right">Budget</th>
                    <th class="text-right">Committed</th>
                    <th class="text-right">Actual</th>
                    <th class="text-right">Variance</th>
                    <th class="text-right">Utilization</th>
                </tr>
            </thead>
            <tbody>
                @forelse($budgets as $budget)
                    @php
                        $variance = $budget->annual_budget - $budget->actual;
                        $utilization = $budget->annual_budget > 0 ? ($budget->actual / $budget->annual_budget * 100) : 0;
                        $isOver = $variance < 0;
                    @endphp
                    <tr>
                        <td class="font-medium">{{ $budget->budget_name }}</td>
                        <td>{{ $budget->department->name ?? '' }}</td>
                        <td class="text-right font-mono">₱{{ number_format($budget->annual_budget, 2) }}</td>
                        <td class="text-right font-mono text-warning-600">₱{{ number_format($budget->committed, 2) }}</td>
                        <td class="text-right font-mono">₱{{ number_format($budget->actual, 2) }}</td>
                        <td class="text-right font-mono font-semibold {{ $isOver ? 'text-danger-600' : 'text-success-600' }}">
                            {{ $isOver ? '-' : '' }}₱{{ number_format(abs($variance), 2) }}
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-20 bg-gray-200 rounded-full h-2">
                                    <div class="{{ $utilization > 100 ? 'bg-danger-500' : ($utilization > 80 ? 'bg-warning-500' : 'bg-success-500') }} h-2 rounded-full" style="width: {{ min($utilization, 100) }}%"></div>
                                </div>
                                <span class="text-sm font-mono {{ $utilization > 100 ? 'text-danger-600' : ($utilization > 80 ? 'text-warning-600' : 'text-success-600') }}">{{ number_format($utilization, 1) }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-secondary-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                            <p>No budget data available. Create budgets first.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($budgets->isNotEmpty())
            <tfoot class="bg-gray-50 font-semibold">
                <tr>
                    <td colspan="2" class="text-right">Totals</td>
                    <td class="text-right font-mono">₱{{ number_format($totalBudget, 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($totalCommitted, 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($totalActual, 2) }}</td>
                    <td class="text-right font-mono {{ ($totalBudget - $totalActual) < 0 ? 'text-danger-600' : 'text-success-600' }}">
                        ₱{{ number_format(abs($totalBudget - $totalActual), 2) }}
                    </td>
                    <td class="text-right font-mono">{{ $totalBudget > 0 ? number_format($totalActual / $totalBudget * 100, 1) : '0.0' }}%</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
