@extends('layouts.app')
@section('title', 'Fee Account Mappings')

@section('content')
<x-page-header title="Fee Account Mappings" subtitle="Map cashier fees from Finance system to accounting accounts">
    <x-slot name="actions">
        <a href="{{ route('reports.fee-collections') }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
            Back to Fee Collections
        </a>
    </x-slot>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif
@if(session('error'))
    <x-alert type="danger" :message="session('error')" class="mb-4" />
@endif

{{-- Auto-Generate Section --}}
<div class="card mb-6">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-700">Auto-Generate Sub-Accounts by Group</h3>
    </div>
    <div class="card-body">
        <p class="text-sm text-secondary-600 mb-4">
            Map each <strong>finance fee group</strong> to an <strong>accounting parent account</strong>.
            Sub-accounts will be created under the selected parent. Already mapped fees will be skipped.
        </p>
        <form action="{{ route('reports.fee-account-mappings.auto-generate') }}" method="POST"
              onsubmit="return confirm('This will create sub-accounts for all unmapped fees under the selected parent accounts. Continue?')">
            @csrf
            <div class="overflow-x-auto mb-4">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Finance Fee Group</th>
                            <th class="text-center w-24">Fees</th>
                            <th class="w-80">Map to Accounting Account</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($financeGroups as $group)
                        <tr>
                            <td class="font-medium">{{ $group->name }}</td>
                            <td class="text-center">{{ $group->child_count }}</td>
                            <td>
                                <select name="group_mappings[{{ $group->id }}]" class="form-input text-sm">
                                    <option value="">-- Skip this group --</option>
                                    @foreach($allAccounts as $acct)
                                        <option value="{{ $acct->id }}">
                                            {{ $acct->account_code }} - {{ $acct->account_name }} ({{ ucfirst($acct->account_type) }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="btn-primary text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    Auto-Generate & Map
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Manual Mapping Section --}}
<form action="{{ route('reports.fee-account-mappings.save') }}" method="POST">
    @csrf

    <div class="card">
        <div class="card-header">
            <div class="flex items-center justify-between w-full">
                <h3 class="text-sm font-semibold text-secondary-700">
                    Manual Mapping
                    <span class="text-xs text-secondary-400 font-normal ml-2">
                        ({{ $mappings->count() }}/{{ $financeFees->count() }} mapped)
                    </span>
                </h3>
                <button type="submit" class="btn-primary text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    Save Mappings
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th>Finance Fee Name</th>
                        <th class="w-80">Accounting Account</th>
                        <th class="w-20 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($financeFees->where('parent_id', '!=', null) as $i => $fee)
                    @php $mapped = $mappings->get($fee->id); @endphp
                    <tr class="{{ $mapped ? 'bg-green-50/50' : '' }}">
                        <td class="text-secondary-400">{{ $i + 1 }}</td>
                        <td class="font-medium">
                            {{ $fee->name }}
                            <input type="hidden" name="mappings[{{ $i }}][finance_fee_id]" value="{{ $fee->id }}">
                            <input type="hidden" name="mappings[{{ $i }}][finance_fee_name]" value="{{ $fee->name }}">
                        </td>
                        <td>
                            <select name="mappings[{{ $i }}][account_id]" class="form-input text-sm">
                                <option value="">-- Not Mapped --</option>
                                @foreach($revenueAccounts as $acct)
                                    <option value="{{ $acct->id }}" {{ $mapped && $mapped->account_id == $acct->id ? 'selected' : '' }}>
                                        {{ $acct->account_code }} - {{ $acct->account_name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-center">
                            @if($mapped)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Mapped</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Unmapped</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-body border-t border-gray-100">
            <div class="flex justify-end">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    Save Mappings
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
