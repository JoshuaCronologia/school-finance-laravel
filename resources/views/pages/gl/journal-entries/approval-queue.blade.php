@extends('layouts.app')
@section('title', 'JE Approval Queue')

@section('content')
<x-page-header title="Journal Entry Approval Queue" subtitle="Review and approve journal entries for posting" />

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Pending Approval --}}
<div class="card mb-6">
    <div class="card-header">
        <h3 class="card-title">Pending Approval</h3>
        <span class="badge badge-warning">{{ $pendingEntries->total() }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Entry #</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Department</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingEntries as $je)
                <tr>
                    <td>
                        <a href="{{ route('gl.journal-entries.show', $je) }}" class="text-primary-600 hover:underline font-medium">{{ $je->entry_number }}</a>
                    </td>
                    <td>{{ $je->entry_date->format('M d, Y') }}</td>
                    <td><span class="badge badge-info">{{ ucfirst($je->journal_type) }}</span></td>
                    <td class="max-w-xs truncate">{{ $je->description ?? '-' }}</td>
                    <td>{{ $je->department->name ?? '-' }}</td>
                    <td class="text-right font-medium">{{ '₱' . number_format($je->total_debit, 2) }}</td>
                    <td class="text-right font-medium">{{ '₱' . number_format($je->total_credit, 2) }}</td>
                    <td class="text-center">
                        <div class="flex items-center justify-center gap-1">
                            <form action="{{ route('gl.journal-entries.approve', $je) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-800 p-1" title="Approve">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                </button>
                            </form>
                            <form action="{{ route('gl.journal-entries.reject', $je) }}" method="POST" class="inline" onsubmit="var r = prompt('Reason for rejection:'); if(!r) return false; this.querySelector('[name=reason]').value = r;">
                                @csrf
                                <input type="hidden" name="reason" value="">
                                <button type="submit" class="text-red-600 hover:text-red-800 p-1" title="Reject">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </button>
                            </form>
                            <a href="{{ route('gl.journal-entries.show', $je) }}" class="text-secondary-500 hover:text-secondary-700 p-1" title="View Details">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-secondary-400 py-6">No entries pending approval.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pendingEntries->hasPages())
    <div class="card-footer">{{ $pendingEntries->withQueryString()->links() }}</div>
    @endif
</div>

{{-- Approved (Ready to Post) --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Approved - Ready to Post</h3>
        <span class="badge badge-success">{{ $approvedEntries->total() }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Entry #</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Department</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approvedEntries as $je)
                <tr>
                    <td>
                        <a href="{{ route('gl.journal-entries.show', $je) }}" class="text-primary-600 hover:underline font-medium">{{ $je->entry_number }}</a>
                    </td>
                    <td>{{ $je->entry_date->format('M d, Y') }}</td>
                    <td><span class="badge badge-info">{{ ucfirst($je->journal_type) }}</span></td>
                    <td class="max-w-xs truncate">{{ $je->description ?? '-' }}</td>
                    <td>{{ $je->department->name ?? '-' }}</td>
                    <td class="text-right font-medium">{{ '₱' . number_format($je->total_debit, 2) }}</td>
                    <td class="text-right font-medium">{{ '₱' . number_format($je->total_credit, 2) }}</td>
                    <td class="text-center">
                        <div class="flex items-center justify-center gap-1">
                            <form action="{{ route('gl.journal-entries.post', $je) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="btn-primary text-xs px-3 py-1">Post</button>
                            </form>
                            <a href="{{ route('gl.journal-entries.show', $je) }}" class="text-secondary-500 hover:text-secondary-700 p-1" title="View">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-secondary-400 py-6">No approved entries waiting to be posted.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($approvedEntries->hasPages())
    <div class="card-footer">{{ $approvedEntries->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
