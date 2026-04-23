@extends('layouts.app')
@section('title', isset($journalEntry) ? 'Edit Journal Entry' : 'New Journal Entry')

@section('content')
<x-page-header :title="isset($journalEntry) ? 'Edit Journal Entry #' . $journalEntry->entry_number : 'New Journal Entry'" :subtitle="isset($journalEntry) ? 'Edit draft entry' : 'Create a new journal entry'" />

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

<div class="card">
    <div class="card-body">
        @php
            $initialLines = isset($journalEntry) && $journalEntry->lines->count() > 0
                ? $journalEntry->lines->map(function ($l) {
                    return [
                        'account_id' => (string) $l->account_id,
                        'description' => $l->description,
                        'debit' => (float) $l->debit,
                        'credit' => (float) $l->credit,
                    ];
                })->toArray()
                : [
                    ['account_id' => '', 'description' => '', 'debit' => 0, 'credit' => 0],
                    ['account_id' => '', 'description' => '', 'debit' => 0, 'credit' => 0],
                ];
        @endphp
        <form action="{{ isset($journalEntry) ? route('gl.journal-entries.update', $journalEntry) : route('gl.journal-entries.store') }}" method="POST" x-data="{
            lines: {{ json_encode($initialLines) }},
            get totalDebit() { return this.lines.reduce((s, l) => s + parseFloat(l.debit || 0), 0); },
            get totalCredit() { return this.lines.reduce((s, l) => s + parseFloat(l.credit || 0), 0); },
            get difference() { return this.totalDebit - this.totalCredit; },
            addLine() { this.lines.push({ account_id: '', description: '', debit: 0, credit: 0 }); },
            removeLine(i) { if (this.lines.length > 2) this.lines.splice(i, 1); }
        }">
            @csrf
            @if(isset($journalEntry))
                @method('PUT')
            @endif

            @if(!isset($journalEntry))
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                <strong>JE Number:</strong> Will be auto-generated upon saving (series-based for audit trail).
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <label class="form-label">Date <span class="text-danger-500">*</span></label>
                    <input type="date" name="entry_date" class="form-input" value="{{ old('entry_date', isset($journalEntry) ? $journalEntry->entry_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                </div>
                <div>
                    <label class="form-label">Reference #</label>
                    <input type="text" name="reference_number" class="form-input" placeholder="Check #, OR #, etc." value="{{ old('reference_number', $journalEntry->reference_number ?? '') }}">
                </div>
                <div>
                    <label class="form-label">Type <span class="text-danger-500">*</span></label>
                    @php $currentType = old('journal_type', $journalEntry->journal_type ?? 'general'); @endphp
                    <select name="journal_type" class="form-input" required>
                        <option value="general" {{ $currentType == 'general' ? 'selected' : '' }}>General</option>
                        <option value="adjusting" {{ $currentType == 'adjusting' ? 'selected' : '' }}>Adjusting</option>
                        <option value="closing" {{ $currentType == 'closing' ? 'selected' : '' }}>Closing</option>
                        <option value="reversing" {{ $currentType == 'reversing' ? 'selected' : '' }}>Reversing</option>
                        <option value="revenue" {{ $currentType == 'revenue' ? 'selected' : '' }}>Revenue</option>
                        <option value="expense" {{ $currentType == 'expense' ? 'selected' : '' }}>Expense</option>
                        <option value="payroll" {{ $currentType == 'payroll' ? 'selected' : '' }}>Payroll</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Description <span class="text-danger-500">*</span></label>
                    <input type="text" name="description" class="form-input" placeholder="e.g. Bank charges for March" value="{{ old('description', $journalEntry->description ?? '') }}" required>
                </div>
            </div>

            {{-- Journal Lines --}}
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-secondary-700 mb-2">Journal Lines</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 px-2 font-medium text-secondary-600">Account</th>
                                <th class="text-left py-2 px-2 font-medium text-secondary-600">Memo</th>
                                <th class="text-right py-2 px-2 font-medium text-secondary-600 w-36">Debit</th>
                                <th class="text-right py-2 px-2 font-medium text-secondary-600 w-36">Credit</th>
                                <th class="w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(line, index) in lines" :key="index">
                                <tr class="border-b border-gray-100">
                                    <td class="py-1 px-2">
                                        <select x-model="line.account_id" :name="'lines['+index+'][account_id]'" class="form-input text-sm" required>
                                            <option value="">Select Account</option>
                                            @foreach($accounts ?? [] as $acct)
                                                <option value="{{ $acct->id }}">{{ $acct->account_code }} - {{ $acct->account_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-1 px-2"><input type="text" x-model="line.description" :name="'lines['+index+'][description]'" class="form-input text-sm" placeholder="Line description" required></td>
                                    <td class="py-1 px-2"><input type="number" x-model="line.debit" :name="'lines['+index+'][debit]'" class="form-input text-sm text-right" step="0.01" min="0" @input="if(parseFloat(line.debit) > 0) line.credit = 0"></td>
                                    <td class="py-1 px-2"><input type="number" x-model="line.credit" :name="'lines['+index+'][credit]'" class="form-input text-sm text-right" step="0.01" min="0" @input="if(parseFloat(line.credit) > 0) line.debit = 0"></td>
                                    <td class="py-1 px-2">
                                        <button type="button" @click="removeLine(index)" x-show="lines.length > 2" class="text-danger-500 hover:text-danger-700">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-300 font-semibold">
                                <td colspan="2" class="py-2 px-2 text-right">Totals:</td>
                                <td class="py-2 px-2 text-right" x-text="'₱' + totalDebit.toFixed(2)"></td>
                                <td class="py-2 px-2 text-right" x-text="'₱' + totalCredit.toFixed(2)"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="py-1 px-2 text-right text-sm">Difference:</td>
                                <td colspan="2" class="py-1 px-2 text-right text-sm font-semibold" :class="difference !== 0 ? 'text-danger-500' : 'text-success-600'" x-text="'₱' + Math.abs(difference).toFixed(2) + (difference !== 0 ? ' (unbalanced)' : ' (balanced)')"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <button type="button" @click="addLine()" class="mt-2 text-sm text-primary-600 hover:text-primary-700 font-medium">+ Add Line</button>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ isset($journalEntry) ? route('gl.journal-entries.show', $journalEntry) : route('gl.journal-entries.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" name="action" value="draft" class="btn-secondary" :disabled="difference !== 0">{{ isset($journalEntry) ? 'Save Changes' : 'Save as Draft' }}</button>
                <button type="submit" name="action" value="submit_approval" class="btn-primary" :disabled="difference !== 0">Submit for Approval</button>
            </div>
        </form>
    </div>
</div>
@endsection
