@extends('layouts.app')
@section('title', $budget->budget_name)

@section('content')
<x-page-header :title="$budget->budget_name" :subtitle="'SY ' . $budget->school_year . ' · ' . ($budget->department->name ?? '-')">
    <x-slot name="actions">
        <a href="{{ route('budget.planning') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
            Back to Planning
        </a>
    </x-slot>
</x-page-header>

{{-- Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="card"><div class="card-body text-center">
        <span class="text-xs text-secondary-500 uppercase">Annual Budget</span>
        <p class="font-bold text-xl text-primary-700">₱{{ number_format($budget->annual_budget, 2) }}</p>
    </div></div>
    <div class="card"><div class="card-body text-center">
        <span class="text-xs text-amber-600 uppercase">Committed</span>
        <p class="font-bold text-xl text-amber-700">₱{{ number_format($budget->committed, 2) }}</p>
        <span class="text-xs text-secondary-400">Approved disbursements (pending payment)</span>
    </div></div>
    <div class="card"><div class="card-body text-center">
        <span class="text-xs text-red-600 uppercase">Actual Spent</span>
        <p class="font-bold text-xl text-red-700">₱{{ number_format($budget->actual, 2) }}</p>
        <span class="text-xs text-secondary-400">Paid disbursements</span>
    </div></div>
    <div class="card border-2 border-green-300"><div class="card-body text-center">
        <span class="text-xs text-green-600 uppercase">Available</span>
        <p class="font-bold text-xl {{ $available < 0 ? 'text-red-700' : 'text-green-700' }}">₱{{ number_format($available, 2) }}</p>
        <span class="text-xs text-secondary-400">{{ $budget->annual_budget > 0 ? number_format(($available / $budget->annual_budget) * 100, 1) : 0 }}% remaining</span>
    </div></div>
</div>

{{-- Utilization Bar --}}
<div class="card mb-6">
    <div class="card-body">
        <div class="flex items-center justify-between mb-2 text-sm">
            <span class="font-semibold">Budget Utilization</span>
            @php
                $utilized = $budget->annual_budget > 0 ? ((float) $budget->committed + (float) $budget->actual) / (float) $budget->annual_budget * 100 : 0;
                $utilized = min(100, $utilized);
            @endphp
            <span class="font-medium {{ $utilized >= 90 ? 'text-red-600' : ($utilized >= 75 ? 'text-amber-600' : 'text-green-600') }}">{{ number_format($utilized, 1) }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
            <div class="h-3 {{ $utilized >= 90 ? 'bg-red-500' : ($utilized >= 75 ? 'bg-amber-500' : 'bg-green-500') }}" style="width: {{ $utilized }}%"></div>
        </div>
        <div class="flex justify-between text-xs text-secondary-500 mt-1">
            <span>₱0</span>
            <span>₱{{ number_format($budget->annual_budget, 2) }}</span>
        </div>
    </div>
</div>

{{-- Budget Info --}}
<div class="card mb-6">
    <div class="card-header"><h3 class="card-title">Budget Information</h3></div>
    <div class="card-body grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div>
            <span class="text-xs text-secondary-500 uppercase">Department</span>
            <p class="font-medium">{{ $budget->department->name ?? '-' }}</p>
        </div>
        <div>
            <span class="text-xs text-secondary-500 uppercase">Category</span>
            <p class="font-medium">{{ $budget->category->name ?? '-' }}</p>
        </div>
        <div>
            <span class="text-xs text-secondary-500 uppercase">Cost Center</span>
            <p class="font-medium">{{ $budget->costCenter->name ?? '-' }}</p>
        </div>
        <div>
            <span class="text-xs text-secondary-500 uppercase">Status</span>
            <p><x-badge :status="$budget->status" /></p>
        </div>
        @if($budget->project)
        <div>
            <span class="text-xs text-secondary-500 uppercase">Project</span>
            <p class="font-medium">{{ $budget->project }}</p>
        </div>
        @endif
        @if($budget->notes)
        <div class="col-span-2 md:col-span-4">
            <span class="text-xs text-secondary-500 uppercase">Notes</span>
            <p class="text-secondary-700">{{ $budget->notes }}</p>
        </div>
        @endif
    </div>
</div>

{{-- Disbursements using this budget --}}
<div class="card">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Disbursements Using This Budget</h3>
        <span class="text-xs text-secondary-500">{{ $disbursements->count() }} transactions</span>
    </div>

    @if($disbursements->count() > 0)
    <div class="grid grid-cols-3 gap-4 p-4 border-b border-gray-100 text-center">
        <div>
            <span class="text-xs text-secondary-500 uppercase">Pending (Draft/For Approval)</span>
            <p class="font-semibold text-secondary-700">₱{{ number_format($pendingTotal, 2) }}</p>
        </div>
        <div>
            <span class="text-xs text-amber-600 uppercase">Approved (Awaiting Payment)</span>
            <p class="font-semibold text-amber-700">₱{{ number_format($approvedTotal, 2) }}</p>
        </div>
        <div>
            <span class="text-xs text-red-600 uppercase">Paid</span>
            <p class="font-semibold text-red-700">₱{{ number_format($paidTotal, 2) }}</p>
        </div>
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Request #</th>
                    <th>Date</th>
                    <th>Payee</th>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                    <th>Status</th>
                    <th>Payment</th>
                </tr>
            </thead>
            <tbody>
                @forelse($disbursements as $dr)
                <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('ap.disbursements.show', $dr) }}'">
                    <td class="font-mono text-sm font-medium">
                        <a href="{{ route('ap.disbursements.show', $dr) }}" class="text-primary-600 hover:underline">{{ $dr->request_number }}</a>
                    </td>
                    <td class="text-sm">{{ $dr->request_date->format('M d, Y') }}</td>
                    <td>{{ $dr->payee_name }}</td>
                    <td class="text-sm text-secondary-600 max-w-xs truncate">{{ $dr->description ?? '-' }}</td>
                    <td class="text-right font-mono font-medium">₱{{ number_format($dr->amount, 2) }}</td>
                    <td><x-badge :status="$dr->status" /></td>
                    <td class="text-sm">
                        @if($dr->payment)
                            <span class="text-green-600 font-mono">{{ $dr->payment->voucher_number }}</span>
                            <div class="text-xs text-secondary-500">{{ \Carbon\Carbon::parse($dr->payment->payment_date)->format('M d, Y') }}</div>
                        @else
                            <span class="text-secondary-400">Not paid</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-8 text-secondary-400">
                        No disbursements yet for this budget. Create one at <a href="{{ route('ap.disbursements.create') }}" class="text-primary-600 hover:underline">Disbursement Requests</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($disbursements->count() > 0)
            <tfoot class="bg-gray-50 font-bold border-t-2">
                <tr>
                    <td colspan="4" class="text-right">Total Used:</td>
                    <td class="text-right">₱{{ number_format($disbursements->sum('amount'), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- Monthly Allocations --}}
@if($budget->allocations && $budget->allocations->count() > 0)
<div class="card mt-6">
    <div class="card-header"><h3 class="card-title">Monthly Allocations</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    @foreach(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $m)
                        <th class="text-right">{{ $m }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    @for($i = 1; $i <= 12; $i++)
                        @php $alloc = $budget->allocations->firstWhere('month', $i); @endphp
                        <td class="text-right text-sm">₱{{ number_format($alloc->amount ?? 0, 0) }}</td>
                    @endfor
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
