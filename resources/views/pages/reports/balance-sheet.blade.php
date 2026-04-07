@extends('layouts.app')
@section('title', 'Balance Sheet')

@section('content')
@php
    $asOfDate = $asOfDate ?? now()->format('Y-m-d');
    $assets = $assets ?? collect();
    $liabilities = $liabilities ?? collect();
    $equity = $equity ?? collect();
    $totalAssets = $totalAssets ?? 0;
    $totalLiabilities = $totalLiabilities ?? 0;
    $totalEquity = $totalEquity ?? 0;
    $netIncome = $netIncome ?? 0;
    $totalEquityWithNI = $totalEquityWithNI ?? ($totalEquity + $netIncome);
    $isBalanced = abs($totalAssets - ($totalLiabilities + $totalEquityWithNI)) < 0.01;
@endphp

<x-page-header title="Balance Sheet" :subtitle="'As of ' . \Carbon\Carbon::parse($asOfDate)->format('F d, Y')">
    <x-slot name="actions">
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot>
</x-page-header>

<div class="card mb-6">
    <form class="card-body">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">As of Date</label>
                <input type="date" name="as_of_date" value="{{ $asOfDate }}" class="form-input w-48">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
        </div>
    </form>
</div>

{{-- Pro Forma Balance Sheet --}}
<div class="card mb-6">
    <div class="card-header bg-gray-50">
        <div class="text-center w-full">
            <h2 class="text-lg font-bold text-secondary-900">Statement of Financial Position</h2>
            <p class="text-sm text-secondary-500">As of {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}</p>
        </div>
    </div>
    <div class="card-body max-w-3xl mx-auto">
        {{-- ASSETS --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3">Assets</h3>
            <table class="w-full text-sm">
                <tbody>
                    @foreach($assets as $account)
                    <tr>
                        <td class="py-1 pl-6">{{ $account->account_name }}</td>
                        <td class="py-1 text-right font-mono w-40">₱{{ number_format(abs($account->balance), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-secondary-900 font-bold">
                        <td class="py-2 pl-2">Total Assets</td>
                        <td class="py-2 text-right font-mono">₱{{ number_format($totalAssets, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- LIABILITIES --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3">Liabilities</h3>
            <table class="w-full text-sm">
                <tbody>
                    @foreach($liabilities as $account)
                    <tr>
                        <td class="py-1 pl-6">{{ $account->account_name }}</td>
                        <td class="py-1 text-right font-mono w-40">₱{{ number_format(abs($account->balance), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-300 font-semibold">
                        <td class="py-2 pl-2">Total Liabilities</td>
                        <td class="py-2 text-right font-mono">₱{{ number_format($totalLiabilities, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- EQUITY --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3">Equity</h3>
            <table class="w-full text-sm">
                <tbody>
                    @foreach($equity as $account)
                    <tr>
                        <td class="py-1 pl-6">{{ $account->account_name }}</td>
                        <td class="py-1 text-right font-mono w-40">₱{{ number_format(abs($account->balance), 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="italic text-secondary-600">
                        <td class="py-1 pl-6">Net Income (Current Period)</td>
                        <td class="py-1 text-right font-mono w-40">₱{{ number_format($netIncome, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-300 font-semibold">
                        <td class="py-2 pl-2">Total Equity</td>
                        <td class="py-2 text-right font-mono">₱{{ number_format($totalEquityWithNI, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- TOTAL L&E --}}
        <div class="border-t-2 border-double border-secondary-900 pt-2 mb-4">
            <table class="w-full">
                <tr class="font-bold text-base">
                    <td class="py-2">Total Liabilities & Equity</td>
                    <td class="py-2 text-right font-mono w-40">₱{{ number_format($totalLiabilities + $totalEquityWithNI, 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="p-3 rounded-lg text-center text-sm font-semibold {{ $isBalanced ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
            {{ $isBalanced ? 'BALANCED' : 'UNBALANCED - Difference: ₱' . number_format(abs($totalAssets - ($totalLiabilities + $totalEquityWithNI)), 2) }}
        </div>
    </div>
</div>
@endsection
