@extends('layouts.app')
@section('title', 'BIR 1601-C')

@section('content')
<x-page-header title="BIR 1601-C" subtitle="Monthly Remittance Return of Income Taxes Withheld on Compensation">
    <x-slot name="actions">
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
                <label class="form-label">Month</label>
                <select name="month" class="form-input w-36">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="form-label">Year</label>
                <input type="number" name="year" class="form-input w-28" value="{{ $year }}">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
        </div>
    </form>
</div>

@php
    $fmt  = function($v) { return number_format(abs($v), 2); };
    $mths = ['','January','February','March','April','May','June','July','August','September','October','November','December'];
    $dueM = $month == 12 ? 1   : $month + 1;
    $dueY = $month == 12 ? $year+1 : $year;
    $due  = date('m/d/Y', mktime(0,0,0,$dueM,10,$dueY));
    $mo   = str_pad($month,2,'0',STR_PAD_LEFT);
@endphp

{{-- ===================== PAGE 1 ===================== --}}
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
                <div style="font-size:28px;font-weight:bold;line-height:1;">1601-C</div>
                <div style="font-size:8px;">January 2018 (ENCS)</div>
                <div style="font-size:8px;">Page 1</div>
            </div>
            <div class="bir-formsubtitle">
                <div style="font-size:11px;font-weight:bold;">Monthly Remittance Return</div>
                <div style="font-size:10px;font-weight:bold;">of Income Taxes Withheld on Compensation</div>
                <div style="font-size:7px;margin-top:2px;">Enter all required information in CAPITAL LETTERS using BLACK ink. Mark all applicable boxes with an "X". Two copies MUST be filed with the BIR and one kept by the Taxpayer.</div>
            </div>
        </div>
        <div class="bir-formno-box">
            <div style="font-size:7px;text-align:right;">1601-C 01/18ENCS P1</div>
        </div>
    </div>

    {{-- Row 1: Month / Amended / Any taxes / Sheets / ATC / Tax Type --}}
    <table class="bir-table">
        <tr>
            <td class="bir-cell" style="width:18%">
                <div class="bir-label">1 For the Month (MM/YYYY)</div>
                <div class="bir-value">{{ $mo }}/{{ $year }}</div>
            </td>
            <td class="bir-cell" style="width:18%">
                <div class="bir-label">2 Amended Return?</div>
                <div class="bir-value">☐ Yes &nbsp; ☑ No</div>
            </td>
            <td class="bir-cell" style="width:18%">
                <div class="bir-label">3 Any Taxes Withheld?</div>
                <div class="bir-value">☐ Yes &nbsp; ☐ No</div>
            </td>
            <td class="bir-cell" style="width:16%">
                <div class="bir-label">4 Number of Sheets Attached</div>
                <div class="bir-value">&nbsp;</div>
            </td>
            <td class="bir-cell" style="width:15%">
                <div class="bir-label">5 ATC</div>
                <div class="bir-value font-bold">WW010</div>
            </td>
            <td class="bir-cell" style="width:15%">
                <div class="bir-label">6 Tax Type Code</div>
                <div class="bir-value font-bold">WC</div>
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
                    @php $tinParts = explode('-', $schoolTin ?: '000-000-000-000'); @endphp
                    @foreach(array_pad($tinParts,4,'000') as $i => $tp)
                        <span class="tin-part">{{ $tp }}</span>@if($i<3)<span class="tin-dash">-</span>@endif
                    @endforeach
                </div>
            </td>
            <td class="bir-cell" style="width:40%">
                <div class="bir-label">7 RDO Code</div>
                <div class="bir-value">{{ $schoolRdo ?: '&nbsp;' }}</div>
            </td>
        </tr>
        <tr>
            <td class="bir-cell" colspan="2">
                <div class="bir-label">8 Withholding Agent's Name (Last Name, First Name, Middle Name for Individual OR Registered Name for Non-Individual)</div>
                <div class="bir-value" style="text-transform:uppercase;font-size:11px;">{{ $schoolName }}</div>
            </td>
        </tr>
        <tr>
            <td class="bir-cell" colspan="2">
                <div class="bir-label">9 Registered Address</div>
                <div class="bir-value" style="font-size:10px;">{{ $schoolAddress ?: '&nbsp;' }}</div>
            </td>
        </tr>
        <tr>
            <td class="bir-cell" style="width:30%">
                <div class="bir-label">10 Contact Number</div>
                <div class="bir-value">&nbsp;</div>
            </td>
            <td class="bir-cell" style="width:10%">
                <div class="bir-label">9A ZIP Code</div>
                <div class="bir-value">&nbsp;</div>
            </td>
            <td class="bir-cell" style="width:30%">
                <div class="bir-label">11 Category of Withholding Agent</div>
                <div class="bir-value">☑ Private &nbsp; ☐ Government</div>
            </td>
        </tr>
        <tr>
            <td class="bir-cell" colspan="3">
                <div class="bir-label">12 Email Address</div>
                <div class="bir-value">&nbsp;</div>
            </td>
        </tr>
        <tr>
            <td class="bir-cell" colspan="3">
                <div class="bir-label">13 Are there payees availing of tax relief under Special Law or International Tax Treaty?</div>
                <div class="bir-value">☐ Yes &nbsp; ☑ No &nbsp;&nbsp; <span class="bir-label">13A If yes, specify</span> ___________________________</div>
            </td>
        </tr>
    </table>

    {{-- Part II: Computation of Tax --}}
    <div class="bir-section-header">Part II – Computation of Tax</div>
    @php
        $compRows = [
            ['14', 'Total Amount of Compensation',                                                             $data['item14'], false, false],
            ['15', 'Less Non-Taxable/Exempt Compensation',                                                     null,            false, false],
            ['16', 'Statutory Minimum Wage for Minimum Wage Earners (MWEs)',                                   $data['item16'], false, true],
            ['17', 'Holiday Pay, Overtime Pay, Night Shift Differential Pay, Hazard Pay (for MWEs only)',      $data['item17'], false, true],
            ['18', '13th Month Pay and Other Benefits',                                                        $data['item18'], false, true],
            ['19', 'De Minimis Benefits',                                                                      $data['item19'], false, true],
            ['20', 'SSS, GSIS, PHIC, HDMF Mandatory Contributions & Union Dues (employee\'s share only)',      $data['item20'], false, true],
            ['21', 'Other Non-Taxable Compensation (specify)',                                                  $data['item21'], false, true],
            ['22', 'Total Non-Taxable Compensation (Sum of Items 15 to 20)',                                   $data['item22'], true,  false],
            ['23', 'Total Taxable Compensation (Item 14 Less Item 21)',                                        $data['item23'], true,  false],
            ['24', 'Less: Taxable compensation not subject to withholding tax (for employees, other than MWEs, earning P250,000 & below for the year)', $data['item24'], false, false],
            ['25', 'Total Taxes Withheld',                                                                     $data['item25'], true,  false],
            ['26', 'Add/(Less) Adjustment of Taxes Withheld from Previous Month(s) (From Part IV-Schedule 1, Item 4)', $data['item26'], false, false],
            ['27', 'Taxes Withheld for Remittance (Sum of Items 25 and 26)',                                   $data['item27'], true,  false],
            ['28', 'Less: Tax Remitted in Return Previously Filed, if this is an amended return',               $data['item28'], false, false],
            ['29', 'Other Remittances Made (specify)',                                                          $data['item29'], false, false],
            ['30', 'Total Tax Remittances Made (Sum of Items 28 and 29)',                                       $data['item30'], true,  false],
            ['31', 'Tax Still Due/(Over-remittance) (Item 27 Less Item 30)',                                   $data['item31'], true,  false],
        ];
    @endphp
    <table class="bir-table">
        @foreach($compRows as [$num,$label,$val,$bold,$indent])
        <tr class="{{ $bold ? 'bir-row-bold' : '' }}">
            <td class="bir-cell bir-item-num">{{ $num }}</td>
            <td class="bir-cell bir-item-label {{ $indent ? 'bir-indent' : '' }}">{{ $label }}</td>
            <td class="bir-cell bir-amount">
                @if($val !== null)
                    <div class="bir-amount-box">{{ $fmt($val) }}</div>
                @endif
            </td>
        </tr>
        @endforeach

        {{-- Add Penalties --}}
        <tr>
            <td class="bir-cell bir-item-num"></td>
            <td class="bir-cell" style="font-size:8px;font-weight:bold;padding:2px 4px;">Add: Penalties</td>
            <td class="bir-cell bir-amount"></td>
        </tr>
        @foreach([
            ['32','17A Surcharge',$data['item32'],false],
            ['33','17B Interest',$data['item33'],false],
            ['34','17C Compromise',$data['item34'],false],
            ['35','17D Total Penalties (Sum of Items 17A to 17C)',$data['item35'],true],
            ['36','18 Total Amount of Remittance (Sum of Items 16 and 17D)',$data['item36'],true],
        ] as [$n,$l,$v,$b])
        <tr class="{{ $b ? 'bir-row-bold' : '' }}">
            <td class="bir-cell bir-item-num">{{ $n }}</td>
            <td class="bir-cell bir-item-label">{{ $l }}</td>
            <td class="bir-cell bir-amount"><div class="bir-amount-box {{ $b ? 'bir-amount-total' : '' }}">{{ $fmt($v) }}</div></td>
        </tr>
        @endforeach
    </table>

    {{-- Signature Section --}}
    <div class="bir-sig-section">
        <div class="bir-sig-text">
            We declare under the penalties of perjury that this remittance form has been made in good faith, verified by me/us, and to the best of my/our knowledge and belief, is true and correct, pursuant to the provisions of the National Internal Revenue Code, as amended, and the regulations issued under authority thereof.
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

    {{-- Part III: Details of Payment --}}
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
            <td class="bir-cell" style="font-size:8px;">36 Cash/Bank Debit Memo</td>
            <td class="bir-cell">&nbsp;</td><td class="bir-cell">&nbsp;</td>
            <td class="bir-cell">&nbsp;</td><td class="bir-cell">&nbsp;</td>
        </tr>
        <tr>
            <td class="bir-cell" style="font-size:8px;">37 Check</td>
            <td class="bir-cell">&nbsp;</td><td class="bir-cell">&nbsp;</td>
            <td class="bir-cell">&nbsp;</td><td class="bir-cell">&nbsp;</td>
        </tr>
        <tr>
            <td class="bir-cell" style="font-size:8px;">38 Tax Debit Memo</td>
            <td class="bir-cell">&nbsp;</td><td class="bir-cell">&nbsp;</td>
            <td class="bir-cell">&nbsp;</td><td class="bir-cell">&nbsp;</td>
        </tr>
        <tr>
            <td class="bir-cell" style="font-size:8px;">39 Others (specify below)</td>
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

{{-- ===================== PAGE 2 ===================== --}}
<div class="bir-page" id="page2">

    {{-- Page 2 header --}}
    <div class="bir-page2-header">
        <table style="width:100%;border-collapse:collapse;font-size:8px;">
            <tr>
                <td style="border:1px solid #000;padding:2px 4px;width:20%;">
                    <div style="font-size:7px;">BIR Form No.</div>
                    <div style="font-size:14px;font-weight:bold;">1601-C</div>
                    <div style="font-size:7px;">January 2018 (ENCS)<br>Page 2</div>
                </td>
                <td style="border:1px solid #000;padding:2px 4px;text-align:center;width:60%;">
                    <div style="font-weight:bold;font-size:10px;">Monthly Remittance Return</div>
                    <div style="font-weight:bold;font-size:9px;">of Income Taxes Withheld on Compensation</div>
                </td>
                <td style="border:1px solid #000;padding:2px 4px;font-size:7px;width:20%;">
                    <div>TIN</div>
                    <div style="font-size:9px;font-weight:bold;font-family:monospace;">{{ $schoolTin ?: '000-000-000-000' }}</div>
                    <div style="margin-top:4px;">Withholding Agent's Name</div>
                    <div style="font-size:8px;font-weight:bold;text-transform:uppercase;">{{ Str::limit($schoolName, 30) }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Part IV - Schedule I --}}
    <div class="bir-section-header">Part IV – Schedule</div>
    <div style="font-size:8px;font-weight:bold;padding:3px 4px;border:1px solid #000;border-top:none;">
        Schedule I – Adjustment of Taxes Withheld on Compensation from Previous Months (attach additional sheets, if necessary)
    </div>
    <table class="bir-table" style="font-size:8px;">
        <tr style="background:#f0f0f0;">
            <th class="bir-cell" style="width:8%;font-size:7px;"></th>
            <th class="bir-cell" style="width:20%;font-size:7px;">Previous Month's (MM/YYYY)<br><span style="font-weight:normal;">1</span></th>
            <th class="bir-cell" style="width:20%;font-size:7px;">Date Paid (MM/DD/YYYY)<br><span style="font-weight:normal;">2</span></th>
            <th class="bir-cell" style="width:22%;font-size:7px;">Drawee Bank/Agency Code<br><span style="font-weight:normal;">3</span></th>
            <th class="bir-cell" style="width:15%;font-size:7px;">Number<br><span style="font-weight:normal;">4</span></th>
            <th class="bir-cell" style="width:15%;font-size:7px;text-align:right;">Tax Paid (Excluding Penalties for the Month)<br><span style="font-weight:normal;">5</span></th>
        </tr>
        @for($i = 1; $i <= 3; $i++)
        <tr>
            <td class="bir-cell" style="text-align:center;">{{ $i }}</td>
            <td class="bir-cell">&nbsp;</td>
            <td class="bir-cell">&nbsp;</td>
            <td class="bir-cell">&nbsp;</td>
            <td class="bir-cell">&nbsp;</td>
            <td class="bir-cell" style="text-align:right;">&nbsp;</td>
        </tr>
        @endfor
        <tr style="background:#f0f0f0;">
            <td class="bir-cell" colspan="3" style="font-size:8px;font-weight:bold;text-align:right;">Should be Tax Due for the Month</td>
            <td class="bir-cell" colspan="2" style="text-align:center;font-size:8px;">Adjustments<br>7 = (6 less 5)</td>
            <td class="bir-cell" style="text-align:right;">&nbsp;</td>
        </tr>
        @for($i = 1; $i <= 3; $i++)
        <tr>
            <td class="bir-cell" style="text-align:center;">{{ $i }}</td>
            <td class="bir-cell">&nbsp;</td>
            <td class="bir-cell">&nbsp;</td>
            <td class="bir-cell">&nbsp;</td>
            <td class="bir-cell">&nbsp;</td>
            <td class="bir-cell" style="text-align:right;">&nbsp;</td>
        </tr>
        @endfor
        <tr style="background:#e8e8e8;font-weight:bold;">
            <td class="bir-cell" style="font-size:8px;">4</td>
            <td class="bir-cell" colspan="4" style="font-size:8px;">Total Adjustment (Sum of Items 1 to 3) (To Part II, Item 26)</td>
            <td class="bir-cell" style="text-align:right;font-family:monospace;">{{ $fmt($data['item26']) }}</td>
        </tr>
    </table>

    {{-- Guidelines --}}
    <div style="margin-top:12px;font-size:7.5px;line-height:1.4;columns:2;column-gap:16px;border-top:2px solid #000;padding-top:6px;">
        <div style="text-align:center;font-weight:bold;font-size:8px;column-span:all;margin-bottom:4px;">
            Guidelines and Instructions for BIR Form No. 1601-C [January 2018 (ENCS)]<br>
            Monthly Remittance Return of Income Taxes Withheld on Compensation
        </div>
        <p><strong>Who Shall File</strong><br>
        This monthly remittance return shall be filed by every withholding agent (W/A) who is required to deduct and withhold taxes on compensation paid to employees.</p>
        <p><strong>When and Where to File and Pay</strong><br>
        The return shall be filed and the tax paid on or before the tenth (10th) day of the month following the month in which withholding was made. If the tenth day falls on a Saturday, Sunday, or holiday, the deadline shall be the next working day.</p>
        <p><strong>Penalties</strong><br>
        A surcharge of twenty-five percent (25%) for the following violations: (a) Failure to file any return and pay the amount of tax; (b) Filing a return with a person or office other than those with whom it is required to be filed; (c) Failure to pay the full or part of the amount shown on the return.</p>
        <p><strong>Required Attachments</strong><br>
        For salary periods of June, September and December, a copy of the list submitted to the DOLE Regional/Provincial Offices-Compliance Monitoring Unit.</p>
    </div>

</div>

{{-- Pending note (screen only) --}}
<div class="no-print mt-4 bg-amber-50 border border-amber-200 rounded p-3 text-xs text-amber-800">
    <strong>Pending payroll integration:</strong> Items 14–25 (compensation & withholding amounts) are ₱0.00 until the payroll module is connected.
</div>

<style>
/* ===== BIR Form Styles ===== */
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

.bir-page2-header { margin-bottom:0; }

/* ===== Print ===== */
@media print {
    body * { visibility: hidden; }
    .bir-page, .bir-page * { visibility: visible; }
    .bir-page { position: static; width: 100%; margin: 0; border: none; box-shadow: none; padding: 8px; page-break-after: always; }
    #page2 { page-break-before: always; page-break-after: auto; }
    .no-print { display: none !important; }
    @page { size: A4; margin: 10mm; }
}
</style>
@endsection
