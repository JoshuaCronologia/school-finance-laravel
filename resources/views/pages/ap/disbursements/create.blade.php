@extends('layouts.app')
@section('title', isset($disbursement) ? 'Edit Disbursement Request' : 'Create Disbursement Request')

@section('content')
<x-page-header :title="isset($disbursement) ? 'Edit Request #' . $disbursement->request_number : 'Create Disbursement Request'" subtitle="Fill in the request details and line items">
    <x-slot:actions>
        <a href="{{ route('ap.disbursements.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
            Back to Requests
        </a>
    </x-slot:actions>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif
@if($errors->any())
    <x-alert type="danger" class="mb-4">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif

<form action="{{ isset($disbursement) ? route('ap.disbursements.update', $disbursement) : route('ap.disbursements.store') }}" method="POST" enctype="multipart/form-data" x-data="disbursementForm()">
    @csrf
    @if(isset($disbursement))
        @method('PUT')
    @endif

    {{-- Request Details --}}
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-secondary-700">Request Details</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Request Date <span class="text-danger-500">*</span></label>
                    <input type="date" name="request_date" class="form-input" value="{{ old('request_date', isset($disbursement) ? $disbursement->request_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                </div>
                <div>
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-input" value="{{ old('due_date', isset($disbursement) ? $disbursement->due_date?->format('Y-m-d') : '') }}">
                </div>
                <div>
                    <label class="form-label">Payee Type <span class="text-danger-500">*</span></label>
                    <select name="payee_type" class="form-input" x-model="payeeType" required>
                        <option value="">Select Type</option>
                        <option value="vendor" {{ old('payee_type', $disbursement->payee_type ?? '') == 'vendor' ? 'selected' : '' }}>Vendor</option>
                        <option value="employee" {{ old('payee_type', $disbursement->payee_type ?? '') == 'employee' ? 'selected' : '' }}>Employee</option>
                        <option value="other" {{ old('payee_type', $disbursement->payee_type ?? '') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Payee <span class="text-danger-500">*</span></label>
                    <template x-if="payeeType === 'vendor'">
                        <div>
                            <select name="payee_id" class="form-input" x-model="payeeId" @change="payeeName = $event.target.options[$event.target.selectedIndex].dataset.name || ''" required>
                                <option value="">Select Vendor</option>
                                @foreach($vendors ?? [] as $vendor)
                                    <option value="{{ $vendor->id }}" data-name="{{ $vendor->name }}" {{ old('payee_id', $disbursement->payee_id ?? '') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="payee_name" :value="payeeName">
                        </div>
                    </template>
                    <template x-if="payeeType === 'employee'">
                        <div>
                            <select name="payee_id" class="form-input" x-model="payeeId" @change="payeeName = $event.target.options[$event.target.selectedIndex].dataset.name || ''" required>
                                <option value="">Select Employee</option>
                                @foreach($employees ?? [] as $emp)
                                    <option value="{{ $emp->id }}" data-name="{{ $emp->name }}" {{ old('payee_id', $disbursement->payee_id ?? '') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="payee_name" :value="payeeName">
                        </div>
                    </template>
                    <template x-if="payeeType === 'other'">
                        <input type="text" name="payee_name" class="form-input" x-model="payeeName" value="{{ old('payee_name', $disbursement->payee_name ?? '') }}" placeholder="Enter payee name" required>
                    </template>
                    <template x-if="!payeeType">
                        <input type="text" class="form-input bg-gray-50" placeholder="Select payee type first" disabled>
                    </template>
                </div>
                <div>
                    <label class="form-label">Department <span class="text-danger-500">*</span></label>
                    <select name="department_id" class="form-input" x-model="departmentId" @change="checkBudget()" required>
                        <option value="">Select Department</option>
                        @foreach($departments ?? [] as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $disbursement->department_id ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Expense Category <span class="text-danger-500">*</span></label>
                    <select name="category_id" class="form-input" x-model="categoryId" @change="checkBudget()" required>
                        <option value="">Select Category</option>
                        @foreach($categories ?? [] as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $disbursement->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Cost Center</label>
                    <select name="cost_center_id" class="form-input">
                        <option value="">Select Cost Center</option>
                        @foreach($costCenters ?? [] as $cc)
                            <option value="{{ $cc->id }}" {{ old('cost_center_id', $disbursement->cost_center_id ?? '') == $cc->id ? 'selected' : '' }}>{{ $cc->name ?? $cc->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Project</label>
                    <input type="text" name="project" class="form-input" value="{{ old('project', $disbursement->project ?? '') }}" placeholder="Project name or code">
                </div>
                <div>
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-input">
                        <option value="">Select Method</option>
                        @foreach(['check', 'bank_transfer', 'cash', 'online'] as $m)
                            <option value="{{ $m }}" {{ old('payment_method', $disbursement->payment_method ?? '') == $m ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $m)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="form-label">Memo</label>
                    <textarea name="description" class="form-input" rows="2" placeholder="Purpose of this disbursement request...">{{ old('description', $disbursement->description ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Budget Check --}}
    <div class="card mb-6" x-show="budgetInfo.loaded" x-cloak>
        <div class="card-header">
            <h3 class="text-sm font-semibold text-secondary-700">Budget Check</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-center">
                <div>
                    <p class="text-xs text-secondary-500">Budget</p>
                    <p class="text-sm font-semibold text-secondary-900" x-text="'₱' + formatNum(budgetInfo.budget)"></p>
                </div>
                <div>
                    <p class="text-xs text-secondary-500">Committed</p>
                    <p class="text-sm font-semibold text-warning-600" x-text="'₱' + formatNum(budgetInfo.committed)"></p>
                </div>
                <div>
                    <p class="text-xs text-secondary-500">Actual</p>
                    <p class="text-sm font-semibold text-secondary-900" x-text="'₱' + formatNum(budgetInfo.actual)"></p>
                </div>
                <div>
                    <p class="text-xs text-secondary-500">Remaining</p>
                    <p class="text-sm font-semibold" :class="budgetInfo.remaining >= 0 ? 'text-success-600' : 'text-danger-600'" x-text="'₱' + formatNum(budgetInfo.remaining)"></p>
                </div>
                <div>
                    <p class="text-xs text-secondary-500">Requested</p>
                    <p class="text-sm font-semibold text-primary-600" x-text="'₱' + formatNum(totalAmount)"></p>
                </div>
            </div>
            <div x-show="totalAmount > budgetInfo.remaining" class="mt-3">
                <x-alert type="warning">
                    <span class="text-sm font-medium">Warning: Requested amount exceeds remaining budget by <span x-text="'₱' + formatNum(totalAmount - budgetInfo.remaining)"></span>.</span>
                </x-alert>
            </div>
        </div>
    </div>

    {{-- Line Items --}}
    <div class="card mb-6">
        <div class="card-header">
            <div class="flex items-center justify-between w-full">
                <h3 class="text-sm font-semibold text-secondary-700">Line Items</h3>
                <button type="button" @click="addLine()" class="btn-secondary text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Add Row
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Memo</th>
                        <th class="w-20 text-right">Qty</th>
                        <th class="w-28 text-right">Unit Cost</th>
                        <th class="w-28 text-right">Amount</th>
                        <th class="w-36">Account Code</th>
                        <th class="w-28">Tax Code</th>
                        <th>Remarks</th>
                        <th class="w-12"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(line, index) in lines" :key="index">
                        <tr>
                            <td>
                                <input type="text" :name="'items[' + index + '][description]'" class="form-input text-sm" x-model="line.description" placeholder="Item description" required>
                            </td>
                            <td>
                                <input type="number" :name="'items[' + index + '][quantity]'" class="form-input text-sm text-right" x-model.number="line.qty" min="1" step="1" @input="calcLine(index)">
                            </td>
                            <td>
                                <input type="number" :name="'items[' + index + '][unit_cost]'" class="form-input text-sm text-right" x-model.number="line.unit_cost" min="0" step="0.01" @input="calcLine(index)">
                            </td>
                            <td>
                                <input type="text" class="form-input text-sm text-right bg-gray-50" :value="formatNum(line.amount)" readonly>
                                <input type="hidden" :name="'items[' + index + '][amount]'" :value="line.amount">
                            </td>
                            <td>
                                <select :name="'items[' + index + '][account_code]'" class="form-input text-sm" x-model="line.account_code">
                                    <option value="">Select Account</option>
                                    @foreach($accounts ?? [] as $account)
                                        <option value="{{ $account->account_code }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select :name="'items[' + index + '][tax_code]'" class="form-input text-sm" x-model="line.tax_code">
                                    <option value="">None</option>
                                    <option value="VAT12">VAT 12%</option>
                                    <option value="VAT_EXEMPT">Exempt</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" :name="'items[' + index + '][remarks]'" class="form-input text-sm" x-model="line.remarks" placeholder="Notes">
                            </td>
                            <td>
                                <button type="button" @click="removeLine(index)" class="btn-icon text-danger-500 hover:text-danger-700" x-show="lines.length > 1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="card-body border-t border-gray-100">
            <div class="flex justify-end">
                <div class="w-64">
                    <div class="flex justify-between text-sm font-semibold">
                        <span class="text-secondary-700">Total Amount</span>
                        <span class="text-primary-700" x-text="'₱' + formatNum(totalAmount)"></span>
                    </div>
                    <input type="hidden" name="amount" :value="totalAmount">
                </div>
            </div>
        </div>
    </div>

    {{-- Attachments --}}
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-secondary-700">Attachments</h3>
        </div>
        <div class="card-body">
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary-400 transition-colors">
                <svg class="w-8 h-8 mx-auto mb-2 text-secondary-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" /></svg>
                <p class="text-sm text-secondary-500 mb-2">Drag and drop files here, or click to browse</p>
                <input type="file" name="attachments[]" multiple class="form-input text-sm" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                <p class="text-xs text-secondary-400 mt-2">Supported: PDF, DOC, XLS, JPG, PNG (max 10MB each)</p>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex justify-end gap-3">
        <a href="{{ route('ap.disbursements.index') }}" class="btn-secondary">Cancel</a>
        <button type="submit" name="action" value="draft" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" /></svg>
            Save Draft
        </button>
        <button type="submit" name="action" value="submit" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            Submit for Approval
        </button>
    </div>
</form>

@php
    $defaultLine = ['description' => '', 'qty' => 1, 'unit_cost' => 0, 'amount' => 0, 'account_code' => '', 'tax_code' => '', 'remarks' => ''];
    $initialLines = old('lines', isset($disbursement) && $disbursement->lines ? $disbursement->lines->toArray() : [$defaultLine]);
@endphp

@push('scripts')
<script>
function disbursementForm() {
    return {
        payeeType: '{{ old('payee_type', $disbursement->payee_type ?? '') }}',
        payeeId: '{{ old('payee_id', $disbursement->payee_id ?? '') }}',
        payeeName: '{{ old('payee_name', $disbursement->payee_name ?? '') }}',
        departmentId: '{{ old('department_id', $disbursement->department_id ?? '') }}',
        categoryId: '{{ old('category_id', $disbursement->category_id ?? '') }}',
        lines: @json($initialLines),
        budgetInfo: { loaded: false, budget: 0, committed: 0, actual: 0, remaining: 0 },

        get totalAmount() {
            return this.lines.reduce((sum, line) => sum + (parseFloat(line.amount) || 0), 0);
        },

        addLine() {
            this.lines.push({ description: '', qty: 1, unit_cost: 0, amount: 0, account_code: '', tax_code: '', remarks: '' });
        },

        removeLine(index) {
            if (this.lines.length > 1) this.lines.splice(index, 1);
        },

        calcLine(index) {
            this.lines[index].amount = parseFloat((this.lines[index].qty * this.lines[index].unit_cost).toFixed(2));
        },

        async checkBudget() {
            if (!this.departmentId || !this.categoryId) return;
            try {
                const resp = await fetch(`/api/budget/check?department_id=${this.departmentId}&category_id=${this.categoryId}`);
                const data = await resp.json();
                this.budgetInfo = { loaded: true, ...data };
            } catch (e) {
                this.budgetInfo.loaded = false;
            }
        },

        formatNum(val) {
            return parseFloat(val || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }
}
</script>
@endpush
@endsection
