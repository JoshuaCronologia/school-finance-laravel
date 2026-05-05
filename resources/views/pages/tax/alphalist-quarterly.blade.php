@extends('layouts.app')
@section('title', 'Alphalist - Quarterly')

@section('content')
@php
    $monthNames = $monthNames ?? ['Month 1', 'Month 2', 'Month 3'];
@endphp

<x-page-header title="Alphalist of Payees (Quarterly)" subtitle="Quarterly Alphalist of Payees Subject to Withholding Tax">
    <x-slot name="actions">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Export Excel
        </a>
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot>
</x-page-header>

<div class="card mb-6">
    <form class="card-body">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">Quarter</label>
                <select name="quarter" class="form-input w-36">
                    @for($q = 1; $q <= 4; $q++)
                        <option value="{{ $q }}" {{ $quarter == $q ? 'selected' : '' }}>Q{{ $q }} ({{ ['Jan-Mar','Apr-Jun','Jul-Sep','Oct-Dec'][$q-1] }})</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="form-label">Year</label>
                <input type="number" name="year" class="form-input w-28" value="{{ $year }}">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header bg-gray-50 text-center">
        <div class="w-full">
            <h2 class="text-sm font-bold">QUARTERLY ALPHALIST OF PAYEES (QAP)</h2>
            <p class="text-xs text-secondary-500">Q{{ $quarter }} {{ $year }} — {{ ['January to March','April to June','July to September','October to December'][$quarter-1] }}</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-xs">
            <thead>
                <tr class="bg-gray-100">
                    <th rowspan="2" class="border border-gray-200 px-2 py-2 text-left w-10">#</th>
                    <th rowspan="2" class="border border-gray-200 px-2 py-2 text-left">TIN</th>
                    <th rowspan="2" class="border border-gray-200 px-2 py-2 text-left">Payee Name</th>
                    <th rowspan="2" class="border border-gray-200 px-2 py-2 text-center w-20">ATC</th>
                    <th colspan="3" class="border border-gray-200 px-2 py-1 text-center bg-blue-50">Month</th>
                    <th rowspan="2" class="border border-gray-200 px-2 py-2 text-right">Total Income</th>
                    <th rowspan="2" class="border border-gray-200 px-2 py-2 text-right">Tax Withheld</th>
                </tr>
                <tr class="bg-blue-50 text-center">
                    <th class="border border-gray-200 px-2 py-1">{{ $monthNames[0] }}</th>
                    <th class="border border-gray-200 px-2 py-1">{{ $monthNames[1] }}</th>
                    <th class="border border-gray-200 px-2 py-1">{{ $monthNames[2] }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alphalist as $i => $row)
                <tr class="hover:bg-gray-50">
                    <td class="border border-gray-200 px-2 py-1 text-center">{{ $i + 1 }}</td>
                    <td class="border border-gray-200 px-2 py-1 font-mono">{{ $row->tin ?: '-' }}</td>
                    <td class="border border-gray-200 px-2 py-1 font-medium">{{ $row->payee_name }}</td>
                    <td class="border border-gray-200 px-2 py-1 text-center font-mono font-semibold">{{ $row->atc ?: '-' }}</td>
                    <td class="border border-gray-200 px-2 py-1 text-right font-mono">{{ $row->m1_income > 0 ? number_format($row->m1_income, 2) : '' }}</td>
                    <td class="border border-gray-200 px-2 py-1 text-right font-mono">{{ $row->m2_income > 0 ? number_format($row->m2_income, 2) : '' }}</td>
                    <td class="border border-gray-200 px-2 py-1 text-right font-mono">{{ $row->m3_income > 0 ? number_format($row->m3_income, 2) : '' }}</td>
                    <td class="border border-gray-200 px-2 py-1 text-right font-mono font-semibold">{{ number_format($row->income_payment, 2) }}</td>
                    <td class="border border-gray-200 px-2 py-1 text-right font-mono font-semibold text-danger-600">{{ number_format($row->tax_withheld, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="9" class="border border-gray-200 text-center text-secondary-400 py-6">No withholding tax transactions for this quarter.</td></tr>
                @endforelse
            </tbody>
            @if($alphalist->count() > 0)
            <tfoot class="bg-gray-100 font-bold">
                <tr>
                    <td colspan="4" class="border border-gray-200 px-2 py-2 text-right">Grand Total:</td>
                    <td class="border border-gray-200 px-2 py-2 text-right font-mono">{{ number_format($alphalist->sum('m1_income'), 2) }}</td>
                    <td class="border border-gray-200 px-2 py-2 text-right font-mono">{{ number_format($alphalist->sum('m2_income'), 2) }}</td>
                    <td class="border border-gray-200 px-2 py-2 text-right font-mono">{{ number_format($alphalist->sum('m3_income'), 2) }}</td>
                    <td class="border border-gray-200 px-2 py-2 text-right font-mono">{{ number_format($alphalist->sum('income_payment'), 2) }}</td>
                    <td class="border border-gray-200 px-2 py-2 text-right font-mono text-danger-600">{{ number_format($alphalist->sum('tax_withheld'), 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
