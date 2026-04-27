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

{{-- ====================== AUTO-GENERATE SECTION ====================== --}}
<div class="card mb-6" x-data="{ open: true }">
    <button type="button" @click="open = !open"
        class="card-header w-full flex items-center justify-between text-left hover:bg-secondary-50">
        <div>
            <h3 class="text-sm font-semibold text-secondary-700">Auto-Generate Sub-Accounts by Group</h3>
            <p class="text-xs text-secondary-400 mt-0.5">Map a finance fee group to an accounting parent — sub-accounts are created automatically. Already-mapped fees are skipped.</p>
        </div>
        <svg class="w-4 h-4 text-secondary-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
    </button>

    <div x-show="open" x-collapse>
        <div class="card-body">
            <form action="{{ route('reports.fee-account-mappings.auto-generate') }}" method="POST"
                  onsubmit="return confirm('This will create sub-accounts for all unmapped fees under the selected parent accounts. Continue?')">
                @csrf
                <div class="overflow-x-auto mb-4">
                    <table class="min-w-full divide-y divide-secondary-200 text-sm">
                        <thead class="bg-secondary-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-secondary-600">Finance Fee Group</th>
                                <th class="px-3 py-2 text-center w-28 font-medium text-secondary-600">Fees</th>
                                <th class="px-3 py-2 w-80 font-medium text-secondary-600">Map to Accounting Account</th>
                                <th class="px-3 py-2 w-10"></th>
                            </tr>
                        </thead>
                        @foreach($financeGroups as $group)
                        <tbody x-data="{ preview: false }" class="divide-y divide-secondary-100">
                            <tr>
                                <td class="px-3 py-2 font-medium">
                                    {{ $group->name }}
                                    @if($group->mapped_count > 0)
                                        <span class="ml-1 text-xs text-green-600 font-normal">({{ $group->mapped_count }} mapped)</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-center">{{ $group->child_count }}</td>
                                <td class="px-3 py-2">
                                    <select name="group_mappings[{{ $group->id }}]" class="form-input text-sm">
                                        <option value="">-- Skip this group --</option>
                                        @foreach($allAccounts as $acct)
                                            <option value="{{ $acct->id }}">
                                                {{ $acct->account_code }} — {{ $acct->account_name }} ({{ ucfirst($acct->account_type) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" @click="preview = !preview"
                                        class="text-primary-600 hover:text-primary-800"
                                        :title="preview ? 'Hide fees' : 'Preview fees in this group'">
                                        <svg class="w-4 h-4 transition-transform inline" :class="preview ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                    </button>
                                </td>
                            </tr>
                            <tr x-show="preview" x-collapse>
                                <td colspan="4" class="px-3 pb-3 pt-1 bg-secondary-50">
                                    <p class="text-xs font-medium text-secondary-500 mb-2">Fees in this group — <span class="text-green-600">green = already mapped</span>:</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($group->children as $child)
                                            @php $isMapped = $mappings->has($child->id); @endphp
                                            <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-xs
                                                {{ $isMapped ? 'bg-green-100 text-green-700' : 'bg-white border border-secondary-200 text-secondary-600' }}">
                                                {{ $child->name }}
                                                @if($isMapped)
                                                    <svg class="w-3 h-3 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        @endforeach
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
</div>

{{-- ====================== MANUAL MAPPING SECTION ====================== --}}
@php $totalMapped = $mappings->count(); $totalFees = $financeGroups->sum('child_count'); @endphp

@php
    $allGroupNames = $financeGroups->pluck('name')->values()->all();
    $allFeeNames   = $financeGroups->flatMap(function($g){ return $g->children->pluck('name'); })->values()->all();
    $allNames      = array_merge($allGroupNames, $allFeeNames);
@endphp
<div x-data="{
    search: '',
    allNames: {{ json_encode($allNames) }},
    get hasResults() {
        if (this.search === '') return true;
        var q = this.search.toLowerCase();
        return this.allNames.some(function(n){ return n.toLowerCase().indexOf(q) !== -1; });
    }
}">
    {{-- Section header + search bar --}}
    <div class="flex items-center justify-between gap-4 mb-3">
        <div>
            <h3 class="text-sm font-semibold text-secondary-700">Manual Mapping</h3>
            <p class="text-xs text-secondary-400 mt-0.5">{{ $totalMapped }} / {{ $totalFees }} fees mapped — expand a group to edit</p>
        </div>
        <div class="relative w-72">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-secondary-400 pointer-events-none"
                fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input type="text" x-model="search" placeholder="Search groups or fees…"
                class="form-input pl-9 pr-8 text-sm w-full">
            <button type="button" x-show="search !== ''" @click="search = ''"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-secondary-400 hover:text-secondary-600">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </div>

    {{-- Groups --}}
    <div class="space-y-2">
        @foreach($financeGroups as $group)
        @php
            $allMapped  = $group->mapped_count === $group->child_count && $group->child_count > 0;
            $someMapped = $group->mapped_count > 0 && !$allMapped;
            $childNamesJson = json_encode($group->children->pluck('name')->values()->all());
        @endphp
        <div x-data="{
                open: false,
                groupName: '{{ addslashes($group->name) }}',
                feeNames: {{ $childNamesJson }},
                get matched() {
                    if (this.search === '') return true;
                    var q = this.search.toLowerCase();
                    return this.groupName.toLowerCase().indexOf(q) !== -1
                        || this.feeNames.some(function(f){ return f.toLowerCase().indexOf(q) !== -1; });
                }
             }"
             x-show="matched"
             x-effect="if (search !== '' && matched) { open = true } else if (search === '') { open = false }"
             class="border border-secondary-200 rounded-lg overflow-hidden">

            {{-- Group Header --}}
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-4 py-3 text-left
                    {{ $allMapped ? 'bg-green-50 hover:bg-green-100' : ($someMapped ? 'bg-yellow-50 hover:bg-yellow-100' : 'bg-white hover:bg-secondary-50') }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-secondary-400 transition-transform flex-shrink-0" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    <span class="text-sm font-medium text-secondary-800">{{ $group->name }}</span>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    @if($allMapped)
                        <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-medium">All mapped</span>
                    @elseif($someMapped)
                        <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 font-medium">{{ $group->mapped_count }}/{{ $group->child_count }} mapped</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-secondary-100 text-secondary-500 font-medium">{{ $group->child_count }} unmapped</span>
                    @endif
                </div>
            </button>

            {{-- Group Content: x-if removes from DOM when collapsed → zero selects rendered on load --}}
            <template x-if="open">
                <form action="{{ route('reports.fee-account-mappings.save') }}" method="POST">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-secondary-100 text-sm">
                            <thead class="bg-secondary-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-secondary-500">Fee Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-secondary-500 w-96">Accounting Account</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-secondary-500 w-24">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-secondary-50 bg-white">
                                @foreach($group->children as $fee)
                                @php $mapped = $mappings->get($fee->id); @endphp
                                <tr class="{{ $mapped ? 'bg-green-50/30' : '' }}"
                                    x-show="search === '' || '{{ addslashes(strtolower($fee->name)) }}'.indexOf(search.toLowerCase()) !== -1">
                                    <td class="px-4 py-2 font-medium text-secondary-800">
                                        {{ $fee->name }}
                                        <input type="hidden" name="mappings[{{ $fee->id }}][finance_fee_id]" value="{{ $fee->id }}">
                                        <input type="hidden" name="mappings[{{ $fee->id }}][finance_fee_name]" value="{{ $fee->name }}">
                                    </td>

                                    {{-- Account cell: mapped = show text+Change toggle; unmapped = show select directly --}}
                                    <td class="px-4 py-2" x-data="{ editing: {{ $mapped ? 'false' : 'true' }} }">
                                        @if($mapped)
                                        {{-- Not editing: account name text + hidden value + Change button --}}
                                        <template x-if="!editing">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm text-secondary-700">
                                                    {{ $mapped->account->account_code ?? '' }}
                                                    @if($mapped->account) — {{ $mapped->account->account_name }} @endif
                                                </span>
                                                <button type="button" @click="editing = true"
                                                    class="text-xs text-primary-600 hover:text-primary-800 hover:underline flex-shrink-0">
                                                    Change
                                                </button>
                                                <input type="hidden" name="mappings[{{ $fee->id }}][account_id]" value="{{ $mapped->account_id }}">
                                            </div>
                                        </template>
                                        @endif
                                        {{-- Editing (or unmapped): full select dropdown --}}
                                        <template x-if="{{ $mapped ? 'editing' : 'true' }}">
                                            <div class="flex items-center gap-2">
                                                <select name="mappings[{{ $fee->id }}][account_id]" class="form-input text-sm flex-1">
                                                    <option value="">-- Not Mapped --</option>
                                                    @foreach($revenueAccounts as $acct)
                                                        <option value="{{ $acct->id }}" {{ $mapped && $mapped->account_id == $acct->id ? 'selected' : '' }}>
                                                            {{ $acct->account_code }} — {{ $acct->account_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if($mapped)
                                                <button type="button" @click="editing = false"
                                                    class="text-xs text-secondary-400 hover:text-secondary-600 flex-shrink-0">
                                                    Cancel
                                                </button>
                                                @endif
                                            </div>
                                        </template>
                                    </td>

                                    <td class="px-4 py-2 text-center">
                                        @if($mapped)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Mapped</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-500">Unmapped</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-3 border-t border-secondary-100 bg-secondary-50 flex justify-end">
                        <button type="submit" class="btn-primary text-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            Save {{ $group->name }}
                        </button>
                    </div>
                </form>
            </template>
        </div>
        @endforeach

        {{-- No results --}}
        <div x-show="!hasResults"
             class="text-center py-10 text-secondary-400 text-sm border border-secondary-200 rounded-lg">
            No groups or fees match "<span x-text="search" class="font-medium text-secondary-600"></span>".
        </div>
    </div>
</div>
@endsection
