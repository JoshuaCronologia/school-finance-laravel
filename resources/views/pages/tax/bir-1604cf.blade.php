@extends('layouts.app')
@section('title', 'BIR 1604-CF')

@section('content')
<x-page-header title="BIR 1604-CF" subtitle="Annual Information Return of Income Tax Withheld on Compensation and Final" />

<div class="card mb-6">
    <form class="card-body">
        <div class="flex flex-wrap items-end gap-4">
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
            <h2 class="text-sm font-bold">BIR FORM 1604-CF</h2>
            <p class="text-xs text-secondary-500">Annual Information Return of Income Tax Withheld on Compensation and Final Withholding Taxes</p>
            <p class="text-xs text-secondary-500">Calendar Year {{ $year }}</p>
        </div>
    </div>
    <div class="card-body max-w-2xl mx-auto">
        <div class="mb-6">
            <h4 class="text-sm font-bold text-secondary-900 border-b pb-1 mb-3">Schedule 1 - Compensation</h4>
            <table class="w-full text-sm">
                <tr class="border-b"><td class="py-2 font-medium">Total Number of Employees</td><td class="py-2 text-right font-mono w-48">0</td></tr>
                <tr class="border-b"><td class="py-2 font-medium">Total Compensation Paid</td><td class="py-2 text-right font-mono w-48">₱0.00</td></tr>
                <tr class="border-b"><td class="py-2 font-medium">Total Taxes Withheld on Compensation</td><td class="py-2 text-right font-mono font-bold w-48">₱0.00</td></tr>
            </table>
        </div>
        <div>
            <h4 class="text-sm font-bold text-secondary-900 border-b pb-1 mb-3">Schedule 2 - Final Withholding Taxes</h4>
            <table class="w-full text-sm">
                <tr class="border-b"><td class="py-2 font-medium">Total Number of Payees</td><td class="py-2 text-right font-mono w-48">0</td></tr>
                <tr class="border-b"><td class="py-2 font-medium">Total Income Payments</td><td class="py-2 text-right font-mono w-48">₱0.00</td></tr>
                <tr class="border-b"><td class="py-2 font-medium">Total Final Taxes Withheld</td><td class="py-2 text-right font-mono font-bold w-48">₱0.00</td></tr>
            </table>
        </div>
        <p class="text-xs text-secondary-400 text-center mt-6">Compensation and final withholding data will be populated from payroll module when available.</p>
    </div>
</div>
@endsection
