@extends('layouts.app')
@section('title', 'Budget Performance Report')

@section('content')
<x-page-header title="Budget Performance Report" subtitle="Approved budget vs actual expenses by category">
    <x-slot:actions>
        <a href="{{ route('reports.budget-performance.export', ['format' => 'csv', 'school_year' => $schoolYear, 'department_id' => $departmentId, 'as_of_date' => $asOfDate]) }}" class="btn-secondary text-sm">Excel</a>
        <a href="{{ route('reports.budget-performance.export', ['format' => 'pdf', 'school_year' => $schoolYear, 'department_id' => $departmentId, 'as_of_date' => $asOfDate]) }}" class="btn-secondary text-sm">PDF</a>
    </x-slot:actions>
</x-page-header>

{{-- Filters --}}
<div class="card mb-6 print:hidden">
    <form method="GET" action="{{ route('reports.budget-performance') }}" class="p-4">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="form-label">School Year</label>
                <select name="school_year" class="form-input w-40">
                    <option value="2025-2026" {{ $schoolYear === '2025-2026' ? 'selected' : '' }}>2025-2026</option>
                    <option value="2024-2025" {{ $schoolYear === '2024-2025' ? 'selected' : '' }}>2024-2025</option>
                </select>
            </div>
            <div>
                <label class="form-label">Department</label>
                <select name="department_id" class="form-input w-52">
                    <option value="">All Departments (Institutional)</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">As Of Date</label>
                <input type="date" name="as_of_date" class="form-input" value="{{ $asOfDate }}">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
            @if(request()->hasAny(['school_year', 'department_id', 'as_of_date']))
                <a href="{{ route('reports.budget-performance') }}" class="btn-secondary">Reset</a>
            @endif
        </div>
    </form>
</div>

{{-- Report --}}
<div class="card mb-6" id="report-content">
    <div class="p-6">
        {{-- Report Header --}}
        <div class="text-center mb-6">
            <h2 class="text-lg font-bold text-secondary-900">PERFORMANCE REPORT SY {{ $schoolYear }}</h2>
            <p class="text-sm text-secondary-600 font-medium">ST. SCHOLASTICA'S COLLEGE, MANILA</p>
            <p class="text-sm text-secondary-600 font-semibold">{{ $reportTitle }}</p>
            <p class="text-sm text-secondary-500 mt-1">FOR THE PERIOD ENDED {{ strtoupper(\Carbon\Carbon::parse($asOfDate)->format('F d, Y')) }}</p>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-3 text-center">
                <p class="text-xs font-medium text-blue-600 uppercase">Approved Budget</p>
                <p class="text-lg font-bold text-blue-800 mt-1">₱{{ number_format($totals['approved_budget'], 2) }}</p>
            </div>
            <div class="bg-green-50 rounded-lg p-3 text-center">
                <p class="text-xs font-medium text-green-600 uppercase">Actual ({{ $periodLabel }})</p>
                <p class="text-lg font-bold text-green-800 mt-1">₱{{ number_format($totals['actual'], 2) }}</p>
            </div>
            <div class="rounded-lg p-3 text-center {{ $totals['variance'] >= 0 ? 'bg-emerald-50' : 'bg-red-50' }}">
                <p class="text-xs font-medium {{ $totals['variance'] >= 0 ? 'text-emerald-600' : 'text-red-600' }} uppercase">Variance</p>
                <p class="text-lg font-bold {{ $totals['variance'] >= 0 ? 'text-emerald-800' : 'text-red-800' }} mt-1">
                    {{ $totals['variance'] >= 0 ? '' : '(' }}₱{{ number_format(abs($totals['variance']), 2) }}{{ $totals['variance'] >= 0 ? '' : ')' }}
                </p>
            </div>
            <div class="bg-purple-50 rounded-lg p-3 text-center">
                <p class="text-xs font-medium text-purple-600 uppercase">Utilization</p>
                <p class="text-lg font-bold text-purple-800 mt-1">{{ $totals['approved_budget'] > 0 ? number_format(($totals['actual'] / $totals['approved_budget']) * 100, 1) : '0.0' }}%</p>
            </div>
        </div>

        {{-- Performance Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="text-left p-3 border border-gray-200 font-semibold text-secondary-700" rowspan="2">Expense Category</th>
                        <th class="text-right p-3 border border-gray-200 font-semibold text-secondary-700" rowspan="2">
                            Approved Budget<br><span class="text-xs font-normal">FY {{ $schoolYear }}</span>
                        </th>
                        <th class="text-right p-3 border border-gray-200 font-semibold text-secondary-700" rowspan="2">
                            Actual<br><span class="text-xs font-normal">({{ $periodLabel }})</span>
                        </th>
                        <th class="text-center p-3 border border-gray-200 font-semibold text-secondary-700" colspan="2">Variance</th>
                    </tr>
                    <tr class="bg-gray-50">
                        <th class="text-right p-2 border border-gray-200 font-medium text-secondary-600 text-xs">Amount (B - A)</th>
                        <th class="text-right p-2 border border-gray-200 font-medium text-secondary-600 text-xs">%</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lineItems as $item)
                    @php
                        $isOver = $item['variance'] < 0;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 border border-gray-200 font-medium">{{ $item['category'] }}</td>
                        <td class="p-3 border border-gray-200 text-right font-mono">₱{{ number_format($item['approved_budget'], 2) }}</td>
                        <td class="p-3 border border-gray-200 text-right font-mono">₱{{ number_format($item['actual'], 2) }}</td>
                        <td class="p-3 border border-gray-200 text-right font-mono font-semibold {{ $isOver ? 'text-danger-600' : 'text-success-700' }}">
                            {{ $isOver ? '(' : '' }}₱{{ number_format(abs($item['variance']), 2) }}{{ $isOver ? ')' : '' }}
                        </td>
                        <td class="p-3 border border-gray-200 text-right font-mono {{ $isOver ? 'text-danger-600' : 'text-success-700' }}">
                            {{ $isOver ? '(' : '' }}{{ number_format(abs($item['variance_pct']), 1) }}%{{ $isOver ? ')' : '' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-secondary-400 border border-gray-200">
                            No budget data found for the selected filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($lineItems->isNotEmpty())
                <tfoot>
                    <tr class="bg-gray-100 font-bold">
                        <td class="p-3 border border-gray-300 text-right">TOTAL</td>
                        <td class="p-3 border border-gray-300 text-right font-mono">₱{{ number_format($totals['approved_budget'], 2) }}</td>
                        <td class="p-3 border border-gray-300 text-right font-mono">₱{{ number_format($totals['actual'], 2) }}</td>
                        <td class="p-3 border border-gray-300 text-right font-mono {{ $totals['variance'] < 0 ? 'text-danger-600' : 'text-success-700' }}">
                            {{ $totals['variance'] < 0 ? '(' : '' }}₱{{ number_format(abs($totals['variance']), 2) }}{{ $totals['variance'] < 0 ? ')' : '' }}
                        </td>
                        <td class="p-3 border border-gray-300 text-right font-mono {{ $totals['variance'] < 0 ? 'text-danger-600' : 'text-success-700' }}">
                            {{ $totals['variance'] < 0 ? '(' : '' }}{{ number_format(abs($totals['variance_pct']), 1) }}%{{ $totals['variance'] < 0 ? ')' : '' }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- Footer --}}
        <div class="mt-6 pt-4 border-t border-gray-200 text-xs text-secondary-400 flex justify-between">
            <span>Generated: {{ now()->format('M d, Y h:i A') }}</span>
            <span>School Finance System</span>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .print\:hidden, nav, header, .page-header-actions { display: none !important; }
        body { font-size: 11px; }
        .card { box-shadow: none !important; border: none !important; }
    }
</style>
@endpush
@endsection
