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
        <label class="form-label">Fees</label>
        <select name="fee_name" class="form-input w-64" onchange="this.form.submit()">
            <option value="">All Fees</option>
            @foreach($feeNames as $fn)
                <option value="{{ $fn }}" {{ $feeName == $fn ? 'selected' : '' }}>{{ $fn }}</option>
            @endforeach
        </select>
    </div>
</x-filter-bar>

<div class="card mt-4">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ACCOUNT NUMBER</th>
                    <th>DESCRIPTION</th>
                    <th>DATE (PAID)</th>
                    <th class="text-right">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                <tr>
                    <td class="font-mono text-xs">{{ $r->account_code }}</td>
                    <td>{{ $r->fee_name }}</td>
                    <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($r->date_paid)->format('M d, Y') }}</td>
                    <td class="text-right">{{ number_format($r->amount, 2) }}</td>
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
                @if($grandTotal !== null)
                <tr class="bg-secondary-100 font-semibold text-sm border-t-4 border-double border-gray-400">
                    <td colspan="3" class="px-3 py-2 text-right text-secondary-700 uppercase tracking-wide text-xs">Grand Total:</td>
                    <td class="px-3 py-2 text-right font-bold">{{ number_format($grandTotal, 2) }}</td>
                </tr>
                @endif
            </tfoot>
            @endif
        </table>
    </div>
    <div class="p-4">
        {{ $records->appends(request()->query())->links() }}
    </div>
</div>
@endsection
