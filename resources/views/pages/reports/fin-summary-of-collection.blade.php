@extends('layouts.app')
@section('title', 'Summary of Collection')

@section('content')
<x-page-header title="Summary of Collection Report" subtitle="Cash receipts from Finance / Cashiering system">
    <x-slot name="actions">
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot>
</x-page-header>

<x-filter-bar action="{{ route('reports.fin.summary-of-collection') }}">
    <div>
        <label class="form-label">Date Range</label>
        <div class="flex items-center gap-2">
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-input" onchange="this.form.submit()">
            <span class="text-secondary-400">—</span>
            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-input" onchange="this.form.submit()">
        </div>
    </div>
    <div>
        <label class="form-label">Search</label>
        <input type="text" name="search" value="{{ $search }}" placeholder="OR No., name..." class="form-input w-48">
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
                    <th class="px-3 py-2 text-left whitespace-nowrap">OR DATE (PAID)</th>
                    <th class="px-3 py-2 text-left whitespace-nowrap">OR NO.</th>
                    <th class="px-3 py-2 text-left whitespace-nowrap">PAYOR</th>
                    <th class="px-3 py-2 text-left whitespace-nowrap">TYPE</th>
                    <th class="px-3 py-2 text-left whitespace-nowrap">PAYMENT METHOD</th>
                    <th class="px-3 py-2 text-left whitespace-nowrap">REMARKS</th>
                    <th class="px-3 py-2 text-right whitespace-nowrap">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                @php
                    $typeMap = [
                        1 => ['label'=>'Student','class'=>'bg-blue-100 text-blue-700','name'=>$r->student_name??'-'],
                        2 => ['label'=>'Employee','class'=>'bg-purple-100 text-purple-700','name'=>$r->employee_name??'-'],
                        3 => ['label'=>'Walk-in','class'=>'bg-yellow-100 text-yellow-700','name'=>trim($r->walkin_name)?:'-'],
                    ];
                    $info = isset($typeMap[$r->customer_type]) ? $typeMap[$r->customer_type] : ['label'=>'Other','class'=>'bg-gray-100 text-gray-600','name'=>'-'];
                @endphp
                <tr class="border-b border-secondary-100 hover:bg-secondary-50">
                    <td class="px-3 py-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($r->date_paid)->format('M d, Y') }}</td>
                    <td class="px-3 py-2 font-mono">{{ $r->receipt_number }}</td>
                    <td class="px-3 py-2">{{ $info['name'] }}</td>
                    <td class="px-3 py-2">
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $info['class'] }}">{{ $info['label'] }}</span>
                    </td>
                    <td class="px-3 py-2 text-secondary-500">{{ $r->payment_method ?: '—' }}</td>
                    <td class="px-3 py-2 text-secondary-500 max-w-xs truncate">{{ $r->remarks ?: '—' }}</td>
                    <td class="px-3 py-2 text-right font-medium">{{ number_format($r->total, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-3 py-6 text-center text-secondary-400">No records found for the selected period.</td></tr>
                @endforelse
            </tbody>
            @if($records->isNotEmpty())
            <tfoot>
                <tr class="bg-secondary-50 font-semibold">
                    <td colspan="6" class="px-3 py-2 text-right">Page Total:</td>
                    <td class="px-3 py-2 text-right">{{ number_format($records->sum('total'), 2) }}</td>
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
