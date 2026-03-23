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
            + Add Vendor
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
<x-filter-bar action="{{ route('ap.vendors.index') }}" method="GET">
    <div>
        <label class="form-label">Type</label>
        <select name="type" class="form-input w-44">
            <option value="">All Types</option>
            @foreach(['vendor', 'supplier', 'contractor'] as $t)
                <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
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
            <th>Email</th>
            <th>Phone</th>
            <th class="text-right">Outstanding Balance</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($vendors as $vendor)
        <tr>
            <td class="font-medium text-secondary-900">{{ $vendor->vendor_code ?? '-' }}</td>
            <td class="font-medium">{{ $vendor->name }}</td>
            <td>{{ ucfirst($vendor->type ?? '-') }}</td>
            <td>{{ $vendor->tin ?? '-' }}</td>
            <td>{{ $vendor->email ?? '-' }}</td>
            <td>{{ $vendor->phone ?? '-' }}</td>
            <td class="text-right font-medium">{{ '₱' . number_format($vendor->outstanding_balance ?? 0, 2) }}</td>
            <td><x-badge :status="$vendor->status ?? 'active'" /></td>
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
    <form action="{{ route('ap.vendors.store') }}" method="POST">
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
                <select name="type" class="form-input" required>
                    <option value="">Select Type</option>
                    <option value="vendor">Vendor</option>
                    <option value="supplier">Supplier</option>
                    <option value="contractor">Contractor</option>
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
                <label class="form-label">VAT Type</label>
                <select name="vat_type" class="form-input">
                    <option value="">Select</option>
                    <option value="vat_registered">VAT Registered</option>
                    <option value="non_vat">Non-VAT</option>
                    <option value="vat_exempt">VAT Exempt</option>
                </select>
            </div>
            <div>
                <label class="form-label">WHT Type</label>
                <select name="wht_type" class="form-input">
                    <option value="">Select</option>
                    <option value="WC010">WC010 - 1%</option>
                    <option value="WC020">WC020 - 2%</option>
                    <option value="WC050">WC050 - 5%</option>
                    <option value="WC100">WC100 - 10%</option>
                    <option value="WC150">WC150 - 15%</option>
                </select>
            </div>
            <div>
                <label class="form-label">Payment Terms</label>
                <select name="payment_terms" class="form-input">
                    <option value="">Select</option>
                    <option value="cod">COD</option>
                    <option value="net_15">Net 15</option>
                    <option value="net_30">Net 30</option>
                    <option value="net_60">Net 60</option>
                </select>
            </div>
            <div>
                <label class="form-label">Bank Name</label>
                <input type="text" name="bank_name" class="form-input" placeholder="e.g., BDO, BPI">
            </div>
            <div>
                <label class="form-label">Account Name</label>
                <input type="text" name="bank_account_name" class="form-input" placeholder="Account holder name">
            </div>
            <div>
                <label class="form-label">Account Number</label>
                <input type="text" name="bank_account_number" class="form-input" placeholder="Bank account number">
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
    <form action="{{ route('ap.vendors.update', $vendor) }}" method="POST">
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
                <select name="type" class="form-input" required>
                    <option value="vendor" {{ $vendor->type == 'vendor' ? 'selected' : '' }}>Vendor</option>
                    <option value="supplier" {{ $vendor->type == 'supplier' ? 'selected' : '' }}>Supplier</option>
                    <option value="contractor" {{ $vendor->type == 'contractor' ? 'selected' : '' }}>Contractor</option>
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
                <label class="form-label">VAT Type</label>
                <select name="vat_type" class="form-input">
                    <option value="">Select</option>
                    <option value="vat_registered" {{ ($vendor->vat_type ?? '') == 'vat_registered' ? 'selected' : '' }}>VAT Registered</option>
                    <option value="non_vat" {{ ($vendor->vat_type ?? '') == 'non_vat' ? 'selected' : '' }}>Non-VAT</option>
                    <option value="vat_exempt" {{ ($vendor->vat_type ?? '') == 'vat_exempt' ? 'selected' : '' }}>VAT Exempt</option>
                </select>
            </div>
            <div>
                <label class="form-label">WHT Type</label>
                <select name="wht_type" class="form-input">
                    <option value="">Select</option>
                    <option value="WC010" {{ ($vendor->wht_type ?? '') == 'WC010' ? 'selected' : '' }}>WC010 - 1%</option>
                    <option value="WC020" {{ ($vendor->wht_type ?? '') == 'WC020' ? 'selected' : '' }}>WC020 - 2%</option>
                    <option value="WC050" {{ ($vendor->wht_type ?? '') == 'WC050' ? 'selected' : '' }}>WC050 - 5%</option>
                    <option value="WC100" {{ ($vendor->wht_type ?? '') == 'WC100' ? 'selected' : '' }}>WC100 - 10%</option>
                    <option value="WC150" {{ ($vendor->wht_type ?? '') == 'WC150' ? 'selected' : '' }}>WC150 - 15%</option>
                </select>
            </div>
            <div>
                <label class="form-label">Payment Terms</label>
                <select name="payment_terms" class="form-input">
                    <option value="">Select</option>
                    <option value="cod" {{ ($vendor->payment_terms ?? '') == 'cod' ? 'selected' : '' }}>COD</option>
                    <option value="net_15" {{ ($vendor->payment_terms ?? '') == 'net_15' ? 'selected' : '' }}>Net 15</option>
                    <option value="net_30" {{ ($vendor->payment_terms ?? '') == 'net_30' ? 'selected' : '' }}>Net 30</option>
                    <option value="net_60" {{ ($vendor->payment_terms ?? '') == 'net_60' ? 'selected' : '' }}>Net 60</option>
                </select>
            </div>
            <div>
                <label class="form-label">Bank Name</label>
                <input type="text" name="bank_name" class="form-input" value="{{ $vendor->bank_name ?? '' }}">
            </div>
            <div>
                <label class="form-label">Account Name</label>
                <input type="text" name="bank_account_name" class="form-input" value="{{ $vendor->bank_account_name ?? '' }}">
            </div>
            <div>
                <label class="form-label">Account Number</label>
                <input type="text" name="bank_account_number" class="form-input" value="{{ $vendor->bank_account_number ?? '' }}">
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    <option value="active" {{ ($vendor->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ ($vendor->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
