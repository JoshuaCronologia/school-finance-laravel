@extends('layouts.app')
@section('title', 'Balance Sheet')

@section('content')
@php
    $asOfDate = $asOfDate ?? now()->format('Y-m-d');
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

{{-- Print Header --}}
<div class="print-only" style="display: none;">
    <div style="text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #333;">
        <h1 style="font-size: 16px; font-weight: bold; margin: 0;">{{ \App\Models\Setting::where('key', 'school_name')->value('value') ?? 'OrangeApps School Finance ERP' }}</h1>
        <h2 style="font-size: 14px; font-weight: bold; margin: 8px 0 4px;">STATEMENT OF FINANCIAL POSITION</h2>
        <p style="font-size: 11px; color: #555;">As of {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}</p>
    </div>
</div>

<div class="card mb-6">
    <div class="card-header bg-gray-50 no-print">
        <div class="text-center w-full">
            <h2 class="text-lg font-bold text-secondary-900">Statement of Financial Position</h2>
            <p class="text-sm text-secondary-500">As of {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}</p>
        </div>
    </div>
    <div class="card-body max-w-3xl mx-auto">
        {{-- ASSETS --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3 cursor-pointer" onclick="document.querySelectorAll('.bs-assets-body').forEach(function(el){el.style.display=el.style.display==='none'?'':'none'}); var a=document.getElementById('bs-arrow-assets');a.style.transform=a.style.transform?'':'rotate(90deg)'">
                <span class="inline-flex items-center gap-1">
                    <svg id="bs-arrow-assets" class="w-4 h-4 text-secondary-400 transition-transform" style="transform:rotate(90deg)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    Assets
                </span>
            </h3>
            <table class="w-full text-sm bs-assets-body">
                <tbody>
                    @foreach($assetGroups as $gi => $group)
                    @if($group->accounts->count() > 1)
                    <tr class="font-semibold cursor-pointer hover:bg-gray-50" onclick="document.querySelectorAll('.bs-asset-{{ $gi }}').forEach(function(el){el.style.display=el.style.display==='none'?'':'none'}); var a=document.getElementById('bs-arrow-a{{ $gi }}');a.style.transform=a.style.transform?'':'rotate(90deg)'">
                        <td class="py-2 pl-2">
                            <span class="inline-flex items-center gap-1">
                                <svg id="bs-arrow-a{{ $gi }}" class="w-3.5 h-3.5 text-secondary-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                {{ $group->label }}
                                <span class="text-xs text-secondary-400 font-normal">({{ $group->accounts->count() }})</span>
                            </span>
                        </td>
                        <td class="py-2 text-right font-mono w-40">₱{{ number_format($group->total, 2) }}</td>
                    </tr>
                    @foreach($group->accounts as $account)
                    <tr class="bs-asset-{{ $gi }} hover:bg-blue-50/50 cursor-pointer" style="display:none" onclick="window.location='{{ route('gl.accounts.show', $account->id) }}'">
                        <td class="py-1 pl-10 text-secondary-600">{{ $account->account_name }}</td>
                        <td class="py-1 text-right font-mono w-40 text-secondary-600">₱{{ number_format(abs($account->balance), 2) }}</td>
                    </tr>
                    @endforeach
                    @else
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('gl.accounts.show', $group->accounts->first()->id) }}'">
                        <td class="py-1.5 pl-6">{{ $group->accounts->first()->account_name }}</td>
                        <td class="py-1.5 text-right font-mono w-40">₱{{ number_format(abs($group->accounts->first()->balance), 2) }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                </tbody>
            </table>
            <table class="w-full text-sm">
                <tr class="border-t-2 border-secondary-900 font-bold">
                    <td class="py-2 pl-2">Total Assets</td>
                    <td class="py-2 text-right font-mono w-40">₱{{ number_format($totalAssets, 2) }}</td>
                </tr>
            </table>
        </div>

        {{-- LIABILITIES --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3 cursor-pointer" onclick="document.querySelectorAll('.bs-liab-body').forEach(function(el){el.style.display=el.style.display==='none'?'':'none'}); var a=document.getElementById('bs-arrow-liab');a.style.transform=a.style.transform?'':'rotate(90deg)'">
                <span class="inline-flex items-center gap-1">
                    <svg id="bs-arrow-liab" class="w-4 h-4 text-secondary-400 transition-transform" style="transform:rotate(90deg)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    Liabilities
                </span>
            </h3>
            <table class="w-full text-sm bs-liab-body">
                <tbody>
                    @foreach($liabilityGroups as $gi => $group)
                    @if($group->accounts->count() > 1)
                    <tr class="font-semibold cursor-pointer hover:bg-gray-50" onclick="document.querySelectorAll('.bs-liab-{{ $gi }}').forEach(function(el){el.style.display=el.style.display==='none'?'':'none'}); var a=document.getElementById('bs-arrow-l{{ $gi }}');a.style.transform=a.style.transform?'':'rotate(90deg)'">
                        <td class="py-2 pl-2">
                            <span class="inline-flex items-center gap-1">
                                <svg id="bs-arrow-l{{ $gi }}" class="w-3.5 h-3.5 text-secondary-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                {{ $group->label }}
                                <span class="text-xs text-secondary-400 font-normal">({{ $group->accounts->count() }})</span>
                            </span>
                        </td>
                        <td class="py-2 text-right font-mono w-40">₱{{ number_format($group->total, 2) }}</td>
                    </tr>
                    @foreach($group->accounts as $account)
                    <tr class="bs-liab-{{ $gi }} hover:bg-blue-50/50 cursor-pointer" style="display:none" onclick="window.location='{{ route('gl.accounts.show', $account->id) }}'">
                        <td class="py-1 pl-10 text-secondary-600">{{ $account->account_name }}</td>
                        <td class="py-1 text-right font-mono w-40 text-secondary-600">₱{{ number_format(abs($account->balance), 2) }}</td>
                    </tr>
                    @endforeach
                    @else
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('gl.accounts.show', $group->accounts->first()->id) }}'">
                        <td class="py-1.5 pl-6">{{ $group->accounts->first()->account_name }}</td>
                        <td class="py-1.5 text-right font-mono w-40">₱{{ number_format(abs($group->accounts->first()->balance), 2) }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                </tbody>
            </table>
            <table class="w-full text-sm">
                <tr class="border-t border-gray-300 font-semibold">
                    <td class="py-2 pl-2">Total Liabilities</td>
                    <td class="py-2 text-right font-mono w-40">₱{{ number_format($totalLiabilities, 2) }}</td>
                </tr>
            </table>
        </div>

        {{-- EQUITY --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3 cursor-pointer" onclick="document.querySelectorAll('.bs-equity-body').forEach(function(el){el.style.display=el.style.display==='none'?'':'none'}); var a=document.getElementById('bs-arrow-equity');a.style.transform=a.style.transform?'':'rotate(90deg)'">
                <span class="inline-flex items-center gap-1">
                    <svg id="bs-arrow-equity" class="w-4 h-4 text-secondary-400 transition-transform" style="transform:rotate(90deg)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    Equity
                </span>
            </h3>
            <table class="w-full text-sm bs-equity-body">
                <tbody>
                    @foreach($equity as $account)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('gl.accounts.show', $account->id) }}'">
                        <td class="py-1 pl-6">{{ $account->account_name }}</td>
                        <td class="py-1 text-right font-mono w-40">₱{{ number_format(abs($account->balance), 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="italic text-secondary-600">
                        <td class="py-1 pl-6">Net Income (Current Period)</td>
                        <td class="py-1 text-right font-mono w-40">₱{{ number_format($netIncome, 2) }}</td>
                    </tr>
                </tbody>
            </table>
            <table class="w-full text-sm">
                <tr class="border-t border-gray-300 font-semibold">
                    <td class="py-2 pl-2">Total Equity</td>
                    <td class="py-2 text-right font-mono w-40">₱{{ number_format($totalEquityWithNI, 2) }}</td>
                </tr>
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

        <p class="text-xs text-secondary-400 text-center mt-3 no-print">Click group headers to expand/collapse. Click account names to view details.</p>
    </div>
</div>

{{-- Print Signature --}}
<div class="print-only" style="display: none;">
    <div style="margin-top: 40px; display: flex; justify-content: space-between; font-size: 10px;">
        <div style="text-align: center; width: 200px;"><div style="border-top: 1px solid #333; padding-top: 5px;">Prepared By</div></div>
        <div style="text-align: center; width: 200px;"><div style="border-top: 1px solid #333; padding-top: 5px;">Reviewed By</div></div>
        <div style="text-align: center; width: 200px;"><div style="border-top: 1px solid #333; padding-top: 5px;">Approved By</div></div>
    </div>
</div>
@endsection
