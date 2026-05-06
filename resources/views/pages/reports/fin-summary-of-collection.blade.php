@extends('layouts.app')
@section('title', 'Summary of Collection')

@section('content')
<x-page-header title="Summary of Collection Report" subtitle="Cash receipts from Finance / Cashiering system">
    <x-slot name="actions">
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot>
</x-page-header>

<x-filter-bar action="{{ route('reports.fin.summary-of-collection') }}">
    <div>
        <label class="form-label">Search</label>
        <input type="text" name="search" value="{{ $search }}" placeholder="OR No., cashier..." class="form-input w-48">
    </div>
</x-filter-bar>

<div class="card mt-4 overflow-x-auto">
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" class="border-r border-gray-200">OR DATE (PAID)</th>
                <th rowspan="2" class="border-r border-gray-200">TRANSACTED BY</th>
                <th rowspan="2" class="border-r border-gray-200">OR NO.</th>
                <th rowspan="2" class="border-r border-gray-200">PAYOR</th>
                <th colspan="5" class="text-center border-r border-gray-200">PAYMENT MODE</th>
                <th rowspan="2" class="text-right border-r border-gray-200">TOTAL</th>
                <th rowspan="2">REMARKS</th>
            </tr>
            <tr>
                <th class="text-right">CASH</th>
                <th class="text-right">CHEQUE</th>
                <th class="text-right">CREDIT CARD</th>
                <th class="text-right">DIRECT DEP.</th>
                <th class="text-right border-r border-gray-200">PDC</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $r)
            @php
                $typeMap = [
                    1 => $r->student_name ?? '—',
                    2 => $r->employee_name ?? '—',
                    3 => trim($r->walkin_name) ?: '—',
                ];
                $payor = isset($typeMap[$r->customer_type]) ? $typeMap[$r->customer_type] : '—';
            @endphp
            <tr>
                <td class="border-r border-gray-100">{{ \Carbon\Carbon::parse($r->date_paid)->format('M d, Y') }}</td>
                <td class="border-r border-gray-100">{{ $r->transacted_by ?: '—' }}</td>
                <td class="font-mono border-r border-gray-100">{{ $r->receipt_number }}</td>
                <td class="border-r border-gray-100">{{ $payor }}</td>
                <td class="text-right">{{ $r->cash_amt > 0 ? number_format($r->cash_amt, 2) : '' }}</td>
                <td class="text-right">{{ $r->cheque_amt > 0 ? number_format($r->cheque_amt, 2) : '' }}</td>
                <td class="text-right">{{ $r->cc_amt > 0 ? number_format($r->cc_amt, 2) : '' }}</td>
                <td class="text-right">{{ $r->dd_amt > 0 ? number_format($r->dd_amt, 2) : '' }}</td>
                <td class="text-right border-r border-gray-100">{{ $r->pdc_amt > 0 ? number_format($r->pdc_amt, 2) : '' }}</td>
                <td class="text-right font-medium border-r border-gray-100">{{ number_format($r->total, 2) }}</td>
                <td class="text-secondary-400 max-w-xs truncate">{{ $r->remarks ?: '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="11" class="px-4 py-6 text-center text-secondary-400">No records found for the selected period.</td></tr>
            @endforelse
        </tbody>
        @if($records->isNotEmpty())
        <tfoot>
            <tr class="bg-gray-50 font-semibold text-sm border-t-2 border-gray-300">
                <td colspan="4" class="px-4 py-2 text-right text-secondary-600 border-r border-gray-200">Page Total:</td>
                <td class="px-4 py-2 text-right">{{ number_format($records->sum('cash_amt'), 2) }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($records->sum('cheque_amt'), 2) }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($records->sum('cc_amt'), 2) }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($records->sum('dd_amt'), 2) }}</td>
                <td class="px-4 py-2 text-right border-r border-gray-200">{{ number_format($records->sum('pdc_amt'), 2) }}</td>
                <td class="px-4 py-2 text-right font-bold border-r border-gray-200">{{ number_format($records->sum('total'), 2) }}</td>
                <td></td>
            </tr>
            @if($grandTotal !== null)
            <tr class="bg-secondary-100 font-semibold text-sm border-t-4 border-double border-gray-400">
                <td colspan="4" class="px-4 py-2 text-right text-secondary-700 uppercase tracking-wide text-xs border-r border-gray-200">Grand Total:</td>
                <td class="px-4 py-2 text-right">{{ number_format($grandTotal->cash_amt, 2) }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($grandTotal->cheque_amt, 2) }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($grandTotal->cc_amt, 2) }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($grandTotal->dd_amt, 2) }}</td>
                <td class="px-4 py-2 text-right border-r border-gray-200">{{ number_format($grandTotal->pdc_amt, 2) }}</td>
                <td class="px-4 py-2 text-right font-bold border-r border-gray-200">{{ number_format($grandTotal->total, 2) }}</td>
                <td></td>
            </tr>
            @endif
        </tfoot>
        @endif
    </table>
    <div class="p-4">
        {{ $records->appends(request()->query())->links() }}
    </div>
</div>
@endsection
