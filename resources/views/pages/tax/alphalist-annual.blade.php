@extends('layouts.app')
@section('title', 'Alphalist - Annual')

@section('content')
<x-page-header title="Alphalist of Payees (Annual)" subtitle="Annual Alphalist of Payees Subject to Withholding Tax">
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

<div class="card">
    <div class="card-header bg-gray-50 text-center">
        <div class="w-full">
            <h2 class="text-sm font-bold">ANNUAL ALPHALIST OF PAYEES</h2>
            <p class="text-xs text-secondary-500">Calendar Year {{ $year }}</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>TIN</th>
                    <th>Payee Name</th>
                    <th>ATC</th>
                    <th class="text-right">Income Payment</th>
                    <th class="text-right">Tax Withheld</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alphalist as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="font-mono text-sm">{{ $row->tin ?: '-' }}</td>
                    <td class="font-medium">{{ $row->payee_name }}</td>
                    <td class="font-mono text-sm">{{ $row->atc ?: '-' }}</td>
                    <td class="text-right font-mono">₱{{ number_format($row->income_payment, 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($row->tax_withheld, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-secondary-400 py-6">No withholding tax transactions for this year.</td></tr>
                @endforelse
            </tbody>
            @if($alphalist->count() > 0)
            <tfoot class="bg-gray-50 font-bold">
                <tr>
                    <td colspan="4" class="text-right">Grand Total:</td>
                    <td class="text-right font-mono">₱{{ number_format($alphalist->sum('income_payment'), 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($alphalist->sum('tax_withheld'), 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
