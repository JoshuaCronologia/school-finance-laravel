@extends('layouts.app')
@section('title', 'AP Aging Report')

@section('content')
<x-page-header title="AP Aging Report" subtitle="Accounts Payable aging analysis per vendor" />

{{-- Date Filter --}}
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">As of Date</label>
                <input type="date" name="as_of_date" class="form-input w-48" value="{{ $asOfDate }}">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <div class="card">
        <div class="card-body text-center py-3">
            <p class="text-xs text-secondary-500">Current</p>
            <p class="text-sm font-bold text-success-600">{{ '₱' . number_format($totals->current, 2) }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body text-center py-3">
            <p class="text-xs text-secondary-500">1-30 Days</p>
            <p class="text-sm font-bold text-warning-600">{{ '₱' . number_format($totals->days_1_30, 2) }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body text-center py-3">
            <p class="text-xs text-secondary-500">31-60 Days</p>
            <p class="text-sm font-bold text-orange-600">{{ '₱' . number_format($totals->days_31_60, 2) }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body text-center py-3">
            <p class="text-xs text-secondary-500">61-90 Days</p>
            <p class="text-sm font-bold text-danger-600">{{ '₱' . number_format($totals->days_61_90, 2) }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body text-center py-3">
            <p class="text-xs text-secondary-500">Over 90 Days</p>
            <p class="text-sm font-bold text-danger-700">{{ '₱' . number_format($totals->over_90, 2) }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body text-center py-3">
            <p class="text-xs text-secondary-500">Total AP</p>
            <p class="text-sm font-bold text-secondary-900">{{ '₱' . number_format($totals->total, 2) }}</p>
        </div>
    </div>
</div>

{{-- Aging Table per Vendor --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Aging per Vendor</h3>
        <span class="text-sm text-secondary-500">As of {{ \Carbon\Carbon::parse($asOfDate)->format('M d, Y') }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Vendor</th>
                    <th>TIN</th>
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
                    <td class="font-medium">{{ $row->vendor->name }}</td>
                    <td class="text-secondary-500">{{ $row->vendor->tin ?? '-' }}</td>
                    <td class="text-right">{{ $row->current > 0 ? '₱' . number_format($row->current, 2) : '-' }}</td>
                    <td class="text-right {{ $row->days_1_30 > 0 ? 'text-warning-600' : '' }}">{{ $row->days_1_30 > 0 ? '₱' . number_format($row->days_1_30, 2) : '-' }}</td>
                    <td class="text-right {{ $row->days_31_60 > 0 ? 'text-orange-600' : '' }}">{{ $row->days_31_60 > 0 ? '₱' . number_format($row->days_31_60, 2) : '-' }}</td>
                    <td class="text-right {{ $row->days_61_90 > 0 ? 'text-danger-600' : '' }}">{{ $row->days_61_90 > 0 ? '₱' . number_format($row->days_61_90, 2) : '-' }}</td>
                    <td class="text-right {{ $row->over_90 > 0 ? 'text-danger-700 font-semibold' : '' }}">{{ $row->over_90 > 0 ? '₱' . number_format($row->over_90, 2) : '-' }}</td>
                    <td class="text-right font-semibold">{{ '₱' . number_format($row->total, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-secondary-400 py-8">No outstanding payables found.</td>
                </tr>
                @endforelse
            </tbody>
            @if(count($agingData) > 0)
            <tfoot>
                <tr class="bg-gray-50 font-bold">
                    <td colspan="2" class="text-right">TOTAL</td>
                    <td class="text-right">{{ '₱' . number_format($totals->current, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($totals->days_1_30, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($totals->days_31_60, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($totals->days_61_90, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($totals->over_90, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($totals->total, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
