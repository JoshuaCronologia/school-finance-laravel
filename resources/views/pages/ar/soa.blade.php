@extends('layouts.app')
@section('title', 'Statement of Account')

@section('content')
<x-page-header title="Statement of Account" />

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif

{{-- Filters --}}
<div class="card mb-6">
    <div class="card-body">
        <form action="{{ route('ar.soa') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[250px]">
                <label class="form-label">Customer <span class="text-danger-500">*</span></label>
                <select name="customer_id" class="form-input">
                    <option value="">Select Customer</option>
                    @foreach($customers ?? [] as $cust)
                        <option value="{{ $cust->id }}" {{ request('customer_id') == $cust->id ? 'selected' : '' }}>{{ $cust->customer_code }} - {{ $cust->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">As of Date</label>
                <input type="date" name="as_of_date" value="{{ request('as_of_date', date('Y-m-d')) }}" class="form-input w-48">
            </div>
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                Generate SOA
            </button>
        </form>
    </div>
</div>

@if(isset($selectedCustomer) && $selectedCustomer)
    {{-- Customer Info Card --}}
    <div class="card mb-6">
        <div class="card-body">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-secondary-500 uppercase tracking-wider">Customer Name</p>
                    <p class="font-semibold text-secondary-900">{{ $selectedCustomer->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-secondary-500 uppercase tracking-wider">Customer Code</p>
                    <p class="font-semibold text-secondary-900">{{ $selectedCustomer->customer_code ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-secondary-500 uppercase tracking-wider">Campus</p>
                    <p class="font-semibold text-secondary-900">{{ $selectedCustomer->campus ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-secondary-500 uppercase tracking-wider">Email</p>
                    <p class="font-semibold text-secondary-900">{{ $selectedCustomer->email ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-secondary-700">Transactions</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th class="text-right">Charges</th>
                        <th class="text-right">Payments</th>
                        <th class="text-right">Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php $runningBalance = 0; @endphp
                    @forelse($transactions ?? [] as $txn)
                    @php
                        $runningBalance += ($txn->charges ?? 0) - ($txn->payments ?? 0);
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($txn->date)->format('M d, Y') }}</td>
                        <td class="font-medium text-primary-600">{{ $txn->reference ?? '-' }}</td>
                        <td>{{ $txn->description ?? '-' }}</td>
                        <td class="text-right">{{ ($txn->charges ?? 0) > 0 ? '₱' . number_format($txn->charges, 2) : '-' }}</td>
                        <td class="text-right text-success-600">{{ ($txn->payments ?? 0) > 0 ? '₱' . number_format($txn->payments, 2) : '-' }}</td>
                        <td class="text-right font-medium {{ $runningBalance > 0 ? 'text-danger-500' : '' }}">{{ '₱' . number_format($runningBalance, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-secondary-400 py-6">No transactions found for this customer.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Account Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-stat-card label="Total Charges" :value="'₱' . number_format($totalCharges ?? 0, 2)" color="blue" />
        <x-stat-card label="Total Payments" :value="'₱' . number_format($totalPayments ?? 0, 2)" color="green" />
        <x-stat-card label="Outstanding Balance" :value="'₱' . number_format(($totalCharges ?? 0) - ($totalPayments ?? 0), 2)" color="red" />
    </div>

    {{-- Actions --}}
    <div class="flex gap-3">
        <a href="{{ route('ar.soa.pdf', $selectedCustomer) }}" target="_blank" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>
            Print SOA
        </a>
        <a href="{{ route('ar.soa.pdf', $selectedCustomer) }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Export CSV
        </a>
    </div>
@else
    {{-- Empty State --}}
    <div class="card">
        <div class="card-body text-center py-16">
            <svg class="w-12 h-12 mx-auto mb-4 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
            <h3 class="text-lg font-medium text-secondary-700 mb-1">No Statement Generated</h3>
            <p class="text-secondary-400">Select a customer to generate their statement of account.</p>
        </div>
    </div>
@endif
@endsection
