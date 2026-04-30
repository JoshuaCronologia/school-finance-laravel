@extends('layouts.app')
@section('title', 'Cash Receipt Books Report')

@section('content')
<x-page-header title="Cash Receipt Books Report" subtitle="Fee-level receipt entries from Finance / Cashiering system">
    <x-slot name="actions">
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot>
</x-page-header>

<x-filter-bar action="{{ route('reports.fin.cash-receipt-books') }}">
    <div>
        <label class="form-label">Date Range</label>
        <div class="flex items-center gap-2">
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-input" onchange="this.form.submit()">
            <span class="text-secondary-400">—</span>
            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-input" onchange="this.form.submit()">
        </div>
    </div>
    <div>
        <label class="form-label">Search</label>
        <input type="text" name="search" value="{{ $search }}" placeholder="OR No., name..." class="form-input w-48">
    </div>
    <div class="flex items-end">
        <button type="submit" class="btn-primary text-sm">Filter</button>
    </div>
</x-filter-bar>

<div class="card mt-4">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>OR#</th>
                    <th>DATE</th>
                    <th>PAYOR</th>
                    <th>REMARKS</th>
                    <th>ACCOUNT</th>
                    <th class="text-right">AMOUNT</th>
                    <th class="text-right">TOTAL AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @php $currentOr = null; @endphp
                @forelse($records as $r)
                @php
                    $typeMap = [
                        1 => $r->student_name ?? '—',
                        2 => $r->employee_name ?? '—',
                        3 => trim($r->walkin_name) ?: '—',
                    ];
                    $payor = isset($typeMap[$r->customer_type]) ? $typeMap[$r->customer_type] : '—';
                    $isNewOr = $r->receipt_number !== $currentOr;
                    $currentOr = $r->receipt_number;
                @endphp
                <tr class="{{ $isNewOr ? 'border-t-2 border-gray-300' : '' }}">
                    <td class="font-mono text-xs">{{ $isNewOr ? $r->receipt_number : '' }}</td>
                    <td class="whitespace-nowrap">{{ $isNewOr ? \Carbon\Carbon::parse($r->date_paid)->format('Y-m-d') : '' }}</td>
                    <td>{{ $isNewOr ? $payor : '' }}</td>
                    <td class="text-secondary-500 max-w-xs truncate">{{ $isNewOr ? ($r->remarks ?: '—') : '' }}</td>
                    <td>{{ $r->account }}</td>
                    <td class="text-right">{{ number_format($r->amount, 2) }}</td>
                    <td class="text-right font-medium">{{ $isNewOr ? number_format($r->batch_total, 2) : '' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-3 py-6 text-center text-secondary-400">No records found for the selected period.</td></tr>
                @endforelse
            </tbody>
            @if($records->isNotEmpty())
            <tfoot>
                <tr class="bg-gray-50 font-semibold text-sm border-t-2 border-gray-300">
                    <td colspan="5" class="px-4 py-2 text-right text-secondary-600">Page Total (fee lines):</td>
                    <td class="px-4 py-2 text-right font-bold">{{ number_format($records->sum('amount'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    <div class="p-4">
        {{ $records->appends(request()->query())->links() }}
    </div>
</div>
@endsection
