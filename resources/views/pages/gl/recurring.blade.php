@extends('layouts.app')
@section('title', 'Recurring Journals')

@section('content')
<x-page-header title="Recurring Journals" subtitle="Click a journal entry to view, then Memorize to copy it as a new draft">
    <x-slot name="actions">
        <a href="{{ route('gl.journal-entries.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
            Back to Journal Entries
        </a>
    </x-slot>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif

{{-- Filters --}}
<x-filter-bar action="{{ route('gl.recurring') }}" method="GET">
    <div>
        <label class="form-label">Journal Type</label>
        <select name="journal_type" class="form-input w-44">
            <option value="">All Types</option>
            <option value="general" {{ request('journal_type') == 'general' ? 'selected' : '' }}>General</option>
            <option value="revenue" {{ request('journal_type') == 'revenue' ? 'selected' : '' }}>Revenue (CRJ)</option>
            <option value="expense" {{ request('journal_type') == 'expense' ? 'selected' : '' }}>Expense (CDJ)</option>
        </select>
    </div>
</x-filter-bar>

{{-- JE Entries Table --}}
<x-data-table search-placeholder="Search entries...">
    <thead>
        <tr>
            <th>Entry #</th>
            <th>Date</th>
            <th>Type</th>
            <th>Reference</th>
            <th>Description</th>
            <th class="text-right">Total Debit</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($entries as $entry)
        @php
            $totalDebit = $entry->lines->sum('debit');
            $typeMap = ['general' => 'badge-neutral', 'revenue' => 'badge-success', 'expense' => 'badge-danger'];
            $typeBadge = $typeMap[$entry->journal_type ?? ''] ?? 'badge-neutral';
        @endphp
        <tr>
            <td class="font-medium">
                <a href="{{ route('gl.journal-entries.show', $entry) }}" class="text-primary-600 hover:text-primary-700 hover:underline">
                    {{ $entry->entry_number }}
                </a>
            </td>
            <td>{{ \Carbon\Carbon::parse($entry->posting_date)->format('M d, Y') }}</td>
            <td><span class="badge {{ $typeBadge }}">{{ ucfirst($entry->journal_type ?? '-') }}</span></td>
            <td class="font-mono text-sm">{{ $entry->reference_number ?? '-' }}</td>
            <td class="max-w-xs truncate">{{ $entry->description ?? '-' }}</td>
            <td class="text-right font-medium">₱{{ number_format($totalDebit, 2) }}</td>
            <td><x-badge :status="$entry->status" /></td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center text-secondary-400 py-8">
                No posted journal entries found.
            </td>
        </tr>
        @endforelse
    </tbody>
    @if($entries instanceof \Illuminate\Pagination\LengthAwarePaginator && $entries->hasPages())
    <x-slot name="footer">
        {{ $entries->withQueryString()->links() }}
    </x-slot>
    @endif
</x-data-table>
@endsection
