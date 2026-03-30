@extends('layouts.app')
@section('title', 'Payment Processing')

@section('content')
<x-page-header title="Payment Processing" subtitle="Process approved disbursements and manage payment history">
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Ready for Payment --}}
@php $readyCount = count($readyForPayment ?? []); @endphp
<div class="card mb-6">
    <div class="card-header">
        <div class="flex items-center gap-2">
            <h3 class="text-sm font-semibold text-secondary-700">Ready for Payment</h3>
            <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-primary-600 rounded-full">{{ $readyCount }}</span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Request #</th>
                    <th>Payee</th>
                    <th>Description</th>
                    <th>Department</th>
                    <th class="text-right">Amount</th>
                    <th>Method</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($readyForPayment ?? [] as $request)
                <tr>
                    <td class="font-medium">
                        <a href="{{ route('ap.disbursements.show', $request) }}" class="text-primary-600 hover:text-primary-700 hover:underline">
                            {{ $request->request_number }}
                        </a>
                    </td>
                    <td>{{ $request->payee_name ?? $request->payee->name ?? '-' }}</td>
                    <td class="max-w-xs truncate">{{ $request->description ?? '-' }}</td>
                    <td>{{ $request->department->name ?? '-' }}</td>
                    <td class="text-right font-medium">{{ '₱' . number_format($request->amount, 2) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $request->payment_method ?? '-')) }}</td>
                    <td>
                        <button @click="$dispatch('open-modal', 'process-payment-{{ $request->id }}')" class="btn-primary text-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                            Process
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-secondary-400 py-8">
                        <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        No approved requests awaiting payment.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Process Payment Modals --}}
@foreach($readyForPayment ?? [] as $request)
<x-modal name="process-payment-{{ $request->id }}" title="Process Payment" maxWidth="3xl">
    <form action="{{ route('ap.payments.store', $request) }}" method="POST" x-data="{
        grossAmount: {{ $request->amount }},
        whtRate: 0.02,
        get whtAmount() { return parseFloat((this.grossAmount * this.whtRate).toFixed(2)); },
        get netAmount() { return parseFloat((this.grossAmount - this.whtAmount).toFixed(2)); },
        fmt(val) { return parseFloat(val || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    }">
        @csrf
        <input type="hidden" name="disbursement_id" value="{{ $request->id }}">

        {{-- Request Summary --}}
        <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-secondary-500">Request #:</span>
                    <span class="font-medium">{{ $request->request_number }}</span>
                </div>
                <div>
                    <span class="text-secondary-500">Payee:</span>
                    <span class="font-medium">{{ $request->payee_name ?? $request->payee->name ?? '-' }}</span>
                </div>
                <div class="col-span-2">
                    <span class="text-secondary-500">Description:</span>
                    <span class="font-medium">{{ $request->description ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Payment Fields --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="form-label">Payment Date <span class="text-danger-500">*</span></label>
                <input type="date" name="payment_date" class="form-input" value="{{ date('Y-m-d') }}" required>
            </div>
            <div>
                <label class="form-label">Bank Account</label>
                <input type="text" name="bank_account" class="form-input" placeholder="e.g., BDO - 1234-5678">
            </div>
            <div>
                <label class="form-label">Payment Method <span class="text-danger-500">*</span></label>
                <select name="payment_method" class="form-input" required>
                    <option value="check" {{ ($request->payment_method ?? '') == 'check' ? 'selected' : '' }}>Check</option>
                    <option value="bank_transfer" {{ ($request->payment_method ?? '') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="cash" {{ ($request->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="online" {{ ($request->payment_method ?? '') == 'online' ? 'selected' : '' }}>Online</option>
                </select>
            </div>
            <div>
                <label class="form-label">Reference / Check Number</label>
                <input type="text" name="reference_number" class="form-input" placeholder="Auto-generated if blank">
                <p class="text-xs text-secondary-400 mt-1">Leave blank to auto-generate. Fill in for manual check numbers.</p>
            </div>
        </div>

        {{-- Calculation --}}
        <div class="bg-gray-50 rounded-lg p-4 mb-4 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-secondary-600">Gross Amount</span>
                <span class="font-medium" x-text="'₱' + fmt(grossAmount)"></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-secondary-600">Withholding Tax (2%)</span>
                <span class="font-medium text-danger-600" x-text="'(₱' + fmt(whtAmount) + ')'"></span>
            </div>
            <div class="flex justify-between text-sm font-semibold border-t border-gray-200 pt-2">
                <span class="text-secondary-900">Net Amount to Pay</span>
                <span class="text-primary-700" x-text="'₱' + fmt(netAmount)"></span>
            </div>
            <input type="hidden" name="gross_amount" :value="grossAmount">
            <input type="hidden" name="wht_amount" :value="whtAmount">
            <input type="hidden" name="net_amount" :value="netAmount">
        </div>

        {{-- Notes --}}
        <div class="mb-4">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-input text-sm" rows="2" placeholder="Payment notes or remarks..."></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'process-payment-{{ $request->id }}')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                Confirm Payment
            </button>
        </div>
    </form>
</x-modal>
@endforeach

{{-- Link to full payment history --}}
<div class="text-center py-4">
    <a href="{{ route('ap.supplier-payments') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
        View all Supplier Payments &rarr;
    </a>
</div>
@endsection
