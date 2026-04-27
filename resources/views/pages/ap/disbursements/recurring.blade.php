@extends('layouts.app')
@section('title', 'Recurring Disbursements')

@section('content')
<x-page-header title="Recurring Disbursements" subtitle="Click a disbursement to view, then Memorize to copy it as a new draft">
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
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

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
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse($disbursements as $dr)
        <tr>
            <td class="font-medium">
                <a href="{{ route('ap.disbursements.show', $dr) }}" class="text-primary-600 hover:text-primary-700 hover:underline">
                    {{ $dr->request_number }}
                </a>
            </td>
            <td>{{ \Carbon\Carbon::parse($dr->request_date)->format('M d, Y') }}</td>
            <td>{{ $dr->payee_name ?? '-' }}</td>
            <td class="max-w-xs truncate">{{ $dr->description ?? '-' }}</td>
            <td>{{ $dr->department->name ?? '-' }}</td>
            <td>{{ $dr->category->name ?? '-' }}</td>
            <td class="text-right font-medium">₱{{ number_format($dr->amount, 2) }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $dr->payment_method ?? '-')) }}</td>
            <td><x-badge :status="$dr->status" /></td>
            <td class="text-right">
                <form method="POST" action="{{ route('ap.disbursements.recurring.memorize', $dr) }}">
                    @csrf
                    <button type="submit" class="btn-secondary text-xs py-1 px-3">Memorize</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center text-secondary-400 py-8">
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
