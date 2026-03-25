@extends('layouts.app')
@section('title', 'Bill #' . $bill->bill_number)

@section('content')
<x-page-header :title="'Bill #' . $bill->bill_number" :subtitle="$bill->vendor->name ?? 'Supplier Bill'">
    <x-slot:actions>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('ap.bills.index') }}" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                Back to Bills
            </a>

            @if($bill->status === 'draft')
                <a href="{{ route('ap.bills.edit', $bill) }}" class="btn-secondary inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                    Edit
                </a>
                <form action="{{ route('ap.bills.approve', $bill) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn-primary inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        Approve
                    </button>
                </form>
            @endif

            @if($bill->status === 'approved')
                <form action="{{ route('ap.bills.post', $bill) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn-primary inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        Post to GL
                    </button>
                </form>
            @endif
        </div>
    </x-slot:actions>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Bill Details --}}
    <div class="card lg:col-span-2">
        <div class="card-header">
            <div class="flex items-center justify-between w-full">
                <h3 class="card-title">Bill Information</h3>
                <x-badge :status="$bill->status" />
            </div>
        </div>
        <div class="card-body">
            <dl class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-4">
                <div>
                    <dt class="text-xs text-secondary-500">Bill Number</dt>
                    <dd class="text-sm font-semibold text-secondary-900">{{ $bill->bill_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Bill Date</dt>
                    <dd class="text-sm font-medium">{{ $bill->bill_date->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Due Date</dt>
                    <dd class="text-sm font-medium {{ $bill->due_date->isPast() && $bill->balance > 0 ? 'text-danger-600' : '' }}">{{ $bill->due_date->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Reference #</dt>
                    <dd class="text-sm font-medium">{{ $bill->reference_number ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Vendor</dt>
                    <dd class="text-sm font-semibold text-secondary-900">{{ $bill->vendor->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Department</dt>
                    <dd class="text-sm font-medium">{{ $bill->department->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Campus</dt>
                    <dd class="text-sm font-medium">{{ $bill->campus->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Category</dt>
                    <dd class="text-sm font-medium">{{ $bill->category->name ?? '-' }}</dd>
                </div>
            </dl>
            @if($bill->description)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <dt class="text-xs text-secondary-500">Description</dt>
                <dd class="text-sm text-secondary-900 mt-1">{{ $bill->description }}</dd>
            </div>
            @endif
        </div>
    </div>

    {{-- Amount Summary --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title">Amount Summary</h3></div>
        <div class="card-body space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-secondary-500">Gross Amount</span>
                <span class="font-medium">{{ '₱' . number_format($bill->gross_amount, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-secondary-500">VAT Amount</span>
                <span class="font-medium">{{ '₱' . number_format($bill->vat_amount, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-secondary-500">Withholding Tax</span>
                <span class="font-medium text-danger-600">{{ $bill->withholding_tax > 0 ? '(₱' . number_format($bill->withholding_tax, 2) . ')' : '-' }}</span>
            </div>
            <div class="flex justify-between text-sm font-semibold border-t border-gray-200 pt-2">
                <span class="text-secondary-700">Net Payable</span>
                <span class="text-primary-700">{{ '₱' . number_format($bill->net_payable, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-secondary-500">Amount Paid</span>
                <span class="font-medium text-success-600">{{ '₱' . number_format($bill->amount_paid ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm font-semibold border-t border-gray-200 pt-2">
                <span class="text-secondary-700">Balance</span>
                <span class="{{ $bill->balance > 0 ? 'text-danger-600' : 'text-success-600' }}">{{ '₱' . number_format($bill->balance, 2) }}</span>
            </div>
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
                    <th class="w-10">#</th>
                    <th>Account</th>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Cost</th>
                    <th class="text-right">Amount</th>
                    <th>Tax</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bill->lines as $i => $line)
                <tr>
                    <td class="text-secondary-400">{{ $i + 1 }}</td>
                    <td class="font-medium">{{ $line->account->account_code ?? '-' }} - {{ $line->account->account_name ?? '' }}</td>
                    <td>{{ $line->description ?? '-' }}</td>
                    <td class="text-right">{{ number_format($line->quantity, 0) }}</td>
                    <td class="text-right">{{ '₱' . number_format($line->unit_cost, 2) }}</td>
                    <td class="text-right font-medium">{{ '₱' . number_format($line->amount, 2) }}</td>
                    <td>{{ $line->taxCode->code ?? $line->taxCode->name ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-50 font-semibold">
                    <td colspan="5" class="text-right">Total</td>
                    <td class="text-right">{{ '₱' . number_format($bill->gross_amount, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Journal Entry (if posted) --}}
@if($bill->journalEntry)
<div class="card mb-6">
    <div class="card-header">
        <h3 class="card-title">GL Journal Entry</h3>
        <a href="{{ route('gl.journal-entries.show', $bill->journalEntry) }}" class="text-sm text-primary-600 hover:underline">{{ $bill->journalEntry->entry_number }}</a>
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
                @foreach($bill->journalEntry->lines as $jeLine)
                <tr>
                    <td class="font-medium">{{ $jeLine->account->account_code ?? '-' }}</td>
                    <td>{{ $jeLine->account->account_name ?? '-' }}</td>
                    <td class="text-right">{{ $jeLine->debit > 0 ? '₱' . number_format($jeLine->debit, 2) : '-' }}</td>
                    <td class="text-right">{{ $jeLine->credit > 0 ? '₱' . number_format($jeLine->credit, 2) : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Status Info --}}
@if($bill->status === 'posted')
<div class="card">
    <div class="card-body text-sm text-secondary-500">
        <p>This bill is posted and cannot be edited.</p>
    </div>
</div>
@endif
@endsection
