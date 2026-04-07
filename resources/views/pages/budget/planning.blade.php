@extends('layouts.app')
@section('title', 'Budget Planning')

@section('content')
<x-page-header title="Budget Planning" subtitle="Create and manage budget plans">
    <x-slot name="actions">
        <button onclick="document.dispatchEvent(new CustomEvent('open-modal', { detail: 'copy-previous-budget' }))" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" /></svg>
            Copy from Previous Year
        </button>
        <a href="#" class="btn-secondary opacity-50 cursor-not-allowed" title="Coming soon">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
            Import
        </a>
        <a href="{{ route('budget.planning.export') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Export
        </a>
        <button @click="$dispatch('open-modal', 'create-budget')" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            New Budget
        </button>
    </x-slot>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Total Budget Summary --}}
@php $grandTotal = $budgets instanceof \Illuminate\Pagination\LengthAwarePaginator ? $budgets->sum('annual_budget') : collect($budgets)->sum('annual_budget'); @endphp
<div class="card mb-4">
    <div class="card-body">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-secondary-600">Total Budget (this page)</span>
            <span class="text-lg font-bold text-secondary-900">{{ '₱' . number_format($grandTotal, 2) }}</span>
        </div>
    </div>
</div>

{{-- Budget Items Table --}}
<x-data-table search-placeholder="Search budgets...">
    {{-- <x-slot name="actions">
        <button @click="$dispatch('open-modal', 'create-budget')" class="btn-primary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            New Budget
        </button>
    </x-slot> --}}
    <thead>
        <tr>
            <th>Budget Name</th>
            <th>Department</th>
            <th>Category</th>
            <th class="text-right">Annual Budget</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($budgets as $budget)
        <tr>
            <td class="font-medium">{{ $budget->budget_name }}</td>
            <td>{{ $budget->department->name ?? '-' }}</td>
            <td>{{ $budget->category->name ?? '-' }}</td>
            <td class="text-right font-medium">{{ '₱' . number_format($budget->annual_budget, 2) }}</td>
            <td><x-badge :status="$budget->status" /></td>
            <td>
                <button @click="$dispatch('open-modal', 'edit-budget-{{ $budget->id }}')" class="text-primary-600 hover:text-primary-700 text-sm font-medium">Edit</button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center text-secondary-400 py-8">
                <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9.75m3 0v3.375m0-3.375h3.375M6.75 3h3.375" /></svg>
                No budgets found. Click "+ New Budget" to create one.
            </td>
        </tr>
        @endforelse
    </tbody>
    @if($budgets instanceof \Illuminate\Pagination\LengthAwarePaginator && $budgets->hasPages())
    <x-slot name="footer">
        {{ $budgets->links() }}
    </x-slot>
    @endif
</x-data-table>

{{-- Create Budget Modal --}}
<x-modal name="create-budget" title="Create New Budget" maxWidth="3xl">
    <form action="{{ route('budget.planning.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Budget Name <span class="text-danger-500">*</span></label>
                <input type="text" name="budget_name" class="form-input" required placeholder="e.g., Office Supplies - Admin">
            </div>
            <div>
                <label class="form-label">School Year</label>
                <select name="school_year" class="form-input">
                    @for($y = now()->year - 1; $y <= now()->year + 2; $y++)
                        <option value="{{ $y }}-{{ $y + 1 }}" {{ ($y == now()->year) ? 'selected' : '' }}>{{ $y }}-{{ $y + 1 }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="form-label">Department <span class="text-danger-500">*</span></label>
                <select name="department_id" class="form-input" required>
                    <option value="">Select Department</option>
                    @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Expense Category <span class="text-danger-500">*</span></label>
                <select name="category_id" class="form-input" required>
                    <option value="">Select Category</option>
                    @foreach($categories ?? [] as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Cost Center</label>
                <select name="cost_center_id" class="form-input">
                    <option value="">Select Cost Center</option>
                    @foreach($costCenters ?? [] as $cc)
                    <option value="{{ $cc->id }}">{{ $cc->name ?? $cc->code }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Fund Source</label>
                <select name="fund_source_id" class="form-input">
                    <option value="">Select Fund Source</option>
                    @foreach($fundSources ?? [] as $fs)
                    <option value="{{ $fs->id }}">{{ $fs->name ?? $fs->code }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Project / Activity</label>
                <input type="text" name="project" class="form-input" placeholder="e.g., Foundation Day 2026">
            </div>
            <div>
                <label class="form-label">Campus</label>
                <input type="text" name="campus" class="form-input" value="Main">
            </div>
            <div>
                <label class="form-label">Annual Budget Amount <span class="text-danger-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400 text-sm">₱</span>
                    <input type="number" name="annual_budget" class="form-input pl-7" step="0.01" min="0" required placeholder="0.00">
                </div>
            </div>
            <div>
                <label class="form-label">Budget Owner</label>
                <input type="text" name="budget_owner" class="form-input" placeholder="Person responsible">
            </div>
            <div class="md:col-span-2">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-input" rows="2" placeholder="Additional notes or justification..."></textarea>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'create-budget')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Create Budget</button>
        </div>
    </form>
</x-modal>

{{-- Edit Budget Modals --}}
{{-- Copy from Previous Year Modal --}}
<x-modal name="copy-previous-budget" title="Copy Budgets from Previous Year" maxWidth="lg">
    <form action="{{ route('budget.planning.copy-previous') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 gap-4">
            <div>
                <label class="form-label">Source School Year <span class="text-danger-500">*</span></label>
                <select name="source_year" class="form-input" required>
                    @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}-{{ $y + 1 }}" {{ ($y == now()->year - 1) ? 'selected' : '' }}>{{ $y }}-{{ $y + 1 }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="form-label">Target School Year <span class="text-danger-500">*</span></label>
                <select name="target_year" class="form-input" required>
                    @for($y = now()->year - 1; $y <= now()->year + 2; $y++)
                        <option value="{{ $y }}-{{ $y + 1 }}" {{ ($y == now()->year) ? 'selected' : '' }}>{{ $y }}-{{ $y + 1 }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="form-label">Budget Adjustment (%)</label>
                <input type="number" name="adjust_percentage" class="form-input" step="0.1" min="-100" max="100" value="0" placeholder="e.g., 5 for 5% increase">
                <p class="text-xs text-secondary-400 mt-1">Positive = increase, negative = decrease. Leave 0 for exact copy.</p>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" onclick="document.dispatchEvent(new CustomEvent('close-modal', { detail: 'copy-previous-budget' }))" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Copy Budgets</button>
        </div>
    </form>
</x-modal>

@foreach($budgets as $budget)
<x-modal name="edit-budget-{{ $budget->id }}" title="Edit Budget" maxWidth="3xl">
    <form action="{{ route('budget.planning.update', $budget) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Budget Name <span class="text-danger-500">*</span></label>
                <input type="text" name="budget_name" class="form-input" value="{{ $budget->budget_name }}" required>
            </div>
            <div>
                <label class="form-label">School Year</label>
                <select name="school_year" class="form-input">
                    @for($y = now()->year - 1; $y <= now()->year + 2; $y++)
                        <option value="{{ $y }}-{{ $y + 1 }}" {{ $budget->school_year == "$y-" . ($y+1) ? 'selected' : '' }}>{{ $y }}-{{ $y + 1 }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="form-label">Department <span class="text-danger-500">*</span></label>
                <select name="department_id" class="form-input" required>
                    @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept->id }}" {{ $budget->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Expense Category <span class="text-danger-500">*</span></label>
                <select name="category_id" class="form-input" required>
                    @foreach($categories ?? [] as $cat)
                    <option value="{{ $cat->id }}" {{ $budget->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Cost Center</label>
                <select name="cost_center_id" class="form-input">
                    <option value="">Select Cost Center</option>
                    @foreach($costCenters ?? [] as $cc)
                    <option value="{{ $cc->id }}" {{ ($budget->cost_center_id ?? '') == $cc->id ? 'selected' : '' }}>{{ $cc->name ?? $cc->code }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Fund Source</label>
                <select name="fund_source_id" class="form-input">
                    <option value="">Select Fund Source</option>
                    @foreach($fundSources ?? [] as $fs)
                    <option value="{{ $fs->id }}" {{ ($budget->fund_source_id ?? '') == $fs->id ? 'selected' : '' }}>{{ $fs->name ?? $fs->code }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Project / Activity</label>
                <input type="text" name="project_activity" class="form-input" value="{{ $budget->project_activity ?? '' }}">
            </div>
            <div>
                <label class="form-label">Campus</label>
                <input type="text" name="campus" class="form-input" value="{{ $budget->campus ?? 'Main' }}">
            </div>
            <div>
                <label class="form-label">Annual Budget Amount <span class="text-danger-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400 text-sm">₱</span>
                    <input type="number" name="annual_budget" class="form-input pl-7" step="0.01" value="{{ $budget->annual_budget }}" required>
                </div>
            </div>
            <div>
                <label class="form-label">Budget Owner</label>
                <input type="text" name="budget_owner" class="form-input" value="{{ $budget->budget_owner ?? '' }}">
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    @foreach(['draft','approved','active','closed'] as $s)
                    <option value="{{ $s }}" {{ $budget->status == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                {{-- Spacer for grid alignment --}}
            </div>
            <div class="md:col-span-2">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-input" rows="2">{{ $budget->notes }}</textarea>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'edit-budget-{{ $budget->id }}')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Update Budget</button>
        </div>
    </form>
</x-modal>
@endforeach
@endsection
