@extends('layouts.app')
@section('title', 'Collections / Official Receipts')

@section('content')
@php
    $collectionItems = $collections instanceof \Illuminate\Pagination\LengthAwarePaginator ? $collections->items() : $collections;
    $totalCollected = collect($collectionItems)->sum('amount_received');
    $receiptsCount = $collections instanceof \Illuminate\Pagination\LengthAwarePaginator ? $collections->total() : count($collections);
@endphp

<x-page-header title="Collections / Official Receipts" :subtitle="$receiptsCount . ' receipts'">
    <x-slot:actions>
        <button @click="$dispatch('open-modal', 'new-collection')" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            New Collection
        </button>
    </x-slot:actions>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Stat Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <x-stat-card label="Total Collected" :value="'₱' . number_format($totalCollected, 2)" color="green"
        icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>' />

    <x-stat-card label="Receipts Count" :value="number_format($receiptsCount)" color="blue"
        icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>' />
</div>

{{-- Search --}}
<div class="card mb-6">
    <div class="card-body">
        <form action="{{ route('ar.collections.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="form-label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="Search by OR #, customer, reference...">
            </div>
            <div>
                <label class="form-label">Payment Method</label>
                <select name="method" class="form-input w-40">
                    <option value="">All Methods</option>
                    @foreach(['cash', 'check', 'bank_transfer', 'online'] as $m)
                        <option value="{{ $m }}" {{ request('method') == $m ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $m)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('ar.collections.index') }}" class="btn-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

{{-- Collections Table --}}
<x-data-table search-placeholder="Search receipts...">
    <thead>
        <tr>
            <th>OR #</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Method</th>
            <th class="text-right">Amount Received</th>
            <th class="text-right">Applied</th>
            <th class="text-right">Unapplied</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($collections as $collection)
        <tr>
            <td class="font-medium text-secondary-900">{{ $collection->or_number ?? '-' }}</td>
            <td>{{ \Carbon\Carbon::parse($collection->collection_date)->format('M d, Y') }}</td>
            <td>{{ $collection->customer->name ?? $collection->customer_name ?? '-' }}</td>
            <td>
                @php
                    $methodBadge = match($collection->payment_method ?? '') {
                        'cash' => 'badge-success',
                        'check' => 'badge-warning',
                        'bank_transfer' => 'badge-info',
                        default => 'badge-neutral',
                    };
                @endphp
                <span class="badge {{ $methodBadge }}">{{ ucfirst(str_replace('_', ' ', $collection->payment_method ?? '-')) }}</span>
            </td>
            <td class="text-right font-medium">{{ '₱' . number_format($collection->amount_received ?? 0, 2) }}</td>
            <td class="text-right">{{ '₱' . number_format($collection->amount_applied ?? 0, 2) }}</td>
            <td class="text-right {{ ($collection->amount_unapplied ?? 0) > 0 ? 'text-warning-600' : '' }}">{{ '₱' . number_format($collection->amount_unapplied ?? 0, 2) }}</td>
            <td><x-badge :status="$collection->status ?? 'posted'" /></td>
            <td>
                <a href="{{ route('ar.collections.print', $collection) }}" target="_blank" class="text-secondary-400 hover:text-secondary-600" title="Print Receipt">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>
                </a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center text-secondary-400 py-8">
                <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                No collections found. Click "+ New Collection" to record one.
            </td>
        </tr>
        @endforelse
    </tbody>
    @if($collections instanceof \Illuminate\Pagination\LengthAwarePaginator && $collections->hasPages())
    <x-slot:footer>
        {{ $collections->withQueryString()->links() }}
    </x-slot:footer>
    @endif
</x-data-table>

{{-- New Collection Modal --}}
<x-modal name="new-collection" title="New Collection / Official Receipt" maxWidth="4xl">
    <form action="{{ route('ar.collections.store') }}" method="POST" data-turbo="false" v-pre x-data="{
        method: 'cash',
        openInvoices: @js($openInvoices ?? []),
        applications: [],
        amountReceived: 0,
        get totalApplied() { return this.applications.reduce((s, a) => s + parseFloat(a.amount || 0), 0); },
        get unapplied() { return parseFloat(this.amountReceived || 0) - this.totalApplied; },
        toggleInvoice(inv, checked) {
            if (checked) {
                this.applications.push({ invoice_id: inv.id, invoice_number: inv.invoice_number, balance: inv.balance, amount: inv.balance });
            } else {
                this.applications = this.applications.filter(a => a.invoice_id !== inv.id);
            }
        },
        isSelected(invId) { return this.applications.some(a => a.invoice_id === invId); }
    }">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="form-label">Customer <span class="text-danger-500">*</span></label>
                <select name="customer_id" class="form-input" required>
                    <option value="">Select Customer</option>
                    @foreach($customers ?? [] as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Collection Date <span class="text-danger-500">*</span></label>
                <input type="date" name="collection_date" class="form-input" value="{{ date('Y-m-d') }}" required>
            </div>
            <div>
                <label class="form-label">Payment Method <span class="text-danger-500">*</span></label>
                <select name="payment_method" x-model="method" class="form-input" required>
                    <option value="cash">Cash</option>
                    <option value="check">Check</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="online">Online Payment</option>
                </select>
            </div>
            <div>
                <label class="form-label">Bank Account</label>
                <select name="bank_account_id" class="form-input">
                    <option value="">Select</option>
                    @foreach($bankAccounts ?? [] as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="method === 'check'" x-transition>
                <label class="form-label">Check Number</label>
                <input type="text" name="check_number" class="form-input" placeholder="Check #">
            </div>
            <div>
                <label class="form-label">Reference</label>
                <input type="text" name="reference" class="form-input" placeholder="Transaction reference">
            </div>
            <div>
                <label class="form-label">Amount Received <span class="text-danger-500">*</span></label>
                <input type="number" name="amount_received" x-model="amountReceived" class="form-input" step="0.01" min="0" required>
            </div>
        </div>

        {{-- Apply to Invoices --}}
        <div class="mb-4">
            <h4 class="text-sm font-semibold text-secondary-700 mb-2">Apply to Invoices</h4>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="py-2 px-3 text-left w-10"></th>
                            <th class="py-2 px-3 text-left font-medium text-secondary-600">Invoice #</th>
                            <th class="py-2 px-3 text-right font-medium text-secondary-600">Balance</th>
                            <th class="py-2 px-3 text-right font-medium text-secondary-600 w-40">Apply Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(inv, idx) in openInvoices" :key="inv.id">
                            <tr class="border-b border-gray-100">
                                <td class="py-2 px-3">
                                    <input type="checkbox" :checked="isSelected(inv.id)" @change="toggleInvoice(inv, $event.target.checked)" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                </td>
                                <td class="py-2 px-3 font-medium" x-text="inv.invoice_number"></td>
                                <td class="py-2 px-3 text-right" x-text="'₱' + parseFloat(inv.balance).toFixed(2)"></td>
                                <td class="py-2 px-3">
                                    <template x-if="isSelected(inv.id)">
                                        <input type="number" :name="'applications['+idx+'][amount]'" x-model="applications.find(a => a.invoice_id === inv.id).amount" class="form-input text-sm text-right w-full" step="0.01" min="0" :max="inv.balance">
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div x-show="openInvoices.length === 0" class="text-center text-secondary-400 py-4 text-sm">
                    No open invoices for this customer.
                </div>
            </div>
            <div class="flex justify-end gap-6 mt-3 text-sm">
                <span class="text-secondary-600">Applied: <strong x-text="'₱' + totalApplied.toFixed(2)"></strong></span>
                <span :class="unapplied < 0 ? 'text-danger-500' : 'text-secondary-600'">Unapplied: <strong x-text="'₱' + unapplied.toFixed(2)"></strong></span>
            </div>
        </div>

        <div>
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-input" rows="2" placeholder="Additional notes..."></textarea>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'new-collection')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save Collection</button>
        </div>
    </form>
</x-modal>
@endsection
