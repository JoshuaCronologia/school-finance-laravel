@extends('layouts.app')
@section('title', 'Bank Reconciliation')

@section('content')
<x-page-header title="Bank Reconciliation" subtitle="Reconcile book balance with bank statement">
    @if($reconData)
    <x-slot name="actions">
        <a href="{{ route('gl.bank-reconciliation.pdf', ['account_id' => $accountId, 'as_of_date' => $asOfDate, 'statement_balance' => $statementBalance]) }}" class="btn-secondary inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
            Download PDF
        </a>
        <button onclick="window.print()" class="btn-secondary text-sm">Print</button>
    </x-slot>
    @endif
</x-page-header>

{{-- Parameters --}}
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">Bank Account <span class="text-danger-500">*</span></label>
                <select name="account_id" class="form-input w-64" required>
                    <option value="">Select Account</option>
                    @foreach($bankAccounts as $ba)
                        <option value="{{ $ba->id }}" {{ $accountId == $ba->id ? 'selected' : '' }}>
                            {{ $ba->account_code }} - {{ $ba->account_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">As of Date</label>
                <input type="date" name="as_of_date" class="form-input w-44" value="{{ $asOfDate }}">
            </div>
            <div>
                <label class="form-label">Bank Statement Balance</label>
                <input type="number" name="statement_balance" class="form-input w-48" step="0.01" placeholder="Enter bank balance" value="{{ $statementBalance }}">
            </div>
            <button type="submit" class="btn-primary">Reconcile</button>
        </form>
    </div>
</div>

@if($reconData)
{{-- Reconciliation Summary - Two Column Layout --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- LEFT: Bank Statement Side --}}
    <div class="card">
        <div class="card-header bg-blue-50">
            <h3 class="card-title text-blue-800">Per Bank Statement</h3>
        </div>
        <div class="card-body space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-secondary-600">Bank Statement Balance</span>
                <span class="font-bold text-lg">{{ $reconData->bank_statement_balance !== null ? '₱' . number_format($reconData->bank_statement_balance, 2) : 'Not entered' }}</span>
            </div>

            @if($reconData->bank_statement_balance !== null)
            <div class="border-t border-gray-100 pt-3">
                <p class="text-xs font-semibold text-secondary-500 uppercase mb-2">Add: Deposits in Transit</p>
                @forelse($reconData->deposits_in_transit as $dep)
                <div class="flex justify-between text-sm pl-4 py-0.5">
                    <span class="text-secondary-600">{{ \Carbon\Carbon::parse($dep->posting_date)->format('M d') }} - {{ $dep->entry_number }} {{ Str::limit($dep->je_description, 30) }}</span>
                    <span class="font-mono">₱{{ number_format($dep->debit, 2) }}</span>
                </div>
                @empty
                <p class="text-sm text-secondary-400 pl-4">None</p>
                @endforelse
                <div class="flex justify-between text-sm font-semibold pl-4 pt-1 border-t border-dashed border-gray-200 mt-1">
                    <span>Total Deposits in Transit</span>
                    <span class="font-mono">₱{{ number_format($reconData->total_deposits_transit, 2) }}</span>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-3">
                <p class="text-xs font-semibold text-secondary-500 uppercase mb-2">Less: Outstanding Checks / Payments</p>
                @forelse($reconData->outstanding_checks as $chk)
                <div class="flex justify-between text-sm pl-4 py-0.5">
                    <span class="text-secondary-600">{{ \Carbon\Carbon::parse($chk->posting_date)->format('M d') }} - {{ $chk->entry_number }} {{ $chk->reference_number ? '('. $chk->reference_number .')' : '' }}</span>
                    <span class="font-mono text-danger-600">(₱{{ number_format($chk->credit, 2) }})</span>
                </div>
                @empty
                <p class="text-sm text-secondary-400 pl-4">None</p>
                @endforelse
                <div class="flex justify-between text-sm font-semibold pl-4 pt-1 border-t border-dashed border-gray-200 mt-1">
                    <span>Total Outstanding Checks</span>
                    <span class="font-mono text-danger-600">(₱{{ number_format($reconData->total_outstanding_checks, 2) }})</span>
                </div>
            </div>

            <div class="border-t-2 border-gray-300 pt-3">
                <div class="flex justify-between font-bold text-base">
                    <span>Adjusted Bank Balance</span>
                    <span class="font-mono text-blue-800">₱{{ number_format($reconData->adjusted_bank_balance, 2) }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- RIGHT: Book Side --}}
    <div class="card">
        <div class="card-header bg-green-50">
            <h3 class="card-title text-green-800">Per Books ({{ $reconData->account->account_code }} - {{ $reconData->account->account_name }})</h3>
        </div>
        <div class="card-body space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-secondary-600">Book Balance per GL</span>
                <span class="font-bold text-lg">₱{{ number_format($reconData->book_balance, 2) }}</span>
            </div>

            <div class="border-t border-gray-100 pt-3">
                <p class="text-xs font-semibold text-secondary-500 uppercase mb-2">Add: Bank Credits not yet recorded</p>
                <p class="text-sm text-secondary-400 pl-4 italic">Interest earned, direct deposits, etc.</p>
                <div class="flex justify-between text-sm font-semibold pl-4 pt-1">
                    <span>Subtotal</span>
                    <span class="font-mono">₱0.00</span>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-3">
                <p class="text-xs font-semibold text-secondary-500 uppercase mb-2">Less: Bank Debits not yet recorded</p>
                <p class="text-sm text-secondary-400 pl-4 italic">Bank charges, NSF checks, etc.</p>
                <div class="flex justify-between text-sm font-semibold pl-4 pt-1">
                    <span>Subtotal</span>
                    <span class="font-mono text-danger-600">(₱0.00)</span>
                </div>
            </div>

            <div class="border-t-2 border-gray-300 pt-3">
                <div class="flex justify-between font-bold text-base">
                    <span>Adjusted Book Balance</span>
                    <span class="font-mono text-green-800">₱{{ number_format($reconData->book_balance, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Reconciliation Result --}}
@if($reconData->difference !== null)
<div class="card mb-6 {{ abs($reconData->difference) < 0.01 ? 'border-green-300' : 'border-red-300' }} border-2">
    <div class="card-body text-center py-6">
        @if(abs($reconData->difference) < 0.01)
            <div class="inline-flex items-center gap-2 text-green-700 text-lg font-bold">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                RECONCILED - Balances Match
            </div>
        @else
            <div class="inline-flex items-center gap-2 text-red-700 text-lg font-bold">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126Z" /></svg>
                UNRECONCILED - Difference: ₱{{ number_format(abs($reconData->difference), 2) }}
            </div>
            <p class="text-sm text-secondary-500 mt-2">Record bank charges, interest, or other adjustments as journal entries to reconcile.</p>
        @endif
    </div>
</div>
@endif

{{-- Recent Transactions --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Transactions (Last 60 Days)</h3>
        <span class="text-sm text-secondary-500">{{ $reconData->transactions->count() }} entries</span>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Entry #</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th class="text-right">Debit (In)</th>
                    <th class="text-right">Credit (Out)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reconData->transactions as $txn)
                <tr>
                    <td class="text-sm">{{ \Carbon\Carbon::parse($txn->posting_date)->format('M d, Y') }}</td>
                    <td class="font-mono text-sm text-primary-600">{{ $txn->entry_number }}</td>
                    <td class="font-mono text-sm">{{ $txn->reference_number ?? '-' }}</td>
                    <td class="text-sm">{{ $txn->je_description ?? $txn->description ?? '-' }}</td>
                    <td class="text-right font-mono">{{ $txn->debit > 0 ? '₱' . number_format($txn->debit, 2) : '' }}</td>
                    <td class="text-right font-mono">{{ $txn->credit > 0 ? '₱' . number_format($txn->credit, 2) : '' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-secondary-400 py-6">No transactions found for this period.</td></tr>
                @endforelse
            </tbody>
            @if($reconData->transactions->count() > 0)
            <tfoot class="bg-gray-50 font-bold">
                <tr>
                    <td colspan="4" class="text-right">Totals:</td>
                    <td class="text-right font-mono">₱{{ number_format($reconData->transactions->sum('debit'), 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($reconData->transactions->sum('credit'), 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endif
@endsection
