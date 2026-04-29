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
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-secondary-100">
                            @foreach($financeGroups as $group)
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
</div>

{{-- ====================== MANUAL MAPPING SECTION ====================== --}}
<div x-data="{
    search: '',
    get hasResults() {
        if (this.search === '') return true;
        var q = this.search.toLowerCase();
        var groups = document.querySelectorAll('[data-group-name]');
        for (var i = 0; i < groups.length; i++) {
            if (groups[i].getAttribute('data-group-name').toLowerCase().indexOf(q) !== -1) return true;
        }
        return false;
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
            <input type="text" x-model="search" placeholder="Search groups…"
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
        @endphp
        <div data-group-name="{{ $group->name }}"
             x-data="{
                open: false,
                loading: false,
                loaded: false,
                html: '',
                get matched() {
                    if (this.search === '') return true;
                    return {{ json_encode(strtolower($group->name)) }}.indexOf(this.search.toLowerCase()) !== -1;
                }
             }"
             x-show="matched"
             x-effect="if (!matched) { open = false }"
             class="border border-secondary-200 rounded-lg overflow-hidden">

            {{-- Group Header --}}
            <button type="button" @click="
                open = !open;
                if (open && !loaded) {
                    loading = true;
                    fetch('{{ route('reports.fee-account-mappings.group-fees', $group->id) }}')
                        .then(function(r) { return r.text(); })
                        .then(function(h) { html = h; loaded = true; loading = false; });
                }
            "
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

            {{-- Group Content — lazy loaded via AJAX --}}
            <div x-show="open">
                <div x-show="loading" class="text-center py-6 text-sm text-secondary-400">
                    <svg class="animate-spin w-5 h-5 mx-auto mb-1" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Loading fees…
                </div>
                <div x-show="loaded" x-html="html"></div>
            </div>
        </div>
        @endforeach

        {{-- No results --}}
        <div x-show="!hasResults"
             class="text-center py-10 text-secondary-400 text-sm border border-secondary-200 rounded-lg">
            No groups match "<span x-text="search" class="font-medium text-secondary-600"></span>".
        </div>
    </div>
</div>
@endsection
