@extends('layouts.print')

@section('title', 'Check Print')

@push('styles')
<style>
    /* Hide letterhead for check printing on pre-formatted paper */
    header.text-center { display: none !important; }
    footer { display: none !important; }

    /* Configurable margins for check alignment */
    :root {
        --check-margin-top: {{ $margins['top'] ?? '0.5in' }};
        --check-margin-left: {{ $margins['left'] ?? '0.5in' }};
        --check-margin-right: {{ $margins['right'] ?? '0.5in' }};
    }

    .check-body {
        margin-top: var(--check-margin-top);
        margin-left: var(--check-margin-left);
        margin-right: var(--check-margin-right);
        font-family: 'Courier New', Courier, monospace;
    }

    @media print {
        @page {
            margin: 0;
            size: 8.5in 3.67in; /* Standard check size */
        }
        body { margin: 0; padding: 0; }
        .no-print { display: none !important; }
        .max-w-4xl { max-width: 100%; padding: 0; }
    }

    .amount-box {
        border: 2px solid #333;
        padding: 2px 8px;
        font-weight: bold;
        font-size: 14px;
        min-width: 120px;
        display: inline-block;
        text-align: right;
    }

    .check-line {
        border-bottom: 1px solid #333;
        min-height: 24px;
        line-height: 24px;
    }
</style>
@endpush

@section('content')
    <div class="check-body">
        {{-- Bank Name and Check Number --}}
        <div class="flex justify-between items-start mb-6">
            <div>
                <p class="text-lg font-bold tracking-wide">{{ $check->bank_name ?? 'BANK NAME' }}</p>
                <p class="text-xs text-gray-500">{{ $check->bank_branch ?? '' }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500 mb-1">Check No.</p>
                <p class="text-lg font-bold text-red-700 tracking-widest">{{ $check->check_number ?? '' }}</p>
            </div>
        </div>

        {{-- Date --}}
        <div class="flex justify-end mb-4">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold">DATE:</span>
                <span class="check-line px-4 text-sm">
                    {{ isset($check->check_date) ? $check->check_date->format('F d, Y') : '' }}
                </span>
            </div>
        </div>

        {{-- Pay To The Order Of --}}
        <div class="flex items-center gap-3 mb-3">
            <span class="text-xs font-bold whitespace-nowrap">PAY TO THE<br>ORDER OF</span>
            <div class="check-line flex-1 px-2 text-sm font-semibold">
                {{ $check->payee_name ?? '' }}
            </div>
            <div class="amount-box">
                &#8369;{{ isset($check) ? number_format($check->amount, 2) : '***0.00' }}
            </div>
        </div>

        {{-- Amount in Words --}}
        <div class="flex items-center gap-3 mb-6">
            <div class="check-line flex-1 px-2 text-sm italic">
                {{ $check->amount_in_words ?? '' }} ONLY
            </div>
            <span class="text-xs font-bold">PESOS</span>
        </div>

        {{-- Memo --}}
        <div class="flex items-center gap-3 mb-8">
            <span class="text-xs font-bold">MEMO:</span>
            <div class="check-line flex-1 px-2 text-xs text-gray-600">
                {{ $check->memo ?? '' }}
            </div>
        </div>

        {{-- Bottom Section: MICR line + Signature --}}
        <div class="flex justify-between items-end mt-8">
            <div class="text-xs font-mono text-gray-400 tracking-widest">
                {{ $check->account_number_masked ?? '****' }}
            </div>
            <div class="text-center w-48">
                <div class="border-b border-gray-800 mb-1 pb-6"></div>
                <p class="text-xs font-semibold">Authorized Signature</p>
            </div>
        </div>
    </div>
@endsection
