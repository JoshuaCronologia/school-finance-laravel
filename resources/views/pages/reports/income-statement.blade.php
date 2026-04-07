@extends('layouts.app')
@section('title', 'Income Statement')

@section('content')
@php
    $totalRevenue = $totalRevenue ?? 0;
    $totalExpenses = $totalExpenses ?? 0;
    $netIncome = $netIncome ?? ($totalRevenue - $totalExpenses);
    $revenueAccounts = $revenueAccounts ?? collect();
    $expenseAccounts = $expenseAccounts ?? collect();
    $netIncomeMargin = $netIncomeMargin ?? ($totalRevenue > 0 ? ($netIncome / $totalRevenue * 100) : 0);
    $dateFrom = $dateFrom ?? now()->startOfYear()->format('Y-m-d');
    $dateTo = $dateTo ?? now()->format('Y-m-d');
@endphp

<x-page-header title="Income Statement" :subtitle="'For the period ' . \Carbon\Carbon::parse($dateFrom)->format('M d, Y') . ' to ' . \Carbon\Carbon::parse($dateTo)->format('M d, Y')">
    <x-slot name="actions">
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot>
</x-page-header>

<div class="card mb-6">
    <form class="card-body">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-input w-44">
            </div>
            <div>
                <label class="form-label">To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-input w-44">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
        </div>
    </form>
</div>

{{-- Pro Forma Income Statement --}}
<div class="card mb-6">
    <div class="card-header bg-gray-50">
        <div class="text-center w-full">
            <h2 class="text-lg font-bold text-secondary-900">Statement of Income</h2>
            <p class="text-sm text-secondary-500">For the period {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}</p>
        </div>
    </div>
    <div class="card-body max-w-3xl mx-auto">
        {{-- REVENUE --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3">Revenue</h3>
            <table class="w-full text-sm">
                <tbody>
                    @foreach($revenueAccounts as $account)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('reports.general-ledger') }}?account_id={{ $account->id }}&date_from={{ $dateFrom }}&date_to={{ $dateTo }}'">
                        <td class="py-1.5 pl-6">
                            <span class="text-primary-600 hover:underline">{{ $account->account_name }}</span>
                        </td>
                        <td class="py-1.5 text-right font-mono w-40">₱{{ number_format(abs($account->balance), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-300 font-semibold">
                        <td class="py-2 pl-2">Total Revenue</td>
                        <td class="py-2 text-right font-mono">₱{{ number_format($totalRevenue, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- EXPENSES --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3">Expenses</h3>
            <table class="w-full text-sm">
                <tbody>
                    @foreach($expenseAccounts as $account)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('reports.general-ledger') }}?account_id={{ $account->id }}&date_from={{ $dateFrom }}&date_to={{ $dateTo }}'">
                        <td class="py-1.5 pl-6">
                            <span class="text-primary-600 hover:underline">{{ $account->account_name }}</span>
                        </td>
                        <td class="py-1.5 text-right font-mono w-40">₱{{ number_format(abs($account->balance), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-300 font-semibold">
                        <td class="py-2 pl-2">Total Expenses</td>
                        <td class="py-2 text-right font-mono">₱{{ number_format($totalExpenses, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- NET INCOME --}}
        <div class="border-t-2 border-double border-secondary-900 pt-2 mb-4">
            <table class="w-full">
                <tr class="font-bold text-base">
                    <td class="py-2 {{ $netIncome >= 0 ? 'text-green-800' : 'text-red-800' }}">Net Income</td>
                    <td class="py-2 text-right font-mono w-40 {{ $netIncome >= 0 ? 'text-green-800' : 'text-red-800' }}">₱{{ number_format($netIncome, 2) }}</td>
                </tr>
            </table>
        </div>

        <p class="text-xs text-secondary-400 text-center">Click on any account name to view its General Ledger detail.</p>
    </div>
</div>
@endsection
