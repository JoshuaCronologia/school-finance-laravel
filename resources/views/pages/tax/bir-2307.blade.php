@extends('layouts.app')
@section('title', 'BIR Form 2307')

@section('content')
@php
    $vendors = $vendors ?? collect();
    $selectedVendor = $selectedVendor ?? null;
    $quarter = $quarter ?? request('quarter', 'Q1');
    $year = $year ?? request('year', date('Y'));
    $formData = $formData ?? null;
    $summary = $summary ?? collect();
    $schoolTin = $schoolTin ?? '000-000-000-000';
    $schoolName = $schoolName ?? 'ORANGEAPPS EDUCATIONAL INSTITUTION';
    $schoolAddress = $schoolAddress ?? 'Manila, Philippines';
@endphp

<x-page-header title="BIR Form 2307" subtitle="Certificate of Creditable Tax Withheld at Source">
    <x-slot name="actions">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Export Excel
        </a>
        @if($selectedVendor)
        <button onclick="window.print()" class="btn-primary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 12H5.25" /></svg>
            Print 2307
        </button>
        @endif
    </x-slot>
</x-page-header>

{{-- Filters --}}
<div class="card mb-6">
    <form class="card-body">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">Vendor / Payee</label>
                <select name="vendor_id" class="form-input w-64">
                    <option value="">Select Vendor</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Quarter</label>
                <select name="quarter" class="form-input w-28">
                    @foreach(['Q1','Q2','Q3','Q4'] as $q)
                        <option value="{{ $q }}" {{ $quarter == $q ? 'selected' : '' }}>{{ $q }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Year</label>
                <input type="number" name="year" value="{{ $year }}" class="form-input w-28" min="2020" max="2030">
            </div>
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                Generate
            </button>
            <a href="{{ request()->url() }}" class="btn-secondary">Clear</a>
        </div>
    </form>
</div>

{{-- BIR 2307 Form --}}
@if($selectedVendor)
<div class="card mb-6 print:shadow-none" id="bir-2307-form">
    <div class="card-body p-8">
        {{-- Header --}}
        <div class="text-center border-b-2 border-black pb-4 mb-6">
            <p class="text-xs text-secondary-600">Republic of the Philippines</p>
            <p class="text-xs text-secondary-600">Department of Finance</p>
            <p class="text-sm font-bold">BUREAU OF INTERNAL REVENUE</p>
            <p class="text-lg font-bold mt-2">BIR FORM 2307</p>
            <p class="text-sm font-semibold">Certificate of Creditable Tax Withheld at Source</p>
        </div>

        {{-- PART I: Payee Information --}}
        <div class="mb-6">
            <h4 class="text-sm font-bold bg-gray-100 px-3 py-2 mb-3">PART I &mdash; PAYEE INFORMATION</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 px-3">
                <div>
                    <label class="text-xs font-semibold text-secondary-600">TIN</label>
                    <p class="text-sm font-mono border-b border-gray-300 py-1">{{ $selectedVendor->tin ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-secondary-600">Registered Name</label>
                    <p class="text-sm border-b border-gray-300 py-1">{{ $selectedVendor->name }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-secondary-600">Registered Address</label>
                    <p class="text-sm border-b border-gray-300 py-1">{{ $selectedVendor->address ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        {{-- PART II: Payor Information --}}
        <div class="mb-6">
            <h4 class="text-sm font-bold bg-gray-100 px-3 py-2 mb-3">PART II &mdash; PAYOR INFORMATION</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 px-3">
                <div>
                    <label class="text-xs font-semibold text-secondary-600">TIN</label>
                    <p class="text-sm font-mono border-b border-gray-300 py-1">{{ $schoolTin }}</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-secondary-600">Registered Name</label>
                    <p class="text-sm border-b border-gray-300 py-1">{{ $schoolName }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-secondary-600">Registered Address</label>
                    <p class="text-sm border-b border-gray-300 py-1">{{ $schoolAddress }}</p>
                </div>
            </div>
        </div>

        {{-- PART III: Details --}}
        <div class="mb-6">
            <h4 class="text-sm font-bold bg-gray-100 px-3 py-2 mb-3">PART III &mdash; DETAILS OF MONTHLY INCOME PAYMENTS AND TAXES WITHHELD</h4>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300 text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 px-3 py-2 text-left">Month</th>
                            <th class="border border-gray-300 px-3 py-2 text-left">ATC</th>
                            <th class="border border-gray-300 px-3 py-2 text-left">Nature of Income Payment</th>
                            <th class="border border-gray-300 px-3 py-2 text-right">Amount of Income Payment</th>
                            <th class="border border-gray-300 px-3 py-2 text-right">Tax Withheld</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $quarterMonths = [
                                'Q1' => ['January', 'February', 'March'],
                                'Q2' => ['April', 'May', 'June'],
                                'Q3' => ['July', 'August', 'September'],
                                'Q4' => ['October', 'November', 'December'],
                            ];
                            $monthNames = $quarterMonths[$quarter] ?? ['Month 1', 'Month 2', 'Month 3'];
                            $monthlyDetails = $formData->monthly ?? collect([
                                (object)['amount' => 0, 'tax' => 0, 'atc' => '', 'nature' => ''],
                                (object)['amount' => 0, 'tax' => 0, 'atc' => '', 'nature' => ''],
                                (object)['amount' => 0, 'tax' => 0, 'atc' => '', 'nature' => ''],
                            ]);
                            $totalAmount = 0;
                            $totalTax = 0;
                        @endphp
                        @foreach($monthlyDetails as $i => $detail)
                            @php
                                $totalAmount += $detail->amount ?? 0;
                                $totalTax += $detail->tax ?? 0;
                            @endphp
                            <tr>
                                <td class="border border-gray-300 px-3 py-2">{{ $monthNames[$i] ?? '' }}</td>
                                <td class="border border-gray-300 px-3 py-2 font-mono">{{ $detail->atc ?? '' }}</td>
                                <td class="border border-gray-300 px-3 py-2">{{ $detail->nature ?? '' }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-right font-mono">₱{{ number_format($detail->amount ?? 0, 2) }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-right font-mono">₱{{ number_format($detail->tax ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="bg-gray-50 font-semibold">
                            <td colspan="3" class="border border-gray-300 px-3 py-2 text-right">TOTAL</td>
                            <td class="border border-gray-300 px-3 py-2 text-right font-mono">₱{{ number_format($totalAmount, 2) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right font-mono">₱{{ number_format($totalTax, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Signatures --}}
        <div class="grid grid-cols-2 gap-8 mt-8 pt-4">
            <div class="text-center">
                <div class="border-b border-gray-400 mb-1 h-12"></div>
                <p class="text-xs text-secondary-600">Signature of Payor / Authorized Representative</p>
            </div>
            <div class="text-center">
                <div class="border-b border-gray-400 mb-1 h-12"></div>
                <p class="text-xs text-secondary-600">Signature of Payee / Authorized Representative</p>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Vendor Tax Withholding Summary --}}
<div class="card">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Vendor Tax Withholding Summary</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Vendor</th>
                    <th>TIN</th>
                    <th class="text-right">Total Income</th>
                    <th class="text-right">Total Tax Withheld</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($summary as $item)
                    <tr>
                        <td class="font-medium">{{ $item->vendor_name ?? '' }}</td>
                        <td class="font-mono text-sm">{{ $item->tin ?? 'N/A' }}</td>
                        <td class="text-right font-mono">₱{{ number_format($item->total_income ?? 0, 2) }}</td>
                        <td class="text-right font-mono">₱{{ number_format($item->total_tax ?? 0, 2) }}</td>
                        <td>
                            <a href="?vendor_id={{ $item->vendor_id ?? '' }}&quarter={{ $quarter }}&year={{ $year }}" class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                                View 2307
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-8 text-secondary-400">No withholding tax data found for the selected period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
