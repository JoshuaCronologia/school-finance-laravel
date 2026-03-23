@extends('layouts.app')
@section('title', 'Journal Entries')

@section('content')
@php
    $jeCount = $journalEntries instanceof \Illuminate\Pagination\LengthAwarePaginator ? $journalEntries->total() : count($journalEntries);
@endphp

<x-page-header title="Journal Entries" :subtitle="$jeCount . ' entries'">
    <x-slot:actions>
        <button @click="$dispatch('open-modal', 'new-journal-entry')" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            + New Journal Entry
        </button>
    </x-slot:actions>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Filters --}}
<x-filter-bar action="{{ route('gl.journal-entries.index') }}" method="GET">
    <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-input w-44">
            <option value="">All Status</option>
            @foreach(['draft', 'posted', 'reversed'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label">Type</label>
        <select name="type" class="form-input w-44">
            <option value="">All Types</option>
            @foreach(['general', 'adjusting', 'closing', 'reversing', 'compound'] as $t)
                <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
    </div>
</x-filter-bar>

{{-- Journal Entries Table --}}
<div class="card">
    <div class="card-header">
        <div class="flex items-center gap-2 bg-gray-100 rounded-lg px-3 py-2 w-full sm:w-72">
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            <input type="text" placeholder="Search journal entries..." class="bg-transparent border-0 text-sm text-gray-700 placeholder-gray-400 focus:outline-none w-full">
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-8"></th>
                    <th>Journal #</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($journalEntries as $je)
                <tr x-data="{ expanded: false }">
                    <td>
                        <button @click="expanded = !expanded" class="text-secondary-400 hover:text-secondary-600 transition-transform" :class="expanded ? 'rotate-90' : ''">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                        </button>
                    </td>
                    <td class="font-medium text-secondary-900">{{ $je->journal_number ?? $je->reference ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($je->entry_date)->format('M d, Y') }}</td>
                    <td class="max-w-xs truncate">{{ $je->description ?? '-' }}</td>
                    <td>
                        @php
                            $jTypeBadge = match($je->type ?? '') {
                                'adjusting' => 'badge-warning',
                                'closing' => 'badge-danger',
                                'reversing' => 'badge-neutral',
                                default => 'badge-info',
                            };
                        @endphp
                        <span class="badge {{ $jTypeBadge }}">{{ ucfirst($je->type ?? 'general') }}</span>
                    </td>
                    <td class="text-right font-medium">{{ '₱' . number_format($je->total_debit ?? 0, 2) }}</td>
                    <td class="text-right font-medium">{{ '₱' . number_format($je->total_credit ?? 0, 2) }}</td>
                    <td><x-badge :status="$je->status ?? 'draft'" /></td>
                </tr>
                {{-- Expandable lines row --}}
                <tr x-data="{ expanded: false }" x-show="expanded" x-transition x-ref="detail_{{ $je->id }}" style="display: none;">
                    <td colspan="8" class="bg-gray-50 p-0">
                        <div class="px-8 py-3">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-secondary-500">
                                        <th class="text-left py-1 font-medium">Account Code</th>
                                        <th class="text-left py-1 font-medium">Account Name</th>
                                        <th class="text-left py-1 font-medium">Description</th>
                                        <th class="text-right py-1 font-medium">Debit</th>
                                        <th class="text-right py-1 font-medium">Credit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($je->lines ?? [] as $line)
                                    <tr class="border-t border-gray-100">
                                        <td class="py-1">{{ $line->account->code ?? $line->account_code ?? '-' }}</td>
                                        <td class="py-1">{{ $line->account->name ?? $line->account_name ?? '-' }}</td>
                                        <td class="py-1">{{ $line->description ?? '-' }}</td>
                                        <td class="py-1 text-right">{{ ($line->debit ?? 0) > 0 ? '₱' . number_format($line->debit, 2) : '-' }}</td>
                                        <td class="py-1 text-right">{{ ($line->credit ?? 0) > 0 ? '₱' . number_format($line->credit, 2) : '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-secondary-400 py-8">
                        <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                        No journal entries found. Click "+ New Journal Entry" to create one.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($journalEntries instanceof \Illuminate\Pagination\LengthAwarePaginator && $journalEntries->hasPages())
    <div class="card-footer">
        {{ $journalEntries->withQueryString()->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // Handle expandable rows - sync the two x-data instances
    document.querySelectorAll('[x-data="{ expanded: false }"]').forEach((el, idx, arr) => {
        if (idx % 2 === 0 && arr[idx + 1]) {
            const trigger = el;
            const detail = arr[idx + 1];
            trigger.addEventListener('click', (e) => {
                if (e.target.closest('button')) {
                    const isExpanded = detail.style.display !== 'none';
                    detail.style.display = isExpanded ? 'none' : '';
                }
            });
        }
    });
});
</script>
@endpush

{{-- New Journal Entry Modal --}}
<x-modal name="new-journal-entry" title="New Journal Entry" maxWidth="5xl">
    <form action="{{ route('gl.journal-entries.store') }}" method="POST" x-data="{
        lines: [
            { account_id: '', description: '', debit: 0, credit: 0 },
            { account_id: '', description: '', debit: 0, credit: 0 }
        ],
        get totalDebit() { return this.lines.reduce((s, l) => s + parseFloat(l.debit || 0), 0); },
        get totalCredit() { return this.lines.reduce((s, l) => s + parseFloat(l.credit || 0), 0); },
        get difference() { return this.totalDebit - this.totalCredit; },
        addLine() { this.lines.push({ account_id: '', description: '', debit: 0, credit: 0 }); },
        removeLine(i) { if (this.lines.length > 2) this.lines.splice(i, 1); }
    }">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="form-label">Date <span class="text-danger-500">*</span></label>
                <input type="date" name="entry_date" class="form-input" value="{{ date('Y-m-d') }}" required>
            </div>
            <div>
                <label class="form-label">Reference</label>
                <input type="text" name="reference" class="form-input" placeholder="Reference #">
            </div>
            <div>
                <label class="form-label">Type <span class="text-danger-500">*</span></label>
                <select name="type" class="form-input" required>
                    <option value="general">General</option>
                    <option value="adjusting">Adjusting</option>
                    <option value="closing">Closing</option>
                    <option value="reversing">Reversing</option>
                    <option value="compound">Compound</option>
                </select>
            </div>
            <div>
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-input" placeholder="Entry description">
            </div>
        </div>

        {{-- Journal Lines --}}
        <div class="mb-4">
            <h4 class="text-sm font-semibold text-secondary-700 mb-2">Journal Lines</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 px-2 font-medium text-secondary-600">Account</th>
                            <th class="text-left py-2 px-2 font-medium text-secondary-600">Description</th>
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
                                            <option value="{{ $acct->id }}">{{ $acct->code }} - {{ $acct->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-1 px-2"><input type="text" x-model="line.description" :name="'lines['+index+'][description]'" class="form-input text-sm" placeholder="Line description"></td>
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

        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'new-journal-entry')" class="btn-secondary">Cancel</button>
            <button type="submit" name="action" value="draft" class="btn-secondary">Save as Draft</button>
            <button type="submit" name="action" value="post" class="btn-primary" :disabled="difference !== 0">Post Entry</button>
        </div>
    </form>
</x-modal>
@endsection
