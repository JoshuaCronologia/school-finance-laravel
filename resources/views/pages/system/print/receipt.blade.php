@extends('layouts.print')

@section('title', 'Official Receipt')
@section('report-title', 'OFFICIAL RECEIPT')

@section('content')
    <div class="mb-8">
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <table class="text-sm">
                    <tr>
                        <td class="font-semibold pr-3 py-1">Receipt No:</td>
                        <td class="text-red-600 font-bold">{{ $receipt->receipt_number ?? '____________' }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold pr-3 py-1">Date:</td>
                        <td>{{ isset($receipt) ? $receipt->receipt_date->format('F d, Y') : '____________' }}</td>
                    </tr>
                </table>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full uppercase">
                    {{ $receipt->status ?? 'ISSUED' }}
                </span>
            </div>
        </div>

        {{-- Received From --}}
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <table class="w-full text-sm">
                <tr>
                    <td class="font-semibold w-36 py-1.5">Received From:</td>
                    <td class="border-b border-gray-400">{{ $receipt->received_from ?? '' }}</td>
                </tr>
                <tr>
                    <td class="font-semibold py-1.5">Address:</td>
                    <td class="border-b border-gray-400">{{ $receipt->address ?? '' }}</td>
                </tr>
                <tr>
                    <td class="font-semibold py-1.5">TIN:</td>
                    <td class="border-b border-gray-400">{{ $receipt->tin ?? '' }}</td>
                </tr>
            </table>
        </div>

        {{-- Amount in Words --}}
        <div class="mb-6">
            <table class="w-full text-sm">
                <tr>
                    <td class="font-semibold w-36 py-1.5">Amount in Words:</td>
                    <td class="border-b border-gray-400 italic">{{ $receipt->amount_in_words ?? '' }}</td>
                </tr>
                <tr>
                    <td class="font-semibold py-1.5">Amount in Figures:</td>
                    <td class="border-b border-gray-400 font-bold text-lg">
                        &#8369;{{ isset($receipt) ? number_format($receipt->amount, 2) : '0.00' }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- Payment Details --}}
        <div class="mb-8">
            <h3 class="text-sm font-bold text-gray-700 mb-2 uppercase">Payment Details</h3>
            <table class="w-full text-sm border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-3 py-2 text-left font-semibold">Description</th>
                        <th class="border border-gray-300 px-3 py-2 text-right font-semibold w-32">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receipt->items ?? [] as $item)
                        <tr>
                            <td class="border border-gray-300 px-3 py-2">{{ $item->description }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right">
                                &#8369;{{ number_format($item->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="border border-gray-300 px-3 py-2">&nbsp;</td>
                            <td class="border border-gray-300 px-3 py-2">&nbsp;</td>
                        </tr>
                    @endforelse
                    <tr class="bg-gray-50 font-bold">
                        <td class="border border-gray-300 px-3 py-2 text-right">TOTAL</td>
                        <td class="border border-gray-300 px-3 py-2 text-right">
                            &#8369;{{ isset($receipt) ? number_format($receipt->amount, 2) : '0.00' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Payment Method --}}
        <div class="mb-8 text-sm">
            <span class="font-semibold">Payment Method:</span>
            <span class="ml-2">{{ ucfirst($receipt->payment_method ?? 'Cash') }}</span>
            @if (($receipt->payment_method ?? '') === 'check')
                <span class="ml-4 text-gray-500">
                    Check No: {{ $receipt->check_number ?? '' }} |
                    Bank: {{ $receipt->bank_name ?? '' }} |
                    Date: {{ isset($receipt->check_date) ? $receipt->check_date->format('m/d/Y') : '' }}
                </span>
            @endif
        </div>

        {{-- Signatures --}}
        <div class="grid grid-cols-2 gap-16 mt-16">
            <div class="text-center">
                <div class="border-b border-gray-800 mb-1 pb-8"></div>
                <p class="text-sm font-semibold">Received By</p>
                <p class="text-xs text-gray-500">Signature over Printed Name</p>
            </div>
            <div class="text-center">
                <div class="border-b border-gray-800 mb-1 pb-8"></div>
                <p class="text-sm font-semibold">Approved By</p>
                <p class="text-xs text-gray-500">Signature over Printed Name</p>
            </div>
        </div>
    </div>
@endsection
