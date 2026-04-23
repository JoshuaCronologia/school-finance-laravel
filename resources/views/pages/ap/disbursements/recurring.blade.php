@extends('layouts.app')
@section('title', 'Recurring Disbursements')

@section('content')
<x-page-header title="Recurring Disbursements" subtitle="Click a request to view, then Memorize to copy it as a new disbursement">
    <x-slot name="actions">
        <a href="{{ route('ap.disbursements.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
            Back to Disbursements
        </a>
    </x-slot>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif

{{-- Filters --}}
<x-filter-bar action="{{ route('ap.disbursements.recurring') }}" method="GET">
    <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-input w-44">
            <option value="">All (Approved + Paid)</option>
            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
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
            <td>{{ $request->payee_name ?? '-' }}</td>
            <td class="max-w-xs truncate">{{ $request->description ?? '-' }}</td>
            <td>{{ $request->department->name ?? '-' }}</td>
            <td>{{ $request->category->name ?? '-' }}</td>
            <td class="text-right font-medium">{{ '₱' . number_format($request->amount, 2) }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $request->payment_method ?? '-')) }}</td>
            <td><x-badge :status="$request->status" /></td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center text-secondary-400 py-8">
                <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                No disbursement requests found.
            </td>
        </tr>
        @endforelse
    </tbody>
    @if($disbursements instanceof \Illuminate\Pagination\LengthAwarePaginator && $disbursements->hasPages())
    <x-slot name="footer">
        {{ $disbursements->withQueryString()->links() }}
    </x-slot>
    @endif
</x-data-table>
@endsection
