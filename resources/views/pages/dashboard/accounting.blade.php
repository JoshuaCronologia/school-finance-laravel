@extends('layouts.app')
@section('title', 'Accounting Dashboard')

@section('content')
<x-page-header title="Accounting Dashboard" subtitle="Financial overview and key metrics" />

{{-- Stat Cards Row 1 --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <x-stat-card label="Total Receivables" value="{{ '₱' . number_format($totalReceivables, 2) }}" color="blue">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75" /></svg></x-slot>
    </x-stat-card>

    <x-stat-card label="Total Payables" value="{{ '₱' . number_format($totalPayables, 2) }}" color="red">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot>
    </x-stat-card>

    <x-stat-card label="Cash & Bank" value="{{ '₱' . number_format($cashBalance, 2) }}" color="green">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21" /></svg></x-slot>
    </x-stat-card>

    <x-stat-card label="Net Income" value="{{ '₱' . number_format($netIncome, 2) }}" color="{{ $netIncome >= 0 ? 'green' : 'red' }}">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg></x-slot>
    </x-stat-card>
</div>

{{-- Stat Cards Row 2 --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <x-stat-card label="Month Revenue" value="{{ '₱' . number_format($totalRevenue, 2) }}" color="indigo">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg></x-slot>
    </x-stat-card>

    <x-stat-card label="Month Expenses" value="{{ '₱' . number_format($totalExpenses, 2) }}" color="yellow">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181" /></svg></x-slot>
    </x-stat-card>

    <x-stat-card label="Unposted Entries" value="{{ $unpostedCount }}" color="yellow" subtitle="{{ $unpostedCount > 0 ? 'Needs attention' : 'All posted' }}">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg></x-slot>
    </x-stat-card>

    <x-stat-card label="Overdue AR" value="{{ '₱' . number_format($overdueAR, 2) }}" color="red" subtitle="Overdue AP: ₱{{ number_format($overdueAP ?? 0, 2) }}">
        <x-slot name="icon"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></x-slot>
    </x-stat-card>
</div>

{{-- Quick Actions --}}
<div class="card mb-6">
    <div class="card-header"><h3 class="card-title">Quick Actions</h3></div>
    <div class="card-body">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('gl.journal-entries.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                New Journal Entry
            </a>
            <a href="{{ route('gl.journal-entries.approval') }}" class="btn-secondary inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                JE Approval Queue
                @if(($pendingApprovalCount ?? 0) > 0)
                    <span class="ml-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">{{ $pendingApprovalCount }}</span>
                @endif
            </a>
            <a href="{{ route('ar.invoices.create') }}" class="btn-secondary">New Invoice</a>
            <a href="{{ route('ar.collections.create') }}" class="btn-secondary">Receive Payment</a>
            <a href="{{ route('ap.bills.create') }}" class="btn-secondary">Create Bill</a>
            <a href="{{ route('ap.disbursements.create') }}" class="btn-secondary">Supplier Payment</a>
            <a href="{{ route('reports.trial-balance') }}" class="btn-secondary">Trial Balance</a>
        </div>
    </div>
</div>

{{-- AR & AP Aging --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- AR Aging Summary --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title">AR Aging Summary</h3></div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Current</th>
                        <th class="text-right">1-30 Days</th>
                        <th class="text-right">31-60 Days</th>
                        <th class="text-right">61-90 Days</th>
                        <th class="text-right">90+ Days</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($arAging))
                    <tr>
                        <td class="text-right">{{ '₱' . number_format($arAging->current ?? 0, 2) }}</td>
                        <td class="text-right">{{ '₱' . number_format($arAging->days_30 ?? 0, 2) }}</td>
                        <td class="text-right">{{ '₱' . number_format($arAging->days_60 ?? 0, 2) }}</td>
                        <td class="text-right">{{ '₱' . number_format($arAging->days_90 ?? 0, 2) }}</td>
                        <td class="text-right text-danger-600 font-semibold">{{ '₱' . number_format($arAging->days_over_90 ?? 0, 2) }}</td>
                        <td class="text-right font-bold">{{ '₱' . number_format($arAging->total ?? 0, 2) }}</td>
                    </tr>
                    @else
                    <tr><td colspan="6" class="text-center text-secondary-400">No AR aging data available</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- AP Aging Summary --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title">AP Aging Summary</h3></div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Current</th>
                        <th class="text-right">1-30 Days</th>
                        <th class="text-right">31-60 Days</th>
                        <th class="text-right">61-90 Days</th>
                        <th class="text-right">90+ Days</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($apAging))
                    <tr>
                        <td class="text-right">{{ '₱' . number_format($apAging->current ?? 0, 2) }}</td>
                        <td class="text-right">{{ '₱' . number_format($apAging->days_30 ?? 0, 2) }}</td>
                        <td class="text-right">{{ '₱' . number_format($apAging->days_60 ?? 0, 2) }}</td>
                        <td class="text-right">{{ '₱' . number_format($apAging->days_90 ?? 0, 2) }}</td>
                        <td class="text-right text-danger-600 font-semibold">{{ '₱' . number_format($apAging->days_over_90 ?? 0, 2) }}</td>
                        <td class="text-right font-bold">{{ '₱' . number_format($apAging->total ?? 0, 2) }}</td>
                    </tr>
                    @else
                    <tr><td colspan="6" class="text-center text-secondary-400">No AP aging data available</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Top Expense Categories & Top Vendors --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Top Expense Categories --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title">Top Expense Categories (YTD)</h3></div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Category</th><th class="text-right">Amount</th></tr></thead>
                <tbody>
                    @forelse($topExpenseCategories ?? [] as $cat)
                    <tr>
                        <td>{{ $cat->account_name ?? 'Unknown' }}</td>
                        <td class="text-right font-medium">{{ '₱' . number_format($cat->total, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center text-secondary-400">No expense data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top Vendors --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title">Top Vendors</h3></div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Vendor</th><th class="text-right">Total Paid</th></tr></thead>
                <tbody>
                    @forelse($topVendors ?? [] as $tv)
                    <tr>
                        <td>{{ $tv->name ?? 'Unknown' }}</td>
                        <td class="text-right font-medium">{{ '₱' . number_format($tv->total_balance ?? $tv->total_paid ?? 0, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center text-secondary-400">No vendor data available</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Recent Journal Entries with Expandable Rows --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Journal Entries</h3>
        <a href="{{ route('gl.journal-entries.index') }}" class="text-sm text-primary-600 hover:text-primary-700">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-8"></th>
                    <th>Entry #</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentJEs ?? [] as $je)
                <tr class="cursor-pointer hover:bg-gray-50 je-toggle-row">
                    <td>
                        <button class="btn-icon p-1">
                            <svg class="w-4 h-4 transition-transform je-chevron" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                        </button>
                    </td>
                    <td><a href="{{ route('gl.journal-entries.show', $je) }}" class="text-primary-600 hover:underline font-medium" onclick="event.stopPropagation()">{{ $je->entry_number }}</a></td>
                    <td>{{ $je->entry_date->format('M d, Y') }}</td>
                    <td><span class="badge badge-info">{{ $je->journal_type }}</span></td>
                    <td class="max-w-xs truncate">{{ $je->description }}</td>
                    <td class="text-right">{{ '₱' . number_format($je->total_debit, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($je->total_credit, 2) }}</td>
                    <td><x-badge :status="$je->status" /></td>
                </tr>
                {{-- Expandable detail row --}}
                <tr class="je-detail-row hidden">
                    <td colspan="8" class="bg-gray-50 p-0">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-2 text-left">Account Code</th>
                                    <th class="px-4 py-2 text-left">Account Name</th>
                                    <th class="px-4 py-2 text-right">Debit</th>
                                    <th class="px-4 py-2 text-right">Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($je->lines ?? [] as $line)
                                <tr class="border-t border-gray-100">
                                    <td class="px-4 py-1.5">{{ $line->account->account_code ?? '-' }}</td>
                                    <td class="px-4 py-1.5">{{ $line->account->account_name ?? '-' }}</td>
                                    <td class="px-4 py-1.5 text-right">{{ $line->debit > 0 ? '₱' . number_format($line->debit, 2) : '-' }}</td>
                                    <td class="px-4 py-1.5 text-right">{{ $line->credit > 0 ? '₱' . number_format($line->credit, 2) : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-secondary-400">No journal entries yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.je-toggle-row').forEach(row => {
        row.addEventListener('click', () => {
            const detail = row.nextElementSibling;
            if (detail && detail.classList.contains('je-detail-row')) {
                detail.classList.toggle('hidden');
                row.querySelector('.je-chevron').classList.toggle('rotate-90');
            }
        });
    });
</script>
@endpush
