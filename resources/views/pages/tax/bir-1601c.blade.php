@extends('layouts.app')
@section('title', 'BIR 1601-C')

@section('content')
<x-page-header title="BIR 1601-C" subtitle="Monthly Remittance Return of Income Taxes Withheld on Compensation" />

<div class="card mb-6">
    <form class="card-body">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">Month</label>
                <select name="month" class="form-input w-36">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
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
            <h2 class="text-sm font-bold">BIR FORM 1601-C</h2>
            <p class="text-xs text-secondary-500">Monthly Remittance Return of Income Taxes Withheld on Compensation</p>
            <p class="text-xs text-secondary-500">For the month of {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}</p>
        </div>
    </div>
    <div class="card-body max-w-2xl mx-auto">
        <table class="w-full text-sm">
            <tr class="border-b"><td class="py-3 font-medium">1. Taxes Withheld on Compensation</td><td class="py-3 text-right font-mono font-bold w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">2. Adjustment from Previous Month(s)</td><td class="py-3 text-right font-mono w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">3. Total Taxes Required to be Withheld (1+2)</td><td class="py-3 text-right font-mono font-bold w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">4. Less: Tax Remitted in Return Previously Filed</td><td class="py-3 text-right font-mono w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">5. Tax Still Due (3 less 4)</td><td class="py-3 text-right font-mono font-bold w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">6. Add: Penalties (Surcharge + Interest + Compromise)</td><td class="py-3 text-right font-mono w-48">₱0.00</td></tr>
            <tr class="bg-gray-50 font-bold"><td class="py-3">7. Total Amount Still Due (5+6)</td><td class="py-3 text-right font-mono w-48 text-primary-700">₱0.00</td></tr>
        </table>
        <p class="text-xs text-secondary-400 text-center mt-4">Compensation withholding tax data will be populated from payroll module when available.</p>
    </div>
</div>
@endsection
