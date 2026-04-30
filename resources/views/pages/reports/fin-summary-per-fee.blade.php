@extends('layouts.app')
@section('title', 'Summary of Collection Per Fee')

@section('content')
<x-page-header title="Summary of Collection Per Fee Report" subtitle="Collections broken down by fee type">
    <x-slot name="actions">
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot>
</x-page-header>

<x-filter-bar action="{{ route('reports.fin.summary-per-fee') }}">
    <div>
        <label class="form-label">Date Range</label>
        <div class="flex items-center gap-2">
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-input" onchange="this.form.submit()">
            <span class="text-secondary-400">—</span>
            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-input" onchange="this.form.submit()">
        </div>
    </div>
    <div>
        <label class="form-label">Fees</label>
        <select name="fee_name" class="form-input w-64" onchange="this.form.submit()">
            <option value="">All Fees</option>
            @foreach($feeNames as $fn)
                <option value="{{ $fn }}" {{ $feeName == $fn ? 'selected' : '' }}>{{ $fn }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex items-end">
        <button type="submit" class="btn-primary text-sm">Filter</button>
    </div>
</x-filter-bar>

<div class="card mt-4">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-primary-800 text-white">
                    <th class="px-3 py-2 text-left whitespace-nowrap">ACCOUNT NUMBER</th>
                    <th class="px-3 py-2 text-left whitespace-nowrap">DESCRIPTION</th>
                    <th class="px-3 py-2 text-left whitespace-nowrap">DATE (PAID)</th>
                    <th class="px-3 py-2 text-right whitespace-nowrap">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                <tr class="border-b border-secondary-100 hover:bg-secondary-50">
                    <td class="px-3 py-2 font-mono text-xs">{{ $r->account_code }}</td>
                    <td class="px-3 py-2">{{ $r->fee_name }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($r->date_paid)->format('M d, Y') }}</td>
                    <td class="px-3 py-2 text-right">{{ number_format($r->amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-3 py-6 text-center text-secondary-400">No records found for the selected period.</td></tr>
                @endforelse
            </tbody>
            @if($records->isNotEmpty())
            <tfoot>
                <tr class="bg-secondary-50 font-semibold">
                    <td colspan="3" class="px-3 py-2 text-right">Page Total:</td>
                    <td class="px-3 py-2 text-right">{{ number_format($records->sum('amount'), 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    <div class="p-4">
        {{ $records->appends(request()->query())->links() }}
    </div>
</div>
@endsection
