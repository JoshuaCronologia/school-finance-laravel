@extends('layouts.app')
@section('title', 'Fee Collection Receipts')

@section('content')
<x-page-header title="Fee Collection Receipts" subtitle="Browse cashier receipts from Finance system">
    <x-slot name="actions">
        <a href="{{ route('reports.fee-collections') }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
            Back to Fee Collections
        </a>
    </x-slot>
</x-page-header>

{{-- Filters --}}
<div class="card mb-6">
    <form action="{{ route('reports.fee-receipts') }}" method="GET" class="card-body">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">School Year</label>
                <select name="school_year" class="form-input w-48">
                    <option value="">All School Years</option>
                    @foreach($schoolYears as $sy)
                        <option value="{{ $sy->year_fr }}" {{ $selectedYear == $sy->year_fr ? 'selected' : '' }}>
                            SY {{ $sy->year_fr }}-{{ $sy->year_to }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Search (OR #, Student Name/ID)</label>
                <input type="text" name="search" class="form-input w-64" value="{{ $search }}" placeholder="e.g. SJ04838 or student name">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    Search
                </button>
                <a href="{{ route('reports.fee-receipts') }}" class="btn-secondary">Clear</a>
            </div>
        </div>
    </form>
</div>

{{-- Receipts Table --}}
<div class="card">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>OR #</th>
                    <th>Student</th>
                    <th>Student ID</th>
                    <th>Date Paid</th>
                    <th>School Year</th>
                    <th class="text-right">Amount</th>
                    <th class="w-20"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($receipts as $r)
                <tr>
                    <td class="font-medium font-mono">{{ $r->receipt_number }}</td>
                    <td>{{ $r->student_name ?? '-' }}</td>
                    <td class="font-mono text-sm text-secondary-500">{{ $r->student_number ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->date_paid)->format('M d, Y') }}</td>
                    <td>{{ $r->year_fr }}-{{ $r->year_to }}</td>
                    <td class="text-right font-medium text-green-600">{{ '₱' . number_format($r->total, 2) }}</td>
                    <td>
                        <a href="{{ route('reports.fee-receipt-detail', $r->id) }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-secondary-400 py-8">No receipts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($receipts->hasPages())
    <div class="card-body border-t border-gray-100">
        {{ $receipts->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
