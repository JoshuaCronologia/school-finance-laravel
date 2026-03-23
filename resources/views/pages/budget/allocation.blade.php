@extends('layouts.app')
@section('title', 'Budget Allocation')

@section('content')
<x-page-header title="Budget Allocation Table" subtitle="Monthly budget distribution">
    <x-slot:actions>
        <a href="{{ route('budget.allocation.export') ?? '#' }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Export to Excel
        </a>
    </x-slot:actions>
</x-page-header>

@if(session('success'))
    <x-alert type="success" :message="session('success')" class="mb-4" />
@endif

{{-- Tip --}}
<div class="flex items-center gap-2 text-sm text-secondary-500 mb-4">
    <svg class="w-4 h-4 flex-shrink-0 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
    <span>Click on any monthly cell to edit the allocation amount. Changes are saved automatically.</span>
</div>

@php
    $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $grandTotalAnnual = 0;
    $grandTotalMonthly = array_fill(1, 12, 0);
@endphp

<div class="card">
    <div class="card-header"><h3 class="card-title">Monthly Allocation Spreadsheet</h3></div>
    <div class="overflow-x-auto">
        <table class="data-table text-sm" id="allocation-table">
            <thead>
                <tr>
                    <th class="sticky left-0 bg-white z-10 min-w-[160px]">Department</th>
                    <th class="min-w-[140px]">Category</th>
                    <th class="text-right min-w-[120px]">Annual Budget</th>
                    @foreach($monthNames as $m)
                    <th class="text-right min-w-[100px]">{{ $m }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($budgets ?? [] as $budget)
                @php
                    $allocations = $budget->allocations ? $budget->allocations->keyBy('month') : collect();
                    $totalAllocated = $allocations->sum('amount');
                    $mismatch = abs($totalAllocated - $budget->annual_budget) > 0.01;
                    $grandTotalAnnual += $budget->annual_budget;
                    for ($m = 1; $m <= 12; $m++) {
                        $grandTotalMonthly[$m] += $allocations[$m]->amount ?? 0;
                    }
                @endphp
                <tr>
                    <td class="sticky left-0 bg-white font-medium">{{ $budget->department->name ?? '-' }}</td>
                    <td>{{ $budget->category->name ?? $budget->budget_name }}</td>
                    <td class="text-right font-medium">{{ '₱' . number_format($budget->annual_budget, 2) }}</td>
                    @for($m = 1; $m <= 12; $m++)
                    <td class="text-right p-0">
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            class="allocation-cell w-full text-right text-sm border-0 bg-transparent px-3 py-2 focus:bg-primary-50 focus:ring-2 focus:ring-primary-300 focus:outline-none rounded transition-colors"
                            value="{{ isset($allocations[$m]) ? number_format($allocations[$m]->amount, 2, '.', '') : '0.00' }}"
                            data-budget-id="{{ $budget->id }}"
                            data-month="{{ $m }}"
                            data-original="{{ isset($allocations[$m]) ? number_format($allocations[$m]->amount, 2, '.', '') : '0.00' }}"
                        >
                    </td>
                    @endfor
                </tr>
                @empty
                <tr><td colspan="{{ 3 + 12 }}" class="text-center text-secondary-400 py-8">No budgets to allocate. Create budgets first in Budget Planning.</td></tr>
                @endforelse
            </tbody>
            @if(count($budgets ?? []) > 0)
            <tfoot>
                <tr class="bg-gray-50 font-bold">
                    <td class="sticky left-0 bg-gray-50">TOTALS</td>
                    <td></td>
                    <td class="text-right">{{ '₱' . number_format($grandTotalAnnual, 2) }}</td>
                    @for($m = 1; $m <= 12; $m++)
                    <td class="text-right" id="month-total-{{ $m }}">{{ '₱' . number_format($grandTotalMonthly[$m], 2) }}</td>
                    @endfor
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- Row-level mismatch warnings --}}
@if(count($budgets ?? []) > 0)
<div id="mismatch-warnings" class="mt-4 space-y-1">
    @foreach($budgets as $budget)
    @php
        $allocations = $budget->allocations ? $budget->allocations->keyBy('month') : collect();
        $totalAllocated = $allocations->sum('amount');
        $mismatch = abs($totalAllocated - $budget->annual_budget) > 0.01;
    @endphp
    @if($mismatch)
    <p class="text-sm text-danger-600">
        <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
        {{ $budget->budget_name }}: Allocated ₱{{ number_format($totalAllocated, 2) }} does not match annual budget ₱{{ number_format($budget->annual_budget, 2) }}
    </p>
    @endif
    @endforeach
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.querySelectorAll('.allocation-cell').forEach(function(input) {
        // Save on blur
        input.addEventListener('blur', function() {
            saveAllocation(this);
        });

        // Save on Enter key
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.blur();
            }
            // Tab navigates naturally
        });

        // Select all text on focus for easy editing
        input.addEventListener('focus', function() {
            this.select();
        });
    });

    function saveAllocation(input) {
        const budgetId = input.dataset.budgetId;
        const month = input.dataset.month;
        const originalValue = input.dataset.original;
        const newValue = parseFloat(input.value) || 0;

        // Skip if value has not changed
        if (parseFloat(originalValue) === newValue) {
            return;
        }

        // Visual loading state
        input.classList.add('opacity-50');

        fetch('{{ route("budget.allocation.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                budget_id: budgetId,
                month: month,
                amount: newValue
            })
        })
        .then(function(response) {
            if (!response.ok) throw new Error('Save failed');
            return response.json();
        })
        .then(function(data) {
            // Update the stored original value
            input.dataset.original = newValue.toFixed(2);
            input.value = newValue.toFixed(2);

            // Flash green to confirm save
            input.classList.remove('opacity-50');
            input.classList.add('bg-success-50');
            setTimeout(function() {
                input.classList.remove('bg-success-50');
            }, 1000);

            // Recalculate column totals
            recalculateTotals();
        })
        .catch(function(error) {
            // Revert to original value on error
            input.value = originalValue;
            input.classList.remove('opacity-50');
            input.classList.add('bg-danger-50');
            setTimeout(function() {
                input.classList.remove('bg-danger-50');
            }, 2000);
            console.error('Failed to save allocation:', error);
        });
    }

    function recalculateTotals() {
        for (var m = 1; m <= 12; m++) {
            var total = 0;
            document.querySelectorAll('.allocation-cell[data-month="' + m + '"]').forEach(function(input) {
                total += parseFloat(input.value) || 0;
            });
            var totalCell = document.getElementById('month-total-' + m);
            if (totalCell) {
                totalCell.textContent = '\u20B1' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }
    }
});
</script>
@endpush

@push('styles')
<style>
    .allocation-cell::-webkit-inner-spin-button,
    .allocation-cell::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .allocation-cell[type=number] {
        -moz-appearance: textfield;
    }
</style>
@endpush
