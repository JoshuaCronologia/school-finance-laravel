@extends('layouts.app')
@section('title', 'AR Aging')

@section('content')
@php
    $agingData = $aging ?? collect();
    $totalCurrent = $agingData->sum('current');
    $total30 = $agingData->sum('days_30');
    $total60 = $agingData->sum('days_60');
    $total90 = $agingData->sum('days_90_plus');
    $grandTotal = $totalCurrent + $total30 + $total60 + $total90;
@endphp

<x-page-header title="Accounts Receivable Aging">
    <x-slot name="actions">
        <a href="{{ route('ar.aging.export', request()->query()) }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Export Excel
        </a>
    </x-slot>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif

{{-- As-of Date Filter --}}
<div class="card mb-6">
    <div class="card-body">
        <form action="{{ route('ar.aging') }}" method="GET" class="flex items-end gap-4">
            <div>
                <label class="form-label">As of Date</label>
                <input type="date" name="as_of_date" value="{{ request('as_of_date', date('Y-m-d')) }}" class="form-input w-48">
            </div>
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                Generate
            </button>
            <a href="{{ route('ar.aging') }}" class="btn-secondary">Reset</a>
        </form>
    </div>
</div>

{{-- Summary Stat Cards --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <x-stat-card label="Current" :value="'₱' . number_format($totalCurrent, 2)" color="green" />
    <x-stat-card label="1-30 Days" :value="'₱' . number_format($total30, 2)" color="yellow" />
    <x-stat-card label="31-60 Days" :value="'₱' . number_format($total60, 2)" color="yellow" />
    <x-stat-card label="Over 90 Days" :value="'₱' . number_format($total90, 2)" color="red" />
    <x-stat-card label="Grand Total" :value="'₱' . number_format($grandTotal, 2)" color="blue" />
</div>

{{-- Aging Table --}}
<div class="card">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Customer Code</th>
                    <th>Customer Name</th>
                    <th class="text-right">Current</th>
                    <th class="text-right">1-30 Days</th>
                    <th class="text-right">31-60 Days</th>
                    <th class="text-right">61-90 Days</th>
                    <th class="text-right">Over 90 Days</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agingData as $row)
                <tr>
                    <td class="font-medium text-secondary-900">{{ $row->customer_code ?? '-' }}</td>
                    <td>{{ $row->customer_name ?? '-' }}</td>
                    <td class="text-right">{{ '₱' . number_format($row->current ?? 0, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($row->days_30 ?? 0, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($row->days_60 ?? 0, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($row->days_90 ?? 0, 2) }}</td>
                    <td class="text-right {{ ($row->days_90_plus ?? 0) > 0 ? 'text-danger-500 font-medium' : '' }}">{{ '₱' . number_format($row->days_90_plus ?? 0, 2) }}</td>
                    <td class="text-right font-semibold">{{ '₱' . number_format(($row->current ?? 0) + ($row->days_30 ?? 0) + ($row->days_60 ?? 0) + ($row->days_90 ?? 0) + ($row->days_90_plus ?? 0), 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-secondary-400 py-8">
                        <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" /></svg>
                        No aging data available. Adjust the date filter and click Generate.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($agingData->isNotEmpty())
            <tfoot>
                <tr class="bg-gray-50 font-semibold">
                    <td colspan="2" class="text-right">Totals:</td>
                    <td class="text-right">{{ '₱' . number_format($totalCurrent, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($total30, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($total60, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($agingData->sum('days_90'), 2) }}</td>
                    <td class="text-right text-danger-500">{{ '₱' . number_format($total90, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($grandTotal, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
