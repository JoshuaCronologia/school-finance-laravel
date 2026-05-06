@extends('layouts.app')
@section('title', 'General Journal')

@section('content')
<x-page-header title="General Journal" subtitle="Book of original entry - all posted journal entries">
    <x-slot name="actions">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Excel
        </a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
            PDF
        </a>
    </x-slot>
</x-page-header>

<x-filter-bar action="{{ route('reports.general-journal') }}">
</x-filter-bar>

<div class="card">
    <div class="card-header bg-gray-50 text-center">
        <div class="w-full">
            <h2 class="text-sm font-bold text-secondary-900">GENERAL JOURNAL</h2>
            <p class="text-xs text-secondary-500">{{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-28">Date</th>
                    <th class="w-28">Entry #</th>
                    <th class="w-28">Ref No.</th>
                    <th>Account / Description</th>
                    <th class="text-right w-32">Debit</th>
                    <th class="text-right w-32">Credit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $je)
                    {{-- Entry header --}}
                    <tr class="bg-gray-50 border-t-2 border-gray-300">
                        <td class="font-medium">{{ $je->posting_date->format('M d, Y') }}</td>
                        <td><a href="{{ route('gl.journal-entries.show', $je) }}" class="text-primary-600 hover:underline font-mono">{{ $je->entry_number }}</a></td>
                        <td class="font-mono text-sm text-secondary-500">{{ $je->reference_number ?? '' }}</td>
                        <td class="font-medium text-secondary-700">{{ $je->description }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                    {{-- Debit lines --}}
                    @foreach($je->lines->where('debit', '>', 0) as $line)
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="pl-6 text-sm">{{ $line->account->account_code ?? '' }} - {{ $line->account->account_name ?? '' }}</td>
                        <td class="text-right font-mono">₱{{ number_format($line->debit, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    {{-- Credit lines (indented) --}}
                    @foreach($je->lines->where('credit', '>', 0) as $line)
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="pl-12 text-sm italic text-secondary-600">{{ $line->account->account_code ?? '' }} - {{ $line->account->account_name ?? '' }}</td>
                        <td></td>
                        <td class="text-right font-mono">₱{{ number_format($line->credit, 2) }}</td>
                    </tr>
                    @endforeach
                @empty
                <tr><td colspan="6" class="text-center text-secondary-400 py-8">No journal entries for this period.</td></tr>
                @endforelse
            </tbody>
            @if($entries->count() > 0)
            <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-400">
                <tr>
                    <td colspan="4" class="text-right">Grand Total:</td>
                    <td class="text-right font-mono">₱{{ number_format($totalDebit, 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($totalCredit, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
