@extends('layouts.app')
@section('title', 'Customers / Students')

@section('content')
@php
    $customerCount = $customers instanceof \Illuminate\Pagination\LengthAwarePaginator ? $customers->total() : count($customers);
@endphp

<x-page-header title="Customers / Students" :subtitle="$customerCount . ' customers'">
    <x-slot name="actions">
        <button @click="$dispatch('open-modal', 'add-customer')" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add Customer
        </button>
    </x-slot>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Filters --}}
<x-filter-bar action="{{ route('ar.customers.index') }}" method="GET">
    <div>
        <label class="form-label">Type</label>
        <select name="type" class="form-input w-44">
            <option value="">All Types</option>
            @foreach(['student', 'parent', 'corporate', 'other'] as $t)
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

{{-- Customers Table --}}
<x-data-table search-placeholder="Search customers...">
    <thead>
        <tr>
            <th>Customer Code</th>
            <th>Name</th>
            <th>Type</th>
            <th>Campus</th>
            <th>Grade Level</th>
            <th>Email</th>
            <th>Phone</th>
            <th class="text-right">Outstanding Balance</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($customers as $customer)
        <tr>
            <td class="font-medium text-secondary-900">{{ $customer->customer_code ?? '-' }}</td>
            <td class="font-medium">{{ $customer->name }}</td>
            <td>
                @php
                    $_map = ['student' => 'badge-info', 'parent' => 'badge-success', 'corporate' => 'badge-warning'];
    $typeBadge = $_map[$customer->type ?? ''] ?? 'badge-neutral';
                @endphp
                <span class="badge {{ $typeBadge }}">{{ ucfirst($customer->type ?? 'other') }}</span>
            </td>
            <td>{{ $customer->campus ?? '-' }}</td>
            <td>{{ $customer->grade_level ?? '-' }}</td>
            <td>{{ $customer->email ?? '-' }}</td>
            <td>{{ $customer->phone ?? '-' }}</td>
            <td class="text-right font-medium {{ ($customer->outstanding_balance ?? 0) > 0 ? 'text-danger-500' : '' }}">{{ '₱' . number_format($customer->outstanding_balance ?? 0, 2) }}</td>
            <td><x-badge :status="$customer->status ?? 'active'" /></td>
            <td>
                <button @click="$dispatch('open-modal', 'edit-customer-{{ $customer->id }}')" class="text-primary-600 hover:text-primary-700 text-sm font-medium">Edit</button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center text-secondary-400 py-8">
                <svg class="w-8 h-8 mx-auto mb-2 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                No customers found. Click "+ Add Customer" to create one.
            </td>
        </tr>
        @endforelse
    </tbody>
    @if($customers instanceof \Illuminate\Pagination\LengthAwarePaginator && $customers->hasPages())
    <x-slot name="footer">
        {{ $customers->withQueryString()->links() }}
    </x-slot>
    @endif
</x-data-table>

{{-- Add Customer Modal --}}
<x-modal name="add-customer" title="Add Customer" maxWidth="4xl">
    <form action="{{ route('ar.customers.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="form-label">Customer Code <span class="text-danger-500">*</span></label>
                <input type="text" name="customer_code" class="form-input" required placeholder="e.g., STU-001">
            </div>
            <div>
                <label class="form-label">Type <span class="text-danger-500">*</span></label>
                <select name="customer_type" class="form-input" required>
                    <option value="">Select Type</option>
                    <option value="student">Student</option>
                    <option value="parent">Parent</option>
                    <option value="organization">Organization</option>
                    <option value="government">Government</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label class="form-label">Name <span class="text-danger-500">*</span></label>
                <input type="text" name="name" class="form-input" required placeholder="Full name">
            </div>
            <div>
                <label class="form-label">Campus</label>
                <select name="campus_id" class="form-input">
                    <option value="">Select Campus</option>
                    @foreach($campuses ?? [] as $campus)
                        <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Grade Level</label>
                <input type="text" name="grade_level" class="form-input" placeholder="e.g., Grade 10">
            </div>
            <div>
                <label class="form-label">Contact Person</label>
                <input type="text" name="contact_person" class="form-input" placeholder="Parent/Guardian name">
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" placeholder="email@example.com">
            </div>
            <div>
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-input" placeholder="e.g., 0917-xxx-xxxx">
            </div>
            <div>
                <label class="form-label">TIN</label>
                <input type="text" name="tin" class="form-input" placeholder="xxx-xxx-xxx-xxx">
            </div>
            <div class="md:col-span-3">
                <label class="form-label">Billing Address</label>
                <input type="text" name="billing_address" class="form-input" placeholder="Complete billing address">
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'add-customer')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save Customer</button>
        </div>
    </form>
</x-modal>

{{-- Edit Customer Modals --}}
@foreach($customers as $customer)
<x-modal name="edit-customer-{{ $customer->id }}" title="Edit Customer" maxWidth="4xl">
    <form action="{{ route('ar.customers.update', $customer) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="form-label">Customer Code <span class="text-danger-500">*</span></label>
                <input type="text" name="customer_code" class="form-input" value="{{ $customer->customer_code }}" required>
            </div>
            <div>
                <label class="form-label">Type <span class="text-danger-500">*</span></label>
                <select name="customer_type" class="form-input" required>
                    <option value="student" {{ $customer->customer_type == 'student' ? 'selected' : '' }}>Student</option>
                    <option value="parent" {{ $customer->customer_type == 'parent' ? 'selected' : '' }}>Parent</option>
                    <option value="organization" {{ $customer->customer_type == 'organization' ? 'selected' : '' }}>Organization</option>
                    <option value="government" {{ $customer->customer_type == 'government' ? 'selected' : '' }}>Government</option>
                    <option value="other" {{ $customer->customer_type == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div>
                <label class="form-label">Name <span class="text-danger-500">*</span></label>
                <input type="text" name="name" class="form-input" value="{{ $customer->name }}" required>
            </div>
            <div>
                <label class="form-label">Campus</label>
                <select name="campus_id" class="form-input">
                    <option value="">Select Campus</option>
                    @foreach($campuses ?? [] as $campus)
                        <option value="{{ $campus->id }}" {{ $customer->campus_id == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Grade Level</label>
                <input type="text" name="grade_level" class="form-input" value="{{ $customer->grade_level ?? '' }}">
            </div>
            <div>
                <label class="form-label">Contact Person</label>
                <input type="text" name="contact_person" class="form-input" value="{{ $customer->contact_person ?? '' }}">
            </div>
            <div>
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" value="{{ $customer->email ?? '' }}">
            </div>
            <div>
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-input" value="{{ $customer->phone ?? '' }}">
            </div>
            <div>
                <label class="form-label">TIN</label>
                <input type="text" name="tin" class="form-input" value="{{ $customer->tin ?? '' }}">
            </div>
            <div class="md:col-span-3">
                <label class="form-label">Billing Address</label>
                <input type="text" name="billing_address" class="form-input" value="{{ $customer->billing_address ?? '' }}">
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    <option value="active" {{ ($customer->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ ($customer->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" @click="$dispatch('close-modal', 'edit-customer-{{ $customer->id }}')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Update Customer</button>
        </div>
    </form>
</x-modal>
@endforeach
@endsection
