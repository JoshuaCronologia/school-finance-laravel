@extends('layouts.app')
@section('title', 'BIR 1601-E')

@section('content')
@php
    $taxableMonth    = $taxableMonth ?? request('month', now()->format('Y-m'));
    $totalTaxWithheld = $totalTaxWithheld ?? 0;
    $taxCredits      = $taxCredits ?? 0;
    $netTaxDue       = $netTaxDue ?? ($totalTaxWithheld - $taxCredits);
    $penalties       = $penalties ?? 0;
    $totalAmountDue  = $totalAmountDue ?? ($netTaxDue + $penalties);
    $atcEntries      = $atcEntries ?? collect();
    $atcCodesUsed    = $atcCodesUsed ?? 0;
    $monthlyTrend    = $monthlyTrend ?? collect();

    $fmt = function($v) { return number_format(abs($v), 2); };

    // Parse month/year from taxableMonth (Y-m format)
    $parts = explode('-', $taxableMonth);
    $year  = isset($parts[0]) ? $parts[0] : date('Y');
    $month = isset($parts[1]) ? (int)$parts[1] : (int)date('m');
    $mo    = str_pad($month, 2, '0', STR_PAD_LEFT);
    $dueM  = $month == 12 ? 1 : $month + 1;
    $dueY  = $month == 12 ? $year + 1 : $year;
    $due   = date('m/d/Y', mktime(0, 0, 0, $dueM, 10, $dueY));
@endphp

<x-page-header title="BIR 1601-E" subtitle="Monthly Remittance Return of Creditable Income Taxes Withheld (Expanded)">
    <x-slot name="actions">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm no-print">Excel</a>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn-secondary text-sm no-print">PDF</a>
        <button onclick="window.print()" class="btn-primary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>
            Print Form
        </button>
    </x-slot>
</x-page-header>

{{-- Screen filter --}}
<div class="card mb-6 no-print">
    <form class="card-body">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">Taxable Month</label>
                <input type="month" name="month" value="{{ $taxableMonth }}" class="form-input w-48">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
            <a href="{{ request()->url() }}" class="btn-secondary">Clear</a>
        </div>
    </form>
</div>

{{-- BIR Form Page --}}
<div class="bir-page" id="page1">

    {{-- Top header bar --}}
    <div class="bir-topbar">
        <div class="bir-foruse">
            <div class="bir-foruse-label">For BIR<br>Use Only</div>
            <div class="bir-foruse-box">BCS/<br>Item</div>
        </div>
        <div class="bir-formtitle">
            <div class="bir-republic">
                Republic of the Philippines<br>
                Department of Finance<br>
                Bureau of Internal Revenue
            </div>
            <div class="bir-formname">
                <div style="font-size:9px;font-weight:bold;">BIR Form No.</div>
                <div style="font-size:28px;font-weight:bold;line-height:1;">1601-E</div>
                <div style="font-size:8px;">September 2005 (ENCS)</div>
                <div style="font-size:8px;">Page 1</div>
            </div>
            <div class="bir-formsubtitle">
                <div style="font-size:11px;font-weight:bold;">Monthly Remittance Return</div>
                <div style="font-size:10px;font-weight:bold;">of Creditable Income Taxes Withheld (Expanded)</div>
                <div style="font-size:7px;margin-top:2px;">Enter all required information in CAPITAL LETTERS using BLACK ink. Mark all applicable boxes with an "X". Two copies MUST be filed with the BIR and one kept by the Taxpayer.</div>
            </div>
        </div>
        <div class="bir-formno-box">
            <div style="font-size:7px;text-align:right;">1601-E 09/05ENCS P1</div>
        </div>
    </div>

    {{-- Row 1: Month / Amended / ATC / Tax Type --}}
    <table class="bir-table">
        <tr>
            <td class="bir-cell" style="width:22%">
                <div class="bir-label">1 For the Month (MM/YYYY)</div>
                <div class="bir-value">{{ $mo }}/{{ $year }}</div>
            </td>
            <td class="bir-cell" style="width:20%">
                <div class="bir-label">2 Amended Return?</div>
                <div class="bir-value">☐ Yes &nbsp; ☑ No</div>
            </td>
            <td class="bir-cell" style="width:20%">
                <div class="bir-label">3 Any Taxes Withheld?</div>
                <div class="bir-value">{{ $totalTaxWithheld > 0 ? '☑ Yes &nbsp; ☐ No' : '☐ Yes &nbsp; ☑ No' }}</div>
            </td>
            <td class="bir-cell" style="width:20%">
                <div class="bir-label">4 ATC</div>
                <div class="bir-value font-bold">ME</div>
            </td>
            <td class="bir-cell" style="width:18%">
                <div class="bir-label">5 Tax Type Code</div>
                <div class="bir-value font-bold">EWT</div>
            </td>
        </tr>
    </table>

    {{-- Part I: Background Information --}}
    <div class="bir-section-header">Part I – Background Information</div>
    <table class="bir-table">
        <tr>
            <td class="bir-cell" style="width:60%">
                <div class="bir-label">6 Taxpayer Identification Number (TIN)</div>
                <div class="bir-value tin-boxes">
                    @php $tinParts = explode('-', $schoolTin ?? '000-000-000-000'); @endphp
                    @foreach(array_pad($tinParts, 4, '000') as $i => $tp)
                        <span class="tin-part">{{ $tp }}</span>@if($i < 3)<span class="tin-dash">-</span>@endif
                    @endforeach
                </div>
            </td>
            <td class="bir-cell" style="width:40%">
                <div class="bir-label">7 RDO Code</div>
                <div class="bir-value">{{ $schoolRdo ?? '&nbsp;' }}</div>
            </td>
        </tr>
        <tr>
            <td class="bir-cell" colspan="2">
                <div class="bir-label">8 Withholding Agent's Name (Last Name, First Name, Middle Name for Individual OR Registered Name for Non-Individual)</div>
                <div class="bir-value" style="text-transform:uppercase;font-size:11px;">{{ $schoolName ?? '&nbsp;' }}</div>
            </td>
        </tr>
        <tr>
            <td class="bir-cell" colspan="2">
                <div class="bir-label">9 Registered Address</div>
                <div class="bir-value" style="font-size:10px;">{{ $schoolAddress ?? '&nbsp;' }}</div>
            </td>
        </tr>
        <tr>
            <td class="bir-cell" style="width:40%">
                <div class="bir-label">10 Due Date (MM/DD/YYYY)</div>
                <div class="bir-value">{{ $due }}</div>
            </td>
            <td class="bir-cell" style="width:20%">
                <div class="bir-label">9A ZIP Code</div>
                <div class="bir-value">&nbsp;</div>
            </td>
            <td class="bir-cell" style="width:40%">
                <div class="bir-label">11 Category of Withholding Agent</div>
                <div class="bir-value">☑ Private &nbsp; ☐ Government</div>
            </td>
        </tr>
    </table>

    {{-- Part II: Computation of Tax --}}
    <div class="bir-section-header">Part II – Computation of Tax</div>
    <table class="bir-table">
        <tr>
            <td class="bir-cell bir-item-num">1</td>
            <td class="bir-cell bir-item-label">Amount of Taxes Withheld for the Month (from Part III)</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box">{{ $fmt($totalTaxWithheld) }}</div></td>
        </tr>
        <tr>
            <td class="bir-cell bir-item-num">2</td>
            <td class="bir-cell bir-item-label">Less: Tax Credits/Payments (attach details)</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box">{{ $fmt($taxCredits) }}</div></td>
        </tr>
        <tr class="bir-row-bold">
            <td class="bir-cell bir-item-num">3</td>
            <td class="bir-cell bir-item-label">Net Amount Still Due (Item 1 Less Item 2)</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box bir-amount-total">{{ $fmt($netTaxDue) }}</div></td>
        </tr>
        <tr>
            <td class="bir-cell bir-item-num"></td>
            <td class="bir-cell" style="font-size:8px;font-weight:bold;padding:2px 4px;">Add: Penalties</td>
            <td class="bir-cell bir-amount"></td>
        </tr>
        <tr>
            <td class="bir-cell bir-item-num">4A</td>
            <td class="bir-cell bir-item-label bir-indent">Surcharge</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box">0.00</div></td>
        </tr>
        <tr>
            <td class="bir-cell bir-item-num">4B</td>
            <td class="bir-cell bir-item-label bir-indent">Interest</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box">0.00</div></td>
        </tr>
        <tr>
            <td class="bir-cell bir-item-num">4C</td>
            <td class="bir-cell bir-item-label bir-indent">Compromise</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box">0.00</div></td>
        </tr>
        <tr class="bir-row-bold">
            <td class="bir-cell bir-item-num">5</td>
            <td class="bir-cell bir-item-label">Total Amount Due (Item 3 Plus Items 4A, 4B &amp; 4C)</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box bir-amount-total">{{ $fmt($totalAmountDue) }}</div></td>
        </tr>
    </table>

    {{-- Signature Section --}}
    <div class="bir-sig-section">
        <div class="bir-sig-text">
            We declare under the penalties of perjury that this return has been made in good faith, verified by me/us, and to the best of my/our knowledge and belief, is true and correct, pursuant to the provisions of the National Internal Revenue Code, as amended, and the regulations issued under authority thereof.
        </div>
        <div class="bir-sig-row">
            <div class="bir-sig-box">
                <div class="bir-sig-line"></div>
                <div class="bir-sig-label">Signature over Printed Name of Taxpayer/Authorized Representative/Tax Agent<br>(Indicate Title/Designation and TIN)</div>
                <div class="bir-sig-subrow">
                    <div class="bir-sig-subbox">
                        <div class="bir-sig-line-sm"></div>
                        <div style="font-size:7px;">Tax Agent Accreditation No./Attorney's Roll No. (if applicable)</div>
                    </div>
                    <div class="bir-sig-subbox">
                        <div class="bir-sig-line-sm"></div>
                        <div style="font-size:7px;">Date of Issue (MM/DD/YYYY)</div>
                    </div>
                    <div class="bir-sig-subbox">
                        <div class="bir-sig-line-sm"></div>
                        <div style="font-size:7px;">Date of Expiry (MM/DD/YYYY)</div>
                    </div>
                </div>
            </div>
            <div class="bir-sig-box">
                <div class="bir-sig-line"></div>
                <div class="bir-sig-label">Signature over Printed Name of President/Vice-President/<br>Authorized Officer or Representative/Tax Agent<br>(Indicate Title/Designation and TIN)</div>
                <div class="bir-sig-subrow">
                    <div class="bir-sig-subbox" style="flex:1">
                        <div class="bir-sig-line-sm"></div>
                        <div style="font-size:7px;">Date of Issue (MM/DD/YYYY)</div>
                    </div>
                    <div class="bir-sig-subbox" style="flex:1">
                        <div class="bir-sig-line-sm"></div>
                        <div style="font-size:7px;">Date of Expiry (MM/DD/YYYY)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Part III Summary of Withholding (on-form ATC table header only for print) --}}
    <div class="bir-section-header">Part III – Schedule of Withholding</div>
    <table class="bir-table" style="font-size:8px;">
        <tr style="background:#f0f0f0;">
            <th class="bir-cell" style="width:12%;font-size:7px;">ATC</th>
            <th class="bir-cell" style="width:48%;font-size:7px;">Nature of Income Payment</th>
            <th class="bir-cell" style="width:10%;font-size:7px;text-align:right;">Rate (%)</th>
            <th class="bir-cell" style="width:15%;font-size:7px;text-align:right;">Tax Base</th>
            <th class="bir-cell" style="width:15%;font-size:7px;text-align:right;">Tax Withheld</th>
        </tr>
        @forelse($atcEntries as $entry)
        <tr>
            <td class="bir-cell" style="font-family:monospace;font-size:8px;">{{ $entry->atc ?? '' }}</td>
            <td class="bir-cell" style="font-size:8px;">{{ $entry->nature ?? '' }}</td>
            <td class="bir-cell" style="text-align:right;font-family:monospace;font-size:8px;">{{ number_format($entry->rate ?? 0, 1) }}%</td>
            <td class="bir-cell" style="text-align:right;font-family:monospace;font-size:8px;">{{ number_format($entry->tax_base ?? 0, 2) }}</td>
            <td class="bir-cell" style="text-align:right;font-family:monospace;font-size:8px;">{{ number_format($entry->tax_withheld ?? 0, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="bir-cell" style="text-align:center;color:#888;">No ATC entries for this period.</td>
        </tr>
        @endforelse
        @if($atcEntries->isNotEmpty())
        <tr style="background:#f5f5f5;font-weight:bold;">
            <td class="bir-cell" colspan="3" style="text-align:right;font-size:8px;">Total</td>
            <td class="bir-cell" style="text-align:right;font-family:monospace;font-size:8px;">{{ number_format($atcEntries->sum('tax_base'), 2) }}</td>
            <td class="bir-cell" style="text-align:right;font-family:monospace;font-size:8px;">{{ number_format($atcEntries->sum('tax_withheld'), 2) }}</td>
        </tr>
        @endif
    </table>

    {{-- Details of Payment --}}
    <div class="bir-section-header">Part IV – Details of Payment</div>
    <table class="bir-table" style="font-size:8px;">
        <tr>
            <th class="bir-cell" style="background:#f0f0f0;font-size:7px;font-weight:bold;">Particulars</th>
            <th class="bir-cell" style="background:#f0f0f0;font-size:7px;font-weight:bold;">Drawee Bank/Agency</th>
            <th class="bir-cell" style="background:#f0f0f0;font-size:7px;font-weight:bold;">Number</th>
            <th class="bir-cell" style="background:#f0f0f0;font-size:7px;font-weight:bold;">Date (MM/DD/YYYY)</th>
            <th class="bir-cell" style="background:#f0f0f0;font-size:7px;font-weight:bold;">Amount</th>
        </tr>
        <tr>
            <td class="bir-cell" style="font-size:8px;">Cash/Bank Debit Memo</td>
            <td class="bir-cell">&nbsp;</td><td class="bir-cell">&nbsp;</td>
            <td class="bir-cell">&nbsp;</td><td class="bir-cell">&nbsp;</td>
        </tr>
        <tr>
            <td class="bir-cell" style="font-size:8px;">Check</td>
            <td class="bir-cell">&nbsp;</td><td class="bir-cell">&nbsp;</td>
            <td class="bir-cell">&nbsp;</td><td class="bir-cell">&nbsp;</td>
        </tr>
        <tr>
            <td class="bir-cell" style="font-size:8px;">Tax Debit Memo</td>
            <td class="bir-cell">&nbsp;</td><td class="bir-cell">&nbsp;</td>
            <td class="bir-cell">&nbsp;</td><td class="bir-cell">&nbsp;</td>
        </tr>
    </table>

    <div class="bir-machine-val">
        <div class="bir-mv-left">Machine Validation/Revenue Official Receipt Details (if not filed with an Authorized Agent Bank)</div>
        <div class="bir-mv-right">Stamp of receiving Office/AAB and Date of Receipt<br>(RO's Signature/Bank Teller's Initial)</div>
    </div>

    <div style="font-size:7px;text-align:center;margin-top:6px;border-top:1px solid #000;padding-top:4px;">
        NOTE: Please read the BIR Data Privacy Policy found in the BIR website (www.bir.gov.ph)
    </div>
</div>

<style>
.bir-page {
    background: #fff;
    font-family: Arial, sans-serif;
    font-size: 9px;
    color: #000;
    width: 100%;
    max-width: 780px;
    margin: 0 auto 32px;
    border: 1px solid #ccc;
    padding: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
}
.bir-topbar { display:flex; align-items:stretch; border:1px solid #000; margin-bottom:0; }
.bir-foruse { display:flex; border-right:1px solid #000; }
.bir-foruse-label { font-size:7px; padding:2px 4px; border-right:1px solid #000; writing-mode:vertical-lr; text-align:center; }
.bir-foruse-box { font-size:7px; padding:2px 4px; }
.bir-formtitle { display:flex; align-items:center; flex:1; gap:8px; padding:4px 8px; }
.bir-republic { font-size:7px; text-align:center; line-height:1.4; }
.bir-formname { text-align:center; border-left:1px solid #ccc; border-right:1px solid #ccc; padding:0 8px; }
.bir-formsubtitle { flex:1; font-size:8px; text-align:center; }
.bir-formno-box { font-size:7px; padding:4px; border-left:1px solid #000; }

.bir-table { width:100%; border-collapse:collapse; }
.bir-cell { border:1px solid #000; padding:2px 4px; vertical-align:top; font-size:8px; }
.bir-label { font-size:7px; color:#333; margin-bottom:2px; }
.bir-value { font-size:9px; font-weight:bold; min-height:14px; }

.bir-section-header {
    background:#000; color:#fff; font-size:8px; font-weight:bold;
    padding:2px 6px; margin:0; border:1px solid #000; border-top:none;
}
.bir-item-num { width:5%; text-align:center; font-weight:bold; font-size:8px; }
.bir-item-label { width:75%; font-size:8px; }
.bir-indent { padding-left:20px !important; }
.bir-amount { width:20%; text-align:right; }
.bir-amount-box { border-bottom:1px solid #555; min-height:14px; text-align:right; font-family:monospace; font-size:9px; padding-right:2px; }
.bir-amount-total { font-weight:bold; border-bottom:2px double #000; }
.bir-row-bold td { background:#f5f5f5; font-weight:bold; }

.tin-boxes { display:flex; align-items:center; gap:2px; font-family:monospace; font-size:10px; font-weight:bold; }
.tin-part { border:1px solid #555; padding:1px 4px; min-width:36px; text-align:center; }
.tin-dash { font-weight:bold; }

.bir-sig-section { border:1px solid #000; border-top:none; padding:4px 6px; }
.bir-sig-text { font-size:7px; margin-bottom:6px; text-align:justify; }
.bir-sig-row { display:flex; gap:8px; }
.bir-sig-box { flex:1; }
.bir-sig-line { border-bottom:1px solid #000; height:28px; margin-bottom:2px; }
.bir-sig-line-sm { border-bottom:1px solid #000; height:16px; margin-bottom:2px; }
.bir-sig-label { font-size:7px; text-align:center; }
.bir-sig-subrow { display:flex; gap:4px; margin-top:4px; }
.bir-sig-subbox { flex:1; }

.bir-machine-val { display:flex; border:1px solid #000; border-top:none; min-height:40px; font-size:7px; }
.bir-mv-left { flex:2; border-right:1px solid #000; padding:4px; }
.bir-mv-right { flex:1; padding:4px; text-align:center; }

@media print {
    body * { visibility: hidden; }
    .bir-page, .bir-page * { visibility: visible; }
    .bir-page { position: static; width: 100%; margin: 0; border: none; box-shadow: none; padding: 8px; }
    .no-print { display: none !important; }
    @page { size: A4; margin: 10mm; }
}
</style>
@endsection

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
