@extends('layouts.app')
@section('title', 'Receipt #' . $receipt->receipt_number)

@section('content')
<x-page-header title="Receipt #{{ $receipt->receipt_number }}" subtitle="Itemized fee breakdown">
    <x-slot name="actions">
        <a href="{{ route('reports.fee-receipts') }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
            Back to Receipts
        </a>
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Receipt Info --}}
    <div class="card lg:col-span-1">
        <div class="card-header">
            <h3 class="card-title">Receipt Information</h3>
        </div>
        <div class="card-body space-y-3">
            <div>
                <span class="text-xs text-secondary-500 uppercase">OR Number</span>
                <p class="font-semibold font-mono text-lg">{{ $receipt->receipt_number }}</p>
            </div>
            <div>
                <span class="text-xs text-secondary-500 uppercase">Student</span>
                <p class="font-medium">{{ $receipt->student_name ?? '-' }}</p>
            </div>
            <div>
                <span class="text-xs text-secondary-500 uppercase">Student Number</span>
                <p class="font-mono">{{ $receipt->student_number ?? '-' }}</p>
            </div>
            <div>
                <span class="text-xs text-secondary-500 uppercase">Control Number</span>
                <p class="font-mono">{{ $receipt->control_number ?? '-' }}</p>
            </div>
            <div>
                <span class="text-xs text-secondary-500 uppercase">Date Paid</span>
                <p class="font-medium">{{ \Carbon\Carbon::parse($receipt->date_paid)->format('F d, Y') }}</p>
            </div>
            <div>
                <span class="text-xs text-secondary-500 uppercase">School Year</span>
                <p>{{ $receipt->year_fr }}-{{ $receipt->year_to }}</p>
            </div>
            <div class="border-t border-gray-100 pt-3">
                <span class="text-xs text-secondary-500 uppercase">Total Amount</span>
                <p class="font-bold text-xl text-green-600">{{ '₱' . number_format($receipt->total, 2) }}</p>
            </div>
        </div>
    </div>

    {{-- Itemized Fees --}}
    <div class="card lg:col-span-2">
        <div class="card-header">
            <h3 class="card-title">Itemized Fee Breakdown</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th>Fee Name</th>
                        <th class="text-right w-36">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receipt->fees as $i => $fee)
                    <tr>
                        <td class="text-secondary-400">{{ $i + 1 }}</td>
                        <td class="font-medium">{{ $fee->fee_name ?? 'Unknown Fee' }}</td>
                        <td class="text-right font-medium">{{ '₱' . number_format($fee->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-400">
                    <tr>
                        <td colspan="2" class="text-right">Total:</td>
                        <td class="text-right text-green-700">{{ '₱' . number_format($receipt->fees->sum('amount'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
