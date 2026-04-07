@extends('layouts.app')
@section('title', 'Invoice #' . $invoice->invoice_number)

@section('content')
<x-page-header :title="'Invoice #' . $invoice->invoice_number" :subtitle="$invoice->customer->name ?? 'AR Invoice'">
    <x-slot name="actions">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('ar.invoices.index') }}" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                Back to Invoices
            </a>

            @if($invoice->status === 'draft')
                <form action="{{ route('ar.invoices.update', $invoice) }}" method="POST" class="inline">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="posted">
                    <button type="submit" class="btn-primary inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        Post Invoice
                    </button>
                </form>
                <form action="{{ route('ar.invoices.update', $invoice) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this invoice?');">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn-secondary text-danger-600 hover:text-danger-700 inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        Cancel
                    </button>
                </form>
            @endif

            <button onclick="window.print()" class="btn-secondary inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M9.75 21h4.5" /></svg>
                Print
            </button>
        </div>
    </x-slot>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Invoice Details --}}
    <div class="card lg:col-span-2">
        <div class="card-header">
            <div class="flex items-center justify-between w-full">
                <h3 class="card-title">Invoice Information</h3>
                <x-badge :status="$invoice->status" />
            </div>
        </div>
        <div class="card-body">
            <dl class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-4">
                <div>
                    <dt class="text-xs text-secondary-500">Invoice Number</dt>
                    <dd class="text-sm font-semibold text-secondary-900">{{ $invoice->invoice_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Invoice Date</dt>
                    <dd class="text-sm font-medium">{{ $invoice->invoice_date->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Due Date</dt>
                    <dd class="text-sm font-medium {{ $invoice->due_date->isPast() && $invoice->balance > 0 ? 'text-danger-600' : '' }}">
                        {{ $invoice->due_date->format('M d, Y') }}
                        @if($invoice->due_date->isPast() && $invoice->balance > 0)
                            <span class="text-xs text-danger-500">(Overdue)</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Reference #</dt>
                    <dd class="text-sm font-medium">{{ $invoice->reference_number ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Customer</dt>
                    <dd class="text-sm font-semibold text-secondary-900">{{ $invoice->customer->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Campus</dt>
                    <dd class="text-sm font-medium">{{ $invoice->campus->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">School Year</dt>
                    <dd class="text-sm font-medium">{{ $invoice->school_year ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Semester</dt>
                    <dd class="text-sm font-medium">{{ $invoice->semester ?: '-' }}</dd>
                </div>
                @if($invoice->description)
                <div class="col-span-2 md:col-span-4">
                    <dt class="text-xs text-secondary-500">Description</dt>
                    <dd class="text-sm font-medium">{{ $invoice->description }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- Financial Summary --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title">Amount Summary</h3></div>
        <div class="card-body space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-secondary-500">Gross Amount</span>
                <span class="font-medium">@currency($invoice->gross_amount)</span>
            </div>
            @if($invoice->discount_amount > 0)
            <div class="flex justify-between text-sm">
                <span class="text-secondary-500">Discount</span>
                <span class="font-medium text-success-600">(@currency($invoice->discount_amount))</span>
            </div>
            @endif
            @if($invoice->tax_amount > 0)
            <div class="flex justify-between text-sm">
                <span class="text-secondary-500">Tax</span>
                <span class="font-medium">@currency($invoice->tax_amount)</span>
            </div>
            @endif
            <div class="flex justify-between text-sm pt-2 border-t border-gray-200 font-semibold">
                <span>Net Receivable</span>
                <span>@currency($invoice->net_receivable)</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-secondary-500">Amount Paid</span>
                <span class="font-medium text-success-600">@currency($invoice->amount_paid)</span>
            </div>
            <div class="flex justify-between text-sm pt-2 border-t border-gray-200">
                <span class="font-bold text-lg">Balance</span>
                <span class="font-bold text-lg {{ $invoice->balance > 0 ? 'text-danger-600' : 'text-success-600' }}">
                    @currency($invoice->balance)
                </span>
            </div>
            @if($invoice->balance <= 0 && $invoice->status !== 'draft')
                <div class="text-center pt-2">
                    <span class="inline-flex items-center gap-1 text-sm font-semibold text-success-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        Fully Paid
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Line Items --}}
<div class="card mb-6">
    <div class="card-header"><h3 class="card-title">Line Items</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fee Code</th>
                    <th>Description</th>
                    <th>Revenue Account</th>
                    <th>Department</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Amount</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->lines as $line)
                <tr>
                    <td class="font-mono text-sm">{{ $line->fee_code ?: '-' }}</td>
                    <td>{{ $line->description }}</td>
                    <td class="text-sm">{{ $line->revenueAccount->account_code ?? '' }} {{ $line->revenueAccount->account_name ?? '-' }}</td>
                    <td>{{ $line->department->name ?? '-' }}</td>
                    <td class="text-right">{{ number_format($line->quantity, 2) }}</td>
                    <td class="text-right">@currency($line->unit_amount)</td>
                    <td class="text-right font-medium">@currency($line->amount)</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-secondary-400 py-4">No line items.</td></tr>
                @endforelse
            </tbody>
            @if($invoice->lines->count() > 0)
            <tfoot class="bg-gray-50 font-semibold">
                <tr>
                    <td colspan="6" class="text-right">Total:</td>
                    <td class="text-right">@currency($invoice->lines->sum('amount'))</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- Collections/Payments Applied --}}
@if($invoice->allocations && $invoice->allocations->count() > 0)
<div class="card mb-6">
    <div class="card-header"><h3 class="card-title">Payments Applied</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Receipt #</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th class="text-right">Amount Applied</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->allocations as $alloc)
                <tr>
                    <td class="font-medium">
                        @if($alloc->collection)
                            <a href="{{ route('ar.collections.show', $alloc->collection_id) }}" class="text-primary-600 hover:underline">
                                {{ $alloc->collection->receipt_number ?? '-' }}
                            </a>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ ($alloc->collection && $alloc->collection->collection_date) ? $alloc->collection->collection_date->format('M d, Y') : '-' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', ($alloc->collection ? $alloc->collection->payment_method : '-'))) }}</td>
                    <td class="text-right font-medium">@currency($alloc->amount_applied ?? $alloc->amount ?? 0)</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Journal Entry --}}
@if($invoice->journalEntry)
<div class="card">
    <div class="card-header">
        <div class="flex items-center justify-between w-full">
            <h3 class="card-title">Journal Entry</h3>
            <a href="{{ route('gl.journal-entries.show', $invoice->journalEntry) }}" class="text-sm text-primary-600 hover:underline">
                {{ $invoice->journalEntry->entry_number }}
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Account Code</th>
                    <th>Account Name</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->journalEntry->lines as $jeLine)
                <tr>
                    <td class="font-mono text-sm">{{ $jeLine->account->account_code ?? '-' }}</td>
                    <td>{{ $jeLine->account->account_name ?? '-' }}</td>
                    <td class="text-right">{{ $jeLine->debit > 0 ? '₱' . number_format($jeLine->debit, 2) : '' }}</td>
                    <td class="text-right">{{ $jeLine->credit > 0 ? '₱' . number_format($jeLine->credit, 2) : '' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 font-semibold">
                <tr>
                    <td colspan="2" class="text-right">Totals:</td>
                    <td class="text-right">@currency($invoice->journalEntry->lines->sum('debit'))</td>
                    <td class="text-right">@currency($invoice->journalEntry->lines->sum('credit'))</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif
@endsection
