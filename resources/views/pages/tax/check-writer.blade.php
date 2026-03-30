@extends('layouts.app')
@section('title', 'Check Writer')

@section('content')
@php
    $checkPayments = $checkPayments ?? collect();
    $pendingChecks = $pendingChecks ?? 0;
    $totalAmount = $totalAmount ?? $checkPayments->sum('net_amount');
    $printHistory = $printHistory ?? collect();
@endphp

<x-page-header title="Check Writer" subtitle="Print checks from generated payment vouchers (read-only — data comes from Payment Processing)" />

{{-- Stat Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <x-stat-card label="Total Check Payments" :value="number_format($checkPayments->count())" color="blue"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z\' /></svg>'" />
    <x-stat-card label="Pending Print" :value="number_format($pendingChecks)" color="yellow"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 12H5.25\' /></svg>'" />
    <x-stat-card label="Total Amount" :value="'₱' . number_format($totalAmount, 2)" color="green"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z\' /></svg>'" />
</div>

{{-- Info Banner --}}
<div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-6 text-sm text-blue-800 flex items-start gap-2">
    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
    <div>
        Check numbers, bank accounts, and amounts are generated from <a href="{{ route('ap.payment-processing') }}" class="font-medium underline">Payment Processing</a>.
        Fields here are read-only to maintain audit trail integrity and bank reconciliation accuracy.
    </div>
</div>

{{-- Check Payments Table --}}
<div class="card mb-6">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Check Payments</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Voucher #</th>
                    <th>Date</th>
                    <th>Payee</th>
                    <th>Bank</th>
                    <th>Check #</th>
                    <th class="text-right">Net Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($checkPayments as $payment)
                    <tr>
                        <td class="font-mono text-sm">{{ $payment->voucher_number ?? '' }}</td>
                        <td class="text-sm">{{ isset($payment->payment_date) ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') : '' }}</td>
                        <td>{{ $payment->disbursement->payee_name ?? '' }}</td>
                        <td class="font-medium">{{ $payment->bank_account ?? '-' }}</td>
                        <td class="font-mono text-sm font-medium">{{ $payment->check_number ?? '---' }}</td>
                        <td class="text-right font-mono font-semibold">₱{{ number_format($payment->net_amount ?? 0, 2) }}</td>
                        <td><x-badge :status="$payment->status ?? 'completed'" /></td>
                        <td>
                            <button @click="$dispatch('open-modal', 'preview-check-{{ $payment->id }}')" class="btn-primary text-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 12H5.25" /></svg>
                                Preview & Print
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-8 text-secondary-400">No check payments found. Generate checks from <a href="{{ route('ap.payment-processing') }}" class="text-primary-600 hover:underline">Payment Processing</a>.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($checkPayments->hasPages())
    <div class="card-footer">
        {{ $checkPayments->links() }}
    </div>
    @endif
</div>

{{-- Preview & Print Modals (one per payment, all read-only) --}}
@foreach($checkPayments as $payment)
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
            {{-- Read-only check details --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-gray-50 rounded-lg p-3 text-sm">
                <div>
                    <span class="text-secondary-500 block">Bank</span>
                    <span class="font-semibold">{{ $payment->bank_account ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-secondary-500 block">Check #</span>
                    <span class="font-semibold font-mono">{{ $payment->check_number ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-secondary-500 block">Voucher #</span>
                    <span class="font-semibold font-mono">{{ $payment->voucher_number }}</span>
                </div>
                <div>
                    <span class="text-secondary-500 block">Net Amount</span>
                    <span class="font-semibold">₱{{ number_format($payment->net_amount ?? 0, 2) }}</span>
                </div>
            </div>

            {{-- Print margin adjustments only --}}
            <div class="flex items-center gap-4 text-sm">
                <span class="text-secondary-500">Print Margins:</span>
                <div class="flex items-center gap-2">
                    <label class="text-xs">Top (mm)</label>
                    <input type="number" x-model="topMargin" class="form-input w-16 text-sm" min="0" max="50">
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs">Left (mm)</label>
                    <input type="number" x-model="leftMargin" class="form-input w-16 text-sm" min="0" max="50">
                </div>
            </div>

            {{-- Check Preview --}}
            <div id="check-print-area-{{ $payment->id }}" class="border-2 border-dashed border-gray-300 rounded-lg p-6 bg-white" :style="'margin-top:'+topMargin+'px; margin-left:'+leftMargin+'px'">
                <div class="max-w-2xl mx-auto">
                    {{-- Check Header --}}
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="text-lg font-bold text-gray-800" x-text="bank"></p>
                            <p class="text-xs text-gray-500">A Banking Corporation</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">No. <span class="font-mono font-bold" x-text="checkNumber || '______'"></span></p>
                        </div>
                    </div>

                    {{-- Date --}}
                    <div class="flex justify-end mb-3">
                        <p class="text-sm">Date: <span class="font-mono border-b border-gray-400 px-4" x-text="checkDate"></span></p>
                    </div>

                    {{-- Pay To --}}
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-sm font-medium whitespace-nowrap">PAY TO THE ORDER OF:</span>
                        <span class="flex-1 border-b border-gray-400 px-2 font-semibold" x-text="payee"></span>
                        <div class="border border-gray-400 px-3 py-1 rounded font-mono font-bold text-lg">
                            ₱<span x-text="amount.toLocaleString('en-US', {minimumFractionDigits: 2})"></span>
                        </div>
                    </div>

                    {{-- Amount in Words --}}
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xs text-gray-500 whitespace-nowrap">AMOUNT IN WORDS:</span>
                        <span class="flex-1 border-b border-gray-400 px-2 text-sm" x-text="amountInWords"></span>
                    </div>

                    {{-- Memo --}}
                    <div class="flex items-center gap-2 mb-6">
                        <span class="text-xs text-gray-500 whitespace-nowrap">MEMO/FOR:</span>
                        <span class="flex-1 border-b border-gray-400 px-2 text-sm" x-text="description || voucher"></span>
                    </div>

                    {{-- Bottom --}}
                    <div class="flex items-end justify-between pt-4">
                        <div>
                            <p class="text-xs font-mono text-gray-400">{{ $payment->voucher_number }}</p>
                        </div>
                        <div class="text-center">
                            <div class="border-b border-gray-400 w-48 mb-1"></div>
                            <p class="text-xs text-gray-500">Authorized Signature</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-slot:footer>
        <button @click="$dispatch('close-modal', 'preview-check-{{ $payment->id }}')" class="btn-secondary">Close</button>
        <button onclick="printCheck('check-print-area-{{ $payment->id }}')" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 12H5.25" /></svg>
            Print Check
        </button>
    </x-slot:footer>
</x-modal>
@endforeach

@endsection

@push('scripts')
<script>
    function printCheck(areaId) {
        var area = document.getElementById(areaId);
        if (!area) { alert('Check preview not found.'); return; }

        var styles = '';
        for (var i = 0; i < document.styleSheets.length; i++) {
            try {
                var sheet = document.styleSheets[i];
                if (sheet.cssRules) {
                    for (var j = 0; j < sheet.cssRules.length; j++) {
                        styles += sheet.cssRules[j].cssText + '\n';
                    }
                }
            } catch(e) {}
        }

        var win = window.open('', '_blank', 'width=900,height=500');
        win.document.write('<!DOCTYPE html><html><head><title>Print Check</title>');
        win.document.write('<style>' + styles + '</style>');
        win.document.write('<style>');
        win.document.write('@page { margin: 0; size: 8.5in 3.67in; }');
        win.document.write('body { margin: 0; padding: 0.25in 0.4in; font-family: Arial, sans-serif; background: white; width: 8.5in; height: 3.67in; overflow: hidden; }');
        win.document.write('[id^="check-print-area"] { border: none !important; border-radius: 0 !important; padding: 0 !important; margin: 0 !important; max-height: 3.2in; overflow: hidden; }');
        win.document.write('</style></head><body>');
        win.document.write(area.outerHTML);
        win.document.write('</body></html>');
        win.document.close();

        setTimeout(function() { win.print(); }, 300);
    }
</script>
@endpush
