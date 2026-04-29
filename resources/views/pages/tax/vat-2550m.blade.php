@extends('layouts.app')
@section('title', 'BIR 2550M - Monthly VAT')

@section('content')
@php
    $taxableMonth  = $taxableMonth ?? request('month', now()->format('Y-m'));
    $taxableSales  = $taxableSales ?? 0;
    $exemptSales   = $exemptSales ?? 0;
    $zeroRatedSales = $zeroRatedSales ?? 0;
    $outputVat     = $outputVat ?? ($taxableSales * 0.12);
    $inputVat      = $inputVat ?? 0;
    $vatPayable    = $vatPayable ?? ($outputVat - $inputVat);
    $revenueBreakdown = $revenueBreakdown ?? collect();

    $fmt = function($v) { return number_format(abs($v), 2); };

    // Parse month/year
    $parts = explode('-', $taxableMonth);
    $year  = isset($parts[0]) ? $parts[0] : date('Y');
    $month = isset($parts[1]) ? (int)$parts[1] : (int)date('m');
    $mo    = str_pad($month, 2, '0', STR_PAD_LEFT);
    $dueM  = $month == 12 ? 1 : $month + 1;
    $dueY  = $month == 12 ? $year + 1 : $year;
    $due   = date('m/d/Y', mktime(0, 0, 0, $dueM, 20, $dueY));

    $totalSales    = $taxableSales + $exemptSales + $zeroRatedSales;
    $surcharge     = 0;
    $interest      = 0;
    $compromise    = 0;
    $totalPenalty  = 0;
    $totalAmountDue = $vatPayable + $totalPenalty;
@endphp

<x-page-header title="BIR 2550M" subtitle="Monthly Value-Added Tax Declaration">
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
                <div style="font-size:28px;font-weight:bold;line-height:1;">2550M</div>
                <div style="font-size:8px;">January 2007 (ENCS)</div>
                <div style="font-size:8px;">Page 1</div>
            </div>
            <div class="bir-formsubtitle">
                <div style="font-size:11px;font-weight:bold;">Monthly Value-Added Tax Declaration</div>
                <div style="font-size:10px;font-weight:bold;">(BIR Form No. 2550M)</div>
                <div style="font-size:7px;margin-top:2px;">Enter all required information in CAPITAL LETTERS using BLACK ink. Mark all applicable boxes with an "X". Two copies MUST be filed with the BIR and one kept by the Taxpayer.</div>
            </div>
        </div>
        <div class="bir-formno-box">
            <div style="font-size:7px;text-align:right;">2550M 01/07ENCS P1</div>
        </div>
    </div>

    {{-- Row 1: Month / Amended / Tax Type --}}
    <table class="bir-table">
        <tr>
            <td class="bir-cell" style="width:25%">
                <div class="bir-label">1 For the Month (MM/YYYY)</div>
                <div class="bir-value">{{ $mo }}/{{ $year }}</div>
            </td>
            <td class="bir-cell" style="width:20%">
                <div class="bir-label">2 Amended Return?</div>
                <div class="bir-value">☐ Yes &nbsp; ☑ No</div>
            </td>
            <td class="bir-cell" style="width:25%">
                <div class="bir-label">3 Due Date (MM/DD/YYYY)</div>
                <div class="bir-value">{{ $due }}</div>
            </td>
            <td class="bir-cell" style="width:15%">
                <div class="bir-label">4 ATC</div>
                <div class="bir-value font-bold">VB</div>
            </td>
            <td class="bir-cell" style="width:15%">
                <div class="bir-label">5 Tax Type Code</div>
                <div class="bir-value font-bold">VAT</div>
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
                <div class="bir-label">8 Taxpayer's Name (Last Name, First Name, Middle Name for Individual OR Registered Name for Non-Individual)</div>
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
                <div class="bir-label">10 Contact Number</div>
                <div class="bir-value">&nbsp;</div>
            </td>
            <td class="bir-cell" style="width:20%">
                <div class="bir-label">9A ZIP Code</div>
                <div class="bir-value">&nbsp;</div>
            </td>
            <td class="bir-cell" style="width:40%">
                <div class="bir-label">11 Taxpayer Classification</div>
                <div class="bir-value">☑ Non-Individual &nbsp; ☐ Individual</div>
            </td>
        </tr>
        <tr>
            <td class="bir-cell" colspan="3">
                <div class="bir-label">12 Line of Business/Industry</div>
                <div class="bir-value">Educational Services</div>
            </td>
        </tr>
    </table>

    {{-- Part II: Computation of Tax --}}
    <div class="bir-section-header">Part II – Computation of Value-Added Tax</div>

    {{-- Sales Section --}}
    <div style="font-size:8px;font-weight:bold;padding:2px 6px;border:1px solid #000;border-top:none;background:#e8e8e8;">
        A. Sales / Receipts
    </div>
    <table class="bir-table">
        <tr>
            <td class="bir-cell bir-item-num">1</td>
            <td class="bir-cell bir-item-label">Taxable Sales / Receipts (Vatable Sales)</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box">{{ $fmt($taxableSales) }}</div></td>
        </tr>
        <tr>
            <td class="bir-cell bir-item-num">2</td>
            <td class="bir-cell bir-item-label">Exempt Sales / Receipts</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box">{{ $fmt($exemptSales) }}</div></td>
        </tr>
        <tr>
            <td class="bir-cell bir-item-num">3</td>
            <td class="bir-cell bir-item-label">Zero-Rated Sales / Receipts</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box">{{ $fmt($zeroRatedSales) }}</div></td>
        </tr>
        <tr class="bir-row-bold">
            <td class="bir-cell bir-item-num">4</td>
            <td class="bir-cell bir-item-label">Total Sales / Receipts (Sum of Items 1, 2 &amp; 3)</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box bir-amount-total">{{ $fmt($totalSales) }}</div></td>
        </tr>
    </table>

    {{-- Output Tax Section --}}
    <div style="font-size:8px;font-weight:bold;padding:2px 6px;border:1px solid #000;border-top:none;background:#e8e8e8;">
        B. Output Tax
    </div>
    <table class="bir-table">
        <tr>
            <td class="bir-cell bir-item-num">5</td>
            <td class="bir-cell bir-item-label">Output VAT (12% of Item 1)</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box">{{ $fmt($outputVat) }}</div></td>
        </tr>
    </table>

    {{-- Input Tax Section --}}
    <div style="font-size:8px;font-weight:bold;padding:2px 6px;border:1px solid #000;border-top:none;background:#e8e8e8;">
        C. Input Tax
    </div>
    <table class="bir-table">
        <tr>
            <td class="bir-cell bir-item-num">6</td>
            <td class="bir-cell bir-item-label">Total Input VAT Available (Creditable Input Tax)</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box">{{ $fmt($inputVat) }}</div></td>
        </tr>
    </table>

    {{-- VAT Payable Section --}}
    <div style="font-size:8px;font-weight:bold;padding:2px 6px;border:1px solid #000;border-top:none;background:#e8e8e8;">
        D. Net VAT Payable / (Creditable)
    </div>
    <table class="bir-table">
        <tr class="bir-row-bold">
            <td class="bir-cell bir-item-num">7</td>
            <td class="bir-cell bir-item-label">VAT Payable/(Creditable) (Item 5 Less Item 6)</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box bir-amount-total">{{ $fmt($vatPayable) }}</div></td>
        </tr>
        <tr>
            <td class="bir-cell bir-item-num"></td>
            <td class="bir-cell" style="font-size:8px;font-weight:bold;padding:2px 4px;">Add: Penalties</td>
            <td class="bir-cell bir-amount"></td>
        </tr>
        <tr>
            <td class="bir-cell bir-item-num">8A</td>
            <td class="bir-cell bir-item-label bir-indent">Surcharge</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box">{{ $fmt($surcharge) }}</div></td>
        </tr>
        <tr>
            <td class="bir-cell bir-item-num">8B</td>
            <td class="bir-cell bir-item-label bir-indent">Interest</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box">{{ $fmt($interest) }}</div></td>
        </tr>
        <tr>
            <td class="bir-cell bir-item-num">8C</td>
            <td class="bir-cell bir-item-label bir-indent">Compromise</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box">{{ $fmt($compromise) }}</div></td>
        </tr>
        <tr class="bir-row-bold">
            <td class="bir-cell bir-item-num">9</td>
            <td class="bir-cell bir-item-label">Total Amount Due (Item 7 Plus Items 8A, 8B &amp; 8C)</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box bir-amount-total">{{ $fmt($totalAmountDue) }}</div></td>
        </tr>
    </table>

    {{-- Signature Section --}}
    <div class="bir-sig-section">
        <div class="bir-sig-text">
            I/We declare under the penalties of perjury that this return has been made in good faith, verified by me/us, and to the best of my/our knowledge and belief, is true and correct, pursuant to the provisions of the National Internal Revenue Code, as amended, and the regulations issued under authority thereof.
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

    {{-- Details of Payment --}}
    <div class="bir-section-header">Part III – Details of Payment</div>
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
        <tr>
            <td class="bir-cell" style="font-size:8px;">Others</td>
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

{{-- Revenue Breakdown — screen only supporting schedule --}}
<div class="no-print mt-6">
    <div class="card">
        <div class="card-header">
            <h3 class="text-sm font-semibold text-secondary-900">Revenue Breakdown by Account</h3>
            <span class="text-xs text-secondary-400 ml-2">(Supporting detail — not printed with the official form)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Account Name</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($revenueBreakdown as $rev)
                    <tr>
                        <td>{{ $rev->account_name ?? '' }}</td>
                        <td class="text-right font-mono">₱{{ number_format($rev->amount ?? 0, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center text-secondary-400 py-4">No revenue data for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
