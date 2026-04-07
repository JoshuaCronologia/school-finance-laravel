@extends('layouts.app')
@section('title', $customer->name)

@section('content')
<x-page-header :title="$customer->name" :subtitle="$customer->customer_code . ' — ' . ucfirst($customer->customer_type ?? 'Customer')">
    <x-slot name="actions">
        <a href="{{ route('ar.customers.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
            Back
        </a>
        <a href="{{ route('ar.soa.detail', $customer) }}" class="btn-secondary">Statement of Account</a>
    </x-slot>
</x-page-header>

{{-- Info + Stats --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="card lg:col-span-2">
        <div class="card-header"><h3 class="card-title">Customer Information</h3></div>
        <div class="card-body">
            <dl class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4">
                <div>
                    <dt class="text-xs text-secondary-500">Code</dt>
                    <dd class="text-sm font-semibold">{{ $customer->customer_code }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Type</dt>
                    <dd class="text-sm font-medium">{{ ucfirst($customer->customer_type ?? '-') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Grade Level</dt>
                    <dd class="text-sm font-medium">{{ $customer->grade_level ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Contact Person</dt>
                    <dd class="text-sm font-medium">{{ $customer->contact_person ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Email</dt>
                    <dd class="text-sm font-medium">{{ $customer->email ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">Phone</dt>
                    <dd class="text-sm font-medium">{{ $customer->phone ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-secondary-500">TIN</dt>
                    <dd class="text-sm font-medium font-mono">{{ $customer->tin ?? '-' }}</dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-xs text-secondary-500">Billing Address</dt>
                    <dd class="text-sm font-medium">{{ $customer->billing_address ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="space-y-4">
        <div class="card p-4">
            <p class="text-xs font-medium text-secondary-500 uppercase">Outstanding Balance</p>
            <p class="text-2xl font-bold {{ $outstandingBalance > 0 ? 'text-danger-600' : 'text-success-600' }} mt-1">@currency($outstandingBalance)</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium text-secondary-500 uppercase">Total Invoiced</p>
            <p class="text-xl font-bold text-secondary-900 mt-1">@currency($totalInvoiced)</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium text-secondary-500 uppercase">Total Paid</p>
            <p class="text-xl font-bold text-success-600 mt-1">@currency($totalPaid)</p>
        </div>
    </div>
</div>

{{-- Invoices --}}
<div class="card">
    <div class="card-header"><h3 class="card-title">Invoices</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">Balance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td class="font-medium">
                        <a href="{{ route('ar.invoices.show', $invoice) }}" class="text-primary-600 hover:underline">{{ $invoice->invoice_number }}</a>
                    </td>
                    <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                    <td class="{{ $invoice->due_date->isPast() && $invoice->balance > 0 ? 'text-danger-600 font-medium' : '' }}">{{ $invoice->due_date->format('M d, Y') }}</td>
                    <td class="max-w-xs truncate">{{ $invoice->description ?? '-' }}</td>
                    <td class="text-right">@currency($invoice->net_receivable)</td>
                    <td class="text-right font-medium {{ $invoice->balance > 0 ? 'text-danger-600' : 'text-success-600' }}">@currency($invoice->balance)</td>
                    <td><x-badge :status="$invoice->status" /></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-secondary-400 py-8">No invoices found for this customer.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
    <div class="card-footer">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
