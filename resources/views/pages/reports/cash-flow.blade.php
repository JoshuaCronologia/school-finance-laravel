@extends('layouts.app')
@section('title', 'Cash Flow Statement')

@section('content')
@php
    $operating = $operating ?? ['inflows' => 0, 'outflows' => 0, 'net' => 0, 'items' => collect()];
    $investing = $investing ?? ['inflows' => 0, 'outflows' => 0, 'net' => 0, 'items' => collect()];
    $financing = $financing ?? ['inflows' => 0, 'outflows' => 0, 'net' => 0, 'items' => collect()];
    $netCashFlow = ($operating['net'] ?? 0) + ($investing['net'] ?? 0) + ($financing['net'] ?? 0);
@endphp

<x-page-header title="Cash Flow Statement" subtitle="Statement of Cash Flows">
    <x-slot:actions>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm">Excel</a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn-secondary text-sm">PDF</a>
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot:actions>
</x-page-header>

<x-filter-bar />

{{-- Operating Activities --}}
<div class="card mb-6">
    <div class="card-header bg-blue-50">
        <div class="flex items-center justify-between w-full">
            <h3 class="text-sm font-semibold text-blue-800">Operating Activities</h3>
            <span class="text-sm font-mono font-bold {{ ($operating['net'] ?? 0) >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                Net: ₱{{ number_format($operating['net'] ?? 0, 2) }}
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <div class="p-3 bg-success-50 rounded-lg">
                <p class="text-xs text-success-600 font-medium">Inflows</p>
                <p class="text-lg font-bold text-success-700 font-mono">₱{{ number_format($operating['inflows'] ?? 0, 2) }}</p>
            </div>
            <div class="p-3 bg-danger-50 rounded-lg">
                <p class="text-xs text-danger-600 font-medium">Outflows</p>
                <p class="text-lg font-bold text-danger-700 font-mono">₱{{ number_format($operating['outflows'] ?? 0, 2) }}</p>
            </div>
            <div class="p-3 {{ ($operating['net'] ?? 0) >= 0 ? 'bg-blue-50' : 'bg-warning-50' }} rounded-lg">
                <p class="text-xs {{ ($operating['net'] ?? 0) >= 0 ? 'text-blue-600' : 'text-warning-600' }} font-medium">Net</p>
                <p class="text-lg font-bold {{ ($operating['net'] ?? 0) >= 0 ? 'text-blue-700' : 'text-warning-700' }} font-mono">₱{{ number_format($operating['net'] ?? 0, 2) }}</p>
            </div>
        </div>
        @if(isset($operating['items']) && $operating['items']->isNotEmpty())
        <table class="data-table">
            <thead><tr><th>Description</th><th class="text-right">Amount</th></tr></thead>
            <tbody>
                @foreach($operating['items'] as $item)
                <tr>
                    <td>{{ $item->description ?? $item->account_name ?? '' }}</td>
                    <td class="text-right font-mono {{ ($item->amount ?? 0) >= 0 ? 'text-success-600' : 'text-danger-600' }}">₱{{ number_format($item->amount ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-center text-secondary-400 py-4">No operating activity data available</p>
        @endif
    </div>
</div>

{{-- Investing Activities --}}
<div class="card mb-6">
    <div class="card-header bg-purple-50">
        <div class="flex items-center justify-between w-full">
            <h3 class="text-sm font-semibold text-purple-800">Investing Activities</h3>
            <span class="text-sm font-mono font-bold {{ ($investing['net'] ?? 0) >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                Net: ₱{{ number_format($investing['net'] ?? 0, 2) }}
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <div class="p-3 bg-success-50 rounded-lg">
                <p class="text-xs text-success-600 font-medium">Inflows</p>
                <p class="text-lg font-bold text-success-700 font-mono">₱{{ number_format($investing['inflows'] ?? 0, 2) }}</p>
            </div>
            <div class="p-3 bg-danger-50 rounded-lg">
                <p class="text-xs text-danger-600 font-medium">Outflows</p>
                <p class="text-lg font-bold text-danger-700 font-mono">₱{{ number_format($investing['outflows'] ?? 0, 2) }}</p>
            </div>
            <div class="p-3 {{ ($investing['net'] ?? 0) >= 0 ? 'bg-purple-50' : 'bg-warning-50' }} rounded-lg">
                <p class="text-xs {{ ($investing['net'] ?? 0) >= 0 ? 'text-purple-600' : 'text-warning-600' }} font-medium">Net</p>
                <p class="text-lg font-bold {{ ($investing['net'] ?? 0) >= 0 ? 'text-purple-700' : 'text-warning-700' }} font-mono">₱{{ number_format($investing['net'] ?? 0, 2) }}</p>
            </div>
        </div>
        @if(isset($investing['items']) && $investing['items']->isNotEmpty())
        <table class="data-table">
            <thead><tr><th>Description</th><th class="text-right">Amount</th></tr></thead>
            <tbody>
                @foreach($investing['items'] as $item)
                <tr>
                    <td>{{ $item->description ?? $item->account_name ?? '' }}</td>
                    <td class="text-right font-mono {{ ($item->amount ?? 0) >= 0 ? 'text-success-600' : 'text-danger-600' }}">₱{{ number_format($item->amount ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-center text-secondary-400 py-4">No investing activity data available</p>
        @endif
    </div>
</div>

{{-- Financing Activities --}}
<div class="card mb-6">
    <div class="card-header bg-indigo-50">
        <div class="flex items-center justify-between w-full">
            <h3 class="text-sm font-semibold text-indigo-800">Financing Activities</h3>
            <span class="text-sm font-mono font-bold {{ ($financing['net'] ?? 0) >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                Net: ₱{{ number_format($financing['net'] ?? 0, 2) }}
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <div class="p-3 bg-success-50 rounded-lg">
                <p class="text-xs text-success-600 font-medium">Inflows</p>
                <p class="text-lg font-bold text-success-700 font-mono">₱{{ number_format($financing['inflows'] ?? 0, 2) }}</p>
            </div>
            <div class="p-3 bg-danger-50 rounded-lg">
                <p class="text-xs text-danger-600 font-medium">Outflows</p>
                <p class="text-lg font-bold text-danger-700 font-mono">₱{{ number_format($financing['outflows'] ?? 0, 2) }}</p>
            </div>
            <div class="p-3 {{ ($financing['net'] ?? 0) >= 0 ? 'bg-indigo-50' : 'bg-warning-50' }} rounded-lg">
                <p class="text-xs {{ ($financing['net'] ?? 0) >= 0 ? 'text-indigo-600' : 'text-warning-600' }} font-medium">Net</p>
                <p class="text-lg font-bold {{ ($financing['net'] ?? 0) >= 0 ? 'text-indigo-700' : 'text-warning-700' }} font-mono">₱{{ number_format($financing['net'] ?? 0, 2) }}</p>
            </div>
        </div>
        @if(isset($financing['items']) && $financing['items']->isNotEmpty())
        <table class="data-table">
            <thead><tr><th>Description</th><th class="text-right">Amount</th></tr></thead>
            <tbody>
                @foreach($financing['items'] as $item)
                <tr>
                    <td>{{ $item->description ?? $item->account_name ?? '' }}</td>
                    <td class="text-right font-mono {{ ($item->amount ?? 0) >= 0 ? 'text-success-600' : 'text-danger-600' }}">₱{{ number_format($item->amount ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-center text-secondary-400 py-4">No financing activity data available</p>
        @endif
    </div>
</div>

{{-- Net Cash Flow Total --}}
<div class="card">
    <div class="card-body">
        <div class="flex items-center justify-between p-4 rounded-lg {{ $netCashFlow >= 0 ? 'bg-success-50' : 'bg-danger-50' }}">
            <span class="text-lg font-bold {{ $netCashFlow >= 0 ? 'text-success-800' : 'text-danger-800' }}">Net Cash Flow</span>
            <span class="text-xl font-mono font-bold {{ $netCashFlow >= 0 ? 'text-success-700' : 'text-danger-700' }}">₱{{ number_format($netCashFlow, 2) }}</span>
        </div>
    </div>
</div>
@endsection
