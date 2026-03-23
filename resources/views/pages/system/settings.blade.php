@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<x-page-header title="System Settings" subtitle="Configure application preferences and defaults" />

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

<div x-data="{ activeTab: '{{ request('tab', 'general') }}' }">
    {{-- Tab Navigation --}}
    <div class="border-b border-gray-200 mb-6">
        <nav class="flex gap-6 -mb-px">
            @foreach(['general' => 'General', 'approval' => 'Approval', 'budget' => 'Budget', 'tax' => 'Tax', 'numbering' => 'Numbering'] as $tab => $label)
            <button @click="activeTab = '{{ $tab }}'"
                    :class="activeTab === '{{ $tab }}' ? 'border-primary-500 text-primary-600' : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-gray-300'"
                    class="py-3 px-1 border-b-2 font-medium text-sm transition-colors">
                {{ $label }}
            </button>
            @endforeach
        </nav>
    </div>

    {{-- General Settings --}}
    <div x-show="activeTab === 'general'" x-transition>
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-secondary-900">General Information</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="general">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">School Name <span class="text-danger-500">*</span></label>
                            <input type="text" name="school_name" class="form-input" value="{{ $settings['school_name'] ?? 'OrangeApps Academy' }}" required>
                        </div>
                        <div>
                            <label class="form-label">TIN</label>
                            <input type="text" name="tin" class="form-input" value="{{ $settings['tin'] ?? '' }}" placeholder="xxx-xxx-xxx-xxx">
                        </div>
                        <div class="md:col-span-2">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-input" value="{{ $settings['address'] ?? '' }}" placeholder="Complete school address">
                        </div>
                        <div>
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-input" value="{{ $settings['phone'] ?? '' }}" placeholder="(02) xxxx-xxxx">
                        </div>
                        <div>
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-input" value="{{ $settings['email'] ?? '' }}" placeholder="finance@school.edu.ph">
                        </div>
                    </div>
                    <div class="flex justify-end mt-6 pt-4 border-t border-gray-100">
                        <button type="submit" class="btn-primary">Save General Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Approval Settings --}}
    <div x-show="activeTab === 'approval'" x-transition>
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-secondary-900">Approval Thresholds</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="approval">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="form-label">Level 1 Threshold (Up to)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400">₱</span>
                                    <input type="number" name="approval_level_1" class="form-input pl-8" value="{{ $settings['approval_level_1'] ?? 50000 }}" step="0.01">
                                </div>
                                <p class="text-xs text-secondary-400 mt-1">Department Head approval</p>
                            </div>
                            <div>
                                <label class="form-label">Level 2 Threshold (Up to)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400">₱</span>
                                    <input type="number" name="approval_level_2" class="form-input pl-8" value="{{ $settings['approval_level_2'] ?? 200000 }}" step="0.01">
                                </div>
                                <p class="text-xs text-secondary-400 mt-1">Finance Director approval</p>
                            </div>
                            <div>
                                <label class="form-label">Level 3 Threshold (Above Level 2)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400">₱</span>
                                    <input type="number" name="approval_level_3" class="form-input pl-8" value="{{ $settings['approval_level_3'] ?? 500000 }}" step="0.01">
                                </div>
                                <p class="text-xs text-secondary-400 mt-1">School Administrator approval</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end mt-6 pt-4 border-t border-gray-100">
                        <button type="submit" class="btn-primary">Save Approval Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Budget Settings --}}
    <div x-show="activeTab === 'budget'" x-transition>
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-secondary-900">Budget Policy</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="budget">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Budget Policy</label>
                            <select name="budget_policy" class="form-input">
                                <option value="strict" {{ ($settings['budget_policy'] ?? '') == 'strict' ? 'selected' : '' }}>Strict - Block over-budget transactions</option>
                                <option value="warning" {{ ($settings['budget_policy'] ?? 'warning') == 'warning' ? 'selected' : '' }}>Warning - Allow with warning</option>
                                <option value="none" {{ ($settings['budget_policy'] ?? '') == 'none' ? 'selected' : '' }}>None - No budget checking</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Overspend Tolerance (%)</label>
                            <input type="number" name="overspend_tolerance" class="form-input" value="{{ $settings['overspend_tolerance'] ?? 10 }}" min="0" max="100" step="1">
                            <p class="text-xs text-secondary-400 mt-1">Percentage above budget allowed before blocking</p>
                        </div>
                    </div>
                    <div class="flex justify-end mt-6 pt-4 border-t border-gray-100">
                        <button type="submit" class="btn-primary">Save Budget Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Tax Settings --}}
    <div x-show="activeTab === 'tax'" x-transition>
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-secondary-900">Tax Rates</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="tax">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">Default VAT Rate (%)</label>
                            <input type="number" name="default_vat_rate" class="form-input" value="{{ $settings['default_vat_rate'] ?? 12 }}" step="0.01" min="0">
                        </div>
                        <div>
                            <label class="form-label">WHT Professional (WC010) %</label>
                            <input type="number" name="wht_professional" class="form-input" value="{{ $settings['wht_professional'] ?? 10 }}" step="0.01" min="0">
                        </div>
                        <div>
                            <label class="form-label">WHT Rental (WC020) %</label>
                            <input type="number" name="wht_rental" class="form-input" value="{{ $settings['wht_rental'] ?? 5 }}" step="0.01" min="0">
                        </div>
                        <div>
                            <label class="form-label">WHT Services (WC050) %</label>
                            <input type="number" name="wht_services" class="form-input" value="{{ $settings['wht_services'] ?? 2 }}" step="0.01" min="0">
                        </div>
                        <div>
                            <label class="form-label">WHT Supplies (WC100) %</label>
                            <input type="number" name="wht_supplies" class="form-input" value="{{ $settings['wht_supplies'] ?? 1 }}" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="flex justify-end mt-6 pt-4 border-t border-gray-100">
                        <button type="submit" class="btn-primary">Save Tax Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Numbering Settings --}}
    <div x-show="activeTab === 'numbering'" x-transition>
        <div class="card">
            <div class="card-header">
                <h3 class="text-sm font-semibold text-secondary-900">Document Numbering Prefixes</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="numbering">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">Disbursement Request (DR)</label>
                            <input type="text" name="prefix_dr" class="form-input" value="{{ $settings['prefix_dr'] ?? 'DR-' }}" placeholder="e.g., DR-">
                        </div>
                        <div>
                            <label class="form-label">Payment Voucher (PV)</label>
                            <input type="text" name="prefix_pv" class="form-input" value="{{ $settings['prefix_pv'] ?? 'PV-' }}" placeholder="e.g., PV-">
                        </div>
                        <div>
                            <label class="form-label">Official Receipt (OR)</label>
                            <input type="text" name="prefix_or" class="form-input" value="{{ $settings['prefix_or'] ?? 'OR-' }}" placeholder="e.g., OR-">
                        </div>
                        <div>
                            <label class="form-label">Journal Entry (JE)</label>
                            <input type="text" name="prefix_je" class="form-input" value="{{ $settings['prefix_je'] ?? 'JE-' }}" placeholder="e.g., JE-">
                        </div>
                        <div>
                            <label class="form-label">Bill (BILL)</label>
                            <input type="text" name="prefix_bill" class="form-input" value="{{ $settings['prefix_bill'] ?? 'BILL-' }}" placeholder="e.g., BILL-">
                        </div>
                        <div>
                            <label class="form-label">Invoice (INV)</label>
                            <input type="text" name="prefix_inv" class="form-input" value="{{ $settings['prefix_inv'] ?? 'INV-' }}" placeholder="e.g., INV-">
                        </div>
                    </div>
                    <div class="flex justify-end mt-6 pt-4 border-t border-gray-100">
                        <button type="submit" class="btn-primary">Save Numbering Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
