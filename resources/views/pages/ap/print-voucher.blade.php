<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Voucher - {{ $payment->voucher_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1a1a1a; padding: 30px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .header h2 { font-size: 14px; font-weight: normal; color: #555; border-bottom: 2px solid #333; padding-bottom: 8px; }
        .meta-table { width: 100%; margin-bottom: 15px; border: none; }
        .meta-table td { padding: 3px 8px; font-size: 10px; border: none; vertical-align: top; }
        .meta-label { font-weight: bold; color: #555; width: 130px; }
        .meta-value { color: #1a1a1a; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.items th { background-color: #2c3e50; color: #fff; padding: 6px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px; }
        table.items td { border: 1px solid #ddd; padding: 5px 8px; font-size: 10px; }
        table.items tfoot td { background-color: #f0f4f8; font-weight: bold; border-top: 2px solid #333; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-box { margin-bottom: 15px; float: right; width: 280px; }
        .summary-box table { width: 100%; border: 1px solid #ddd; }
        .summary-box td { padding: 4px 10px; font-size: 10px; border-bottom: 1px solid #eee; }
        .summary-box .total-row td { background-color: #f0f4f8; font-weight: bold; font-size: 11px; border-top: 2px solid #333; }
        .description-box { margin-bottom: 15px; padding: 8px 10px; border: 1px solid #ddd; background: #f9fafb; font-size: 10px; clear: both; }
        .description-box .label { font-weight: bold; color: #555; }
        .signature-section { margin-top: 40px; clear: both; }
        .signature-section table { width: 100%; border: none; }
        .signature-section td { border: none; padding: 5px 20px; text-align: center; vertical-align: bottom; width: 33.33%; }
        .signature-line { border-top: 1px solid #333; margin-top: 35px; padding-top: 4px; font-size: 9px; color: #555; }
        .signature-name { font-size: 10px; font-weight: bold; margin-top: 2px; }
        .footer { margin-top: 20px; font-size: 8px; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 5px; clear: both; }
        .status-badge { display: inline-block; padding: 2px 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; border-radius: 3px; background: #d4edda; color: #155724; }
        .clearfix::after { content: ""; display: table; clear: both; }
    </style>
</head>
<body>
    @php
        $d = $payment->disbursement;
    @endphp

    <div class="header">
        <h1>Disbursement Voucher</h1>
        <h2>{{ $payment->voucher_number }}</h2>
    </div>

    {{-- Voucher Metadata --}}
    <table class="meta-table">
        <tr>
            <td class="meta-label">Voucher Number:</td>
            <td class="meta-value">{{ $payment->voucher_number }}</td>
            <td class="meta-label">Payment Date:</td>
            <td class="meta-value">{{ \Carbon\Carbon::parse($payment->payment_date)->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Request Number:</td>
            <td class="meta-value">{{ $d->request_number ?? '-' }}</td>
            <td class="meta-label">Request Date:</td>
            <td class="meta-value">{{ $d->request_date ? $d->request_date->format('F d, Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Payee:</td>
            <td class="meta-value"><strong>{{ $d->payee_name ?? '-' }}</strong></td>
            <td class="meta-label">Payee Type:</td>
            <td class="meta-value">{{ ucfirst($d->payee_type ?? '-') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Department:</td>
            <td class="meta-value">{{ $d->department->name ?? '-' }}</td>
            <td class="meta-label">Category:</td>
            <td class="meta-value">{{ $d->category->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Payment Method:</td>
            <td class="meta-value">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? '-')) }}</td>
            <td class="meta-label">Check/Ref #:</td>
            <td class="meta-value">{{ $payment->check_number ?? $payment->reference_number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Bank Account:</td>
            <td class="meta-value">{{ $payment->bank_account ?? '-' }}</td>
            <td class="meta-label">Status:</td>
            <td class="meta-value"><span class="status-badge">{{ strtoupper($payment->status) }}</span></td>
        </tr>
    </table>

    {{-- Description --}}
    @if($d->description)
    <div class="description-box">
        <span class="label">Purpose/Description:</span> {{ $d->description }}
    </div>
    @endif

    {{-- Line Items --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">Description</th>
                <th class="text-right" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 15%;">Unit Cost</th>
                <th class="text-right" style="width: 15%;">Amount</th>
                <th style="width: 10%;">Account</th>
                <th style="width: 10%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($d->items ?? [] as $i => $item)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $item->description ?? '-' }}</td>
                <td class="text-right">{{ number_format($item->quantity, 0) }}</td>
                <td class="text-right">{{ '₱' . number_format($item->unit_cost, 2) }}</td>
                <td class="text-right">{{ '₱' . number_format($item->amount, 2) }}</td>
                <td>{{ $item->account_code ?? '-' }}</td>
                <td>{{ $item->remarks ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Amount Summary --}}
    <div class="summary-box">
        <table>
            <tr>
                <td>Gross Amount</td>
                <td class="text-right">{{ '₱' . number_format($payment->gross_amount, 2) }}</td>
            </tr>
            @if($payment->withholding_tax > 0)
            <tr>
                <td>Less: Withholding Tax</td>
                <td class="text-right" style="color: #c0392b;">({{ '₱' . number_format($payment->withholding_tax, 2) }})</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>Net Amount Paid</td>
                <td class="text-right">{{ '₱' . number_format($payment->net_amount, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="clearfix"></div>

    {{-- Signature Section with Prepared by / Approved by --}}
    <div class="signature-section">
        <table>
            <tr>
                <td>
                    @php
                        $preparedBy = $d->requested_by ?? null;
                    @endphp
                    @if($preparedBy)
                        <div class="signature-name">{{ $preparedBy }}</div>
                    @endif
                    <div class="signature-line">Prepared by</div>
                </td>
                <td>
                    <div class="signature-line">Checked/Reviewed by</div>
                </td>
                <td>
                    @php
                        $approvedBy = null;
                        if ($d->approvals && $d->approvals->count() > 0) {
                            $approval = $d->approvals->where('action', 'approved')->first();
                            $approvedBy = $approval->approver->name ?? $approval->approver_name ?? null;
                        }
                    @endphp
                    @if($approvedBy)
                        <div class="signature-name">{{ $approvedBy }}</div>
                    @endif
                    <div class="signature-line">Approved by</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Received by --}}
    <div class="signature-section" style="margin-top: 25px;">
        <table>
            <tr>
                <td>
                    <div class="signature-line">Received by (Payee Signature)</div>
                </td>
                <td>
                    <div class="signature-line">Date Received</div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        School Finance ERP &mdash; Disbursement Voucher &mdash; Printed on {{ $printedAt }} by {{ $printedBy }}
    </div>
</body>
</html>
