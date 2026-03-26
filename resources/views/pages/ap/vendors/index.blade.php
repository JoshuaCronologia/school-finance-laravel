@extends('layouts.app')
@section('title', 'Vendors / Payees')

@section('content')
@php
    $vendorCount = $vendors instanceof \Illuminate\Pagination\LengthAwarePaginator ? $vendors->total() : count($vendors);
@endphp

<x-page-header title="Vendors / Payees" :subtitle="$vendorCount . ' vendors'">
    <x-slot:actions>
        <button @click="$dispatch('open-modal', 'add-vendor')" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add Vendor
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
<x-filter-bar action="{{ route('vendors.index') }}" method="GET">
    <div>
        <label class="form-label">Type</label>
        <select name="vendor_type" class="form-input w-44">
            <option value="">All Types</option>
            @foreach(['supplier', 'contractor', 'utility', 'government', 'individual', 'other'] as $t)
                <option value="{{ $t }}" {{ request('vendor_type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-input w-36">
            <option value="">All</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
</x-filter-bar>

{{-- Vendors Table --}}
<x-data-table search-placeholder="Search vendors...">
    <thead>
        <tr>
            <th>Vendor Code</th>
            <th>Name</th>
            <th>Type</th>
            <th>TIN</th>
            <th>Tax Classification</th>
            <th>Phone</th>
            <th class="text-right">Outstanding Balance</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($vendors as $vendor)
        @php
            $vatLabels = ['vatable' => 'VAT', 'non-vatable' => 'Non-VAT', 'zero-rated' => 'Zero Rated', 'tax_exempt' => 'Tax Exempt'];
        @endphp
        <tr>
            <td class="font-medium text-secondary-900">{{ $vendor->vendor_code ?? '-' }}</td>
            <td class="font-medium">{{ $vendor->name }}</td>
            <td>{{ ucfirst($vendor->vendor_type ?? '-') }}</td>
            <td>{{ $vendor->tin ?? '-' }}</td>
            <td>
                <span class="text-xs">{{ $vatLabels[$vendor->vat_type] ?? '-' }}</span>
                @if($vendor->withholding_tax_type)
                    <span class="text-xs text-secondary-400">/ {{ $vendor->withholding_tax_type }}</span>
                @endif
            </td>
            <td>{{ $vendor->phone ?? '-' }}</td>
            <td class="text-right font-medium">{{ '₱' . number_format($vendor->outstanding_balance ?? 0, 2) }}</td>
            <td><x-badge :status="$vendor->is_active ? 'active' : 'inactive'" /></td>
            <td>
                <button @click="$dispatch('open-modal', 'edit-vendor-{{ $vendor->id }}')" class="text-primary-600 hover:text-primary-700 text-sm font-medium">Edit</button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center text-secondary-400 py-8">
                <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                No vendors found. Click "+ Add Vendor" to create one.
            </td>
        </tr>
        @endforelse
    </tbody>
    @if($vendors instanceof \Illuminate\Pagination\LengthAwarePaginator && $vendors->hasPages())
    <x-slot:footer>
        {{ $vendors->withQueryString()->links() }}
    </x-slot:footer>
    @endif
</x-data-table>

{{-- Add Vendor Modal --}}
<x-modal name="add-vendor" title="Add Vendor" maxWidth="4xl">
    <form action="{{ route('vendors.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="form-label">Vendor Code <span class="text-danger-500">*</span></label>
                <input type="text" name="vendor_code" class="form-input" required placeholder="e.g., V-001">
            </div>
            <div>
                <label class="form-label">Name <span class="text-danger-500">*</span></label>
                <input type="text" name="name" class="form-input" required placeholder="Vendor / Company name">
            </div>
            <div>
                <label class="form-label">Type <span class="text-danger-500">*</span></label>
                <select name="vendor_type" class="form-input" required>
                    <option value="">Select Type</option>
                    <option value="supplier">Supplier</option>
                    <option value="contractor">Contractor</option>
                    <option value="utility">Utility</option>
                    <option value="government">Government</option>
                    <option value="individual">Individual</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label class="form-label">Contact Person</label>
                <input type="text" name="contact_person" class="form-input" placeholder="Full name">
            </div>
            <div>
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-input" placeholder="e.g., 0917-xxx-xxxx">
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" placeholder="vendor@email.com">
            </div>
            <div class="md:col-span-3">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-input" placeholder="Complete address">
            </div>
            <div>
                <label class="form-label">TIN</label>
                <input type="text" name="tin" class="form-input" placeholder="xxx-xxx-xxx-xxx">
            </div>
            <div>
                <label class="form-label">Tax Classification</label>
                <select name="vat_type" class="form-input">
                    <option value="">Select</option>
                    <option value="vatable">VATable (VAT Registered)</option>
                    <option value="non-vatable">Non-VAT</option>
                    <option value="zero-rated">Zero Rated</option>
                    <option value="tax_exempt">Tax Exempt</option>
                </select>
            </div>
            <div>
                <label class="form-label">ATC (Alphanumeric Tax Code)</label>
                <select name="withholding_tax_type" class="form-input">
                    <option value="">Select ATC</option>
                    <option value="WI010">WI010 - EWT Prof. fees (individual) 5%</option>
                    <option value="WI020">WI020 - EWT Prof. fees (individual) 10%</option>
                    <option value="WI100">WI100 - EWT Prof. fees (individual) 15%</option>
                    <option value="WC010">WC010 - EWT Prof. fees (corporate) 10%</option>
                    <option value="WC020">WC020 - EWT Prof. fees (corporate) 15%</option>
                    <option value="WC100">WC100 - EWT Rental (corporate) 5%</option>
                    <option value="WB010">WB010 - EWT Goods 1%</option>
                    <option value="WB020">WB020 - EWT Services 2%</option>
                    <option value="WB050">WB050 - EWT Rentals 5%</option>
                </select>
            </div>
            <div>
                <label class="form-label">Payment Terms</label>
                <select name="payment_terms_id" class="form-input">
                    <option value="">Select</option>
                    @foreach($paymentTerms ?? [] as $pt)
                        <option value="{{ $pt->id }}">{{ $pt->name ?? $pt->code }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Bank Name</label>
                <input type="text" name="bank_name" class="form-input" placeholder="e.g., BDO, BPI">
            </div>
            <div>
                <label class="form-label">Account Name</label>
                <input type="text" name="account_name" class="form-input" placeholder="Account holder name">
            </div>
            <div>
                <label class="form-label">Account Number</label>
                <input type="text" name="account_number" class="form-input" placeholder="Bank account number">
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'add-vendor')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save Vendor</button>
        </div>
    </form>
</x-modal>

{{-- Edit Vendor Modals --}}
@foreach($vendors as $vendor)
<x-modal name="edit-vendor-{{ $vendor->id }}" title="Edit Vendor" maxWidth="4xl">
    <form action="{{ route('vendors.update', $vendor) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="form-label">Vendor Code <span class="text-danger-500">*</span></label>
                <input type="text" name="vendor_code" class="form-input" value="{{ $vendor->vendor_code }}" required>
            </div>
            <div>
                <label class="form-label">Name <span class="text-danger-500">*</span></label>
                <input type="text" name="name" class="form-input" value="{{ $vendor->name }}" required>
            </div>
            <div>
                <label class="form-label">Type <span class="text-danger-500">*</span></label>
                <select name="vendor_type" class="form-input" required>
                    <option value="supplier" {{ ($vendor->vendor_type ?? '') == 'supplier' ? 'selected' : '' }}>Supplier</option>
                    <option value="contractor" {{ ($vendor->vendor_type ?? '') == 'contractor' ? 'selected' : '' }}>Contractor</option>
                    <option value="utility" {{ ($vendor->vendor_type ?? '') == 'utility' ? 'selected' : '' }}>Utility</option>
                    <option value="government" {{ ($vendor->vendor_type ?? '') == 'government' ? 'selected' : '' }}>Government</option>
                    <option value="individual" {{ ($vendor->vendor_type ?? '') == 'individual' ? 'selected' : '' }}>Individual</option>
                    <option value="other" {{ ($vendor->vendor_type ?? '') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div>
                <label class="form-label">Contact Person</label>
                <input type="text" name="contact_person" class="form-input" value="{{ $vendor->contact_person ?? '' }}">
            </div>
            <div>
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-input" value="{{ $vendor->phone ?? '' }}">
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" value="{{ $vendor->email ?? '' }}">
            </div>
            <div class="md:col-span-3">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-input" value="{{ $vendor->address ?? '' }}">
            </div>
            <div>
                <label class="form-label">TIN</label>
                <input type="text" name="tin" class="form-input" value="{{ $vendor->tin ?? '' }}">
            </div>
            <div>
                <label class="form-label">Tax Classification</label>
                <select name="vat_type" class="form-input">
                    <option value="">Select</option>
                    <option value="vatable" {{ ($vendor->vat_type ?? '') == 'vatable' ? 'selected' : '' }}>VATable (VAT Registered)</option>
                    <option value="non-vatable" {{ ($vendor->vat_type ?? '') == 'non-vatable' ? 'selected' : '' }}>Non-VAT</option>
                    <option value="zero-rated" {{ ($vendor->vat_type ?? '') == 'zero-rated' ? 'selected' : '' }}>Zero Rated</option>
                    <option value="tax_exempt" {{ ($vendor->vat_type ?? '') == 'tax_exempt' ? 'selected' : '' }}>Tax Exempt</option>
                </select>
            </div>
            <div>
                <label class="form-label">ATC (Alphanumeric Tax Code)</label>
                <select name="withholding_tax_type" class="form-input">
                    <option value="">Select ATC</option>
                    <option value="WI010" {{ ($vendor->withholding_tax_type ?? '') == 'WI010' ? 'selected' : '' }}>WI010 - EWT Prof. fees (indiv.) 5%</option>
                    <option value="WI020" {{ ($vendor->withholding_tax_type ?? '') == 'WI020' ? 'selected' : '' }}>WI020 - EWT Prof. fees (indiv.) 10%</option>
                    <option value="WI100" {{ ($vendor->withholding_tax_type ?? '') == 'WI100' ? 'selected' : '' }}>WI100 - EWT Prof. fees (indiv.) 15%</option>
                    <option value="WC010" {{ ($vendor->withholding_tax_type ?? '') == 'WC010' ? 'selected' : '' }}>WC010 - EWT Prof. fees (corp.) 10%</option>
                    <option value="WC020" {{ ($vendor->withholding_tax_type ?? '') == 'WC020' ? 'selected' : '' }}>WC020 - EWT Prof. fees (corp.) 15%</option>
                    <option value="WC100" {{ ($vendor->withholding_tax_type ?? '') == 'WC100' ? 'selected' : '' }}>WC100 - EWT Rental (corp.) 5%</option>
                    <option value="WB010" {{ ($vendor->withholding_tax_type ?? '') == 'WB010' ? 'selected' : '' }}>WB010 - EWT Goods 1%</option>
                    <option value="WB020" {{ ($vendor->withholding_tax_type ?? '') == 'WB020' ? 'selected' : '' }}>WB020 - EWT Services 2%</option>
                    <option value="WB050" {{ ($vendor->withholding_tax_type ?? '') == 'WB050' ? 'selected' : '' }}>WB050 - EWT Rentals 5%</option>
                </select>
            </div>
            <div>
                <label class="form-label">Payment Terms</label>
                <select name="payment_terms_id" class="form-input">
                    <option value="">Select</option>
                    @foreach($paymentTerms ?? [] as $pt)
                        <option value="{{ $pt->id }}" {{ ($vendor->payment_terms_id ?? '') == $pt->id ? 'selected' : '' }}>{{ $pt->name ?? $pt->code }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Bank Name</label>
                <input type="text" name="bank_name" class="form-input" value="{{ $vendor->bank_name ?? '' }}">
            </div>
            <div>
                <label class="form-label">Account Name</label>
                <input type="text" name="account_name" class="form-input" value="{{ $vendor->account_name ?? '' }}">
            </div>
            <div>
                <label class="form-label">Account Number</label>
                <input type="text" name="account_number" class="form-input" value="{{ $vendor->account_number ?? '' }}">
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="is_active" class="form-input">
                    <option value="1" {{ $vendor->is_active ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$vendor->is_active ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'edit-vendor-{{ $vendor->id }}')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Update Vendor</button>
        </div>
    </form>
</x-modal>
@endforeach
@endsection
