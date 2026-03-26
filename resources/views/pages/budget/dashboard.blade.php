@extends('layouts.app')
@section('title', 'Budget Dashboard')

@section('content')
<x-page-header title="Budget Dashboard" subtitle="Budget monitoring and analysis" />

{{-- Department Filter & PDF Export --}}
<div class="card mb-6">
    <div class="card-body">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-input w-64" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments ?? [] as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if(request('department_id'))
                    <a href="{{ request()->url() }}" class="btn-secondary">Clear Filter</a>
                @endif
            </form>

            <a href="{{ route('budget.budget-vs-actual.pdf', ['department_id' => request('department_id')]) }}" class="btn-primary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                Budget vs Actual PDF
            </a>
        </div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <x-stat-card label="Total Budget" value="{{ '₱' . number_format($totalBudget, 2) }}" color="blue">
        <x-slot:icon><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75" /></svg></x-slot:icon>
    </x-stat-card>

    <x-stat-card label="Committed" value="{{ '₱' . number_format($totalCommitted, 2) }}" color="yellow">
        <x-slot:icon><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot:icon>
    </x-stat-card>

    <x-stat-card label="Actual Spent" value="{{ '₱' . number_format($totalActual, 2) }}" color="red">
        <x-slot:icon><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg></x-slot:icon>
    </x-stat-card>

    <x-stat-card label="Remaining" value="{{ '₱' . number_format($totalRemaining, 2) }}" color="green" subtitle="Utilization: {{ number_format($utilizationRate, 1) }}%">
        <x-slot:icon><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot:icon>
    </x-stat-card>
</div>

{{-- Charts --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6" data-vue-root>
    {{-- Budget by Department Bar Chart --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title">Budget by Department</h3></div>
        <div class="card-body">
            <div id="dept-budget-chart" style="min-height: 320px;">
                <bar-chart :labels='@json($deptLabels)' :datasets='@json($deptDatasets)' :currency="true"></bar-chart>
            </div>
            {{-- Fallback: progress bars when Vue is not available --}}
            <noscript>
                @foreach($departmentBudgets ?? [] as $db)
                @php $pct = $db->budget > 0 ? round(($db->actual / $db->budget) * 100, 1) : 0; @endphp
                <div class="mb-3">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium">{{ $db->department->name ?? 'Unassigned' }}</span>
                        <span class="text-secondary-500">{{ '₱' . number_format($db->actual, 2) }} / {{ '₱' . number_format($db->budget, 2) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="{{ $pct > 90 ? 'bg-danger-500' : ($pct > 70 ? 'bg-warning-500' : 'bg-primary-500') }} h-3 rounded-full" style="width: {{ min($pct, 100) }}%"></div>
                    </div>
                    <div class="text-xs text-secondary-400 mt-0.5">{{ $pct }}% utilized</div>
                </div>
                @endforeach
            </noscript>
        </div>
    </div>

    {{-- Budget Utilization Doughnut --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title">Budget Utilization</h3></div>
        <div class="card-body flex flex-col items-center justify-center">
            <div id="utilization-chart" style="max-width: 320px; width: 100%;">
                <doughnut-chart
                    :labels='@json($utilizationLabels)'
                    :data='@json($utilizationValues)'
                    :currency="true"
                ></doughnut-chart>
            </div>
            {{-- Fallback SVG donut --}}
            <noscript>
                <div class="relative w-48 h-48">
                    <svg viewBox="0 0 36 36" class="w-48 h-48">
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e5e7eb" stroke-width="3" />
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#3b82f6" stroke-width="3" stroke-dasharray="{{ $utilizationRate }}, 100" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-3xl font-bold text-secondary-900">{{ $utilizationRate }}%</span>
                        <span class="text-xs text-secondary-500">Utilized</span>
                    </div>
                </div>
            </noscript>
            <div class="mt-4 grid grid-cols-3 gap-6 text-center text-sm">
                <div><div class="font-semibold text-primary-600">{{ '₱' . number_format($totalActual, 0) }}</div><div class="text-secondary-400">Actual</div></div>
                <div><div class="font-semibold text-warning-600">{{ '₱' . number_format($totalCommitted, 0) }}</div><div class="text-secondary-400">Committed</div></div>
                <div><div class="font-semibold text-success-600">{{ '₱' . number_format($totalRemaining, 0) }}</div><div class="text-secondary-400">Remaining</div></div>
            </div>
        </div>
    </div>
</div>

{{-- All Budgets Table --}}
<div class="card">
    <div class="card-header"><h3 class="card-title">All Budgets</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Budget Name</th>
                    <th>Department</th>
                    <th>Category</th>
                    <th class="text-right">Annual Budget</th>
                    <th class="text-right">Committed</th>
                    <th class="text-right">Actual</th>
                    <th class="text-right">Remaining</th>
                    <th>Utilization %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($budgets ?? [] as $budget)
                @php
                    $pct = $budget->utilization_percent ?? ($budget->annual_budget > 0 ? round(($budget->actual / $budget->annual_budget) * 100, 1) : 0);
                @endphp
                <tr>
                    <td class="font-medium">{{ $budget->budget_name }}</td>
                    <td>{{ $budget->department->name ?? '-' }}</td>
                    <td>{{ $budget->category->name ?? '-' }}</td>
                    <td class="text-right">{{ '₱' . number_format($budget->annual_budget, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($budget->committed ?? 0, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($budget->actual, 2) }}</td>
                    <td class="text-right {{ ($budget->remaining ?? 0) < 0 ? 'text-danger-600 font-semibold' : '' }}">{{ '₱' . number_format($budget->remaining ?? 0, 2) }}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                <div class="{{ $pct > 90 ? 'bg-danger-500' : ($pct > 70 ? 'bg-warning-500' : 'bg-primary-500') }} h-2 rounded-full transition-all" style="width: {{ min($pct, 100) }}%"></div>
                            </div>
                            <span class="text-xs font-medium {{ $pct > 100 ? 'text-danger-600' : '' }}">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-secondary-400 py-8">
                        <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9.75m3 0v3.375m0-3.375h3.375M6.75 3h3.375" /></svg>
                        No budgets found. Create a budget to get started.
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
    // Vue chart components are mounted via app.js; data is passed as props above.
</script>
@endpush
