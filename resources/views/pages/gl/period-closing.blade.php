@extends('layouts.app')
@section('title', 'Period Closing')

@section('content')
@php
    $allPeriods = $periods ?? collect();
    $totalPeriods = $allPeriods->count();
    $openPeriods = $allPeriods->where('status', 'open');
    $closedPeriods = $allPeriods->where('status', 'closed');
@endphp

<x-page-header title="Period Closing" />

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Stat Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <x-stat-card label="Total Periods" :value="$totalPeriods" color="blue"
        icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>' />

    <x-stat-card label="Open" :value="$openPeriods->count()" color="green"
        icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>' />

    <x-stat-card label="Closed" :value="$closedPeriods->count()" color="gray"
        icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>' />
</div>

{{-- Pre-Closing Checklist --}}
<div class="card mb-6">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Pre-Closing Checklist</h3>
    </div>
    <div class="card-body">
        <div class="space-y-3">
            @php
                $checklist = $closingChecklist ?? [
                    ['label' => 'All journal entries are posted', 'passed' => false],
                    ['label' => 'No draft bills pending approval', 'passed' => false],
                    ['label' => 'No unreconciled collections', 'passed' => false],
                    ['label' => 'Bank reconciliation completed', 'passed' => false],
                    ['label' => 'Depreciation entries posted', 'passed' => false],
                    ['label' => 'Accrual entries posted', 'passed' => false],
                    ['label' => 'Trial balance is balanced', 'passed' => false],
                ];
            @endphp
            @foreach($checklist as $item)
            <div class="flex items-center gap-3">
                @if($item['passed'] ?? false)
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-success-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-success-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    </div>
                    <span class="text-sm text-secondary-700">{{ $item['label'] }}</span>
                @else
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-danger-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-danger-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                    </div>
                    <span class="text-sm text-secondary-500">{{ $item['label'] }}</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Open Periods Table --}}
<div class="card mb-6">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Open Periods</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Period Name</th>
                    <th>School Year</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($openPeriods as $period)
                <tr>
                    <td class="font-medium text-secondary-900">{{ $period->name }}</td>
                    <td>{{ $period->school_year ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($period->start_date)->format('M d, Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($period->end_date)->format('M d, Y') }}</td>
                    <td><x-badge status="active" /></td>
                    <td>
                        <button @click="$dispatch('open-modal', 'close-period-{{ $period->id }}')" class="btn-danger text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                            Close Period
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-secondary-400 py-6">No open periods.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Closed Periods Table --}}
<div class="card">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Closed Periods</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Period Name</th>
                    <th>School Year</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($closedPeriods as $period)
                <tr>
                    <td class="font-medium text-secondary-900">{{ $period->name }}</td>
                    <td>{{ $period->school_year ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($period->start_date)->format('M d, Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($period->end_date)->format('M d, Y') }}</td>
                    <td><x-badge status="completed" /></td>
                    <td>
                        <button @click="$dispatch('open-modal', 'reopen-period-{{ $period->id }}')" class="btn-secondary text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                            Reopen
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-secondary-400 py-6">No closed periods yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Close Period Confirmation Modals --}}
@foreach($openPeriods as $period)
<x-modal name="close-period-{{ $period->id }}" title="Close Period" maxWidth="md">
    <div class="text-center mb-6">
        <div class="mx-auto w-12 h-12 rounded-full bg-warning-100 flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-warning-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
        </div>
        <h4 class="text-lg font-semibold text-secondary-900 mb-2">Close "{{ $period->name }}"?</h4>
        <p class="text-sm text-secondary-500">Once closed, no more transactions can be posted to this period. This action can be reversed by reopening the period.</p>
    </div>
    <form action="{{ route('gl.period-closing.close') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="flex justify-center gap-3">
            <button type="button" @click="$dispatch('close-modal', 'close-period-{{ $period->id }}')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-danger">Yes, Close Period</button>
        </div>
    </form>
</x-modal>
@endforeach

{{-- Reopen Period Confirmation Modals --}}
@foreach($closedPeriods as $period)
<x-modal name="reopen-period-{{ $period->id }}" title="Reopen Period" maxWidth="md">
    <div class="text-center mb-6">
        <div class="mx-auto w-12 h-12 rounded-full bg-warning-100 flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-warning-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
        </div>
        <h4 class="text-lg font-semibold text-secondary-900 mb-2">Reopen "{{ $period->name }}"?</h4>
        <p class="text-sm text-secondary-500">Reopening this period will allow new transactions to be posted. Ensure this is authorized before proceeding.</p>
    </div>
    <form action="{{ route('gl.period-closing.reopen') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="flex justify-center gap-3">
            <button type="button" @click="$dispatch('close-modal', 'reopen-period-{{ $period->id }}')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Yes, Reopen Period</button>
        </div>
    </form>
</x-modal>
@endforeach
@endsection
