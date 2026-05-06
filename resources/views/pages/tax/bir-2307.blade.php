@extends('layouts.app')
@section('title', 'BIR Form 2307')

@section('content')
@php
    $vendors        = $vendors ?? collect();
    $selectedVendor = $selectedVendor ?? null;
    $quarter        = $quarter ?? request('quarter', 'Q1');
    $year           = $year ?? request('year', date('Y'));
    $formData       = $formData ?? null;
    $summary        = $summary ?? collect();
    $schoolTin      = $schoolTin ?? '000-000-000-000';
    $schoolName     = $schoolName ?? '';
    $schoolAddress  = $schoolAddress ?? '';
    $authRepName    = $authRepName ?? '';
    $authRepTin     = $authRepTin ?? '';
@endphp

<style>
.f2307 { font-family: Arial, sans-serif; font-size: 9px; color: #000; width: 100%; max-width: 780px; margin: 0 auto; border: 1px solid #000; }
.f2307 table { border-collapse: collapse; width: 100%; }
.f2307 td, .f2307 th { border: 1px solid #000; padding: 2px 4px; vertical-align: top; }
.f2307 .no-border td, .f2307 .no-border th { border: none; }
.f2307 .sh { background: #d0d0d0; font-weight: bold; font-size: 9px; text-align: center; padding: 2px 4px; }
.f2307 .field-label { font-size: 8px; color: #333; }
.f2307 .field-val { font-size: 10px; min-height: 14px; }
.f2307 .tin-box { display:inline-block; width:13px; height:15px; border:1px solid #000; text-align:center; font-size:10px; line-height:15px; font-family:monospace; }
.f2307 .tin-dash { display:inline-block; width:6px; text-align:center; font-size:10px; }
.f2307 .part3-desc { font-size:8px; }
.f2307 .amount-cell { text-align:right; font-family:monospace; font-size:9px; white-space:nowrap; }
.f2307 .total-row td { background:#e8e8e8; font-weight:bold; }
.f2307 .decl { font-size:7.5px; line-height:1.4; padding:4px; }
@media print {
    .no-print { display: none !important; }
    .f2307 { max-width: 100%; border: 1px solid #000; }
    body { background: white; }
}
.sig-input {
    border: none;
    border-bottom: 1px dashed #aaa;
    outline: none;
    font-size: 9px;
    font-weight: bold;
    font-family: Arial, sans-serif;
    text-align: center;
    width: 100%;
    background: transparent;
    padding: 1px 2px;
}
.sig-input-tin {
    border: none;
    border-bottom: 1px dashed #aaa;
    outline: none;
    font-size: 8px;
    font-family: Arial, sans-serif;
    text-align: center;
    width: 60%;
    background: transparent;
    padding: 1px 2px;
}
@media print {
    .sig-input, .sig-input-tin {
        border: none;
        border-bottom: 1px solid #000;
    }
}
</style>

{{-- Controls --}}
<div class="no-print">
<x-page-header title="BIR Form 2307" subtitle="Certificate of Creditable Tax Withheld at Source">
    <x-slot name="actions">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn-secondary text-sm">Export Excel</a>
        @if($selectedVendor)
        <button onclick="window.print()" class="btn-primary text-sm">Print 2307</button>
        @endif
    </x-slot>
</x-page-header>

<div class="card mb-6">
    <form class="card-body">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">Vendor / Payee</label>
                <select name="vendor_id" class="form-input w-64">
                    <option value="">Select Vendor</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Quarter</label>
                <select name="quarter" class="form-input w-28">
                    @foreach(['Q1','Q2','Q3','Q4'] as $q)
                        <option value="{{ $q }}" {{ $quarter == $q ? 'selected' : '' }}>{{ $q }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Year</label>
                <input type="number" name="year" value="{{ $year }}" class="form-input w-28" min="2020" max="2030">
            </div>
            <button type="submit" class="btn-primary">Generate</button>
            <a href="{{ request()->url() }}" class="btn-secondary">Clear</a>
        </div>
    </form>
</div>
</div>

@if($selectedVendor)
@php
    $q          = (int) str_replace('Q', '', $quarter);
    $startMonth = ($q - 1) * 3 + 1;
    $periodFrom = \Carbon\Carbon::create($year, $startMonth, 1)->format('m/d/Y');
    $periodTo   = \Carbon\Carbon::create($year, $startMonth + 2, 1)->endOfMonth()->format('m/d/Y');
    $mn = [
        1 => ['January','February','March'],
        2 => ['April','May','June'],
        3 => ['July','August','September'],
        4 => ['October','November','December'],
    ][$q] ?? ['Month 1','Month 2','Month 3'];
    $atcEntries = $formData->atcEntries ?? collect();
    $ewtPad     = max(0, 10 - $atcEntries->count());

    // TIN digit boxes helper
    $tinHtml = function($tin) {
        $tin = trim($tin ?? '');
        if (!$tin) {
            // render 12 empty boxes with dashes
            return '<span class="tin-box"></span><span class="tin-box"></span><span class="tin-box"></span>'
                 . '<span class="tin-dash">-</span>'
                 . '<span class="tin-box"></span><span class="tin-box"></span><span class="tin-box"></span>'
                 . '<span class="tin-dash">-</span>'
                 . '<span class="tin-box"></span><span class="tin-box"></span><span class="tin-box"></span>'
                 . '<span class="tin-dash">-</span>'
                 . '<span class="tin-box"></span><span class="tin-box"></span><span class="tin-box"></span>';
        }
        $parts = explode('-', $tin);
        $out = '';
        foreach ($parts as $pi => $part) {
            if ($pi > 0) $out .= '<span class="tin-dash">-</span>';
            foreach (str_split($part) as $ch) {
                $out .= '<span class="tin-box">' . htmlspecialchars($ch) . '</span>';
            }
        }
        return $out;
    };
@endphp

<div class="f2307" id="bir-2307-form">

    {{-- ===== TOP HEADER ===== --}}
    <table>
        <tr>
            <td style="width:15%;border-right:1px solid #000;vertical-align:top;padding:3px;">
                <div style="font-size:7px;">For BIR<br>Use Only</div>
                <div style="font-size:7px;margin-top:4px;">BCS/<br>Item:</div>
            </td>
            <td style="width:12%;border-right:1px solid #000;padding:3px;">
                <div style="font-size:10px;font-weight:bold;">BIR Form No.</div>
                <div style="font-size:22px;font-weight:bold;line-height:1;">2307</div>
                <div style="font-size:8px;">January 2018 (ENCS)</div>
            </td>
            <td style="text-align:center;padding:4px;">
                <div style="font-size:8px;">Republic of the Philippines</div>
                <div style="font-size:8px;">Department of Finance</div>
                <div style="font-size:9px;font-weight:bold;">BUREAU OF INTERNAL REVENUE</div>
                <div style="font-size:16px;font-weight:bold;margin:2px 0;">Certificate of Creditable Tax</div>
                <div style="font-size:16px;font-weight:bold;">Withheld at Source</div>
            </td>
            <td style="width:14%;border-left:1px solid #000;padding:3px;text-align:right;font-size:8px;vertical-align:bottom;">
                2307 01/18ENCS
            </td>
        </tr>
    </table>

    {{-- Instruction --}}
    <div style="border-top:1px solid #000;padding:2px 4px;font-size:8px;">
        Fill in all applicable spaces. Mark all appropriate boxes with an "X".
    </div>

    {{-- Line 1: For the Period --}}
    <table>
        <tr>
            <td style="width:20%;border:none;padding:2px 4px;">
                <span class="field-label">1 &nbsp;For the Period</span>
            </td>
            <td style="border:none;padding:2px 4px;">
                <span class="field-label">From</span>
                <span style="display:inline-block;border-bottom:1px solid #000;min-width:80px;font-size:9px;padding:0 4px;">{{ $periodFrom }}</span>
                &nbsp;&nbsp;
                <span class="field-label">To</span>
                <span style="display:inline-block;border-bottom:1px solid #000;min-width:80px;font-size:9px;padding:0 4px;">{{ $periodTo }}</span>
                &nbsp;&nbsp;<span class="field-label">(MM/DD/YYYY)</span>
            </td>
        </tr>
    </table>

    {{-- ===== PART I: PAYEE ===== --}}
    <div class="sh">Part I &ndash; Payee Information</div>
    <table>
        <tr>
            <td style="width:30%;">
                <div class="field-label">2 &nbsp;Taxpayer Identification Number <em>(TIN)</em></div>
                <div style="padding:2px 0;">{!! $tinHtml($selectedVendor->tin ?? '') !!}</div>
            </td>
            <td>
                <div class="field-label">3 &nbsp;Payee's Name <em>(Last Name, First Name, Middle Name for Individual OR Registered Name for Non-Individual)</em></div>
                <div class="field-val">{{ $selectedVendor->name }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding:0;">
                <table style="width:100%;">
                    <tr>
                        <td style="width:85%;border:none;border-right:1px solid #000;padding:2px 4px;">
                            <div class="field-label">4 &nbsp;Registered Address</div>
                            <div class="field-val">{{ $selectedVendor->address ?? '' }}</div>
                        </td>
                        <td style="border:none;padding:2px 4px;">
                            <div class="field-label">4A &nbsp;ZIP Code</div>
                            <div class="field-val">{{ $selectedVendor->zip_code ?? '' }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="field-label">5 &nbsp;Foreign Address, <em>if applicable</em></div>
                <div class="field-val">&nbsp;</div>
            </td>
        </tr>
    </table>

    {{-- ===== PART II: PAYOR ===== --}}
    <div class="sh">Part II &ndash; Payor Information</div>
    <table>
        <tr>
            <td style="width:30%;">
                <div class="field-label">6 &nbsp;Taxpayer Identification Number <em>(TIN)</em></div>
                <div style="padding:2px 0;">{!! $tinHtml($schoolTin) !!}</div>
            </td>
            <td>
                <div class="field-label">7 &nbsp;Payor's Name <em>(Last Name, First Name, Middle Name for Individual OR Registered Name for Non-Individual)</em></div>
                <div class="field-val">{{ $schoolName }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding:0;">
                <table style="width:100%;">
                    <tr>
                        <td style="width:85%;border:none;border-right:1px solid #000;padding:2px 4px;">
                            <div class="field-label">8 &nbsp;Registered Address</div>
                            <div class="field-val">{{ $schoolAddress }}</div>
                        </td>
                        <td style="border:none;padding:2px 4px;">
                            <div class="field-label">8A &nbsp;ZIP Code</div>
                            <div class="field-val">&nbsp;</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ===== PART III ===== --}}
    <div class="sh">Part III &ndash; Details of Monthly Income Payments and Taxes Withheld</div>
    <table>
        <thead>
            <tr style="background:#e8e8e8;">
                <th rowspan="2" style="width:28%;text-align:left;" class="part3-desc">Income Payments Subject to Expanded<br>Withholding Tax</th>
                <th rowspan="2" style="width:8%;text-align:center;font-size:8px;">ATC</th>
                <th colspan="4" style="text-align:center;font-size:8px;">AMOUNT OF INCOME PAYMENTS</th>
                <th rowspan="2" style="width:14%;text-align:center;font-size:8px;">Tax Withheld for the<br>Quarter</th>
            </tr>
            <tr style="background:#f0f0f0;">
                <th style="width:13%;text-align:center;font-size:8px;">1st Month of the<br>Quarter</th>
                <th style="width:13%;text-align:center;font-size:8px;">2nd Month of the<br>Quarter</th>
                <th style="width:13%;text-align:center;font-size:8px;">3rd Month of the<br>Quarter</th>
                <th style="width:11%;text-align:center;font-size:8px;">Total</th>
            </tr>
        </thead>
        <tbody>
            {{-- EWT data rows --}}
            @foreach($atcEntries as $entry)
            <tr>
                <td class="part3-desc">Income payments subject to EWT</td>
                <td style="text-align:center;font-weight:bold;font-family:monospace;">{{ $entry->atc }}</td>
                <td class="amount-cell">{{ $entry->m1 > 0 ? number_format($entry->m1, 2) : '' }}</td>
                <td class="amount-cell">{{ $entry->m2 > 0 ? number_format($entry->m2, 2) : '' }}</td>
                <td class="amount-cell">{{ $entry->m3 > 0 ? number_format($entry->m3, 2) : '' }}</td>
                <td class="amount-cell">{{ number_format($entry->total, 2) }}</td>
                <td class="amount-cell">{{ number_format($entry->tax, 2) }}</td>
            </tr>
            @endforeach
            {{-- Blank padding rows --}}
            @for($i = 0; $i < $ewtPad; $i++)
            <tr><td style="height:14px;"></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            @endfor
            {{-- EWT Total --}}
            <tr class="total-row">
                <td colspan="5" style="text-align:right;font-size:8px;font-weight:bold;">Total</td>
                <td class="amount-cell">{{ number_format($formData->total_income ?? 0, 2) }}</td>
                <td class="amount-cell">{{ number_format($formData->total_tax ?? 0, 2) }}</td>
            </tr>
            {{-- Business Tax section --}}
            <tr>
                <td colspan="7" style="font-size:8px;font-weight:bold;background:#f0f0f0;">
                    Money Payments Subject to Withholding of Business Tax (Government &amp; Private)
                </td>
            </tr>
            @for($i = 0; $i < 8; $i++)
            <tr><td style="height:14px;"></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            @endfor
            <tr class="total-row">
                <td colspan="5" style="text-align:right;font-size:8px;font-weight:bold;">Total</td>
                <td class="amount-cell"></td>
                <td class="amount-cell"></td>
            </tr>
        </tbody>
    </table>

    {{-- Declaration --}}
    <div class="decl">
        We declare under the penalties of perjury that this certificate has been made in good faith, verified by us, and to the best of our knowledge and belief, is true and correct, pursuant to the provisions of the National Internal Revenue Code, as amended, and the regulations issued under authority thereof. Further, we give our consent to the processing of our information as contemplated under the *Data Privacy Act of 2012 (R.A. No. 10173) for legitimate and lawful purposes.
    </div>

    {{-- Payor Signature --}}
    <table>
        <tr>
            <td style="width:50%;padding:4px;text-align:center;border-right:1px solid #000;">
                <div style="height:20px;"></div>
                <div style="margin-bottom:2px;">
                    <input type="text" class="sig-input" value="{{ $authRepName ?? '' }}" placeholder="Authorized Representative Name / Title">
                </div>
                <div style="margin-bottom:4px;">
                    <input type="text" class="sig-input-tin" value="{{ $authRepTin ?? '' }}" placeholder="TIN">
                </div>
                <div style="border-top:1px solid #000;font-size:8px;padding-top:2px;">
                    Signature over Printed Name of Payor/Payor's Authorized Representative/Tax Agent<br>
                    <em>(Indicate Title/Designation and TIN)</em>
                </div>
            </td>
            <td style="padding:0;">
                <table style="width:100%;border-collapse:collapse;">
                    <tr>
                        <td style="border:none;border-bottom:1px solid #000;padding:2px 4px;font-size:8px;width:50%;">
                            Tax Agent Accreditation No./<br>Attorney's Roll No. (if applicable)
                        </td>
                        <td style="border:none;border-bottom:1px solid #000;border-left:1px solid #000;padding:2px 4px;font-size:8px;">
                            Date of Issue<br><em>(MM/DD/YYYY)</em>
                        </td>
                        <td style="border:none;border-bottom:1px solid #000;border-left:1px solid #000;padding:2px 4px;font-size:8px;">
                            Date of Expiry<br><em>(MM/DD/YYYY)</em>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="border:none;padding:2px 4px;font-size:8px;height:20px;"></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- CONFORME --}}
    <div style="text-align:center;font-weight:bold;font-size:9px;border-top:1px solid #000;border-bottom:1px solid #000;padding:2px;">CONFORME:</div>

    {{-- Payee Signature --}}
    <table>
        <tr>
            <td style="width:50%;padding:4px;text-align:center;border-right:1px solid #000;">
                <div style="height:20px;"></div>
                <div style="margin-bottom:2px;">
                    <input type="text" class="sig-input" value="" placeholder="Payee / Authorized Representative Name / Title">
                </div>
                <div style="margin-bottom:4px;">
                    <input type="text" class="sig-input-tin" value="" placeholder="TIN">
                </div>
                <div style="border-top:1px solid #000;font-size:8px;padding-top:2px;">
                    Signature over Printed Name of Payee/Payee's Authorized Representative/Tax Agent<br>
                    <em>(Indicate Title/Designation and TIN)</em>
                </div>
            </td>
            <td style="padding:0;">
                <table style="width:100%;border-collapse:collapse;">
                    <tr>
                        <td style="border:none;border-bottom:1px solid #000;padding:2px 4px;font-size:8px;width:50%;">
                            Tax Agent Accreditation No./<br>Attorney's Roll No. (if applicable)
                        </td>
                        <td style="border:none;border-bottom:1px solid #000;border-left:1px solid #000;padding:2px 4px;font-size:8px;">
                            Date of Issue<br><em>(MM/DD/YYYY)</em>
                        </td>
                        <td style="border:none;border-bottom:1px solid #000;border-left:1px solid #000;padding:2px 4px;font-size:8px;">
                            Date of Expiry<br><em>(MM/DD/YYYY)</em>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="border:none;padding:2px 4px;font-size:8px;height:20px;"></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div style="font-size:7.5px;padding:2px 4px;border-top:1px solid #000;">
        *NOTE: The BIR Data Privacy is in the BIR website (www.bir.gov.ph)
    </div>

</div>
@endif

{{-- Vendor Summary --}}
<div class="card mt-6 no-print">
    <div class="card-header">
        <h3 class="text-sm font-semibold text-secondary-900">Vendor Tax Withholding Summary</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Vendor</th>
                    <th>TIN</th>
                    <th>ATC</th>
                    <th class="text-right">Total Income</th>
                    <th class="text-right">Total Tax Withheld</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($summary as $item)
                <tr>
                    <td class="font-medium">{{ $item->vendor->name ?? '' }}</td>
                    <td class="font-mono text-sm">{{ $item->vendor->tin ?? 'N/A' }}</td>
                    <td class="font-mono text-sm">{{ $item->atc ?? '' }}</td>
                    <td class="text-right font-mono">₱{{ number_format($item->income_payment ?? 0, 2) }}</td>
                    <td class="text-right font-mono">₱{{ number_format($item->tax_withheld ?? 0, 2) }}</td>
                    <td>
                        <a href="?vendor_id={{ $item->vendor->id ?? '' }}&quarter={{ $quarter }}&year={{ $year }}" class="text-primary-600 hover:text-primary-800 text-sm font-medium">View 2307</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-8 text-secondary-400">No withholding tax data found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
