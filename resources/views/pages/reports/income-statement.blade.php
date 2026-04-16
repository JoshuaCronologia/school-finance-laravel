@extends('layouts.app')
@section('title', 'Income Statement')

@section('content')
@php
    $totalRevenue = $totalRevenue ?? 0;
    $totalExpenses = $totalExpenses ?? 0;
    $netIncome = $netIncome ?? ($totalRevenue - $totalExpenses);
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

{{-- Print Header --}}
<div class="print-only" style="display: none;">
    <div style="text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #333;">
        <h1 style="font-size: 16px; font-weight: bold; margin: 0;">{{ \App\Models\Setting::where('key', 'school_name')->value('value') ?? 'OrangeApps School Finance ERP' }}</h1>
        <h2 style="font-size: 14px; font-weight: bold; margin: 8px 0 4px;">STATEMENT OF INCOME</h2>
        <p style="font-size: 11px; color: #555;">For the period {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}</p>
    </div>
</div>

{{-- Income Statement --}}
<div class="card mb-6">
    <div class="card-header bg-gray-50 no-print">
        <div class="text-center w-full">
            <h2 class="text-lg font-bold text-secondary-900">Statement of Income</h2>
            <p class="text-sm text-secondary-500">For the period {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}</p>
        </div>
    </div>
    <div class="card-body max-w-3xl mx-auto">
        {{-- REVENUE --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3 cursor-pointer" onclick="document.querySelectorAll('.is-revenue-body').forEach(function(el){el.style.display=el.style.display==='none'?'':'none'}); var a=document.getElementById('is-arrow-rev');a.style.transform=a.style.transform?'':'rotate(90deg)'">
                <span class="inline-flex items-center gap-1">
                    <svg id="is-arrow-rev" class="w-4 h-4 text-secondary-400 transition-transform" style="transform:rotate(90deg)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    Revenue
                </span>
            </h3>
            <table class="w-full text-sm is-revenue-body">
                <tbody>
                    {{-- Grouped revenue (collapsible) --}}
                    @foreach($revenueGroups ?? [] as $rgi => $group)
                    @if(count($group->children) > 0)
                    <tr class="font-semibold cursor-pointer hover:bg-gray-50" onclick="document.querySelectorAll('.is-rev-{{ $rgi }}').forEach(function(el){el.style.display=el.style.display==='none'?'':'none'}); var a=document.getElementById('is-arrow-r{{ $rgi }}');a.style.transform=a.style.transform?'':'rotate(90deg)'">
                        <td class="py-2 pl-2">
                            <span class="inline-flex items-center gap-1">
                                <svg id="is-arrow-r{{ $rgi }}" class="w-3.5 h-3.5 text-secondary-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                {{ $group->account_name }}
                                <span class="text-xs text-secondary-400 font-normal">({{ count($group->children) }})</span>
                            </span>
                        </td>
                        <td class="py-2 text-right font-mono w-40">₱{{ number_format($group->total, 2) }}</td>
                    </tr>
                    @foreach($group->children as $child)
                    <tr class="is-rev-{{ $rgi }} hover:bg-blue-50/50 cursor-pointer" style="display:none" onclick="window.location='{{ route('gl.accounts.show', $child->id) }}'">
                        <td class="py-1 pl-10 text-secondary-600">{{ $child->account_name }}</td>
                        <td class="py-1 text-right font-mono w-40 text-secondary-600">₱{{ number_format(abs($child->balance), 2) }}</td>
                    </tr>
                    @endforeach
                    @else
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('gl.accounts.show', $group->id) }}'">
                        <td class="py-1.5 pl-6"><span class="text-primary-600 hover:underline">{{ $group->account_name }}</span></td>
                        <td class="py-1.5 text-right font-mono w-40">₱{{ number_format($group->total, 2) }}</td>
                    </tr>
                    @endif
                    @endforeach

                    {{-- Standalone revenue accounts --}}
                    @foreach($standaloneRevenue ?? [] as $account)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('gl.accounts.show', $account->id) }}'">
                        <td class="py-1.5 pl-6"><span class="text-primary-600 hover:underline">{{ $account->account_name }}</span></td>
                        <td class="py-1.5 text-right font-mono w-40">₱{{ number_format(abs($account->balance), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                </tbody>
            </table>
            <table class="w-full text-sm">
                <tr class="border-t border-gray-300 font-semibold">
                    <td class="py-2 pl-2">Total Revenue</td>
                    <td class="py-2 text-right font-mono w-40">₱{{ number_format($totalRevenue, 2) }}</td>
                </tr>
            </table>
        </div>

        {{-- EXPENSES --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3 cursor-pointer" onclick="document.querySelectorAll('.is-expense-body').forEach(function(el){el.style.display=el.style.display==='none'?'':'none'}); var a=document.getElementById('is-arrow-exp');a.style.transform=a.style.transform?'':'rotate(90deg)'">
                <span class="inline-flex items-center gap-1">
                    <svg id="is-arrow-exp" class="w-4 h-4 text-secondary-400 transition-transform" style="transform:rotate(90deg)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    Expenses
                </span>
            </h3>
            <table class="w-full text-sm is-expense-body">
                <tbody>
                    {{-- Grouped expenses (collapsible) --}}
                    @foreach($expenseGroups ?? [] as $egi => $group)
                    @if(count($group->children) > 0)
                    <tr class="font-semibold cursor-pointer hover:bg-gray-50" onclick="document.querySelectorAll('.is-exp-{{ $egi }}').forEach(function(el){el.style.display=el.style.display==='none'?'':'none'}); var a=document.getElementById('is-arrow-e{{ $egi }}');a.style.transform=a.style.transform?'':'rotate(90deg)'">
                        <td class="py-2 pl-2">
                            <span class="inline-flex items-center gap-1">
                                <svg id="is-arrow-e{{ $egi }}" class="w-3.5 h-3.5 text-secondary-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                {{ $group->account_name }}
                                <span class="text-xs text-secondary-400 font-normal">({{ count($group->children) }})</span>
                            </span>
                        </td>
                        <td class="py-2 text-right font-mono w-40">₱{{ number_format($group->total, 2) }}</td>
                    </tr>
                    @foreach($group->children as $child)
                    <tr class="is-exp-{{ $egi }} hover:bg-blue-50/50 cursor-pointer" style="display:none" onclick="window.location='{{ route('gl.accounts.show', $child->id) }}'">
                        <td class="py-1 pl-10 text-secondary-600">{{ $child->account_name }}</td>
                        <td class="py-1 text-right font-mono w-40 text-secondary-600">₱{{ number_format(abs($child->balance), 2) }}</td>
                    </tr>
                    @endforeach
                    @else
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('gl.accounts.show', $group->id) }}'">
                        <td class="py-1.5 pl-6"><span class="text-primary-600 hover:underline">{{ $group->account_name }}</span></td>
                        <td class="py-1.5 text-right font-mono w-40">₱{{ number_format($group->total, 2) }}</td>
                    </tr>
                    @endif
                    @endforeach

                    {{-- Standalone expense accounts --}}
                    @foreach($standaloneExpense ?? [] as $account)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('gl.accounts.show', $account->id) }}'">
                        <td class="py-1.5 pl-6"><span class="text-primary-600 hover:underline">{{ $account->account_name }}</span></td>
                        <td class="py-1.5 text-right font-mono w-40">₱{{ number_format(abs($account->balance), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <table class="w-full text-sm">
                <tr class="border-t border-gray-300 font-semibold">
                    <td class="py-2 pl-2">Total Expenses</td>
                    <td class="py-2 text-right font-mono w-40">₱{{ number_format($totalExpenses, 2) }}</td>
                </tr>
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

        <p class="text-xs text-secondary-400 text-center no-print">Click on any account name to view details. Click group headers to expand/collapse.</p>
    </div>
</div>

{{-- Print Signature --}}
<div class="print-only" style="display: none;">
    <div style="margin-top: 40px; display: flex; justify-content: space-between; font-size: 10px;">
        <div style="text-align: center; width: 200px;"><div style="border-top: 1px solid #333; padding-top: 5px;">Prepared By</div></div>
        <div style="text-align: center; width: 200px;"><div style="border-top: 1px solid #333; padding-top: 5px;">Reviewed By</div></div>
        <div style="text-align: center; width: 200px;"><div style="border-top: 1px solid #333; padding-top: 5px;">Approved By</div></div>
    </div>
</div>
@endsection
