@extends('layouts.app')
@section('title', 'Recurring Journals')

@section('content')
<x-page-header title="Recurring Journals">
    <x-slot:actions>
        <button @click="$dispatch('open-modal', 'create-template')" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            New Template
        </button>
    </x-slot:actions>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Recurring Templates Table --}}
<x-data-table search-placeholder="Search templates...">
    <thead>
        <tr>
            <th>Template Name</th>
            <th>Frequency</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Last Generated</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($templates ?? [] as $template)
        <tr>
            <td class="font-medium text-secondary-900">{{ $template->template_name }}</td>
            <td>
                @php
                    $freqBadge = match($template->frequency ?? '') {
                        'daily' => 'badge-info',
                        'weekly' => 'badge-info',
                        'monthly' => 'badge-success',
                        'quarterly' => 'badge-warning',
                        'annually' => 'badge-neutral',
                        default => 'badge-neutral',
                    };
                @endphp
                <span class="badge {{ $freqBadge }}">{{ ucfirst($template->frequency ?? '-') }}</span>
            </td>
            <td>{{ $template->start_date ? \Carbon\Carbon::parse($template->start_date)->format('M d, Y') : '-' }}</td>
            <td>{{ $template->end_date ? \Carbon\Carbon::parse($template->end_date)->format('M d, Y') : 'No End' }}</td>
            <td><x-badge :status="$template->status ?? 'active'" /></td>
            <td>{{ $template->last_generated_at ? \Carbon\Carbon::parse($template->last_generated_at)->format('M d, Y') : 'Never' }}</td>
            <td class="flex items-center gap-2">
                <button @click="$dispatch('open-modal', 'edit-template-{{ $template->id }}')" class="text-primary-600 hover:text-primary-700 text-sm font-medium">Edit</button>
                <form action="{{ route('gl.recurring.generate', $template) }}" method="POST" data-turbo="false" class="inline">
                    @csrf
                    <button type="submit" class="text-success-600 hover:text-success-700 text-sm font-medium">Generate</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center text-secondary-400 py-8">
                <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 0 0-3.7-3.7 48.678 48.678 0 0 0-7.324 0 4.006 4.006 0 0 0-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 0 0 3.7 3.7 48.656 48.656 0 0 0 7.324 0 4.006 4.006 0 0 0 3.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3-3 3" /></svg>
                No recurring templates found. Click "+ New Template" to create one.
            </td>
        </tr>
        @endforelse
    </tbody>
</x-data-table>

{{-- Create Template Modal --}}
<x-modal name="create-template" title="Create Recurring Journal Template" maxWidth="4xl">
    <form action="{{ route('gl.recurring.store') }}" method="POST" data-turbo="false" v-pre x-data="{
        lines: [
            { account_id: '', description: '', debit: 0, credit: 0 },
            { account_id: '', description: '', debit: 0, credit: 0 }
        ],
        get totalDebit() { return this.lines.reduce((s, l) => s + parseFloat(l.debit || 0), 0); },
        get totalCredit() { return this.lines.reduce((s, l) => s + parseFloat(l.credit || 0), 0); },
        addLine() { this.lines.push({ account_id: '', description: '', debit: 0, credit: 0 }); },
        removeLine(i) { if (this.lines.length > 2) this.lines.splice(i, 1); }
    }">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="form-label">Template Name <span class="text-danger-500">*</span></label>
                <input type="text" name="template_name" class="form-input" required placeholder="e.g., Monthly Depreciation">
            </div>
            <div>
                <label class="form-label">Frequency <span class="text-danger-500">*</span></label>
                <select name="frequency" class="form-input" required>
                    <option value="">Select</option>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="semi-annually">Semi-Annually</option>
                    <option value="annually">Annually</option>
                </select>
            </div>
            <div>
                <label class="form-label">JE Type</label>
                <select name="type" class="form-input">
                    <option value="general">General</option>
                    <option value="adjusting">Adjusting</option>
                </select>
            </div>
            <div>
                <label class="form-label">Start Date <span class="text-danger-500">*</span></label>
                <input type="date" name="start_date" class="form-input" required>
            </div>
            <div>
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-input" placeholder="Optional">
            </div>
            <div class="md:col-span-3">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-input" placeholder="Template description">
            </div>
        </div>

        {{-- Journal Lines --}}
        <div class="mb-4">
            <h4 class="text-sm font-semibold text-secondary-700 mb-2">Template Lines</h4>
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
                                            <option value="{{ $acct->id }}">{{ $acct->account_code }} - {{ $acct->account_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-1 px-2"><input type="text" x-model="line.description" :name="'lines['+index+'][description]'" class="form-input text-sm" placeholder="Description"></td>
                                <td class="py-1 px-2"><input type="number" x-model="line.debit" :name="'lines['+index+'][debit]'" class="form-input text-sm text-right" step="0.01" min="0"></td>
                                <td class="py-1 px-2"><input type="number" x-model="line.credit" :name="'lines['+index+'][credit]'" class="form-input text-sm text-right" step="0.01" min="0"></td>
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
                    </tfoot>
                </table>
            </div>
            <button type="button" @click="addLine()" class="mt-2 text-sm text-primary-600 hover:text-primary-700 font-medium">+ Add Line</button>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'create-template')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save Template</button>
        </div>
    </form>
</x-modal>

{{-- Edit Template Modals --}}
@foreach($templates ?? [] as $template)
<x-modal name="edit-template-{{ $template->id }}" title="Edit Template: {{ $template->template_name }}" maxWidth="4xl">
    <form action="{{ route('gl.recurring.update', $template) }}" method="POST" data-turbo="false">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="form-label">Template Name <span class="text-danger-500">*</span></label>
                <input type="text" name="template_name" class="form-input" value="{{ $template->template_name }}" required>
            </div>
            <div>
                <label class="form-label">Frequency <span class="text-danger-500">*</span></label>
                <select name="frequency" class="form-input" required>
                    @foreach(['daily', 'weekly', 'monthly', 'quarterly', 'annually'] as $f)
                        <option value="{{ $f }}" {{ ($template->frequency ?? '') == $f ? 'selected' : '' }}>{{ ucfirst($f) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    <option value="active" {{ ($template->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ ($template->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-input" value="{{ $template->start_date ? \Carbon\Carbon::parse($template->start_date)->format('Y-m-d') : '' }}">
            </div>
            <div>
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-input" value="{{ $template->end_date ? \Carbon\Carbon::parse($template->end_date)->format('Y-m-d') : '' }}">
            </div>
            <div class="md:col-span-3">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-input" value="{{ $template->description ?? '' }}">
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'edit-template-{{ $template->id }}')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Update Template</button>
        </div>
    </form>
</x-modal>
@endforeach
@endsection
