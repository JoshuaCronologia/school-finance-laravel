@extends('layouts.app')
@section('title', 'Approval Queue')

@section('content')
@php
    $pendingCount = count($pendingRequests ?? []);
@endphp

<x-page-header title="Approval Queue" :subtitle="$pendingCount . ' pending requests awaiting your approval'">
    <x-slot name="actions">
        <a href="{{ route('ap.disbursements.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
            All Requests
        </a>
    </x-slot>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

@forelse($pendingRequests ?? [] as $request)
<div class="card mb-4" x-data="{ showActions: false, actionType: '', comment: '' }">
    <div class="card-body">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            {{-- Request Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('ap.disbursements.show', $request) }}" class="text-primary-600 hover:text-primary-700 font-semibold text-sm hover:underline">
                        {{ $request->request_number }}
                    </a>
                    <x-badge :status="$request->status" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                    <div>
                        <span class="text-secondary-500">Payee:</span>
                        <span class="font-medium text-secondary-900">{{ $request->payee_name ?? $request->payee->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-secondary-500">Department:</span>
                        <span class="font-medium text-secondary-900">{{ $request->department->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-secondary-500">Amount:</span>
                        <span class="font-bold text-secondary-900">{{ '₱' . number_format($request->amount, 2) }}</span>
                    </div>
                    <div>
                        <span class="text-secondary-500">Date:</span>
                        <span class="font-medium text-secondary-900">{{ \Carbon\Carbon::parse($request->request_date)->format('M d, Y') }}</span>
                    </div>
                </div>
                @if($request->description)
                    <p class="text-sm text-secondary-600 mt-2">{{ Str::limit($request->description, 120) }}</p>
                @endif

                {{-- Budget Check --}}
                @php $budget = $request->budgetInfo ?? null; @endphp
                @if($budget)
                <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-4 text-xs">
                        <span class="text-secondary-500">Remaining Budget: <strong class="{{ ($budget->remaining ?? 0) >= 0 ? 'text-success-600' : 'text-danger-600' }}">{{ '₱' . number_format($budget->remaining ?? 0, 2) }}</strong></span>
                        <span class="text-secondary-500">Requested: <strong class="text-primary-600">{{ '₱' . number_format($request->amount, 2) }}</strong></span>
                    </div>
                    @if($request->amount > ($budget->remaining ?? 0))
                        <div class="mt-2 flex items-center gap-1.5 text-warning-700 text-xs">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                            <span class="font-medium">Over budget by {{ '₱' . number_format($request->amount - ($budget->remaining ?? 0), 2) }}</span>
                        </div>
                    @endif
                </div>
                @endif
            </div>

            {{-- Action Buttons --}}
            <div class="flex-shrink-0 flex flex-col gap-2" x-show="!showActions">
                <button @click="showActions = true; actionType = 'approve'" class="btn-primary bg-success-600 hover:bg-success-700 text-sm w-full justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    Approve
                </button>
                <button @click="showActions = true; actionType = 'return'" class="btn-secondary text-warning-600 border-warning-300 hover:bg-warning-50 text-sm w-full justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                    Return
                </button>
                <button @click="showActions = true; actionType = 'reject'" class="btn-secondary text-danger-600 border-danger-300 hover:bg-danger-50 text-sm w-full justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    Reject
                </button>
            </div>
        </div>

        {{-- Action Form (expanded) --}}
        <div x-show="showActions" x-cloak class="mt-4 pt-4 border-t border-gray-100">
            <form :action="actionType === 'approve'
                    ? '{{ route('ap.approval.approve', $request) }}'
                    : (actionType === 'return'
                        ? '{{ route('ap.approval.return', $request) }}'
                        : '{{ route('ap.approval.reject', $request) }}')"
                  method="POST">
                @csrf
                <div class="flex items-start gap-3">
                    <div class="flex-1">
                        <label class="form-label">
                            <span x-text="actionType === 'approve' ? 'Approval Comments (optional)' : 'Reason (required)'"></span>
                        </label>
                        <textarea name="comments" class="form-input text-sm" rows="2" x-model="comment"
                                  :required="actionType !== 'approve'"
                                  :placeholder="actionType === 'approve' ? 'Optional comments...' : 'Please provide a reason...'"></textarea>
                    </div>
                    <div class="flex flex-col gap-2 mt-5">
                        <button type="submit" class="text-sm px-4 py-2 rounded-lg font-medium text-white"
                                :class="actionType === 'approve' ? 'bg-success-600 hover:bg-success-700' : (actionType === 'return' ? 'bg-warning-500 hover:bg-warning-600' : 'bg-danger-600 hover:bg-danger-700')">
                            <span x-text="actionType === 'approve' ? 'Confirm Approve' : (actionType === 'return' ? 'Confirm Return' : 'Confirm Reject')"></span>
                        </button>
                        <button type="button" @click="showActions = false; actionType = ''; comment = ''" class="btn-secondary text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@empty
<div class="card">
    <div class="card-body py-12 text-center">
        <svg class="w-12 h-12 mx-auto mb-3 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
        <h3 class="text-sm font-semibold text-secondary-700 mb-1">All Caught Up</h3>
        <p class="text-sm text-secondary-400">There are no pending requests in your approval queue.</p>
    </div>
</div>
@endforelse
@endsection
