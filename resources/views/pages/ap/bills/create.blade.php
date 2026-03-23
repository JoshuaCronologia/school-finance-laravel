@extends('layouts.app')
@section('title', isset($bill) ? 'Edit Bill' : 'Create Bill')

@section('content')
<x-page-header :title="isset($bill) ? 'Edit Bill #' . $bill->bill_number : 'Create Supplier Bill'" subtitle="Enter bill details and line items">
    <x-slot:actions>
        <a href="{{ route('ap.bills.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
            Back to Bills
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

<form action="{{ isset($bill) ? route('ap.bills.update', $bill) : route('ap.bills.store') }}" method="POST" id="bill-form">
    @csrf
    @if(isset($bill))
        @method('PUT')
    @endif

    {{-- Header Fields --}}
    <div class="card mb-6">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-secondary-700">Bill Information</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Bill Date <span class="text-danger-500">*</span></label>
                    <input type="date" name="bill_date" class="form-input" value="{{ old('bill_date', isset($bill) ? $bill->bill_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                </div>
                <div>
                    <label class="form-label">Due Date <span class="text-danger-500">*</span></label>
                    <input type="date" name="due_date" class="form-input" value="{{ old('due_date', isset($bill) ? $bill->due_date->format('Y-m-d') : '') }}" required>
                </div>
                <div>
                    <label class="form-label">Reference Number</label>
                    <input type="text" name="reference_number" class="form-input" value="{{ old('reference_number', $bill->reference_number ?? '') }}" placeholder="e.g., INV-2026-001">
                </div>
                <div>
                    <label class="form-label">Vendor <span class="text-danger-500">*</span></label>
                    <select name="vendor_id" class="form-input" required>
                        <option value="">Select Vendor</option>
                        @foreach($vendors ?? [] as $vendor)
                            <option value="{{ $vendor->id }}" {{ old('vendor_id', $bill->vendor_id ?? '') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->name }} {{ $vendor->vendor_code ? '(' . $vendor->vendor_code . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-input">
                        <option value="">Select Department</option>
                        @foreach($departments ?? [] as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $bill->department_id ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-input" value="{{ old('description', $bill->description ?? '') }}" placeholder="Brief description of the bill">
                </div>
            </div>
        </div>
    </div>

    {{-- Line Items --}}
    <div class="card mb-6" x-data="billLineItems()">
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
                        <th class="w-40">Account Code</th>
                        <th>Description</th>
                        <th class="w-20 text-right">Qty</th>
                        <th class="w-28 text-right">Unit Cost</th>
                        <th class="w-28 text-right">Amount</th>
                        <th class="w-32">Tax Code</th>
                        <th class="w-32">WHT Code</th>
                        <th class="w-12"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(line, index) in lines" :key="index">
                        <tr>
                            <td>
                                <select :name="'lines[' + index + '][account_code]'" class="form-input text-sm" x-model="line.account_code">
                                    <option value="">Select</option>
                                    @foreach($accounts ?? [] as $account)
                                        <option value="{{ $account->code }}">{{ $account->code }} - {{ $account->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" :name="'lines[' + index + '][description]'" class="form-input text-sm" x-model="line.description" placeholder="Item description">
                            </td>
                            <td>
                                <input type="number" :name="'lines[' + index + '][qty]'" class="form-input text-sm text-right" x-model.number="line.qty" min="1" step="1" @input="calcLineAmount(index)">
                            </td>
                            <td>
                                <input type="number" :name="'lines[' + index + '][unit_cost]'" class="form-input text-sm text-right" x-model.number="line.unit_cost" min="0" step="0.01" @input="calcLineAmount(index)">
                            </td>
                            <td>
                                <input type="text" class="form-input text-sm text-right bg-gray-50" :value="formatCurrency(line.amount)" readonly>
                                <input type="hidden" :name="'lines[' + index + '][amount]'" :value="line.amount">
                            </td>
                            <td>
                                <select :name="'lines[' + index + '][tax_code]'" class="form-input text-sm" x-model="line.tax_code" @change="recalcTotals()">
                                    <option value="">None</option>
                                    <option value="VAT12">VAT 12%</option>
                                    <option value="VAT_EXEMPT">VAT Exempt</option>
                                    <option value="ZERO_RATED">Zero Rated</option>
                                </select>
                            </td>
                            <td>
                                <select :name="'lines[' + index + '][wht_code]'" class="form-input text-sm" x-model="line.wht_code" @change="recalcTotals()">
                                    <option value="">None</option>
                                    <option value="WC010">WC010 - 1%</option>
                                    <option value="WC020">WC020 - 2%</option>
                                    <option value="WC050">WC050 - 5%</option>
                                    <option value="WC100">WC100 - 10%</option>
                                    <option value="WC150">WC150 - 15%</option>
                                </select>
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

        {{-- Summary --}}
        <div class="card-body border-t border-gray-100">
            <div class="flex justify-end">
                <div class="w-72 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-secondary-600">Gross Amount</span>
                        <span class="font-medium" x-text="'₱' + formatCurrency(totals.gross)"></span>
                        <input type="hidden" name="gross_amount" :value="totals.gross">
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-secondary-600">VAT Amount</span>
                        <span class="font-medium" x-text="'₱' + formatCurrency(totals.vat)"></span>
                        <input type="hidden" name="vat_amount" :value="totals.vat">
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-secondary-600">Withholding Tax</span>
                        <span class="font-medium text-danger-600" x-text="'(₱' + formatCurrency(totals.wht) + ')'"></span>
                        <input type="hidden" name="wht_amount" :value="totals.wht">
                    </div>
                    <div class="flex justify-between text-sm font-semibold border-t border-gray-200 pt-2">
                        <span class="text-secondary-900">Net Payable</span>
                        <span class="text-primary-700" x-text="'₱' + formatCurrency(totals.net)"></span>
                        <input type="hidden" name="net_payable" :value="totals.net">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex justify-end gap-3">
        <a href="{{ route('ap.bills.index') }}" class="btn-secondary">Cancel</a>
        <button type="submit" name="action" value="draft" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" /></svg>
            Save as Draft
        </button>
        <button type="submit" name="action" value="submit" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            Submit for Approval
        </button>
    </div>
</form>

@push('scripts')
<script>
function billLineItems() {
    return {
        lines: @json(old('lines', isset($bill) && $bill->lines ? $bill->lines->toArray() : [
            { account_code: '', description: '', qty: 1, unit_cost: 0, amount: 0, tax_code: '', wht_code: '' }
        ])),
        totals: { gross: 0, vat: 0, wht: 0, net: 0 },

        init() {
            this.recalcTotals();
        },

        addLine() {
            this.lines.push({ account_code: '', description: '', qty: 1, unit_cost: 0, amount: 0, tax_code: '', wht_code: '' });
        },

        removeLine(index) {
            if (this.lines.length > 1) {
                this.lines.splice(index, 1);
                this.recalcTotals();
            }
        },

        calcLineAmount(index) {
            this.lines[index].amount = parseFloat((this.lines[index].qty * this.lines[index].unit_cost).toFixed(2));
            this.recalcTotals();
        },

        recalcTotals() {
            let gross = 0, vat = 0, wht = 0;
            this.lines.forEach(line => {
                const amount = parseFloat(line.amount) || 0;
                gross += amount;

                if (line.tax_code === 'VAT12') {
                    vat += amount * 0.12;
                }

                const whtRates = { 'WC010': 0.01, 'WC020': 0.02, 'WC050': 0.05, 'WC100': 0.10, 'WC150': 0.15 };
                if (whtRates[line.wht_code]) {
                    wht += amount * whtRates[line.wht_code];
                }
            });

            this.totals.gross = parseFloat(gross.toFixed(2));
            this.totals.vat = parseFloat(vat.toFixed(2));
            this.totals.wht = parseFloat(wht.toFixed(2));
            this.totals.net = parseFloat((gross + vat - wht).toFixed(2));
        },

        formatCurrency(val) {
            return parseFloat(val || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }
}
</script>
@endpush
@endsection
