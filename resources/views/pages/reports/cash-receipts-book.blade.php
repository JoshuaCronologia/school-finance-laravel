@extends('layouts.app')
@section('title', 'Cash Receipts Book')

@section('content')
@php
    $tab       = $tab ?? 'gl';
    $dateFrom  = $dateFrom ?? now()->startOfMonth()->toDateString();
    $dateTo    = $dateTo ?? now()->toDateString();
    $search    = $search ?? '';
    $entries   = $entries ?? collect();
    $finRecords = $finRecords ?? collect();
@endphp

<x-page-header title="Cash Receipts Book" subtitle="GL entries & Cashier receipts">
    <x-slot name="actions">
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot>
</x-page-header>

{{-- Filters --}}
<form method="GET" action="{{ route('reports.cash-receipts-book') }}" class="card mb-4 p-4">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <div class="flex flex-wrap items-end gap-4">
        <div>
            <label class="form-label">From</label>
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-input">
        </div>
        <div>
            <label class="form-label">To</label>
            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-input">
        </div>
        @if($tab === 'cashier')
        <div>
            <label class="form-label">Search</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="OR No., name..." class="form-input w-48">
        </div>
        @endif
        <button type="submit" class="btn-primary">Filter</button>
        <a href="{{ route('reports.cash-receipts-book', ['tab' => $tab]) }}" class="btn-secondary">Clear</a>
    </div>
</form>

{{-- Tabs --}}
<div class="flex border-b border-gray-200 mb-4 no-print">
    <a href="{{ route('reports.cash-receipts-book', ['date_from' => $dateFrom, 'date_to' => $dateTo, 'tab' => 'gl']) }}"
       class="px-5 py-2 text-sm font-medium border-b-2 {{ $tab === 'gl' ? 'border-primary-600 text-primary-700' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
        GL / Books of Account
    </a>
    <a href="{{ route('reports.cash-receipts-book', ['date_from' => $dateFrom, 'date_to' => $dateTo, 'tab' => 'cashier']) }}"
       class="px-5 py-2 text-sm font-medium border-b-2 {{ $tab === 'cashier' ? 'border-primary-600 text-primary-700' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
        Cashier Receipts
    </a>
</div>

{{-- GL Tab --}}
@if($tab === 'gl')
<div class="card">
    <div class="card-header bg-gray-50 text-center">
        <div class="w-full">
            <h2 class="text-sm font-bold text-secondary-900">CASH RECEIPTS BOOK</h2>
            <p class="text-xs text-secondary-500">{{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-28">Date</th>
                    <th class="w-28">Entry #</th>
                    <th class="w-28">Ref No.</th>
                    <th>Description</th>
                    <th>Cash/Bank Account</th>
                    <th class="text-right w-32">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($entry->posting_date)->format('M d, Y') }}</td>
                    <td><span class="font-mono text-sm">{{ $entry->entry_number }}</span></td>
                    <td class="font-mono text-sm text-secondary-500">{{ $entry->reference_number ?? '-' }}</td>
                    <td>{{ $entry->je_description ?? $entry->description ?? '-' }}</td>
                    <td class="text-sm">{{ $entry->account_code }} - {{ $entry->account_name }}</td>
                    <td class="text-right font-mono font-medium">₱{{ number_format($entry->debit, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-secondary-400 py-8">No cash receipts for this period.</td></tr>
                @endforelse
            </tbody>
            @if($entries->count() > 0)
            <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-400">
                <tr>
                    <td colspan="5" class="text-right">Total Cash Receipts:</td>
                    <td class="text-right font-mono">₱{{ number_format($totalAmount, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endif

{{-- Cashier Tab --}}
@if($tab === 'cashier')
<div class="card">
    <div class="card-header bg-gray-50 text-center">
        <div class="w-full">
            <h2 class="text-sm font-bold text-secondary-900">CASHIER RECEIPTS</h2>
            <p class="text-xs text-secondary-500">{{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}</p>
        </div>
    </div>
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
                @forelse($finRecords as $r)
                @php
                    $typeMap = [1 => $r->student_name ?? '—', 2 => $r->employee_name ?? '—', 3 => trim($r->walkin_name) ?: '—'];
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
            @if($finRecords->isNotEmpty())
            <tfoot>
                <tr class="bg-gray-50 font-semibold text-sm border-t-2 border-gray-300">
                    <td colspan="5" class="px-4 py-2 text-right text-secondary-600">Total Receipts:</td>
                    <td class="px-4 py-2 text-right font-bold">{{ number_format($finRecords->sum('amount'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    @if(method_exists($finRecords, 'appends'))
    <div class="p-4">{{ $finRecords->appends(request()->query())->links() }}</div>
    @endif
</div>
@endif

@endsection
