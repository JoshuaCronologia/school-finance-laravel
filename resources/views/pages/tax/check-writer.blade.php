@extends('layouts.app')
@section('title', 'Check Writer')

@section('content')
@php
    $checkPayments = $checkPayments ?? collect();
    $pendingChecks = $pendingChecks ?? 0;
    $totalAmount = $totalAmount ?? $checkPayments->sum('amount');
    $printHistory = $printHistory ?? collect();
@endphp

<x-page-header title="Check Writer" subtitle="Generate and print checks for payment vouchers" />

{{-- Stat Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <x-stat-card label="Total Check Payments" :value="number_format($checkPayments->count())" color="blue"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z\' /></svg>'" />
    <x-stat-card label="Pending Checks" :value="number_format($pendingChecks)" color="yellow"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z\' /></svg>'" />
    <x-stat-card label="Total Amount" :value="'₱' . number_format($totalAmount, 2)" color="green"
        :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z\' /></svg>'" />
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
                    <th class="text-right">Amount</th>
                    <th>Check #</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($checkPayments as $payment)
                    <tr>
                        <td class="font-mono text-sm">{{ $payment->voucher_number ?? '' }}</td>
                        <td class="text-sm">{{ isset($payment->payment_date) ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') : '' }}</td>
                        <td>{{ $payment->payee_name ?? '' }}</td>
                        <td class="text-right font-mono font-semibold">₱{{ number_format($payment->amount ?? $payment->net_amount ?? 0, 2) }}</td>
                        <td class="font-mono text-sm">{{ $payment->check_number ?? '---' }}</td>
                        <td><x-badge :status="$payment->status ?? 'pending'" /></td>
                        <td>
                            <button @click="$dispatch('open-modal', 'write-check'); $dispatch('check-data', {
                                id: {{ $payment->id ?? 0 }},
                                payee: '{{ addslashes($payment->payee_name ?? '') }}',
                                amount: {{ $payment->amount ?? $payment->net_amount ?? 0 }},
                                description: '{{ addslashes($payment->description ?? '') }}',
                                voucher: '{{ $payment->voucher_number ?? '' }}',
                                date: '{{ $payment->payment_date ?? now()->format('Y-m-d') }}'
                            })" class="btn-primary text-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                Write Check
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-8 text-secondary-400">No check payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Write Check Modal --}}
<x-modal name="write-check" title="Write Check" maxWidth="4xl">
    <div x-data="{
        bank: 'BDO',
        checkNumber: '',
        accountNumber: '',
        topMargin: 10,
        leftMargin: 15,
        payee: '',
        amount: 0,
        description: '',
        voucher: '',
        checkDate: '{{ now()->format('m/d/Y') }}',
        init() {
            this.$el.addEventListener('check-data', (e) => {
                this.payee = e.detail.payee;
                this.amount = e.detail.amount;
                this.description = e.detail.description;
                this.voucher = e.detail.voucher;
                this.checkDate = new Date(e.detail.date).toLocaleDateString('en-US', {month:'2-digit',day:'2-digit',year:'numeric'});
            });
        },
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
        },
        get maskedAccount() {
            if (!this.accountNumber) return '****-****-****';
            return '****-****-' + this.accountNumber.slice(-4);
        }
    }" @check-data.window="payee = $event.detail.payee; amount = $event.detail.amount; description = $event.detail.description; voucher = $event.detail.voucher; checkDate = new Date($event.detail.date).toLocaleDateString('en-US');">
        <div class="space-y-6">
            {{-- Check Settings --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">Bank</label>
                    <select x-model="bank" class="form-input">
                        <option value="BDO">BDO Unibank</option>
                        <option value="BPI">Bank of the Philippine Islands</option>
                        <option value="Metrobank">Metropolitan Bank</option>
                        <option value="Landbank">Land Bank of the Philippines</option>
                        <option value="PNB">Philippine National Bank</option>
                        <option value="RCBC">Rizal Commercial Banking Corp</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Check Number</label>
                    <input type="text" x-model="checkNumber" placeholder="000001" class="form-input">
                </div>
                <div>
                    <label class="form-label">Account Number</label>
                    <input type="text" x-model="accountNumber" placeholder="1234567890" class="form-input">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="form-label">Top (mm)</label>
                        <input type="number" x-model="topMargin" class="form-input" min="0" max="50">
                    </div>
                    <div>
                        <label class="form-label">Left (mm)</label>
                        <input type="number" x-model="leftMargin" class="form-input" min="0" max="50">
                    </div>
                </div>
            </div>

            {{-- Check Preview --}}
            <div id="check-print-area" class="border-2 border-dashed border-gray-300 rounded-lg p-6 bg-white" :style="'margin-top:'+topMargin+'px; margin-left:'+leftMargin+'px'">
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
                        <span class="flex-1 border-b border-gray-400 px-2 font-semibold" x-text="payee || '______________________'"></span>
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
                        <span class="flex-1 border-b border-gray-400 px-2 text-sm" x-text="description || voucher || '______________________'"></span>
                    </div>

                    {{-- Bottom --}}
                    <div class="flex items-end justify-between pt-4">
                        <div>
                            <p class="text-xs font-mono text-gray-400" x-text="maskedAccount"></p>
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
        <button @click="$dispatch('close-modal', 'write-check')" class="btn-secondary">Cancel</button>
        <button onclick="printCheck()" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 12H5.25" /></svg>
            Print Check
        </button>
    </x-slot:footer>
</x-modal>

{{-- Print History --}}
<div class="card">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Print History</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Check #</th>
                    <th>Bank</th>
                    <th>Payee</th>
                    <th class="text-right">Amount</th>
                    <th>Printed Date</th>
                    <th>Printed By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($printHistory as $print)
                    <tr>
                        <td class="font-mono text-sm">{{ $print->check_number ?? '' }}</td>
                        <td>{{ $print->bank ?? '' }}</td>
                        <td>{{ $print->payee_name ?? '' }}</td>
                        <td class="text-right font-mono">₱{{ number_format($print->amount ?? 0, 2) }}</td>
                        <td class="text-sm">{{ isset($print->printed_at) ? \Carbon\Carbon::parse($print->printed_at)->format('M d, Y H:i') : '' }}</td>
                        <td>{{ $print->printed_by ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-6 text-secondary-400">No print history available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        /* Hide everything except the check */
        body * { visibility: hidden !important; }
        #check-print-area, #check-print-area * { visibility: visible !important; }
        #check-print-area {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 8in !important;
            border: none !important;
            border-radius: 0 !important;
            padding: 0.5in !important;
            margin: 0 !important;
            background: white !important;
        }
        /* Remove dashed border for print */
        #check-print-area { border-style: none !important; }
    }
</style>
@endpush

@push('scripts')
<script>
    function printCheck() {
        var area = document.getElementById('check-print-area');
        if (!area) { alert('Check preview not found.'); return; }
        window.print();
    }
</script>
@endpush
