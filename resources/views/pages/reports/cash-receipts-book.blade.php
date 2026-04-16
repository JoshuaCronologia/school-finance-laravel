@extends('layouts.app')
@section('title', 'Cash Receipts Book')

@section('content')
<x-page-header title="Cash Receipts Book (CRB)" subtitle="All cash/bank receipts (debits to cash accounts)">
    <x-slot name="actions"><button onclick="window.print()" class="btn-secondary text-sm">Print</button></x-slot>
</x-page-header>

<x-filter-bar action="{{ route('reports.cash-receipts-book') }}">
</x-filter-bar>

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
@endsection
