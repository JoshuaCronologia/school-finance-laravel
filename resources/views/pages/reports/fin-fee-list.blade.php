@extends('layouts.app')
@section('title', 'Fee List Report')

@section('content')
<x-page-header title="Fee List Report" subtitle="Collections per student, grouped by fee">
    <x-slot name="actions">
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot>
</x-page-header>

<x-filter-bar action="{{ route('reports.fin.fee-list') }}">
    <div>
        <label class="form-label">Date Range</label>
        <div class="flex items-center gap-2">
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-input" onchange="this.form.submit()">
            <span class="text-secondary-400">—</span>
            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-input" onchange="this.form.submit()">
        </div>
    </div>
    <div>
        <label class="form-label">Fees</label>
        <select name="fee_name" class="form-input w-64" onchange="this.form.submit()">
            <option value="">All Fees</option>
            @foreach($feeNames as $fn)
                <option value="{{ $fn }}" {{ $feeName == $fn ? 'selected' : '' }}>{{ $fn }}</option>
            @endforeach
        </select>
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
                    <th>STUDENT ID</th>
                    <th>NAME</th>
                    <th>GRADE/YEAR LEVEL</th>
                    <th>COURSE/STRAND</th>
                    <th>SECTION</th>
                    <th>RECEIPT NUMBER</th>
                    <th>OR DATE</th>
                    <th>REMARK</th>
                    <th class="text-right">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @php $currentFee = null; $feeTotal = 0; @endphp
                @forelse($records as $r)
                    @if($r->fee_name !== $currentFee)
                        @if($currentFee !== null)
                        <tr class="bg-secondary-100 font-semibold">
                            <td colspan="8" class="px-3 py-1.5 text-right text-xs uppercase tracking-wide text-secondary-600">TOTAL</td>
                            <td class="px-3 py-1.5 text-right">{{ number_format($feeTotal, 2) }}</td>
                        </tr>
                        @endif
                        @php $currentFee = $r->fee_name; $feeTotal = 0; @endphp
                        <tr class="bg-gray-100">
                            <td colspan="9" class="px-3 py-2 font-semibold text-xs uppercase tracking-wide text-secondary-700">{{ $r->fee_name }}</td>
                        </tr>
                    @endif
                    @php $feeTotal += $r->amount; @endphp
                    <tr>
                        <td class="font-mono text-xs">{{ $r->student_id ?: '—' }}</td>
                        <td>{{ $r->student_name ?: '—' }}</td>
                        <td>{{ $r->year_level ?: '—' }}</td>
                        <td>{{ $r->course_strand ?: '—' }}</td>
                        <td>{{ $r->section ?: '—' }}</td>
                        <td class="font-mono">{{ $r->receipt_number }}</td>
                        <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($r->date_paid)->format('M d, Y') }}</td>
                        <td class="text-secondary-500 max-w-xs truncate">{{ $r->remark ?: '—' }}</td>
                        <td class="text-right">{{ number_format($r->amount, 2) }}</td>
                    </tr>
                @empty
                <tr><td colspan="9" class="px-3 py-6 text-center text-secondary-400">No records found for the selected period.</td></tr>
                @endforelse
                @if($currentFee !== null)
                <tr class="bg-secondary-100 font-semibold">
                    <td colspan="8" class="px-3 py-1.5 text-right text-xs uppercase tracking-wide text-secondary-600">TOTAL</td>
                    <td class="px-3 py-1.5 text-right">{{ number_format($feeTotal, 2) }}</td>
                </tr>
                @endif
            </tbody>
            @if($records->isNotEmpty())
            <tfoot>
                <tr class="bg-gray-50 font-semibold text-sm border-t-2 border-gray-300">
                    <td colspan="8" class="px-4 py-2 text-right text-secondary-600">Page Total:</td>
                    <td class="px-4 py-2 text-right font-bold">{{ number_format($records->sum('amount'), 2) }}</td>
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
