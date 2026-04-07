@extends('layouts.app')
@section('title', 'BIR 1601-EQ')

@section('content')
<x-page-header title="BIR 1601-EQ" subtitle="Quarterly Remittance Return of Creditable Income Taxes Withheld (Expanded)">
    <x-slot name="actions">
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

<div class="card mb-6">
    <div class="card-header bg-gray-50 text-center">
        <div class="w-full">
            <h2 class="text-sm font-bold">BIR FORM 1601-EQ</h2>
            <p class="text-xs text-secondary-500">Quarterly Remittance Return of Creditable Income Taxes Withheld (Expanded)</p>
            <p class="text-xs text-secondary-500">For Q{{ $quarter }} {{ $year }} ({{ ['January-March','April-June','July-September','October-December'][$quarter-1] }})</p>
        </div>
    </div>
    <div class="card-body max-w-2xl mx-auto">
        <table class="w-full text-sm">
            <tr class="border-b"><td class="py-3 font-medium">1. Total Taxes Withheld for the Quarter</td><td class="py-3 text-right font-mono font-bold w-48">₱{{ number_format($totalTaxWithheld, 2) }}</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">2. Less: Monthly Remittances (0619-E) for the Quarter</td><td class="py-3 text-right font-mono w-48">₱{{ number_format($totalTaxWithheld, 2) }}</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">3. Tax Still Due (1 less 2)</td><td class="py-3 text-right font-mono font-bold w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">4. Less: Tax Credits</td><td class="py-3 text-right font-mono w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">5. Net Tax Due</td><td class="py-3 text-right font-mono font-bold w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">6. Add: Penalties</td><td class="py-3 text-right font-mono w-48">₱0.00</td></tr>
            <tr class="bg-gray-50 font-bold"><td class="py-3">7. Total Amount Due</td><td class="py-3 text-right font-mono w-48 text-primary-700">₱0.00</td></tr>
        </table>
    </div>
</div>

{{-- Quarterly Alphalist of Payees (QAP) --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quarterly Alphalist of Payees (QAP)</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Payee / Vendor</th>
                    <th>TIN</th>
                    <th>ATC</th>
                    <th class="text-right">Income Payment</th>
                    <th class="text-right">Tax Withheld</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $grouped = $payments->groupBy(function ($p) { return $p->disbursement->payee_name ?? 'Unknown'; });
                @endphp
                @forelse($grouped as $payee => $group)
                <tr>
                    <td class="font-medium">{{ $payee }}</td>
                    <td class="font-mono text-sm">{{ $group->first()->disbursement->vendor->tin ?? '-' }}</td>
                    <td class="font-mono text-sm">{{ $group->first()->disbursement->vendor->withholding_tax_type ?? '-' }}</td>
                    <td class="text-right font-mono">₱{{ number_format($group->sum('gross_amount'), 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($group->sum('withholding_tax'), 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-secondary-400 py-4">No withholding tax transactions for this quarter.</td></tr>
                @endforelse
            </tbody>
            @if($payments->count() > 0)
            <tfoot class="bg-gray-50 font-bold">
                <tr>
                    <td colspan="3" class="text-right">Totals:</td>
                    <td class="text-right font-mono">₱{{ number_format($totalTaxBase, 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($totalTaxWithheld, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
