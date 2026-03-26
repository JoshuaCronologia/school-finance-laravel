@extends('layouts.app')
@section('title', 'AR Invoices / Charges')

@section('content')
@php
    $invoiceCount = $invoices instanceof \Illuminate\Pagination\LengthAwarePaginator ? $invoices->total() : count($invoices);
    $totalInvoiced = collect($invoices instanceof \Illuminate\Pagination\LengthAwarePaginator ? $invoices->items() : $invoices)->sum('net_amount');
    $totalCollected = collect($invoices instanceof \Illuminate\Pagination\LengthAwarePaginator ? $invoices->items() : $invoices)->sum('amount_paid');
    $totalOutstanding = $totalInvoiced - $totalCollected;
@endphp

<x-page-header title="AR Invoices / Charges" :subtitle="$invoiceCount . ' invoices'">
    <x-slot:actions>
        <button @click="$dispatch('open-modal', 'new-invoice')" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            New Invoice
        </button>
    </x-slot:actions>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Stat Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <x-stat-card label="Total Invoiced" :value="'₱' . number_format($totalInvoiced, 2)" color="blue"
        icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>' />

    <x-stat-card label="Total Collected" :value="'₱' . number_format($totalCollected, 2)" color="green"
        icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>' />

    <x-stat-card label="Outstanding" :value="'₱' . number_format($totalOutstanding, 2)" color="red"
        icon='<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>' />
</div>

{{-- Filters --}}
<x-filter-bar action="{{ route('ar.invoices.index') }}" method="GET">
    <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-input w-44">
            <option value="">All Status</option>
            @foreach(['draft', 'posted', 'partially_paid', 'paid', 'overdue', 'voided'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label">School Year</label>
        <select name="school_year" class="form-input w-40">
            <option value="">All Years</option>
            @foreach($schoolYears ?? [] as $sy)
                <option value="{{ $sy }}" {{ request('school_year') == $sy ? 'selected' : '' }}>{{ $sy }}</option>
            @endforeach
        </select>
    </div>
</x-filter-bar>

{{-- Invoices Table --}}
<x-data-table search-placeholder="Search invoices...">
    <thead>
        <tr>
            <th>Invoice #</th>
            <th>Date</th>
            <th>Customer</th>
            <th>School Year</th>
            <th>Semester</th>
            <th>Description</th>
            <th class="text-right">Gross</th>
            <th class="text-right">Discount</th>
            <th class="text-right">Net</th>
            <th class="text-right">Paid</th>
            <th class="text-right">Balance</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($invoices as $invoice)
        <tr>
            <td class="font-medium">
                <a href="#" @click.prevent="$dispatch('open-modal', 'edit-invoice-{{ $invoice->id }}')" class="text-primary-600 hover:text-primary-700 hover:underline">
                    {{ $invoice->invoice_number }}
                </a>
            </td>
            <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }}</td>
            <td>{{ $invoice->customer->name ?? $invoice->customer_name ?? '-' }}</td>
            <td>{{ $invoice->school_year ?? '-' }}</td>
            <td>{{ $invoice->semester ?? '-' }}</td>
            <td class="max-w-xs truncate">{{ $invoice->description ?? '-' }}</td>
            <td class="text-right">{{ '₱' . number_format($invoice->gross_amount ?? 0, 2) }}</td>
            <td class="text-right">{{ '₱' . number_format($invoice->discount_amount ?? 0, 2) }}</td>
            <td class="text-right font-medium">{{ '₱' . number_format($invoice->net_amount ?? 0, 2) }}</td>
            <td class="text-right">{{ '₱' . number_format($invoice->amount_paid ?? 0, 2) }}</td>
            <td class="text-right font-medium {{ ($invoice->balance ?? 0) > 0 ? 'text-danger-500' : '' }}">{{ '₱' . number_format($invoice->balance ?? 0, 2) }}</td>
            <td><x-badge :status="$invoice->status ?? 'draft'" /></td>
        </tr>
        @empty
        <tr>
            <td colspan="12" class="text-center text-secondary-400 py-8">
                <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                No invoices found. Click "+ New Invoice" to create one.
            </td>
        </tr>
        @endforelse
    </tbody>
    @if($invoices instanceof \Illuminate\Pagination\LengthAwarePaginator && $invoices->hasPages())
    <x-slot:footer>
        {{ $invoices->withQueryString()->links() }}
    </x-slot:footer>
    @endif
</x-data-table>

{{-- New Invoice Modal --}}
<x-modal name="new-invoice" title="New Invoice" maxWidth="5xl">
    <form action="{{ route('ar.invoices.store') }}" method="POST" v-pre x-data="{
        lines: [{ fee_code: '', description: '', qty: 1, unit_amount: 0, amount: 0, revenue_account: '' }],
        discount: 0,
        tax: 0,
        get gross() { return this.lines.reduce((s, l) => s + parseFloat(l.amount || 0), 0); },
        get net() { return this.gross - parseFloat(this.discount || 0) + parseFloat(this.tax || 0); },
        updateAmount(i) { this.lines[i].amount = (parseFloat(this.lines[i].qty || 0) * parseFloat(this.lines[i].unit_amount || 0)).toFixed(2); },
        addLine() { this.lines.push({ fee_code: '', description: '', qty: 1, unit_amount: 0, amount: 0, revenue_account: '' }); },
        removeLine(i) { if (this.lines.length > 1) this.lines.splice(i, 1); }
    }">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="form-label">Customer <span class="text-danger-500">*</span></label>
                <select name="customer_id" class="form-input" required>
                    <option value="">Select Customer</option>
                    @foreach($customers ?? [] as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Invoice Date <span class="text-danger-500">*</span></label>
                <input type="date" name="invoice_date" class="form-input" value="{{ date('Y-m-d') }}" required>
            </div>
            <div>
                <label class="form-label">Due Date <span class="text-danger-500">*</span></label>
                <input type="date" name="due_date" class="form-input" required>
            </div>
            <div>
                <label class="form-label">School Year</label>
                <input type="text" name="school_year" class="form-input" placeholder="e.g., 2025-2026">
            </div>
            <div>
                <label class="form-label">Semester</label>
                <select name="semester" class="form-input">
                    <option value="">Select</option>
                    <option value="1st Semester">1st Semester</option>
                    <option value="2nd Semester">2nd Semester</option>
                    <option value="Summer">Summer</option>
                    <option value="Full Year">Full Year</option>
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-input" placeholder="Invoice description">
            </div>
        </div>

        {{-- Line Items --}}
        <div class="mb-4">
            <h4 class="text-sm font-semibold text-secondary-700 mb-2">Line Items</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 px-2 font-medium text-secondary-600">Fee Code</th>
                            <th class="text-left py-2 px-2 font-medium text-secondary-600">Description</th>
                            <th class="text-right py-2 px-2 font-medium text-secondary-600 w-20">Qty</th>
                            <th class="text-right py-2 px-2 font-medium text-secondary-600 w-32">Unit Amount</th>
                            <th class="text-right py-2 px-2 font-medium text-secondary-600 w-32">Amount</th>
                            <th class="text-left py-2 px-2 font-medium text-secondary-600">Revenue Account</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(line, index) in lines" :key="index">
                            <tr class="border-b border-gray-100">
                                <td class="py-1 px-2"><input type="text" x-model="line.fee_code" :name="'lines['+index+'][fee_code]'" class="form-input text-sm" placeholder="FEE-001"></td>
                                <td class="py-1 px-2"><input type="text" x-model="line.description" :name="'lines['+index+'][description]'" class="form-input text-sm" placeholder="Description"></td>
                                <td class="py-1 px-2"><input type="number" x-model="line.qty" :name="'lines['+index+'][quantity]'" @input="updateAmount(index)" class="form-input text-sm text-right" min="1"></td>
                                <td class="py-1 px-2"><input type="number" x-model="line.unit_amount" :name="'lines['+index+'][unit_amount]'" @input="updateAmount(index)" class="form-input text-sm text-right" step="0.01" min="0"></td>
                                <td class="py-1 px-2"><input type="text" :value="parseFloat(line.amount).toFixed(2)" class="form-input text-sm text-right bg-gray-50" readonly>
                                    <input type="hidden" :name="'lines['+index+'][amount]'" :value="line.amount"></td>
                                <td class="py-1 px-2">
                                    <select x-model="line.revenue_account" :name="'lines['+index+'][revenue_account_id]'" class="form-input text-sm">
                                        <option value="">Select</option>
                                        @foreach($revenueAccounts ?? [] as $acct)
                                            <option value="{{ $acct->id }}">{{ $acct->account_code }} - {{ $acct->account_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-1 px-2">
                                    <button type="button" @click="removeLine(index)" x-show="lines.length > 1" class="text-danger-500 hover:text-danger-700">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <button type="button" @click="addLine()" class="mt-2 text-sm text-primary-600 hover:text-primary-700 font-medium">+ Add Line Item</button>
        </div>

        {{-- Summary --}}
        <div class="flex justify-end">
            <div class="w-64 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-secondary-600">Gross Amount:</span><span class="font-medium" x-text="'₱' + gross.toFixed(2)"></span></div>
                <div class="flex justify-between items-center">
                    <span class="text-secondary-600">Discount:</span>
                    <input type="number" name="discount_amount" x-model="discount" class="form-input w-28 text-right text-sm" step="0.01" min="0">
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-secondary-600">Tax:</span>
                    <input type="number" name="tax_amount" x-model="tax" class="form-input w-28 text-right text-sm" step="0.01" min="0">
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-200 font-semibold"><span>Net Amount:</span><span x-text="'₱' + net.toFixed(2)"></span></div>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'new-invoice')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save Invoice</button>
        </div>
    </form>
</x-modal>

{{-- Edit Invoice Modals --}}
@foreach($invoices as $invoice)
<x-modal name="edit-invoice-{{ $invoice->id }}" title="Edit Invoice #{{ $invoice->invoice_number }}" maxWidth="5xl">
    <form action="{{ route('ar.invoices.update', $invoice) }}" method="POST" v-pre x-data="{
        lines: @js($invoice->lines ?? [['fee_code' => '', 'description' => '', 'qty' => 1, 'unit_amount' => 0, 'amount' => 0, 'revenue_account' => '']]),
        discount: {{ $invoice->discount_amount ?? 0 }},
        tax: {{ $invoice->tax_amount ?? 0 }},
        get gross() { return this.lines.reduce((s, l) => s + parseFloat(l.amount || 0), 0); },
        get net() { return this.gross - parseFloat(this.discount || 0) + parseFloat(this.tax || 0); },
        updateAmount(i) { this.lines[i].amount = (parseFloat(this.lines[i].qty || 0) * parseFloat(this.lines[i].unit_amount || 0)).toFixed(2); },
        addLine() { this.lines.push({ fee_code: '', description: '', qty: 1, unit_amount: 0, amount: 0, revenue_account: '' }); },
        removeLine(i) { if (this.lines.length > 1) this.lines.splice(i, 1); }
    }">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="form-label">Customer <span class="text-danger-500">*</span></label>
                <select name="customer_id" class="form-input" required>
                    <option value="">Select Customer</option>
                    @foreach($customers ?? [] as $customer)
                        <option value="{{ $customer->id }}" {{ ($invoice->customer_id ?? '') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Invoice Date <span class="text-danger-500">*</span></label>
                <input type="date" name="invoice_date" class="form-input" value="{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') }}" required>
            </div>
            <div>
                <label class="form-label">Due Date <span class="text-danger-500">*</span></label>
                <input type="date" name="due_date" class="form-input" value="{{ \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') }}" required>
            </div>
            <div>
                <label class="form-label">School Year</label>
                <input type="text" name="school_year" class="form-input" value="{{ $invoice->school_year ?? '' }}">
            </div>
            <div>
                <label class="form-label">Semester</label>
                <select name="semester" class="form-input">
                    <option value="">Select</option>
                    @foreach(['1st Semester', '2nd Semester', 'Summer', 'Full Year'] as $sem)
                        <option value="{{ $sem }}" {{ ($invoice->semester ?? '') == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-input" value="{{ $invoice->description ?? '' }}">
            </div>
        </div>

        {{-- Line Items --}}
        <div class="mb-4">
            <h4 class="text-sm font-semibold text-secondary-700 mb-2">Line Items</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 px-2 font-medium text-secondary-600">Fee Code</th>
                            <th class="text-left py-2 px-2 font-medium text-secondary-600">Description</th>
                            <th class="text-right py-2 px-2 font-medium text-secondary-600 w-20">Qty</th>
                            <th class="text-right py-2 px-2 font-medium text-secondary-600 w-32">Unit Amount</th>
                            <th class="text-right py-2 px-2 font-medium text-secondary-600 w-32">Amount</th>
                            <th class="text-left py-2 px-2 font-medium text-secondary-600">Revenue Account</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(line, index) in lines" :key="index">
                            <tr class="border-b border-gray-100">
                                <td class="py-1 px-2"><input type="text" x-model="line.fee_code" :name="'lines['+index+'][fee_code]'" class="form-input text-sm"></td>
                                <td class="py-1 px-2"><input type="text" x-model="line.description" :name="'lines['+index+'][description]'" class="form-input text-sm"></td>
                                <td class="py-1 px-2"><input type="number" x-model="line.qty" :name="'lines['+index+'][quantity]'" @input="updateAmount(index)" class="form-input text-sm text-right" min="1"></td>
                                <td class="py-1 px-2"><input type="number" x-model="line.unit_amount" :name="'lines['+index+'][unit_amount]'" @input="updateAmount(index)" class="form-input text-sm text-right" step="0.01" min="0"></td>
                                <td class="py-1 px-2"><input type="text" :value="parseFloat(line.amount).toFixed(2)" class="form-input text-sm text-right bg-gray-50" readonly>
                                    <input type="hidden" :name="'lines['+index+'][amount]'" :value="line.amount"></td>
                                <td class="py-1 px-2">
                                    <select x-model="line.revenue_account" :name="'lines['+index+'][revenue_account_id]'" class="form-input text-sm">
                                        <option value="">Select</option>
                                        @foreach($revenueAccounts ?? [] as $acct)
                                            <option value="{{ $acct->id }}">{{ $acct->account_code }} - {{ $acct->account_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-1 px-2">
                                    <button type="button" @click="removeLine(index)" x-show="lines.length > 1" class="text-danger-500 hover:text-danger-700">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <button type="button" @click="addLine()" class="mt-2 text-sm text-primary-600 hover:text-primary-700 font-medium">+ Add Line Item</button>
        </div>

        {{-- Summary --}}
        <div class="flex justify-end">
            <div class="w-64 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-secondary-600">Gross Amount:</span><span class="font-medium" x-text="'₱' + gross.toFixed(2)"></span></div>
                <div class="flex justify-between items-center">
                    <span class="text-secondary-600">Discount:</span>
                    <input type="number" name="discount_amount" x-model="discount" class="form-input w-28 text-right text-sm" step="0.01" min="0">
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-secondary-600">Tax:</span>
                    <input type="number" name="tax_amount" x-model="tax" class="form-input w-28 text-right text-sm" step="0.01" min="0">
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-200 font-semibold"><span>Net Amount:</span><span x-text="'₱' + net.toFixed(2)"></span></div>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'edit-invoice-{{ $invoice->id }}')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Update Invoice</button>
        </div>
    </form>
</x-modal>
@endforeach
@endsection
