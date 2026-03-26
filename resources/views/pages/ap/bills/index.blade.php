@extends('layouts.app')
@section('title', 'Supplier Bills')

@section('content')
@php
    $billCount = $bills instanceof \Illuminate\Pagination\LengthAwarePaginator ? $bills->total() : count($bills);
    $totalNet = $bills instanceof \Illuminate\Pagination\LengthAwarePaginator ? $bills->sum('net_payable') : collect($bills)->sum('net_payable');
@endphp

<x-page-header title="Supplier Bills" :subtitle="$billCount . ' bills · Total Net Payable: ₱' . number_format($totalNet, 2)">
    <x-slot:actions>
        <a href="{{ route('ap.bills.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            New Bill
        </a>
    </x-slot:actions>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Filters --}}
<x-filter-bar action="{{ route('ap.bills.index') }}" method="GET">
    <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-input w-44">
            <option value="">All Status</option>
            @foreach(['draft', 'posted', 'paid', 'partially_paid', 'overdue', 'voided'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label">Vendor</label>
        <select name="vendor_id" class="form-input w-48">
            <option value="">All Vendors</option>
            @foreach($vendors ?? [] as $vendor)
                <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
            @endforeach
        </select>
    </div>
</x-filter-bar>

{{-- Bills Table --}}
<x-data-table search-placeholder="Search bills...">
    <thead>
        <tr>
            <th>Bill #</th>
            <th>Date</th>
            <th>Vendor</th>
            <th>Description</th>
            <th class="text-right">Gross</th>
            <th class="text-right">VAT</th>
            <th class="text-right">WHT</th>
            <th class="text-right">Net Payable</th>
            <th class="text-right">Balance</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($bills as $bill)
        <tr>
            <td class="font-medium">
                <a href="{{ route('ap.bills.show', $bill) }}" class="text-primary-600 hover:text-primary-700 hover:underline">
                    {{ $bill->bill_number }}
                </a>
            </td>
            <td>{{ \Carbon\Carbon::parse($bill->bill_date)->format('M d, Y') }}</td>
            <td>{{ $bill->vendor->name ?? $bill->vendor_name ?? '-' }}</td>
            <td class="max-w-xs truncate">{{ $bill->description ?? '-' }}</td>
            <td class="text-right">@currency($bill->gross_amount ?? 0)</td>
            <td class="text-right">@currency($bill->vat_amount ?? 0)</td>
            <td class="text-right">@currency($bill->withholding_tax ?? 0)</td>
            <td class="text-right font-medium">@currency($bill->net_payable ?? 0)</td>
            <td class="text-right font-medium">@currency($bill->balance ?? 0)</td>
            <td><x-badge :status="$bill->status" /></td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center text-secondary-400 py-8">
                <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                No bills found. Click "+ New Bill" to create one.
            </td>
        </tr>
        @endforelse
    </tbody>
    @if($bills instanceof \Illuminate\Pagination\LengthAwarePaginator && $bills->hasPages())
    <x-slot:footer>
        {{ $bills->withQueryString()->links() }}
    </x-slot:footer>
    @endif
</x-data-table>
@endsection
