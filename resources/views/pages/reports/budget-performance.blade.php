@extends('layouts.app')
@section('title', 'Budget Performance Report')

@section('content')
<x-page-header title="Budget Performance Report" subtitle="Revenue & Expenses with Budget vs Actual">
    <x-slot name="actions"><button onclick="window.print()" class="btn-secondary text-sm">Print</button></x-slot>
</x-page-header>

<x-filter-bar action="{{ route('reports.budget-performance') }}">
</x-filter-bar>

{{-- Print Header --}}
<div class="print-only" style="display: none;">
    <div style="text-align: center; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 2px solid #333;">
        <h1 style="font-size: 16px; font-weight: bold; margin: 0;">{{ \App\Models\Setting::where('key', 'school_name')->value('value') ?? 'OrangeApps School Finance ERP' }}</h1>
        <h2 style="font-size: 14px; font-weight: bold; margin: 6px 0 3px;">BUDGET PERFORMANCE REPORT</h2>
        <p style="font-size: 11px; color: #555;">{{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}</p>
    </div>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6 no-print">
    <x-stat-card label="Total Revenue" value="{{ '₱' . number_format($totalRevenue, 2) }}" color="green">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot>
    </x-stat-card>
    <x-stat-card label="Total Expenses" value="{{ '₱' . number_format($totalExpenses, 2) }}" color="red">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898" /></svg></x-slot>
    </x-stat-card>
    <x-stat-card label="Total Budget" value="{{ '₱' . number_format($totalBudget, 2) }}" color="blue">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5" /></svg></x-slot>
    </x-stat-card>
    <x-stat-card label="Net Income" value="{{ '₱' . number_format($netIncome, 2) }}" color="{{ $netIncome >= 0 ? 'green' : 'red' }}">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot>
    </x-stat-card>
</div>

<div class="card mb-6">
    <div class="card-header bg-gray-50 no-print">
        <div class="text-center w-full">
            <h2 class="text-lg font-bold text-secondary-900">Budget Performance Report</h2>
            <p class="text-sm text-secondary-500">{{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}</p>
        </div>
    </div>
    <div class="card-body max-w-4xl mx-auto">
        {{-- REVENUE --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3 cursor-pointer" onclick="document.querySelectorAll('.bp-rev-body').forEach(function(el){el.style.display=el.style.display==='none'?'':'none'}); var a=document.getElementById('bp-arrow-rev');a.style.transform=a.style.transform?'':'rotate(90deg)'">
                <span class="inline-flex items-center gap-1">
                    <svg id="bp-arrow-rev" class="w-4 h-4 text-secondary-400 transition-transform" style="transform:rotate(90deg)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    Revenue
                </span>
            </h3>
            <table class="w-full text-sm bp-rev-body">
                <tbody>
                    @foreach($revenueGroups['groups'] ?? [] as $rgi => $group)
                    @if(count($group->children) > 0)
                    <tr class="font-semibold cursor-pointer hover:bg-gray-50" onclick="document.querySelectorAll('.bp-rev-{{ $rgi }}').forEach(function(el){el.style.display=el.style.display==='none'?'':'none'}); var a=document.getElementById('bp-arrow-rv{{ $rgi }}');a.style.transform=a.style.transform?'':'rotate(90deg)'">
                        <td class="py-2 pl-2">
                            <span class="inline-flex items-center gap-1">
                                <svg id="bp-arrow-rv{{ $rgi }}" class="w-3.5 h-3.5 text-secondary-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                {{ $group->account_name }}
                                <span class="text-xs text-secondary-400 font-normal">({{ count($group->children) }})</span>
                            </span>
                        </td>
                        <td class="py-2 text-right font-mono w-40">₱{{ number_format($group->total, 2) }}</td>
                    </tr>
                    @foreach($group->children as $child)
                    <tr class="bp-rev-{{ $rgi }} hover:bg-blue-50/50 cursor-pointer" style="display:none" onclick="window.location='{{ route('gl.accounts.show', $child->id) }}'">
                        <td class="py-1 pl-10 text-secondary-600">{{ $child->account_name }}</td>
                        <td class="py-1 text-right font-mono w-40 text-secondary-600">₱{{ number_format(abs($child->balance), 2) }}</td>
                    </tr>
                    @endforeach
                    @else
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('gl.accounts.show', $group->id) }}'">
                        <td class="py-1.5 pl-6">{{ $group->account_name }}</td>
                        <td class="py-1.5 text-right font-mono w-40">₱{{ number_format($group->total, 2) }}</td>
                    </tr>
                    @endif
                    @endforeach
                    @foreach($revenueGroups['standalone'] ?? [] as $account)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('gl.accounts.show', $account->id) }}'">
                        <td class="py-1.5 pl-6">{{ $account->account_name }}</td>
                        <td class="py-1.5 text-right font-mono w-40">₱{{ number_format(abs($account->balance), 2) }}</td>
                    </tr>
                    @endforeach
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
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3 cursor-pointer" onclick="document.querySelectorAll('.bp-exp-body').forEach(function(el){el.style.display=el.style.display==='none'?'':'none'}); var a=document.getElementById('bp-arrow-exp');a.style.transform=a.style.transform?'':'rotate(90deg)'">
                <span class="inline-flex items-center gap-1">
                    <svg id="bp-arrow-exp" class="w-4 h-4 text-secondary-400 transition-transform" style="transform:rotate(90deg)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    Expenses
                </span>
            </h3>
            <table class="w-full text-sm bp-exp-body">
                <tbody>
                    @foreach($expenseAccounts as $account)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('gl.accounts.show', $account->id) }}'">
                        <td class="py-1.5 pl-6">{{ $account->account_name }}</td>
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
        <div class="border-t-2 border-double border-secondary-900 pt-2 mb-6">
            <table class="w-full">
                <tr class="font-bold text-base">
                    <td class="py-2 {{ $netIncome >= 0 ? 'text-green-800' : 'text-red-800' }}">Net Income</td>
                    <td class="py-2 text-right font-mono w-40 {{ $netIncome >= 0 ? 'text-green-800' : 'text-red-800' }}">₱{{ number_format($netIncome, 2) }}</td>
                </tr>
            </table>
        </div>

        {{-- BUDGET VS ACTUAL --}}
        <div class="mb-6">
            <h3 class="text-sm font-bold text-secondary-900 uppercase border-b-2 border-secondary-900 pb-1 mb-3 cursor-pointer" onclick="document.querySelectorAll('.bp-budget-body').forEach(function(el){el.style.display=el.style.display==='none'?'':'none'}); var a=document.getElementById('bp-arrow-bgt');a.style.transform=a.style.transform?'':'rotate(90deg)'">
                <span class="inline-flex items-center gap-1">
                    <svg id="bp-arrow-bgt" class="w-4 h-4 text-secondary-400 transition-transform" style="transform:rotate(90deg)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    Budget Details
                </span>
            </h3>
            <table class="w-full text-sm bp-budget-body">
                <thead>
                    <tr class="text-xs text-secondary-500 uppercase">
                        <th class="py-1 text-left pl-4">Category</th>
                        <th class="py-1 text-right w-32">Budget</th>
                        <th class="py-1 text-right w-32">Actual</th>
                        <th class="py-1 text-right w-32">Variance</th>
                        <th class="py-1 text-right w-20">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($budgetByCategory as $bci => $cat)
                    @php $variance = $cat->budget - $cat->actual; @endphp
                    <tr class="font-semibold cursor-pointer hover:bg-gray-50" onclick="document.querySelectorAll('.bp-bgt-{{ $bci }}').forEach(function(el){el.style.display=el.style.display==='none'?'':'none'}); var a=document.getElementById('bp-arrow-bg{{ $bci }}');a.style.transform=a.style.transform?'':'rotate(90deg)'">
                        <td class="py-2 pl-2">
                            <span class="inline-flex items-center gap-1">
                                <svg id="bp-arrow-bg{{ $bci }}" class="w-3.5 h-3.5 text-secondary-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                {{ $cat->category }}
                                <span class="text-xs text-secondary-400 font-normal">({{ $cat->items->count() }})</span>
                            </span>
                        </td>
                        <td class="py-2 text-right font-mono">₱{{ number_format($cat->budget, 2) }}</td>
                        <td class="py-2 text-right font-mono">₱{{ number_format($cat->actual, 2) }}</td>
                        <td class="py-2 text-right font-mono {{ $variance >= 0 ? 'text-green-600' : 'text-red-600' }}">₱{{ number_format(abs($variance), 2) }}</td>
                        <td class="py-2 text-right text-sm {{ $variance >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $cat->budget > 0 ? number_format(($cat->actual / $cat->budget) * 100, 0) : 0 }}%</td>
                    </tr>
                    @foreach($cat->items as $item)
                    <tr class="bp-bgt-{{ $bci }}" style="display:none">
                        <td class="py-1 pl-10 text-secondary-600">{{ $item->budget_name }}</td>
                        <td class="py-1 text-right font-mono text-secondary-600">₱{{ number_format($item->annual_budget, 2) }}</td>
                        <td class="py-1 text-right font-mono text-secondary-600">₱{{ number_format($item->actual, 2) }}</td>
                        @php $iVar = $item->annual_budget - $item->actual; @endphp
                        <td class="py-1 text-right font-mono {{ $iVar >= 0 ? 'text-green-600' : 'text-red-600' }}">₱{{ number_format(abs($iVar), 2) }}</td>
                        <td class="py-1 text-right text-sm {{ $iVar >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $item->annual_budget > 0 ? number_format(($item->actual / $item->annual_budget) * 100, 0) : 0 }}%</td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
            <table class="w-full text-sm">
                @php $totalVariance = $totalBudget - $totalActual; @endphp
                <tr class="border-t border-gray-300 font-semibold">
                    <td class="py-2 pl-2">Total Budget</td>
                    <td class="py-2 text-right font-mono w-32">₱{{ number_format($totalBudget, 2) }}</td>
                    <td class="py-2 text-right font-mono w-32">₱{{ number_format($totalActual, 2) }}</td>
                    <td class="py-2 text-right font-mono w-32 {{ $totalVariance >= 0 ? 'text-green-600' : 'text-red-600' }}">₱{{ number_format(abs($totalVariance), 2) }}</td>
                    <td class="py-2 text-right w-20 {{ $totalVariance >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $totalBudget > 0 ? number_format(($totalActual / $totalBudget) * 100, 0) : 0 }}%</td>
                </tr>
            </table>
        </div>

        <p class="text-xs text-secondary-400 text-center no-print">Click headers to collapse/expand. Click accounts for details.</p>
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
