@extends('layouts.app')
@section('title', 'Fee Collection Receipts')

@section('content')
<x-page-header title="Fee Collection Receipts" subtitle="Browse cashier receipts from Finance system">
    <x-slot name="actions">
        <a href="{{ route('reports.fee-collections') }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
            Back to Fee Collections
        </a>
    </x-slot>
</x-page-header>

{{-- Filters --}}
<div class="card mb-6">
    <form action="{{ route('reports.fee-receipts') }}" method="GET" class="card-body">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="form-label">School Year</label>
                <select name="school_year" class="form-input">
                    <option value="">All School Years</option>
                    @foreach($schoolYears as $sy)
                        <option value="{{ $sy->year_fr }}" {{ $selectedYear == $sy->year_fr ? 'selected' : '' }}>
                            SY {{ $sy->year_fr }}-{{ $sy->year_to }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Fee Type</label>
                <select name="fee_name" class="form-input">
                    <option value="">All Fees</option>
                    @foreach($feeNames as $fn)
                        <option value="{{ $fn }}" {{ $feeName === $fn ? 'selected' : '' }}>{{ $fn }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Search (OR #, Name, ID)</label>
                <input type="text" name="search" class="form-input" value="{{ $search }}" placeholder="Receipt #, student name…">
            </div>
            <div>
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-input" value="{{ $dateFrom }}">
            </div>
            <div>
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-input" value="{{ $dateTo }}">
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                Search
            </button>
            <a href="{{ route('reports.fee-receipts') }}" class="btn-secondary">Clear</a>
        </div>
    </form>
</div>

{{-- Receipts Table --}}
<div class="card">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>OR #</th>
                    <th>Type</th>
                    <th>Payer</th>
                    <th>ID / Number</th>
                    <th>Date Paid</th>
                    <th>School Year</th>
                    <th class="text-right">Amount</th>
                    <th class="w-20"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($receipts as $r)
                @php
                    $type = (int) $r->customer_type;
                    $typeMap = [
                        1 => ['label' => 'Student',  'class' => 'bg-blue-100 text-blue-700',
                              'name' => $r->student_name ?? '-',  'id' => $r->student_number ?? '-'],
                        2 => ['label' => 'Employee', 'class' => 'bg-purple-100 text-purple-700',
                              'name' => $r->employee_name ?? '-', 'id' => $r->employee_number ?? '-'],
                        3 => ['label' => 'Walk-in',  'class' => 'bg-yellow-100 text-yellow-700',
                              'name' => trim($r->walkin_name) ?: '-', 'id' => $r->walkin_number ?? '-'],
                    ];
                    $info       = isset($typeMap[$type]) ? $typeMap[$type] : ['label' => 'Type '.$type, 'class' => 'bg-secondary-100 text-secondary-600', 'name' => '-', 'id' => '-'];
                    $label      = $info['label'];
                    $labelClass = $info['class'];
                    $payerName  = $info['name'];
                    $payerId    = $info['id'];
                @endphp
                <tr>
                    <td class="font-medium font-mono">{{ $r->receipt_number }}</td>
                    <td>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $labelClass }}">{{ $label }}</span>
                    </td>
                    <td>{{ $payerName }}</td>
                    <td class="font-mono text-sm text-secondary-500">{{ $payerId }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->date_paid)->format('M d, Y') }}</td>
                    <td>{{ $r->year_fr ? $r->year_fr.'-'.$r->year_to : '-' }}</td>
                    <td class="text-right font-medium text-green-600">₱{{ number_format($r->total, 2) }}</td>
                    <td>
                        <a href="{{ route('reports.fee-receipt-detail', $r->id) }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-secondary-400 py-8">No receipts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($receipts->hasPages())
    <div class="card-body border-t border-gray-100">
        {{ $receipts->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
