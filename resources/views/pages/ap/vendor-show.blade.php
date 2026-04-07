@extends('layouts.app')
@section('title', $vendor->name)

@section('content')
<x-page-header :title="$vendor->name" :subtitle="$vendor->vendor_code . ' — ' . ucfirst(str_replace('_', ' ', $vendor->vendor_type ?? 'Vendor'))">
    <x-slot name="actions">
        <a href="{{ route('vendors.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
            Back
        </a>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Vendor Info --}}
    <div class="card lg:col-span-2">
        <div class="card-header"><h3 class="card-title">Vendor Information</h3></div>
        <div class="card-body">
            <dl class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4">
                <div><dt class="text-xs text-secondary-500">Code</dt><dd class="text-sm font-semibold">{{ $vendor->vendor_code }}</dd></div>
                <div><dt class="text-xs text-secondary-500">Type</dt><dd class="text-sm font-medium">{{ ucfirst(str_replace('_', ' ', $vendor->vendor_type ?? '-')) }}</dd></div>
                <div><dt class="text-xs text-secondary-500">Contact Person</dt><dd class="text-sm font-medium">{{ $vendor->contact_person ?? '-' }}</dd></div>
                <div><dt class="text-xs text-secondary-500">Email</dt><dd class="text-sm font-medium">{{ $vendor->email ?? '-' }}</dd></div>
                <div><dt class="text-xs text-secondary-500">Phone</dt><dd class="text-sm font-medium">{{ $vendor->phone ?? '-' }}</dd></div>
                <div><dt class="text-xs text-secondary-500">TIN</dt><dd class="text-sm font-medium font-mono">{{ $vendor->tin ?? '-' }}</dd></div>
                <div><dt class="text-xs text-secondary-500">VAT Type</dt><dd class="text-sm font-medium">{{ ucfirst(str_replace('-', ' ', $vendor->vat_type ?? '-')) }}</dd></div>
                <div><dt class="text-xs text-secondary-500">WHT Type</dt><dd class="text-sm font-medium">{{ $vendor->withholding_tax_type ?? '-' }}</dd></div>
                <div><dt class="text-xs text-secondary-500">Status</dt><dd class="text-sm"><x-badge :status="$vendor->is_active ? 'active' : 'inactive'" /></dd></div>
                <div class="col-span-2 md:col-span-3"><dt class="text-xs text-secondary-500">Address</dt><dd class="text-sm font-medium">{{ $vendor->address ?? '-' }}</dd></div>
                @if($vendor->bank_name)
                <div><dt class="text-xs text-secondary-500">Bank</dt><dd class="text-sm font-medium">{{ $vendor->bank_name }}</dd></div>
                <div><dt class="text-xs text-secondary-500">Account Name</dt><dd class="text-sm font-medium">{{ $vendor->account_name ?? '-' }}</dd></div>
                <div><dt class="text-xs text-secondary-500">Account #</dt><dd class="text-sm font-medium font-mono">{{ $vendor->account_number ?? '-' }}</dd></div>
                @endif
            </dl>
        </div>
    </div>

    {{-- Stats --}}
    <div class="space-y-4">
        <div class="card p-4">
            <p class="text-xs font-medium text-secondary-500 uppercase">Outstanding Balance</p>
            <p class="text-2xl font-bold {{ $outstandingBalance > 0 ? 'text-danger-600' : 'text-success-600' }} mt-1">@currency($outstandingBalance)</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium text-secondary-500 uppercase">Total Purchases</p>
            <p class="text-xl font-bold text-secondary-900 mt-1">@currency($totalPurchases)</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-medium text-secondary-500 uppercase">Total Payments</p>
            <p class="text-xl font-bold text-success-600 mt-1">@currency($totalPayments)</p>
        </div>
    </div>
</div>

{{-- Bills --}}
<div class="card mb-6">
    <div class="card-header"><h3 class="card-title">Bills</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Bill #</th><th>Date</th><th>Due Date</th><th>Description</th><th class="text-right">Amount</th><th class="text-right">Balance</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($bills as $bill)
                <tr>
                    <td class="font-medium"><a href="{{ route('ap.bills.show', $bill) }}" class="text-primary-600 hover:underline">{{ $bill->bill_number }}</a></td>
                    <td>{{ $bill->bill_date->format('M d, Y') }}</td>
                    <td>{{ $bill->due_date->format('M d, Y') }}</td>
                    <td class="max-w-xs truncate">{{ $bill->description ?? '-' }}</td>
                    <td class="text-right">@currency($bill->gross_amount)</td>
                    <td class="text-right font-medium {{ $bill->balance > 0 ? 'text-danger-600' : '' }}">@currency($bill->balance)</td>
                    <td><x-badge :status="$bill->status" /></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-secondary-400 py-6">No bills found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($bills->hasPages())<div class="card-footer">{{ $bills->links() }}</div>@endif
</div>

{{-- Payments --}}
<div class="card">
    <div class="card-header"><h3 class="card-title">Payments</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Reference</th><th>Method</th><th class="text-right">Amount</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</td>
                    <td class="font-medium">{{ $payment->reference_number ?? $payment->check_number ?? '-' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? '-')) }}</td>
                    <td class="text-right font-medium">@currency($payment->net_amount)</td>
                    <td><x-badge :status="$payment->status" /></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-secondary-400 py-6">No payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())<div class="card-footer">{{ $payments->links() }}</div>@endif
</div>
@endsection
