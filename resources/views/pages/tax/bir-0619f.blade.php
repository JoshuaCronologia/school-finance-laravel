@extends('layouts.app')
@section('title', 'BIR 0619-F')

@section('content')
<x-page-header title="BIR 0619-F" subtitle="Monthly Remittance of Final Income Taxes Withheld" />

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
            <h2 class="text-sm font-bold">BIR FORM 0619-F</h2>
            <p class="text-xs text-secondary-500">Monthly Remittance Form for Final Income Taxes Withheld</p>
            <p class="text-xs text-secondary-500">For the month of {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}</p>
        </div>
    </div>
    <div class="card-body max-w-2xl mx-auto">
        <table class="w-full text-sm">
            <tr class="border-b"><td class="py-3 font-medium">1. Total Taxes Withheld for the Month (Final)</td><td class="py-3 text-right font-mono font-bold w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">2. Less: Tax Credits/Payments</td><td class="py-3 text-right font-mono w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">3. Tax Still Due</td><td class="py-3 text-right font-mono font-bold w-48">₱0.00</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">4. Add: Penalties</td><td class="py-3 text-right font-mono w-48">₱0.00</td></tr>
            <tr class="bg-gray-50 font-bold"><td class="py-3">5. Total Amount Due</td><td class="py-3 text-right font-mono w-48 text-primary-700">₱0.00</td></tr>
        </table>
        <p class="text-xs text-secondary-400 text-center mt-4">Final withholding tax data will be populated from payroll and other final tax transactions when available.</p>
    </div>
</div>
@endsection
