@extends('layouts.app')
@section('title', 'Ledger Inquiry')

@section('content')
<x-page-header title="Ledger Inquiry" subtitle="Drill-down view of all transactions per account">
    <x-slot name="actions">
        <a href="{{ route('reports.general-ledger') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
            Full GL Report
        </a>
        <button onclick="window.print()" class="btn-secondary">Print</button>
    </x-slot>
</x-page-header>

{{-- Filters --}}
<div class="card mb-6 no-print">
    <div class="card-body">
        <form action="{{ route('gl.ledger-inquiry') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[300px]">
                <label class="form-label">Account <span class="text-danger-500">*</span></label>
                <select name="account_id" class="form-input" required>
                    <option value="">Select Account...</option>
                    @foreach($accounts ?? [] as $acct)
                        <option value="{{ $acct->id }}" {{ request('account_id') == $acct->id ? 'selected' : '' }}>
                            {{ $acct->account_code }} - {{ $acct->account_name }} ({{ ucfirst($acct->account_type) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from', now()->startOfYear()->toDateString()) }}" class="form-input w-40">
            </div>
            <div>
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to', now()->toDateString()) }}" class="form-input w-40">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary">Inquire</button>
                <a href="{{ route('gl.ledger-inquiry') }}" class="btn-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

@if($selectedAccount)
    {{-- Account Info Header --}}
    <div class="card mb-6">
        <div class="card-body">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <span class="text-xs text-secondary-500 uppercase">Account Code</span>
                    <p class="font-bold text-lg font-mono">{{ $selectedAccount->account_code }}</p>
                </div>
                <div>
                    <span class="text-xs text-secondary-500 uppercase">Account Name</span>
                    <p class="font-medium">{{ $selectedAccount->account_name }}</p>
                </div>
                <div>
                    <span class="text-xs text-secondary-500 uppercase">Type</span>
                    @php
                        $_map = ['asset' => 'badge-info', 'liability' => 'badge-warning', 'equity' => 'badge-success', 'revenue' => 'badge-success', 'expense' => 'badge-danger'];
                        $typeBadge = $_map[$selectedAccount->account_type ?? ''] ?? 'badge-neutral';
                    @endphp
                    <p><span class="badge {{ $typeBadge }}">{{ ucfirst($selectedAccount->account_type) }}</span></p>
                </div>
                <div>
                    <span class="text-xs text-secondary-500 uppercase">Normal Balance</span>
                    <p class="font-medium">{{ ucfirst($selectedAccount->normal_balance) }}</p>
                </div>
            </div>
            @if($selectedAccount->parent)
            <div class="mt-3 text-sm text-secondary-600">
                Parent: <a href="{{ route('gl.ledger-inquiry', ['account_id' => $selectedAccount->parent_id, 'date_from' => request('date_from'), 'date_to' => request('date_to')]) }}" class="text-primary-600 hover:underline">{{ $selectedAccount->parent->account_code }} - {{ $selectedAccount->parent->account_name }}</a>
            </div>
            @endif
        </div>
    </div>

    {{-- Balance Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="card"><div class="card-body text-center">
            <span class="text-xs text-secondary-500 uppercase">Opening Balance</span>
            <p class="font-bold text-lg">₱{{ number_format($openingBalance, 2) }}</p>
        </div></div>
        <div class="card"><div class="card-body text-center">
            <span class="text-xs text-blue-600 uppercase">Total Debits</span>
            <p class="font-bold text-lg text-blue-700">₱{{ number_format($totalDebits, 2) }}</p>
        </div></div>
        <div class="card"><div class="card-body text-center">
            <span class="text-xs text-amber-600 uppercase">Total Credits</span>
            <p class="font-bold text-lg text-amber-700">₱{{ number_format($totalCredits, 2) }}</p>
        </div></div>
        <div class="card"><div class="card-body text-center">
            <span class="text-xs text-secondary-500 uppercase">Net Change</span>
            <p class="font-bold text-lg {{ ($closingBalance - $openingBalance) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ ($closingBalance - $openingBalance) >= 0 ? '+' : '' }}₱{{ number_format($closingBalance - $openingBalance, 2) }}
            </p>
        </div></div>
        <div class="card border-2 border-primary-200"><div class="card-body text-center">
            <span class="text-xs text-primary-600 uppercase">Closing Balance</span>
            <p class="font-bold text-lg text-primary-700">₱{{ number_format($closingBalance, 2) }}</p>
        </div></div>
    </div>

    {{-- Transaction Details --}}
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-secondary-900">
                Transaction Details ({{ $ledgerEntries->count() }} entries)
            </h3>
            <span class="text-xs text-secondary-500">{{ \Carbon\Carbon::parse(request('date_from', now()->startOfYear()))->format('M d, Y') }} to {{ \Carbon\Carbon::parse(request('date_to', now()))->format('M d, Y') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Entry #</th>
                        <th>Source</th>
                        <th>Reference</th>
                        <th>Description / Memo</th>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Credit</th>
                        <th class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Opening balance row --}}
                    <tr class="bg-gray-50 font-semibold">
                        <td>{{ \Carbon\Carbon::parse(request('date_from', now()->startOfYear()))->format('M d, Y') }}</td>
                        <td colspan="4" class="text-secondary-600">Opening Balance</td>
                        <td class="text-right">-</td>
                        <td class="text-right">-</td>
                        <td class="text-right font-mono">₱{{ number_format($openingBalance, 2) }}</td>
                    </tr>

                    @forelse($ledgerEntries as $entry)
                    <tr>
                        <td class="text-sm">{{ \Carbon\Carbon::parse($entry->posting_date)->format('M d, Y') }}</td>
                        <td class="font-mono text-sm font-medium">
                            @if(($entry->source_type ?? 'JE') === 'JE')
                                <a href="{{ url('/gl/journal-entries/' . ($entry->journal_entry_id ?? '')) }}" class="text-primary-600 hover:underline">{{ $entry->entry_number }}</a>
                            @else
                                {{ $entry->entry_number }}
                            @endif
                        </td>
                        <td>
                            @if(($entry->source_type ?? 'JE') === 'FEE')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Finance</span>
                            @elseif(!empty($entry->source_module))
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ strtoupper($entry->source_module) }}</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ strtoupper($entry->journal_type ?? 'JE') }}</span>
                            @endif
                        </td>
                        <td class="font-mono text-sm">{{ $entry->reference_number ?? '-' }}</td>
                        <td class="text-sm">
                            <div class="font-medium">{{ $entry->description ?? $entry->je_description ?? '-' }}</div>
                            @if(!empty($entry->je_description) && $entry->description !== $entry->je_description)
                            <div class="text-xs text-secondary-500">{{ $entry->je_description }}</div>
                            @endif
                        </td>
                        <td class="text-right font-mono">{{ ($entry->debit ?? 0) > 0 ? '₱' . number_format($entry->debit, 2) : '' }}</td>
                        <td class="text-right font-mono">{{ ($entry->credit ?? 0) > 0 ? '₱' . number_format($entry->credit, 2) : '' }}</td>
                        <td class="text-right font-mono font-medium">₱{{ number_format($entry->running_balance, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-secondary-400 py-8">No transactions for this account in the selected period.</td></tr>
                    @endforelse
                </tbody>
                @if($ledgerEntries->count() > 0)
                <tfoot class="bg-gray-100 font-bold border-t-2">
                    <tr>
                        <td colspan="5" class="text-right">Totals:</td>
                        <td class="text-right font-mono">₱{{ number_format($totalDebits, 2) }}</td>
                        <td class="text-right font-mono">₱{{ number_format($totalCredits, 2) }}</td>
                        <td class="text-right font-mono text-primary-700">₱{{ number_format($closingBalance, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-16">
            <svg class="w-12 h-12 mx-auto mb-4 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
            <h3 class="text-lg font-medium text-secondary-700 mb-1">Ledger Inquiry</h3>
            <p class="text-secondary-400">Select an account and date range to view all transactions with details.</p>
        </div>
    </div>
@endif
@endsection
