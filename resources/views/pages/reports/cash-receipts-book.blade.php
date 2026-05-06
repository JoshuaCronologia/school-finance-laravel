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
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Excel
        </a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
            PDF
        </a>
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
                @php $currentOr = null; $grandTotal = $grandTotal ?? null; @endphp
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
                    <td colspan="5" class="px-4 py-2 text-right text-secondary-600">Page Total:</td>
                    <td class="px-4 py-2 text-right font-bold">{{ number_format($finRecords->sum('amount'), 2) }}</td>
                    <td></td>
                </tr>
                @if($grandTotal !== null)
                <tr class="bg-secondary-100 font-semibold text-sm border-t-4 border-double border-gray-400">
                    <td colspan="5" class="px-4 py-2 text-right text-secondary-700 uppercase tracking-wide text-xs">Grand Total:</td>
                    <td class="px-4 py-2 text-right font-bold">{{ number_format($grandTotal, 2) }}</td>
                    <td></td>
                </tr>
                @endif
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
