@extends('layouts.app')
@section('title', 'General Journal')

@section('content')
<x-page-header title="General Journal" subtitle="Book of original entry - all posted journal entries">
    <x-slot:actions><button onclick="window.print()" class="btn-secondary text-sm">Print</button></x-slot:actions>
</x-page-header>

<x-filter-bar action="{{ route('reports.general-journal') }}">
    <div>
        <label class="form-label">From</label>
        <input type="date" name="date_from" class="form-input w-40" value="{{ $dateFrom }}">
    </div>
    <div>
        <label class="form-label">To</label>
        <input type="date" name="date_to" class="form-input w-40" value="{{ $dateTo }}">
    </div>
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
