@extends('layouts.app')
@section('title', 'Check Writer')

@section('content')
@php
    $checks = $checks ?? collect();
    $totalIssued = $totalIssued ?? 0;
    $totalCleared = $totalCleared ?? 0;
    $totalAmount = $totalAmount ?? 0;
    $filter = $filter ?? 'all';
@endphp

<x-page-header title="Check Writer" subtitle="Manage issued checks, clearing, and printing" />

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif

{{-- Stat Cards --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <x-stat-card label="Total Checks" :value="number_format($checks->total())" color="blue">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" /></svg></x-slot>
    </x-stat-card>
    <x-stat-card label="IC - Issued (Outstanding)" :value="number_format($totalIssued)" color="yellow">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot>
    </x-stat-card>
    <x-stat-card label="CC - Cleared Checks" :value="number_format($totalCleared)" color="green">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot>
    </x-stat-card>
    <x-stat-card label="Total Amount" :value="'₱' . number_format($totalAmount, 2)" color="blue">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot>
    </x-stat-card>
</div>

{{-- Filter --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="flex items-end gap-4">
            <div>
                <label class="form-label">Filter</label>
                <select name="filter" class="form-input w-56" onchange="this.form.submit()">
                    <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Checks</option>
                    <option value="ic" {{ $filter === 'ic' ? 'selected' : '' }}>IC - Issued Checks (Outstanding)</option>
                    <option value="cc" {{ $filter === 'cc' ? 'selected' : '' }}>CC - Cleared Checks</option>
                </select>
            </div>
        </form>
    </div>
</div>

{{-- Check Payments Table --}}
<form action="{{ route('tax.check-writer.batch-clear') }}" method="POST" id="batch-clear-form">
@csrf
<div class="card mb-6">
    <div class="card-header">
        <div class="flex items-center justify-between w-full">
            <h3 class="text-sm font-semibold text-secondary-900">
                {{ $filter === 'ic' ? 'Issued Checks (Outstanding)' : ($filter === 'cc' ? 'Cleared Checks' : 'All Checks') }}
            </h3>
            <div class="flex items-center gap-3">
                <span id="selected-count" class="text-sm text-secondary-500" style="display:none">
                    <span id="count-num">0</span> selected
                </span>
                <button type="submit" id="batch-clear-btn" class="btn-primary text-sm" style="display:none" onclick="return confirm('Mark selected checks as cleared?')">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    Mark Selected as Cleared
                </button>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-10"><input type="checkbox" id="select-all" onchange="toggleAll(this)"></th>
                    <th>Voucher #</th>
                    <th>Date</th>
                    <th>Payee</th>
                    <th>Bank</th>
                    <th>Check #</th>
                    <th class="text-right">Net Amount</th>
                    <th>Status</th>
                    <th class="w-16">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($checks as $check)
                <tr>
                    <td>
                        @if($check->status === 'outstanding')
                            <input type="checkbox" name="check_ids[]" value="{{ $check->id }}" class="check-select" onchange="updateCount()">
                        @endif
                    </td>
                    <td class="font-mono text-sm">{{ $check->disbursement_payment_id ? optional(\App\Models\DisbursementPayment::find($check->disbursement_payment_id))->voucher_number : '-' }}</td>
                    <td class="text-sm">{{ $check->check_date->format('M d, Y') }}</td>
                    <td>{{ $check->payee }}</td>
                    <td class="font-medium">{{ $check->bankAccount->bank_name ?? '-' }} {{ $check->bankAccount->account_type ?? '' }}</td>
                    <td class="font-mono text-sm font-medium">{{ $check->check_number }}</td>
                    <td class="text-right font-mono font-semibold">₱{{ number_format($check->amount, 2) }}</td>
                    <td>
                        @if($check->status === 'cleared')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">CC - Cleared</span>
                        @elseif($check->status === 'voided')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Voided</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">IC - Outstanding</span>
                        @endif
                    </td>
                    <td>
                        @if($check->disbursement_payment_id)
                        <div class="flex gap-2 items-center">
                            <a href="{{ route('ap.payments.print', $check->disbursement_payment_id) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium" target="_blank">Voucher</a>
                            <span class="text-secondary-300">|</span>
                            <button type="button" @click="$dispatch('open-modal', 'preview-check-{{ $check->disbursement_payment_id }}')" class="text-primary-600 hover:text-primary-700 text-sm font-medium">Check</button>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-8 text-secondary-400">No checks found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($checks->hasPages())
    <div class="card-body border-t">{{ $checks->appends(request()->query())->links() }}</div>
    @endif
</div>
</form>

{{-- Preview & Print Modals (only for checks linked to disbursement payments) --}}
@php $printablePayments = \App\Models\DisbursementPayment::with('disbursement')->whereIn('id', $checks->pluck('disbursement_payment_id')->filter())->get(); @endphp
@foreach($printablePayments as $payment)
<x-modal name="preview-check-{{ $payment->id }}" title="Check Preview" maxWidth="4xl">
    <div x-data="{
        bank: '{{ $payment->bank_account ?? '' }}',
        checkNumber: '{{ $payment->check_number ?? '' }}',
        payee: '{{ addslashes($payment->disbursement->payee_name ?? '') }}',
        amount: {{ $payment->net_amount ?? 0 }},
        description: '{{ addslashes($payment->disbursement->description ?? '') }}',
        voucher: '{{ $payment->voucher_number ?? '' }}',
        checkDate: '{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('m/d/Y') : now()->format('m/d/Y') }}',
        topMargin: 10,
        leftMargin: 15,
        get amountInWords() {
            const ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
            const tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
            const num = Math.floor(this.amount);
            const cents = Math.round((this.amount - num) * 100);
            if (num === 0) return 'Zero Pesos';
            let words = '';
            if (num >= 1000000) { words += ones[Math.floor(num/1000000)] + ' Million '; }
            const rem = num % 1000000;
            if (rem >= 1000) {
                const t = Math.floor(rem/1000);
                if (t >= 100) { words += ones[Math.floor(t/100)] + ' Hundred '; }
                const t2 = t % 100;
                if (t2 >= 20) { words += tens[Math.floor(t2/10)] + ' ' + ones[t2%10] + ' '; }
                else if (t2 > 0) { words += ones[t2] + ' '; }
                words += 'Thousand ';
            }
            const h = rem % 1000;
            if (h >= 100) { words += ones[Math.floor(h/100)] + ' Hundred '; }
            const d = h % 100;
            if (d >= 20) { words += tens[Math.floor(d/10)] + ' ' + ones[d%10] + ' '; }
            else if (d > 0) { words += ones[d] + ' '; }
            words += 'Pesos';
            if (cents > 0) words += ' and ' + cents + '/100';
            return words.trim();
        }
    }">
        <div class="space-y-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-gray-50 rounded-lg p-3 text-sm">
                <div><span class="text-secondary-500 block">Bank</span><span class="font-semibold">{{ $payment->bank_account ?? '-' }}</span></div>
                <div><span class="text-secondary-500 block">Check #</span><span class="font-semibold font-mono">{{ $payment->check_number ?? '-' }}</span></div>
                <div><span class="text-secondary-500 block">Voucher #</span><span class="font-semibold font-mono">{{ $payment->voucher_number }}</span></div>
                <div><span class="text-secondary-500 block">Net Amount</span><span class="font-semibold">₱{{ number_format($payment->net_amount ?? 0, 2) }}</span></div>
            </div>

            <div id="check-print-area-{{ $payment->id }}" class="border-2 border-dashed border-gray-300 rounded-lg p-6 bg-white" :style="'margin-top:'+topMargin+'px; margin-left:'+leftMargin+'px'">
                <div class="max-w-2xl mx-auto">
                    <div class="flex items-start justify-between mb-4">
                        <div><p class="text-lg font-bold text-gray-800" x-text="bank"></p><p class="text-xs text-gray-500">A Banking Corporation</p></div>
                        <div class="text-right"><p class="text-sm text-gray-600">No. <span class="font-mono font-bold" x-text="checkNumber || '______'"></span></p></div>
                    </div>
                    <div class="flex justify-end mb-3"><p class="text-sm">Date: <span class="font-mono border-b border-gray-400 px-4" x-text="checkDate"></span></p></div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-sm font-medium whitespace-nowrap">PAY TO THE ORDER OF:</span>
                        <span class="flex-1 border-b border-gray-400 px-2 font-semibold" x-text="payee"></span>
                        <div class="border border-gray-400 px-3 py-1 rounded font-mono font-bold text-lg">₱<span x-text="amount.toLocaleString('en-US', {minimumFractionDigits: 2})"></span></div>
                    </div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xs text-gray-500 whitespace-nowrap">AMOUNT IN WORDS:</span>
                        <span class="flex-1 border-b border-gray-400 px-2 text-sm" x-text="amountInWords"></span>
                    </div>
                    <div class="flex items-center gap-2 mb-6">
                        <span class="text-xs text-gray-500 whitespace-nowrap">MEMO/FOR:</span>
                        <span class="flex-1 border-b border-gray-400 px-2 text-sm" x-text="description || voucher"></span>
                    </div>
                    <div class="flex items-end justify-between pt-4">
                        <p class="text-xs font-mono text-gray-400">{{ $payment->voucher_number }}</p>
                        <div class="text-center"><div class="border-b border-gray-400 w-48 mb-1"></div><p class="text-xs text-gray-500">Authorized Signature</p></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-slot name="footer">
        <button @click="$dispatch('close-modal', 'preview-check-{{ $payment->id }}')" class="btn-secondary">Close</button>
        <button onclick="printCheck('check-print-area-{{ $payment->id }}')" class="btn-primary">Print Check</button>
    </x-slot>
</x-modal>
@endforeach
@endsection

@push('scripts')
<script>
    function toggleAll(master) {
        document.querySelectorAll('.check-select').forEach(function(cb) { cb.checked = master.checked; });
        updateCount();
    }
    function updateCount() {
        var checked = document.querySelectorAll('.check-select:checked').length;
        document.getElementById('selected-count').style.display = checked > 0 ? '' : 'none';
        document.getElementById('batch-clear-btn').style.display = checked > 0 ? '' : 'none';
        document.getElementById('count-num').textContent = checked;
    }
    function printCheck(areaId) {
        var area = document.getElementById(areaId);
        if (!area) { alert('Check preview not found.'); return; }
        var styles = '';
        for (var i = 0; i < document.styleSheets.length; i++) {
            try { var sheet = document.styleSheets[i]; if (sheet.cssRules) { for (var j = 0; j < sheet.cssRules.length; j++) { styles += sheet.cssRules[j].cssText + '\n'; } } } catch(e) {}
        }
        var win = window.open('', '_blank', 'width=900,height=500');
        win.document.write('<!DOCTYPE html><html><head><title>Print Check</title>');
        win.document.write('<style>' + styles + '</style>');
        win.document.write('<style>@page { margin: 0; size: 8.5in 3.67in; } body { margin: 0; padding: 0.25in 0.4in; font-family: Arial, sans-serif; background: white; }</style>');
        win.document.write('</head><body>' + area.outerHTML + '</body></html>');
        win.document.close();
        setTimeout(function() { win.print(); }, 300);
    }
</script>
@endpush
