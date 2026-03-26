@extends('layouts.app')
@section('title', 'Supplier Payments')

@section('content')
<x-page-header title="Supplier Payments" subtitle="View and manage all processed payments">
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="card p-4">
        <p class="text-xs font-medium text-secondary-500 uppercase">Total Paid</p>
        <p class="text-xl font-bold text-secondary-900 mt-1">@currency($totalPaid)</p>
    </div>
    <div class="card p-4">
        <p class="text-xs font-medium text-secondary-500 uppercase">Total Voided</p>
        <p class="text-xl font-bold text-danger-600 mt-1">@currency($totalVoided)</p>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-6">
    <form method="GET" action="{{ route('ap.supplier-payments') }}" class="p-4">
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-input" placeholder="Voucher #, Request #, Payee..." value="{{ request('search') }}">
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    <option value="">All</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>Voided</option>
                </select>
            </div>
            <div>
                <label class="form-label">Method</label>
                <select name="method" class="form-input">
                    <option value="">All</option>
                    <option value="check" {{ request('method') === 'check' ? 'selected' : '' }}>Check</option>
                    <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="online" {{ request('method') === 'online' ? 'selected' : '' }}>Online</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Filter</button>
            @if(request()->hasAny(['search', 'status', 'method']))
                <a href="{{ route('ap.supplier-payments') }}" class="btn-secondary">Clear</a>
            @endif
        </div>
    </form>
</div>

{{-- Payment History Table --}}
<div class="card">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-700">Payment History</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Voucher #</th>
                    <th>Date</th>
                    <th>Request #</th>
                    <th>Payee</th>
                    <th>Department</th>
                    <th class="text-right">Gross</th>
                    <th class="text-right">WHT</th>
                    <th class="text-right">Net Paid</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th class="w-20">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td class="font-medium">{{ $payment->voucher_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</td>
                    <td>
                        <a href="{{ route('ap.disbursements.show', $payment->disbursement_id) }}" class="text-primary-600 hover:text-primary-700 hover:underline">
                            {{ $payment->disbursement->request_number ?? '-' }}
                        </a>
                    </td>
                    <td>{{ $payment->payee_name ?? $payment->disbursement->payee_name ?? '-' }}</td>
                    <td>{{ $payment->disbursement->department->name ?? '-' }}</td>
                    <td class="text-right">@currency($payment->gross_amount)</td>
                    <td class="text-right">@currency($payment->withholding_tax ?? 0)</td>
                    <td class="text-right font-medium">@currency($payment->net_amount)</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? '-')) }}</td>
                    <td><x-badge :status="$payment->status ?? 'completed'" /></td>
                    <td>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('ap.payments.print', $payment) }}" class="btn-icon text-secondary-500 hover:text-secondary-700" title="Print Voucher" target="_blank">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M9.75 21h4.5" /></svg>
                            </a>
                            @if($payment->status !== 'voided')
                            <form method="POST" action="{{ route('ap.payments.void', $payment) }}" onsubmit="return confirm('Are you sure you want to void this payment?');">
                                @csrf
                                <button type="submit" class="btn-icon text-danger-500 hover:text-danger-700" title="Void Payment">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center text-secondary-400 py-8">
                        <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                        No payments found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
    <div class="card-footer">
        {{ $payments->links() }}
    </div>
    @endif
</div>
@endsection
