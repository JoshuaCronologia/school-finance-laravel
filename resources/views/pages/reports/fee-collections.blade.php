@extends('layouts.app')
@section('title', 'Fee Collections Report')

@section('content')
<x-page-header title="Fee Collections Report" subtitle="Summary of fee collections from Finance system">
    <x-slot name="actions">
        <a href="{{ route('reports.fee-receipts') }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
            View Receipts
        </a>
        <a href="{{ route('reports.fee-account-mappings') }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m9.86-2.54a4.5 4.5 0 0 0-1.242-7.244l4.5-4.5a4.5 4.5 0 0 1 6.364 6.364l-1.757 1.757" /></svg>
            Fee Mappings
        </a>
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot>
</x-page-header>

<x-filter-bar action="{{ route('reports.fee-collections') }}">
    <div>
        <label class="form-label">School Year</label>
        <select name="school_year" class="form-input w-48" onchange="this.form.submit()">
            @foreach($schoolYears as $sy)
                <option value="{{ $sy->year_fr }}" {{ $selectedYear == $sy->year_fr ? 'selected' : '' }}>
                    SY {{ $sy->year_fr }}-{{ $sy->year_to }}
                </option>
            @endforeach
        </select>
    </div>
</x-filter-bar>

{{-- Summary Cards (hidden on print) --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6 no-print">
    <x-stat-card label="Total Collected" value="{{ '₱' . number_format($totalCollected, 2) }}" color="green">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75" /></svg></x-slot>
    </x-stat-card>
    <x-stat-card label="Total Transactions" value="{{ number_format($totalTransactions) }}" color="blue">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg></x-slot>
    </x-stat-card>
    <x-stat-card label="Fee Types" value="{{ $feeSummary->count() }}" color="purple">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" /></svg></x-slot>
    </x-stat-card>
</div>

{{-- Print Header (only visible on print) --}}
<div class="print-only" style="display: none;">
    <div style="text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #333;">
        <h1 style="font-size: 16px; font-weight: bold; margin: 0;">{{ \App\Models\Setting::where('key', 'school_name')->value('value') ?? 'OrangeApps School Finance ERP' }}</h1>
        <h2 style="font-size: 14px; font-weight: bold; margin: 8px 0 4px;">FEE COLLECTIONS SUMMARY REPORT</h2>
        <p style="font-size: 11px; color: #555; margin: 0;">School Year {{ $selectedYear }}-{{ $selectedYear + 1 }}</p>
        <p style="font-size: 10px; color: #888; margin: 4px 0 0;">Generated: {{ now()->format('F d, Y - h:i A') }}</p>
    </div>
    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 11px;">
        <div><strong>Total Collected:</strong> {{ '₱' . number_format($totalCollected, 2) }}</div>
        <div><strong>Total Transactions:</strong> {{ number_format($totalTransactions) }}</div>
        <div><strong>Fee Types:</strong> {{ $feeSummary->count() }}</div>
    </div>
</div>

{{-- Fee Breakdown Table --}}
<div class="card">
    <div class="card-header bg-gray-50 text-center no-print">
        <div class="w-full">
            <h2 class="text-sm font-bold text-secondary-900">FEE COLLECTIONS SUMMARY</h2>
            <p class="text-xs text-secondary-500">School Year {{ $selectedYear }}-{{ $selectedYear + 1 }}</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-12">#</th>
                    <th>Fee Name</th>
                    <th class="text-right w-32">Transactions</th>
                    <th class="text-right w-40">Total Collected</th>
                    <th class="text-right w-28">% Share</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feeSummary as $i => $fee)
                <tr>
                    <td class="text-secondary-400">{{ $i + 1 }}</td>
                    <td class="font-medium">{{ $fee->fee_name ?? 'Unknown Fee' }}</td>
                    <td class="text-right">{{ number_format($fee->txn_count) }}</td>
                    <td class="text-right font-medium">{{ '₱' . number_format($fee->total_amount, 2) }}</td>
                    <td class="text-right text-secondary-500">{{ $totalCollected > 0 ? number_format(($fee->total_amount / $totalCollected) * 100, 1) : 0 }}%</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-secondary-400 py-8">No fee collections found for this school year.</td></tr>
                @endforelse
            </tbody>
            @if($feeSummary->count() > 0)
            <tfoot style="background: #f3f4f6; font-weight: bold; border-top: 2px solid #999;">
                <tr>
                    <td colspan="2" class="text-right">Total:</td>
                    <td class="text-right">{{ number_format($totalTransactions) }}</td>
                    <td class="text-right">{{ '₱' . number_format($totalCollected, 2) }}</td>
                    <td class="text-right">100%</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- Print Footer (only visible on print) --}}
<div class="print-only" style="display: none;">
    <div style="margin-top: 40px; display: flex; justify-content: space-between; font-size: 10px;">
        <div style="text-align: center; width: 200px;">
            <div style="border-top: 1px solid #333; padding-top: 5px;">Prepared By</div>
        </div>
        <div style="text-align: center; width: 200px;">
            <div style="border-top: 1px solid #333; padding-top: 5px;">Reviewed By</div>
        </div>
        <div style="text-align: center; width: 200px;">
            <div style="border-top: 1px solid #333; padding-top: 5px;">Approved By</div>
        </div>
    </div>
</div>
@endsection
