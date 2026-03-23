@extends('layouts.print')
@section('title', 'Payment Voucher - ' . ($voucher->voucher_number ?? 'PV'))

@section('report-title', 'PAYMENT VOUCHER')

@section('content')
{{-- Voucher Details --}}
<div class="grid grid-cols-2 gap-6 mb-6">
    <div class="space-y-2">
        <div class="flex">
            <span class="text-sm font-semibold text-secondary-700 w-28">Voucher No:</span>
            <span class="text-sm text-secondary-900 font-medium">{{ $voucher->voucher_number ?? '-' }}</span>
        </div>
        <div class="flex">
            <span class="text-sm font-semibold text-secondary-700 w-28">Date:</span>
            <span class="text-sm text-secondary-900">{{ isset($voucher->voucher_date) ? \Carbon\Carbon::parse($voucher->voucher_date)->format('F d, Y') : '-' }}</span>
        </div>
    </div>
    <div class="space-y-2">
        <div class="flex">
            <span class="text-sm font-semibold text-secondary-700 w-28">Payee:</span>
            <span class="text-sm text-secondary-900 font-medium">{{ $voucher->payee_name ?? $voucher->vendor->name ?? '-' }}</span>
        </div>
        <div class="flex">
            <span class="text-sm font-semibold text-secondary-700 w-28">Address:</span>
            <span class="text-sm text-secondary-900">{{ $voucher->payee_address ?? $voucher->vendor->address ?? '-' }}</span>
        </div>
        <div class="flex">
            <span class="text-sm font-semibold text-secondary-700 w-28">TIN:</span>
            <span class="text-sm text-secondary-900">{{ $voucher->payee_tin ?? $voucher->vendor->tin ?? '-' }}</span>
        </div>
    </div>
</div>

{{-- Line Items Table --}}
<table class="w-full border-collapse border border-gray-400 mb-6">
    <thead>
        <tr class="bg-gray-100">
            <th class="border border-gray-400 px-4 py-2 text-left text-sm font-semibold text-secondary-800">Description</th>
            <th class="border border-gray-400 px-4 py-2 text-right text-sm font-semibold text-secondary-800 w-40">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($voucher->lines ?? $voucher->items ?? [] as $line)
        <tr>
            <td class="border border-gray-400 px-4 py-2 text-sm text-secondary-700">{{ $line->description ?? '-' }}</td>
            <td class="border border-gray-400 px-4 py-2 text-sm text-right text-secondary-900">{{ '₱' . number_format($line->amount ?? 0, 2) }}</td>
        </tr>
        @endforeach
        {{-- Fill empty rows for print layout --}}
        @for($i = count($voucher->lines ?? $voucher->items ?? []); $i < 5; $i++)
        <tr>
            <td class="border border-gray-400 px-4 py-2 text-sm">&nbsp;</td>
            <td class="border border-gray-400 px-4 py-2 text-sm">&nbsp;</td>
        </tr>
        @endfor
    </tbody>
</table>

{{-- Summary --}}
<div class="flex justify-end mb-8">
    <div class="w-72">
        <div class="flex justify-between py-2 border-b border-gray-300">
            <span class="text-sm font-semibold text-secondary-700">Gross Amount:</span>
            <span class="text-sm text-secondary-900">{{ '₱' . number_format($voucher->gross_amount ?? 0, 2) }}</span>
        </div>
        <div class="flex justify-between py-2 border-b border-gray-300">
            <span class="text-sm font-semibold text-secondary-700">Less: Withholding Tax:</span>
            <span class="text-sm text-danger-600">({{ '₱' . number_format($voucher->wht_amount ?? 0, 2) }})</span>
        </div>
        <div class="flex justify-between py-2 border-b-2 border-gray-800">
            <span class="text-sm font-bold text-secondary-900">Net Amount:</span>
            <span class="text-sm font-bold text-secondary-900">{{ '₱' . number_format($voucher->net_amount ?? 0, 2) }}</span>
        </div>
    </div>
</div>

{{-- Payment Method --}}
@if($voucher->payment_method ?? null)
<div class="mb-8 text-sm">
    <span class="font-semibold text-secondary-700">Payment Method:</span>
    <span class="text-secondary-900">{{ ucfirst(str_replace('_', ' ', $voucher->payment_method)) }}</span>
    @if($voucher->check_number ?? null)
        &bull; <span class="font-semibold text-secondary-700">Check No:</span>
        <span class="text-secondary-900">{{ $voucher->check_number }}</span>
    @endif
    @if($voucher->bank_name ?? null)
        &bull; <span class="font-semibold text-secondary-700">Bank:</span>
        <span class="text-secondary-900">{{ $voucher->bank_name }}</span>
    @endif
</div>
@endif

{{-- Signature Lines --}}
<div class="grid grid-cols-3 gap-8 mt-16">
    <div class="text-center">
        <div class="border-b border-gray-800 mb-2 pt-10"></div>
        <p class="text-sm font-semibold text-secondary-800">Prepared By</p>
        <p class="text-xs text-secondary-500 mt-1">{{ $voucher->prepared_by ?? '' }}</p>
    </div>
    <div class="text-center">
        <div class="border-b border-gray-800 mb-2 pt-10"></div>
        <p class="text-sm font-semibold text-secondary-800">Checked By</p>
        <p class="text-xs text-secondary-500 mt-1">{{ $voucher->checked_by ?? '' }}</p>
    </div>
    <div class="text-center">
        <div class="border-b border-gray-800 mb-2 pt-10"></div>
        <p class="text-sm font-semibold text-secondary-800">Approved By</p>
        <p class="text-xs text-secondary-500 mt-1">{{ $voucher->approved_by ?? '' }}</p>
    </div>
</div>

{{-- Received By --}}
<div class="mt-12 pt-6 border-t border-gray-300">
    <div class="grid grid-cols-2 gap-8">
        <div>
            <p class="text-sm text-secondary-600 mb-8">Received the amount stated above:</p>
            <div class="border-b border-gray-800 mb-2 w-64"></div>
            <p class="text-sm font-semibold text-secondary-800">Received By (Signature over Printed Name)</p>
        </div>
        <div>
            <p class="text-sm text-secondary-600 mb-8">Date received:</p>
            <div class="border-b border-gray-800 mb-2 w-48"></div>
            <p class="text-sm font-semibold text-secondary-800">Date</p>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        table { page-break-inside: avoid; }
        .grid { page-break-inside: avoid; }
    }
</style>
@endpush
@endsection
