@extends('layouts.app')
@section('title', "Statement of Changes in Owner's Equity")

@section('content')
<x-page-header title="Statement of Changes in Owner's Equity" subtitle="Changes in equity for the selected period">
    <x-slot name="actions">
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot>
</x-page-header>

{{-- Filters --}}
<div class="card mb-6 no-print">
    <div class="card-body">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-input w-40">
            </div>
            <div>
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-input w-40">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
        </form>
    </div>
</div>

{{-- Report --}}
<div class="card">
    {{-- Header --}}
    <div class="card-header bg-gray-50 text-center py-4">
        <h2 class="text-sm font-bold uppercase tracking-wide">Statement of Changes in Owner's Equity</h2>
        <p class="text-xs text-secondary-500 mt-0.5">
            For the period {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}
        </p>
    </div>

    <div class="card-body max-w-2xl mx-auto py-6">
        <table class="w-full text-sm">
            <tbody>

                {{-- Opening Balance --}}
                <tr class="border-b border-gray-200">
                    <td class="py-2 text-secondary-700">
                        Owner's equity at {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }}
                    </td>
                    <td class="py-2 text-right font-mono w-36"></td>
                    <td class="py-2 text-right font-mono w-36">{{ number_format($openingEquity, 2) }}</td>
                </tr>

                {{-- ADD section --}}
                <tr>
                    <td class="pt-4 pb-1 font-semibold text-secondary-800" colspan="3">Add:</td>
                </tr>

                {{-- Contributions --}}
                @if($contributions->isNotEmpty())
                    @foreach($contributions as $c)
                    <tr>
                        <td class="py-1 pl-8 text-secondary-600">{{ $c->account_name }}</td>
                        <td class="py-1 text-right font-mono">{{ number_format($c->amount, 2) }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td class="py-1 pl-8 text-secondary-600">Additional capital contributions</td>
                        <td class="py-1 text-right font-mono">0.00</td>
                        <td></td>
                    </tr>
                @endif

                {{-- Net income --}}
                <tr class="border-b border-gray-200">
                    <td class="py-1 pl-8 text-secondary-600">Net income</td>
                    <td class="py-1 text-right font-mono {{ $netIncome < 0 ? 'text-danger-600' : '' }}">
                        {{ number_format($netIncome, 2) }}
                    </td>
                    <td class="py-1 text-right font-mono border-t border-gray-400">
                        {{ number_format($totalContributions + $netIncome, 2) }}
                    </td>
                </tr>

                {{-- Total --}}
                <tr class="border-b border-gray-300">
                    <td class="py-2 font-semibold">Total</td>
                    <td></td>
                    <td class="py-2 text-right font-mono font-semibold">
                        {{ number_format($openingEquity + $totalContributions + $netIncome, 2) }}
                    </td>
                </tr>

                {{-- LESS section --}}
                @if($withdrawals->isNotEmpty())
                <tr>
                    <td class="pt-4 pb-1 font-semibold text-secondary-800" colspan="3">Less:</td>
                </tr>
                @foreach($withdrawals as $w)
                <tr class="border-b border-gray-200">
                    <td class="py-1 pl-8 text-secondary-600">{{ $w->account_name }}</td>
                    <td class="py-1 text-right font-mono">{{ number_format($w->amount, 2) }}</td>
                    <td class="py-1 text-right font-mono border-t border-gray-400">
                        {{ number_format($totalWithdrawals, 2) }}
                    </td>
                </tr>
                @endforeach
                @else
                <tr class="border-b border-gray-200">
                    <td class="py-2 pl-2 text-secondary-600">Less: Withdrawals / Distributions</td>
                    <td></td>
                    <td class="py-2 text-right font-mono">0.00</td>
                </tr>
                @endif

                {{-- Closing Balance --}}
                <tr class="border-t-2 border-double border-secondary-900">
                    <td class="py-3 font-bold text-secondary-900">
                        Owner's equity at {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}
                    </td>
                    <td></td>
                    <td class="py-3 text-right font-mono font-bold text-lg {{ $closingEquity >= 0 ? 'text-secondary-900' : 'text-danger-700' }}">
                        {{ number_format($closingEquity, 2) }}
                    </td>
                </tr>

            </tbody>
        </table>

        {{-- Equity account breakdown --}}
        @if($equityAccounts->isNotEmpty())
        <div class="mt-8 border-t border-gray-200 pt-4">
            <p class="text-xs font-semibold text-secondary-500 uppercase mb-2">Opening Equity Breakdown</p>
            <table class="w-full text-xs text-secondary-600">
                @foreach($equityAccounts as $acct)
                <tr class="hover:bg-gray-50">
                    <td class="py-1 pl-2">{{ $acct->account_code }} — {{ $acct->account_name }}</td>
                    <td class="py-1 text-right font-mono">{{ number_format(abs($acct->balance), 2) }}</td>
                </tr>
                @endforeach
                <tr class="border-t border-gray-300 font-semibold text-secondary-700">
                    <td class="py-1 pl-2">Total Opening Equity</td>
                    <td class="py-1 text-right font-mono">{{ number_format($openingEquity, 2) }}</td>
                </tr>
            </table>
        </div>
        @endif

        {{-- Net income detail --}}
        <div class="mt-4 border-t border-gray-200 pt-4">
            <p class="text-xs font-semibold text-secondary-500 uppercase mb-2">Net Income Detail</p>
            <table class="w-full text-xs text-secondary-600">
                <tr>
                    <td class="py-1 pl-2">Total Revenue</td>
                    <td class="py-1 text-right font-mono text-green-700">{{ number_format($periodRevenue, 2) }}</td>
                </tr>
                <tr class="border-b border-gray-200">
                    <td class="py-1 pl-2">Total Expenses</td>
                    <td class="py-1 text-right font-mono text-danger-600">({{ number_format($periodExpenses, 2) }})</td>
                </tr>
                <tr class="font-semibold text-secondary-700">
                    <td class="py-1 pl-2">Net Income</td>
                    <td class="py-1 text-right font-mono {{ $netIncome < 0 ? 'text-danger-600' : 'text-green-700' }}">
                        {{ number_format($netIncome, 2) }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection
