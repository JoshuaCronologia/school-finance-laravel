@extends('layouts.app')
@section('title', 'Payment Processing')

@section('content')
<x-page-header title="Payment Processing" subtitle="Generate vouchers and check numbers for approved disbursements">
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Batch Generation Results --}}
@if(session('batch_results'))
@php $batchResults = session('batch_results'); @endphp
<div class="card mb-6 border-2 border-success-200 bg-success-50">
    <div class="card-header flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-success-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            <h3 class="text-sm font-semibold text-success-800">Generated Vouchers & Check Numbers ({{ count($batchResults) }} items)</h3>
        </div>
        <button onclick="window.print()" class="btn-secondary text-xs flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M9.75 21h4.5" /></svg>
            Print List
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Voucher #</th>
                    <th>Request #</th>
                    <th>Payee</th>
                    <th>Bank</th>
                    <th>Method</th>
                    <th>Check / Ref #</th>
                    <th class="text-right">Gross</th>
                    <th class="text-right">WHT (2%)</th>
                    <th class="text-right">Net Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($batchResults as $result)
                <tr>
                    <td class="font-medium text-primary-700">{{ $result['voucher_number'] }}</td>
                    <td>{{ $result['request_number'] }}</td>
                    <td>{{ $result['payee_name'] }}</td>
                    <td>{{ $result['bank_account'] ?? '-' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $result['payment_method'])) }}</td>
                    <td class="font-medium font-mono">{{ $result['check_number'] ?? $result['reference_number'] ?? '-' }}</td>
                    <td class="text-right">{{ '₱' . number_format($result['gross_amount'], 2) }}</td>
                    <td class="text-right text-danger-600">{{ '₱' . number_format($result['withholding_tax'], 2) }}</td>
                    <td class="text-right font-semibold">{{ '₱' . number_format($result['net_amount'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-semibold bg-gray-50">
                    <td colspan="6" class="text-right">Totals:</td>
                    <td class="text-right">{{ '₱' . number_format(collect($batchResults)->sum('gross_amount'), 2) }}</td>
                    <td class="text-right text-danger-600">{{ '₱' . number_format(collect($batchResults)->sum('withholding_tax'), 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format(collect($batchResults)->sum('net_amount'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

{{-- Filter & Batch Actions --}}
<div class="card mb-6" x-data="batchPayment()">
    <div class="card-header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-semibold text-secondary-700">Ready for Payment</h3>
                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-primary-600 rounded-full">{{ count($readyForPayment ?? []) }}</span>
            </div>
        </div>
    </div>

    {{-- Date Filter --}}
    <div class="p-4 border-b border-gray-100 bg-gray-50">
        <form method="GET" action="{{ route('ap.payment-processing') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="form-label">Approved As Of</label>
                <input type="date" name="as_of_date" class="form-input" value="{{ request('as_of_date', date('Y-m-d')) }}">
            </div>
            <div>
                <label class="form-label">Payment Method</label>
                <select name="filter_method" class="form-input">
                    <option value="">All Methods</option>
                    <option value="check" {{ request('filter_method') === 'check' ? 'selected' : '' }}>Check</option>
                    <option value="bank_transfer" {{ request('filter_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="cash" {{ request('filter_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="online" {{ request('filter_method') === 'online' ? 'selected' : '' }}>Online</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                Filter
            </button>
            @if(request()->hasAny(['as_of_date', 'filter_method']))
                <a href="{{ route('ap.payment-processing') }}" class="btn-secondary">Clear</a>
            @endif
        </form>
    </div>

    {{-- Batch Action Bar --}}
    <div class="p-4 border-b border-gray-100 space-y-4" x-show="selectedIds.length > 0" x-cloak>
        <div class="text-sm text-secondary-700">
            <span class="font-semibold text-primary-700" x-text="selectedIds.length"></span> item(s) selected
            — Total: <span class="font-semibold" x-text="'₱' + selectedTotal()"></span>
        </div>

        {{-- Last Check Series Info --}}
        @if($lastCheckUsed)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm">
            <div class="flex items-center gap-2 text-blue-800 font-medium mb-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                Last Check Used
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-blue-700">
                <div>Check #: <span class="font-semibold">{{ $lastCheckUsed->check_number ?? '-' }}</span></div>
                <div>Bank: <span class="font-semibold">{{ $lastCheckUsed->bank_account ?? '-' }}</span></div>
                <div>Date: <span class="font-semibold">{{ $lastCheckUsed->payment_date ? \Carbon\Carbon::parse($lastCheckUsed->payment_date)->format('M d, Y') : '-' }}</span></div>
                <div>Payee: <span class="font-semibold">{{ $lastCheckUsed->disbursement->payee_name ?? '-' }}</span></div>
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('ap.payments.batch') }}" id="batchForm">
            @csrf
            <template x-for="id in selectedIds" :key="id">
                <input type="hidden" name="disbursement_ids[]" :value="id">
            </template>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="form-label">Payment Date <span class="text-danger-500">*</span></label>
                    <input type="date" name="payment_date" class="form-input text-sm" value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label class="form-label">Bank Account <span class="text-danger-500">*</span></label>
                    <select name="bank_account" class="form-input text-sm" required>
                        <option value="">Select Bank...</option>
                        <option value="BDO" {{ ($lastCheckUsed->bank_account ?? '') === 'BDO' ? 'selected' : '' }}>BDO Unibank</option>
                        <option value="BPI" {{ ($lastCheckUsed->bank_account ?? '') === 'BPI' ? 'selected' : '' }}>Bank of the Philippine Islands</option>
                        <option value="Metrobank" {{ ($lastCheckUsed->bank_account ?? '') === 'Metrobank' ? 'selected' : '' }}>Metropolitan Bank</option>
                        <option value="Landbank" {{ ($lastCheckUsed->bank_account ?? '') === 'Landbank' ? 'selected' : '' }}>Land Bank of the Philippines</option>
                        <option value="PNB" {{ ($lastCheckUsed->bank_account ?? '') === 'PNB' ? 'selected' : '' }}>Philippine National Bank</option>
                        <option value="RCBC" {{ ($lastCheckUsed->bank_account ?? '') === 'RCBC' ? 'selected' : '' }}>Rizal Commercial Banking Corp</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Starting Check # <span class="text-xs text-secondary-400">(auto if blank)</span></label>
                    <input type="text" name="starting_check_number" class="form-input text-sm" placeholder="{{ $nextCheckNumber ?? 'Auto-generate' }}">
                </div>
                <div>
                    <button type="submit" class="btn-primary text-sm w-full"
                        onclick="return confirm('Generate vouchers and check numbers for ' + document.querySelectorAll('input[name=\'disbursement_ids[]\']').length + ' selected items?')">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        Generate Vouchers & Checks
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-10">
                        <input type="checkbox" class="form-checkbox" @change="toggleAll($event)">
                    </th>
                    <th>Request #</th>
                    <th>Request Date</th>
                    <th>Payee</th>
                    <th>Description</th>
                    <th>Department</th>
                    <th>Method</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($readyForPayment ?? [] as $request)
                <tr>
                    <td>
                        <input type="checkbox" class="form-checkbox"
                            value="{{ $request->id }}"
                            data-amount="{{ $request->amount }}"
                            @change="toggleItem($event, {{ $request->id }}, {{ $request->amount }})">
                    </td>
                    <td class="font-medium">
                        <a href="{{ route('ap.disbursements.show', $request) }}" class="text-primary-600 hover:text-primary-700 hover:underline">
                            {{ $request->request_number }}
                        </a>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($request->request_date)->format('M d, Y') }}</td>
                    <td>{{ $request->payee_name ?? $request->payee->name ?? '-' }}</td>
                    <td class="max-w-xs truncate">{{ $request->description ?? '-' }}</td>
                    <td>{{ $request->department->name ?? '-' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $request->payment_method ?? '-')) }}</td>
                    <td class="text-right font-medium">{{ '₱' . number_format($request->amount, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-secondary-400 py-8">
                        <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        No approved requests awaiting payment.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Link to full payment history --}}
<div class="text-center py-4">
    <a href="{{ route('ap.supplier-payments') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
        View all Supplier Payments &rarr;
    </a>
</div>

@push('scripts')
<script>
function batchPayment() {
    return {
        selectedIds: [],
        amounts: {},

        toggleAll(event) {
            const checked = event.target.checked;
            const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
            this.selectedIds = [];
            this.amounts = {};

            checkboxes.forEach(cb => {
                cb.checked = checked;
                if (checked) {
                    const id = parseInt(cb.value);
                    const amount = parseFloat(cb.dataset.amount);
                    this.selectedIds.push(id);
                    this.amounts[id] = amount;
                }
            });
        },

        toggleItem(event, id, amount) {
            if (event.target.checked) {
                this.selectedIds.push(id);
                this.amounts[id] = amount;
            } else {
                this.selectedIds = this.selectedIds.filter(i => i !== id);
                delete this.amounts[id];
            }
        },

        selectedTotal() {
            let total = 0;
            this.selectedIds.forEach(id => {
                total += this.amounts[id] || 0;
            });
            return total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    };
}
</script>
@endpush
@endsection
