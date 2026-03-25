@extends('layouts.app')
@section('title', $journalEntry->entry_number)

@section('content')
<x-page-header :title="$journalEntry->entry_number" subtitle="Journal Entry Details">
    <x-slot:actions>
        <div class="flex flex-wrap gap-2">
            {{-- Print --}}
            @if($journalEntry->status === 'posted')
            <a href="{{ route('gl.journal-entries.print', $journalEntry) }}" class="btn-secondary inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" /></svg>
                Print JV
            </a>
            @endif

            {{-- Status Actions --}}
            @if($journalEntry->status === 'draft')
            <form action="{{ route('gl.journal-entries.submit-approval', $journalEntry) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn-primary inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                    Submit for Approval
                </button>
            </form>
            @endif

            @if($journalEntry->status === 'pending_approval')
            <form action="{{ route('gl.journal-entries.approve', $journalEntry) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn-primary inline-flex items-center gap-1 bg-green-600 hover:bg-green-700">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    Approve
                </button>
            </form>
            <button @click="$dispatch('open-modal', 'reject-je')" class="btn-secondary inline-flex items-center gap-1 text-red-600 border-red-300 hover:bg-red-50">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                Reject
            </button>
            @endif

            @if($journalEntry->status === 'approved')
            <form action="{{ route('gl.journal-entries.post', $journalEntry) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn-primary inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    Post Entry
                </button>
            </form>
            @endif

            @if($journalEntry->status === 'posted')
            <form action="{{ route('gl.journal-entries.reverse', $journalEntry) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to reverse this entry?')">
                @csrf
                <button type="submit" class="btn-secondary inline-flex items-center gap-1 text-red-600 border-red-300 hover:bg-red-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                    Reverse
                </button>
            </form>
            @endif

            <a href="{{ route('gl.journal-entries.index') }}" class="btn-secondary">Back to List</a>
        </div>
    </x-slot:actions>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Entry Header Info --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="card lg:col-span-2">
        <div class="card-header"><h3 class="card-title">Entry Information</h3></div>
        <div class="card-body">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-secondary-500">Entry Number</span>
                    <p class="font-semibold text-secondary-900">{{ $journalEntry->entry_number }}</p>
                </div>
                <div>
                    <span class="text-secondary-500">Entry Date</span>
                    <p class="font-semibold">{{ $journalEntry->entry_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <span class="text-secondary-500">Journal Type</span>
                    <p><span class="badge badge-info">{{ ucfirst($journalEntry->journal_type) }}</span></p>
                </div>
                <div>
                    <span class="text-secondary-500">Status</span>
                    <p><x-badge :status="$journalEntry->status" /></p>
                </div>
                <div>
                    <span class="text-secondary-500">Reference</span>
                    <p class="font-medium">{{ $journalEntry->reference_number ?: '-' }}</p>
                </div>
                <div>
                    <span class="text-secondary-500">Department</span>
                    <p class="font-medium">{{ $journalEntry->department->name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-secondary-500">Campus</span>
                    <p class="font-medium">{{ $journalEntry->campus->name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-secondary-500">Posting Date</span>
                    <p class="font-medium">{{ $journalEntry->posting_date ? $journalEntry->posting_date->format('M d, Y') : '-' }}</p>
                </div>
            </div>
            @if($journalEntry->description)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <span class="text-sm text-secondary-500">Description</span>
                <p class="text-sm font-medium text-secondary-900 mt-1">{{ $journalEntry->description }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Totals Card --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title">Totals</h3></div>
        <div class="card-body flex flex-col gap-4">
            <div class="text-center">
                <span class="text-sm text-secondary-500">Total Debit</span>
                <p class="text-2xl font-bold text-secondary-900">{{ '₱' . number_format($totalDebit, 2) }}</p>
            </div>
            <div class="text-center">
                <span class="text-sm text-secondary-500">Total Credit</span>
                <p class="text-2xl font-bold text-secondary-900">{{ '₱' . number_format($totalCredit, 2) }}</p>
            </div>
            <div class="text-center pt-3 border-t border-gray-100">
                @if(round($totalDebit, 2) === round($totalCredit, 2))
                    <span class="inline-flex items-center gap-1 text-green-600 font-semibold text-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        Balanced
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-red-600 font-semibold text-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126Z" /></svg>
                        Unbalanced (₱{{ number_format(abs($totalDebit - $totalCredit), 2) }})
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Journal Lines --}}
<div class="card">
    <div class="card-header"><h3 class="card-title">Journal Lines</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-10">#</th>
                    <th>Account Code</th>
                    <th>Account Name</th>
                    <th>Description</th>
                    <th>Department</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($journalEntry->lines as $i => $line)
                <tr>
                    <td class="text-secondary-400">{{ $i + 1 }}</td>
                    <td class="font-medium">{{ $line->account->account_code ?? '-' }}</td>
                    <td>{{ $line->account->account_name ?? '-' }}</td>
                    <td class="text-secondary-600">{{ $line->description ?? '-' }}</td>
                    <td class="text-secondary-600">{{ $line->department->name ?? '-' }}</td>
                    <td class="text-right font-medium">{{ $line->debit > 0 ? '₱' . number_format($line->debit, 2) : '-' }}</td>
                    <td class="text-right font-medium">{{ $line->credit > 0 ? '₱' . number_format($line->credit, 2) : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-50 font-semibold">
                    <td colspan="5" class="text-right">TOTAL</td>
                    <td class="text-right">{{ '₱' . number_format($totalDebit, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($totalCredit, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Workflow Info --}}
@if($journalEntry->status === 'posted')
<div class="card mt-6">
    <div class="card-body text-sm text-secondary-500">
        <div class="flex flex-wrap gap-6">
            <div>Created by: <span class="font-medium text-secondary-700">{{ $journalEntry->created_by ?? '-' }}</span></div>
            @if($journalEntry->approved_by)
            <div>Approved by: <span class="font-medium text-secondary-700">{{ $journalEntry->approved_by }}</span></div>
            @endif
            <div>Posted by: <span class="font-medium text-secondary-700">{{ $journalEntry->posted_by ?? '-' }}</span></div>
            <div>Posted on: <span class="font-medium text-secondary-700">{{ $journalEntry->posting_date ? $journalEntry->posting_date->format('M d, Y') : '-' }}</span></div>
        </div>
        <p class="mt-2 text-xs text-secondary-400">This entry is posted and cannot be edited. To correct, create a reversing entry.</p>
    </div>
</div>
@endif

{{-- Reject Modal --}}
@if($journalEntry->status === 'pending_approval')
<x-modal name="reject-je" title="Reject Journal Entry">
    <form action="{{ route('gl.journal-entries.reject', $journalEntry) }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="form-label">Reason for Rejection</label>
            <textarea name="reason" class="form-input w-full" rows="3" placeholder="Please provide a reason..." required></textarea>
        </div>
        <div class="flex justify-end gap-3">
            <button type="button" @click="$dispatch('close-modal', 'reject-je')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary bg-red-600 hover:bg-red-700">Reject Entry</button>
        </div>
    </form>
</x-modal>
@endif
@endsection
