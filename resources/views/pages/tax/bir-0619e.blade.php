@extends('layouts.app')
@section('title', 'BIR 0619-E')

@section('content')
<x-page-header title="BIR 0619-E" subtitle="Monthly Remittance of Creditable Income Taxes Withheld (Expanded)">
    <x-slot name="actions">
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot>
</x-page-header>

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
    <div class="card-header bg-gray-50">
        <div class="text-center w-full">
            <h2 class="text-sm font-bold text-secondary-900">BIR FORM 0619-E</h2>
            <p class="text-xs text-secondary-500">Monthly Remittance Form for Creditable Income Taxes Withheld (Expanded)</p>
            <p class="text-xs text-secondary-500">For the month of {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}</p>
        </div>
    </div>
    <div class="card-body max-w-2xl mx-auto">
        <table class="w-full text-sm">
            <tbody>
                <tr class="border-b border-gray-200">
                    <td class="py-3 font-medium">1. Total Amount of Taxes Withheld for the Month</td>
                    <td class="py-3 text-right font-mono font-bold w-48">₱{{ number_format($totalTaxWithheld, 2) }}</td>
                </tr>
                <tr class="border-b border-gray-200">
                    <td class="py-3 font-medium">2. Less: Tax Credits/Payments</td>
                    <td class="py-3 text-right font-mono w-48">₱0.00</td>
                </tr>
                <tr class="border-b border-gray-200">
                    <td class="py-3 font-medium">3. Tax Still Due (1 less 2)</td>
                    <td class="py-3 text-right font-mono font-bold w-48">₱{{ number_format($totalTaxWithheld, 2) }}</td>
                </tr>
                <tr class="border-b border-gray-200">
                    <td class="py-3 font-medium">4. Add: Penalties</td>
                    <td class="py-3 text-right font-mono w-48">₱0.00</td>
                </tr>
                <tr class="bg-gray-50 font-bold">
                    <td class="py-3">5. Total Amount Due (3 plus 4)</td>
                    <td class="py-3 text-right font-mono w-48 text-primary-700">₱{{ number_format($totalTaxWithheld, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="mt-6">
            <h4 class="text-sm font-semibold text-secondary-700 mb-3">Supporting Schedule</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Payee</th>
                        <th>Voucher #</th>
                        <th class="text-right">Income Payment</th>
                        <th class="text-right">Tax Withheld</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($p->payment_date)->format('M d, Y') }}</td>
                        <td>{{ $p->disbursement->payee_name ?? '-' }}</td>
                        <td class="font-mono">{{ $p->voucher_number }}</td>
                        <td class="text-right font-mono">₱{{ number_format($p->gross_amount, 2) }}</td>
                        <td class="text-right font-mono">₱{{ number_format($p->withholding_tax, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-secondary-400 py-4">No withholding tax transactions for this period.</td></tr>
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
</div>
@endsection
