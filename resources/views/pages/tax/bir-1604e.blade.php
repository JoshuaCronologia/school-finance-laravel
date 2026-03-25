@extends('layouts.app')
@section('title', 'BIR 1604-E')

@section('content')
<x-page-header title="BIR 1604-E" subtitle="Annual Information Return of Creditable Income Taxes Withheld (Expanded)">
    <x-slot:actions><button onclick="window.print()" class="btn-secondary text-sm">Print</button></x-slot:actions>
</x-page-header>

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

<div class="card mb-6">
    <div class="card-header bg-gray-50 text-center">
        <div class="w-full">
            <h2 class="text-sm font-bold">BIR FORM 1604-E</h2>
            <p class="text-xs text-secondary-500">Annual Information Return of Creditable Income Taxes Withheld (Expanded)</p>
            <p class="text-xs text-secondary-500">Calendar Year {{ $year }}</p>
        </div>
    </div>
    <div class="card-body max-w-2xl mx-auto">
        <table class="w-full text-sm">
            <tr class="border-b"><td class="py-3 font-medium">Total Number of Payees</td><td class="py-3 text-right font-mono font-bold w-48">{{ $payeeCount }}</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">Total Amount of Income Payments</td><td class="py-3 text-right font-mono font-bold w-48">₱{{ number_format($totalTaxBase, 2) }}</td></tr>
            <tr class="border-b"><td class="py-3 font-medium">Total Taxes Withheld</td><td class="py-3 text-right font-mono font-bold w-48">₱{{ number_format($totalTaxWithheld, 2) }}</td></tr>
            <tr class="bg-gray-50 font-bold"><td class="py-3">Total Remittances for the Year</td><td class="py-3 text-right font-mono w-48 text-primary-700">₱{{ number_format($totalTaxWithheld, 2) }}</td></tr>
        </table>
    </div>
</div>

{{-- Annual Alphalist --}}
<div class="card">
    <div class="card-header"><h3 class="card-title">Annual Alphalist of Payees</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr><th>Payee</th><th>TIN</th><th>ATC</th><th class="text-right">Income Payment</th><th class="text-right">Tax Withheld</th></tr>
            </thead>
            <tbody>
                @php $grouped = $payments->groupBy(fn($p) => $p->disbursement->payee_name ?? 'Unknown'); @endphp
                @forelse($grouped as $payee => $group)
                <tr>
                    <td class="font-medium">{{ $payee }}</td>
                    <td class="font-mono text-sm">{{ $group->first()->disbursement->vendor->tin ?? '-' }}</td>
                    <td class="font-mono text-sm">{{ $group->first()->disbursement->vendor->withholding_tax_type ?? '-' }}</td>
                    <td class="text-right font-mono">₱{{ number_format($group->sum('gross_amount'), 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($group->sum('withholding_tax'), 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-secondary-400 py-4">No data for this year.</td></tr>
                @endforelse
            </tbody>
            @if($payments->count() > 0)
            <tfoot class="bg-gray-50 font-bold">
                <tr><td colspan="3" class="text-right">Totals:</td><td class="text-right font-mono">₱{{ number_format($totalTaxBase, 2) }}</td><td class="text-right font-mono">₱{{ number_format($totalTaxWithheld, 2) }}</td></tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
