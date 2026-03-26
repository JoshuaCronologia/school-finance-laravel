@extends('layouts.app')
@section('title', 'Disbursement Requests')

@section('content')
@php
    $requestCount = $disbursements instanceof \Illuminate\Pagination\LengthAwarePaginator ? $disbursements->total() : count($disbursements);
    $totalAmount = $disbursements instanceof \Illuminate\Pagination\LengthAwarePaginator ? $disbursements->sum('amount') : collect($disbursements)->sum('amount');
@endphp

<x-page-header title="Disbursement Requests" :subtitle="$requestCount . ' requests · Total: ₱' . number_format($totalAmount, 2)">
    <x-slot:actions>
        <a href="{{ route('ap.disbursements.export') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Export
        </a>
        <a href="{{ route('ap.disbursements.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            New Request
        </a>
    </x-slot:actions>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Filters --}}
<x-filter-bar action="{{ route('ap.disbursements.index') }}" method="GET">
    <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-input w-44">
            <option value="">All Status</option>
            @foreach(['draft', 'pending', 'for_approval', 'approved', 'rejected', 'paid', 'cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label">Department</label>
        <select name="department_id" class="form-input w-44">
            <option value="">All Departments</option>
            @foreach($departments ?? [] as $dept)
                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>
</x-filter-bar>

{{-- Disbursements Table --}}
<x-data-table search-placeholder="Search requests...">
    <thead>
        <tr>
            <th>Request #</th>
            <th>Date</th>
            <th>Payee</th>
            <th>Description</th>
            <th>Department</th>
            <th>Category</th>
            <th class="text-right">Amount</th>
            <th>Method</th>
            <th>Status</th>
            <th>Requested By</th>
        </tr>
    </thead>
    <tbody>
        @forelse($disbursements as $request)
        <tr>
            <td class="font-medium">
                <a href="{{ route('ap.disbursements.show', $request) }}" class="text-primary-600 hover:text-primary-700 hover:underline">
                    {{ $request->request_number }}
                </a>
            </td>
            <td>{{ \Carbon\Carbon::parse($request->request_date)->format('M d, Y') }}</td>
            <td>{{ $request->payee_name ?? $request->payee->name ?? '-' }}</td>
            <td class="max-w-xs truncate">{{ $request->description ?? '-' }}</td>
            <td>{{ $request->department->name ?? '-' }}</td>
            <td>{{ $request->category->name ?? $request->expense_category ?? '-' }}</td>
            <td class="text-right font-medium">{{ '₱' . number_format($request->amount, 2) }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $request->payment_method ?? '-')) }}</td>
            <td><x-badge :status="$request->status" /></td>
            <td>{{ $request->requested_by ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center text-secondary-400 py-8">
                <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                No disbursement requests found. Click "+ New Request" to create one.
            </td>
        </tr>
        @endforelse
    </tbody>
    @if($disbursements instanceof \Illuminate\Pagination\LengthAwarePaginator && $disbursements->hasPages())
    <x-slot:footer>
        {{ $disbursements->withQueryString()->links() }}
    </x-slot:footer>
    @endif
</x-data-table>
@endsection
