@extends('layouts.app')
@section('title', 'Cash Flow Statement')

@section('content')
@php
    $dateFrom = $dateFrom ?? now()->startOfYear()->format('Y-m-d');
    $dateTo = $dateTo ?? now()->format('Y-m-d');
@endphp

<x-page-header title="Statement of Cash Flows" :subtitle="'For the period ' . \Carbon\Carbon::parse($dateFrom)->format('M d, Y') . ' to ' . \Carbon\Carbon::parse($dateTo)->format('M d, Y')">
    <x-slot name="actions">
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot>
</x-page-header>

<div class="card mb-6">
    <form class="card-body">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-input w-44">
            </div>
            <div>
                <label class="form-label">To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-input w-44">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
        </div>
    </form>
</div>

{{-- Pro Forma Cash Flow Statement --}}
<div class="card mb-6">
    <div class="card-header bg-gray-50">
        <div class="text-center w-full">
            <h2 class="text-lg font-bold text-secondary-900">Statement of Cash Flows</h2>
            <p class="text-sm text-secondary-500">For the period {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}</p>
        </div>
    </div>
    <div class="card-body max-w-3xl mx-auto">

        {{-- OPERATING ACTIVITIES --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3">Cash Flows from Operating Activities</h3>
            <table class="w-full text-sm">
                <tbody>
                    <tr>
                        <td class="py-1.5 pl-6">Net Income</td>
                        <td class="py-1.5 text-right font-mono w-40">₱{{ number_format($netIncome ?? 0, 2) }}</td>
                    </tr>
                    <tr class="text-secondary-500 italic">
                        <td class="py-1 pl-6 text-xs" colspan="2">Adjustments for non-cash items:</td>
                    </tr>
                    <tr>
                        <td class="py-1.5 pl-10">Cash received from customers</td>
                        <td class="py-1.5 text-right font-mono w-40">₱{{ number_format($cashFromCustomers ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="py-1.5 pl-10">Cash paid to suppliers</td>
                        <td class="py-1.5 text-right font-mono w-40 text-danger-600">(₱{{ number_format(abs($cashToSuppliers ?? 0), 2) }})</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-300 font-semibold">
                        <td class="py-2 pl-2">Net Cash from Operating Activities</td>
                        <td class="py-2 text-right font-mono {{ ($operatingCashFlow ?? 0) >= 0 ? '' : 'text-danger-600' }}">₱{{ number_format($operatingCashFlow ?? 0, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- INVESTING ACTIVITIES --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3">Cash Flows from Investing Activities</h3>
            <table class="w-full text-sm">
                <tbody>
                    <tr class="text-secondary-400 italic">
                        <td class="py-1.5 pl-6" colspan="2">No investing activities recorded for this period.</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-300 font-semibold">
                        <td class="py-2 pl-2">Net Cash from Investing Activities</td>
                        <td class="py-2 text-right font-mono w-40">₱0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- FINANCING ACTIVITIES --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3">Cash Flows from Financing Activities</h3>
            <table class="w-full text-sm">
                <tbody>
                    <tr class="text-secondary-400 italic">
                        <td class="py-1.5 pl-6" colspan="2">No financing activities recorded for this period.</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-300 font-semibold">
                        <td class="py-2 pl-2">Net Cash from Financing Activities</td>
                        <td class="py-2 text-right font-mono w-40">₱0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- NET CHANGE --}}
        <div class="border-t-2 border-secondary-900 pt-3 mb-4">
            <table class="w-full text-sm">
                <tr class="font-semibold">
                    <td class="py-1.5">Net Increase (Decrease) in Cash</td>
                    <td class="py-1.5 text-right font-mono w-40 {{ ($netCashChange ?? 0) >= 0 ? '' : 'text-danger-600' }}">₱{{ number_format($netCashChange ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-1.5">Cash at Beginning of Period</td>
                    <td class="py-1.5 text-right font-mono w-40">₱{{ number_format($beginningCash ?? 0, 2) }}</td>
                </tr>
            </table>
        </div>
        <div class="border-t-2 border-double border-secondary-900 pt-2">
            <table class="w-full">
                <tr class="font-bold text-base">
                    <td class="py-2">Cash at End of Period</td>
                    <td class="py-2 text-right font-mono w-40">₱{{ number_format($endingCash ?? 0, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection
