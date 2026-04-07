@extends('layouts.app')
@section('title', 'BIR 2550M - Monthly VAT')

@section('content')
@php
    $taxableMonth = $taxableMonth ?? request('month', now()->format('Y-m'));
    $taxableSales = $taxableSales ?? 0;
    $exemptSales = $exemptSales ?? 0;
    $zeroRatedSales = $zeroRatedSales ?? 0;
    $outputVat = $outputVat ?? ($taxableSales * 0.12);
    $inputVat = $inputVat ?? 0;
    $vatPayable = $vatPayable ?? ($outputVat - $inputVat);
    $revenueBreakdown = $revenueBreakdown ?? collect();
@endphp

<x-page-header title="BIR 2550M" subtitle="Monthly Value-Added Tax Declaration">
    <x-slot name="actions">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm">Excel</a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn-secondary text-sm">PDF</a>
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot>
</x-page-header>

{{-- Filter --}}
<div class="card mb-6">
    <form class="card-body">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">Taxable Month</label>
                <input type="month" name="month" value="{{ $taxableMonth }}" class="form-input w-48">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
            <a href="{{ request()->url() }}" class="btn-secondary">Clear</a>
        </div>
    </form>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card label="Taxable Sales" :value="'₱' . number_format($taxableSales, 2)" color="blue"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z\' /></svg>'" />
    <x-stat-card label="Output VAT (12%)" :value="'₱' . number_format($outputVat, 2)" color="red"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z\' /></svg>'" />
    <x-stat-card label="Input VAT" :value="'₱' . number_format($inputVat, 2)" color="green"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z\' /></svg>'" />
    <x-stat-card label="VAT Payable" :value="'₱' . number_format($vatPayable, 2)" :color="$vatPayable >= 0 ? 'yellow' : 'green'"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0 0 12 2.25Z\' /></svg>'" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Revenue Breakdown --}}
    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-secondary-900">Revenue Breakdown</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Account Name</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($revenueBreakdown as $rev)
                        <tr>
                            <td>{{ $rev->account_name ?? '' }}</td>
                            <td class="text-right font-mono">₱{{ number_format($rev->amount ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-secondary-400 py-4">No revenue data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- BIR Form 2550M Summary --}}
    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-secondary-900">BIR Form 2550M Summary</h3>
        </div>
        <div class="card-body">
            <div class="space-y-3">
                @php
                    $lines = [
                        ['1', 'Taxable Sales', $taxableSales],
                        ['2', 'Exempt Sales', $exemptSales],
                        ['3', 'Zero-Rated Sales', $zeroRatedSales],
                        ['4', 'Output Tax (12%)', $outputVat],
                        ['5', 'Less: Input Tax', $inputVat],
                        ['6', 'VAT Payable', $vatPayable],
                    ];
                @endphp
                @foreach($lines as $line)
                    <div class="flex items-center justify-between py-2 {{ $loop->last ? 'bg-primary-50 px-3 rounded-lg font-bold' : 'border-b border-gray-100' }}">
                        <span class="text-sm {{ $loop->last ? 'text-primary-800' : 'text-secondary-700' }}">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full {{ $loop->last ? 'bg-primary-200 text-primary-800' : 'bg-gray-100 text-secondary-600' }} text-xs font-mono mr-2">{{ $line[0] }}</span>
                            {{ $line[1] }}
                        </span>
                        <span class="font-mono {{ $loop->last ? 'text-primary-800 text-lg' : '' }}">₱{{ number_format($line[2], 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
