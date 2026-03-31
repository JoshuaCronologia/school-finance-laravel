@extends('layouts.app')
@section('title', 'Finance Dashboard')

@section('content')
{{-- <x-page-header title="Finance Dashboard" subtitle="School Year 2025-2026 Overview">
    <x-slot:actions>
        <a href="/ap/disbursements/create" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            New Request
        </a>
    </x-slot:actions>
</x-page-header> --}}

<div data-vue-root>
{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <x-stat-card label="Total Annual Budget" value="{{ '₱' . number_format($totalBudget, 2) }}" color="blue">
        <x-slot:icon><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75" /></svg></x-slot:icon>
    </x-stat-card>

    <x-stat-card label="Committed Budget" value="{{ '₱' . number_format($committed, 2) }}" color="yellow">
        <x-slot:icon><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot:icon>
    </x-stat-card>

    <x-stat-card label="Actual Spending" value="{{ '₱' . number_format($actual, 2) }}" color="red">
        <x-slot:icon><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg></x-slot:icon>
    </x-stat-card>

    <x-stat-card label="Remaining Budget" value="{{ '₱' . number_format($remaining, 2) }}" color="green">
        <x-slot:icon><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot:icon>
    </x-stat-card>
</div>

{{-- Charts Row --}}
<div data-vue-root>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Budget vs Actual by Department --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Budget vs Actual by Department</h3>
            </div>
            <div class="card-body">
                <div id="bar-chart-container" style="min-height: 320px;">
                    <bar-chart :labels='@json($departmentLabels)' :datasets='@json($departmentDatasets)' :currency="true"></bar-chart>
                </div>
            </div>
        </div>

        {{-- Monthly Expense Trend --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Monthly Expense Trend</h3>
            </div>
            <div class="card-body">
                <div id="line-chart-container" style="min-height: 320px;">
                    <line-chart :labels='@json($monthlyLabels)' :datasets='@json($monthlyDatasets)' :currency="true" :fill="true"></line-chart>
                </div>
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
                <doughnut-chart :labels='@json($categoryLabels)' :data='@json($categoryValues)' :currency="true"></doughnut-chart>
            </div>
        </div>
    </div>
</div>

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
                        <a href="/ap/disbursements/{{ $dr->id }}" class="text-primary-600 hover:underline">{{ $dr->request_number }}</a>
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

</div>{{-- /data-vue-root --}}
@endsection

@push('scripts')
<script>
    // Vue chart components are mounted via app.js; data is passed as props above.
</script>
@endpush
