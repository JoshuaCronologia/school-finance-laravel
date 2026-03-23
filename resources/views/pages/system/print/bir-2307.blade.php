@extends('layouts.print')

@section('title', 'BIR Form 2307')

@push('styles')
<style>
    /* Hide default letterhead */
    header.text-center { display: none !important; }

    @media print {
        @page {
            margin: 0.25in;
            size: A4 landscape;
        }
        body { font-size: 10px; }
    }

    .bir-form {
        font-family: 'Courier New', Courier, monospace;
        font-size: 11px;
        border: 2px solid #000;
        max-width: 100%;
    }

    .bir-header {
        background: #000;
        color: #fff;
        text-align: center;
        padding: 4px 8px;
        font-weight: bold;
        font-size: 12px;
    }

    .bir-subheader {
        background: #e5e5e5;
        padding: 3px 8px;
        font-weight: bold;
        font-size: 10px;
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
    }

    .bir-row {
        display: flex;
        border-bottom: 1px solid #ccc;
    }

    .bir-cell {
        padding: 3px 6px;
        border-right: 1px solid #ccc;
        min-height: 22px;
        display: flex;
        align-items: center;
    }

    .bir-cell:last-child {
        border-right: none;
    }

    .bir-label {
        font-size: 9px;
        color: #555;
        font-weight: bold;
    }

    .bir-value {
        font-size: 11px;
    }

    .tin-box {
        display: inline-flex;
        gap: 0;
    }

    .tin-digit {
        width: 18px;
        height: 20px;
        border: 1px solid #000;
        text-align: center;
        line-height: 20px;
        font-weight: bold;
        font-size: 12px;
    }

    .tin-dash {
        width: 8px;
        height: 20px;
        text-align: center;
        line-height: 20px;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
    @php
        $withholdingAgent = $form->withholding_agent ?? (object)[];
        $payee = $form->payee ?? (object)[];
        $items = $form->items ?? [];
        $agentTin = str_split(str_replace('-', '', $withholdingAgent->tin ?? ''));
        $payeeTin = str_split(str_replace('-', '', $payee->tin ?? ''));
    @endphp

    <div class="bir-form mx-auto">
        {{-- Form Title --}}
        <div class="bir-header">
            BIR FORM NO. 2307
        </div>
        <div class="text-center py-2 border-b border-black text-xs">
            <strong>CERTIFICATE OF CREDITABLE TAX WITHHELD AT SOURCE</strong>
        </div>

        {{-- Part I: Withholding Agent/Payor --}}
        <div class="bir-subheader">
            Part I &mdash; Withholding Agent / Payor
        </div>

        <div class="p-3 border-b border-gray-300">
            <div class="mb-2">
                <span class="bir-label">1. TIN: </span>
                <div class="tin-box ml-2">
                    @for ($i = 0; $i < 9; $i++)
                        @if ($i === 3 || $i === 6)
                            <div class="tin-dash">-</div>
                        @endif
                        <div class="tin-digit">{{ $agentTin[$i] ?? '' }}</div>
                    @endfor
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="bir-label">2. Name:</span>
                    <span class="bir-value ml-1">{{ $withholdingAgent->name ?? 'OrangeApps Academy' }}</span>
                </div>
                <div>
                    <span class="bir-label">3. RDO Code:</span>
                    <span class="bir-value ml-1">{{ $withholdingAgent->rdo_code ?? '' }}</span>
                </div>
            </div>

            <div class="mt-2 text-sm">
                <span class="bir-label">4. Address:</span>
                <span class="bir-value ml-1">{{ $withholdingAgent->address ?? '123 Education Avenue, Makati City, Metro Manila' }}</span>
            </div>

            <div class="grid grid-cols-2 gap-3 mt-2 text-sm">
                <div>
                    <span class="bir-label">5. Zip Code:</span>
                    <span class="bir-value ml-1">{{ $withholdingAgent->zip_code ?? '1200' }}</span>
                </div>
                <div>
                    <span class="bir-label">6. Phone No.:</span>
                    <span class="bir-value ml-1">{{ $withholdingAgent->phone ?? '' }}</span>
                </div>
            </div>
        </div>

        {{-- Part II: Payee / Income Recipient --}}
        <div class="bir-subheader">
            Part II &mdash; Payee / Income Recipient
        </div>

        <div class="p-3 border-b border-gray-300">
            <div class="mb-2">
                <span class="bir-label">7. TIN: </span>
                <div class="tin-box ml-2">
                    @for ($i = 0; $i < 9; $i++)
                        @if ($i === 3 || $i === 6)
                            <div class="tin-dash">-</div>
                        @endif
                        <div class="tin-digit">{{ $payeeTin[$i] ?? '' }}</div>
                    @endfor
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="bir-label">8. Name:</span>
                    <span class="bir-value ml-1">{{ $payee->name ?? '' }}</span>
                </div>
                <div>
                    <span class="bir-label">9. RDO Code:</span>
                    <span class="bir-value ml-1">{{ $payee->rdo_code ?? '' }}</span>
                </div>
            </div>

            <div class="mt-2 text-sm">
                <span class="bir-label">10. Address:</span>
                <span class="bir-value ml-1">{{ $payee->address ?? '' }}</span>
            </div>

            <div class="grid grid-cols-2 gap-3 mt-2 text-sm">
                <div>
                    <span class="bir-label">11. Zip Code:</span>
                    <span class="bir-value ml-1">{{ $payee->zip_code ?? '' }}</span>
                </div>
                <div>
                    <span class="bir-label">12. Phone No.:</span>
                    <span class="bir-value ml-1">{{ $payee->phone ?? '' }}</span>
                </div>
            </div>
        </div>

        {{-- Part III: Details of Income Payment and Tax Withheld --}}
        <div class="bir-subheader">
            Part III &mdash; Details of Income Payment and Tax Withheld
        </div>

        <div class="p-3">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr>
                        <th class="border border-gray-800 px-2 py-1.5 text-left bg-gray-100" rowspan="2">ATC</th>
                        <th class="border border-gray-800 px-2 py-1.5 text-left bg-gray-100" rowspan="2">Nature of Income Payment</th>
                        <th class="border border-gray-800 px-2 py-1.5 text-center bg-gray-100" colspan="3">Amount of Income Payment</th>
                        <th class="border border-gray-800 px-2 py-1.5 text-center bg-gray-100" colspan="3">Amount of Tax Withheld</th>
                    </tr>
                    <tr>
                        <th class="border border-gray-800 px-2 py-1 text-right bg-gray-50 text-[9px]">1st Qtr</th>
                        <th class="border border-gray-800 px-2 py-1 text-right bg-gray-50 text-[9px]">2nd Qtr</th>
                        <th class="border border-gray-800 px-2 py-1 text-right bg-gray-50 text-[9px]">3rd Qtr</th>
                        <th class="border border-gray-800 px-2 py-1 text-right bg-gray-50 text-[9px]">1st Qtr</th>
                        <th class="border border-gray-800 px-2 py-1 text-right bg-gray-50 text-[9px]">2nd Qtr</th>
                        <th class="border border-gray-800 px-2 py-1 text-right bg-gray-50 text-[9px]">3rd Qtr</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td class="border border-gray-800 px-2 py-1">{{ $item->atc ?? '' }}</td>
                            <td class="border border-gray-800 px-2 py-1">{{ $item->nature ?? '' }}</td>
                            <td class="border border-gray-800 px-2 py-1 text-right">{{ isset($item->income_q1) ? number_format($item->income_q1, 2) : '' }}</td>
                            <td class="border border-gray-800 px-2 py-1 text-right">{{ isset($item->income_q2) ? number_format($item->income_q2, 2) : '' }}</td>
                            <td class="border border-gray-800 px-2 py-1 text-right">{{ isset($item->income_q3) ? number_format($item->income_q3, 2) : '' }}</td>
                            <td class="border border-gray-800 px-2 py-1 text-right">{{ isset($item->tax_q1) ? number_format($item->tax_q1, 2) : '' }}</td>
                            <td class="border border-gray-800 px-2 py-1 text-right">{{ isset($item->tax_q2) ? number_format($item->tax_q2, 2) : '' }}</td>
                            <td class="border border-gray-800 px-2 py-1 text-right">{{ isset($item->tax_q3) ? number_format($item->tax_q3, 2) : '' }}</td>
                        </tr>
                    @empty
                        @for ($i = 0; $i < 5; $i++)
                            <tr>
                                <td class="border border-gray-800 px-2 py-2">&nbsp;</td>
                                <td class="border border-gray-800 px-2 py-2">&nbsp;</td>
                                <td class="border border-gray-800 px-2 py-2">&nbsp;</td>
                                <td class="border border-gray-800 px-2 py-2">&nbsp;</td>
                                <td class="border border-gray-800 px-2 py-2">&nbsp;</td>
                                <td class="border border-gray-800 px-2 py-2">&nbsp;</td>
                                <td class="border border-gray-800 px-2 py-2">&nbsp;</td>
                                <td class="border border-gray-800 px-2 py-2">&nbsp;</td>
                            </tr>
                        @endfor
                    @endforelse
                    <tr class="font-bold bg-gray-100">
                        <td class="border border-gray-800 px-2 py-1.5" colspan="2">TOTAL</td>
                        <td class="border border-gray-800 px-2 py-1.5 text-right">{{ isset($form->total_income_q1) ? number_format($form->total_income_q1, 2) : '' }}</td>
                        <td class="border border-gray-800 px-2 py-1.5 text-right">{{ isset($form->total_income_q2) ? number_format($form->total_income_q2, 2) : '' }}</td>
                        <td class="border border-gray-800 px-2 py-1.5 text-right">{{ isset($form->total_income_q3) ? number_format($form->total_income_q3, 2) : '' }}</td>
                        <td class="border border-gray-800 px-2 py-1.5 text-right">{{ isset($form->total_tax_q1) ? number_format($form->total_tax_q1, 2) : '' }}</td>
                        <td class="border border-gray-800 px-2 py-1.5 text-right">{{ isset($form->total_tax_q2) ? number_format($form->total_tax_q2, 2) : '' }}</td>
                        <td class="border border-gray-800 px-2 py-1.5 text-right">{{ isset($form->total_tax_q3) ? number_format($form->total_tax_q3, 2) : '' }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- Signatures --}}
            <div class="grid grid-cols-2 gap-12 mt-8 text-xs">
                <div>
                    <p class="font-bold mb-6">Withholding Agent / Authorized Representative</p>
                    <div class="border-b border-gray-800 mb-1 pb-6"></div>
                    <p class="font-semibold">Signature over Printed Name</p>
                    <div class="mt-3">
                        <span class="font-bold">Date: </span>
                        <span class="border-b border-gray-800 inline-block w-40">
                            {{ isset($form->date_signed) ? $form->date_signed->format('m/d/Y') : '' }}
                        </span>
                    </div>
                    <div class="mt-2">
                        <span class="font-bold">TIN: </span>
                        <span class="border-b border-gray-800 inline-block w-40">
                            {{ $withholdingAgent->tin ?? '' }}
                        </span>
                    </div>
                </div>
                <div>
                    <p class="font-bold mb-6">Payee / Income Recipient</p>
                    <div class="border-b border-gray-800 mb-1 pb-6"></div>
                    <p class="font-semibold">Signature over Printed Name</p>
                    <div class="mt-3">
                        <span class="font-bold">Date: </span>
                        <span class="border-b border-gray-800 inline-block w-40"></span>
                    </div>
                    <div class="mt-2">
                        <span class="font-bold">TIN: </span>
                        <span class="border-b border-gray-800 inline-block w-40">
                            {{ $payee->tin ?? '' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
