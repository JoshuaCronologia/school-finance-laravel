<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Journal Voucher - {{ $entry->entry_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1a1a1a; padding: 30px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: bold; margin-bottom: 2px; text-transform: uppercase; }
        .header h2 { font-size: 14px; font-weight: normal; color: #555; border-bottom: 2px solid #333; padding-bottom: 8px; }
        .meta-table { width: 100%; margin-bottom: 15px; border: none; }
        .meta-table td { padding: 3px 8px; font-size: 10px; border: none; vertical-align: top; }
        .meta-label { font-weight: bold; color: #555; width: 120px; }
        .meta-value { color: #1a1a1a; }
        table.lines { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.lines th { background-color: #2c3e50; color: #fff; padding: 6px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px; }
        table.lines td { border: 1px solid #ddd; padding: 5px 8px; font-size: 10px; }
        table.lines tfoot td { background-color: #f0f4f8; font-weight: bold; border-top: 2px solid #333; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .description-box { margin-bottom: 15px; padding: 8px 10px; border: 1px solid #ddd; background: #f9fafb; font-size: 10px; }
        .description-box .label { font-weight: bold; color: #555; }
        .signature-section { margin-top: 40px; }
        .signature-section table { width: 100%; border: none; }
        .signature-section td { border: none; padding: 5px 15px; text-align: center; vertical-align: bottom; }
        .signature-line { border-top: 1px solid #333; margin-top: 30px; padding-top: 4px; font-size: 9px; color: #555; }
        .footer { margin-top: 20px; font-size: 8px; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 5px; }
        .status-badge { display: inline-block; padding: 2px 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; border-radius: 3px; }
        .status-posted { background: #d4edda; color: #155724; }
        .status-draft { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Journal Voucher</h1>
        <h2>{{ $entry->entry_number }}</h2>
    </div>

    {{-- Entry Metadata --}}
    <table class="meta-table">
        <tr>
            <td class="meta-label">JV Number:</td>
            <td class="meta-value">{{ $entry->entry_number }}</td>
            <td class="meta-label">Entry Date:</td>
            <td class="meta-value">{{ $entry->entry_date->format('F d, Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Journal Type:</td>
            <td class="meta-value">{{ ucfirst($entry->journal_type) }}</td>
            <td class="meta-label">Posting Date:</td>
            <td class="meta-value">{{ $entry->posting_date ? $entry->posting_date->format('F d, Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Reference #:</td>
            <td class="meta-value">{{ $entry->reference_number ?: '-' }}</td>
            <td class="meta-label">Status:</td>
            <td class="meta-value">
                <span class="status-badge {{ $entry->status === 'posted' ? 'status-posted' : 'status-draft' }}">
                    {{ strtoupper($entry->status) }}
                </span>
            </td>
        </tr>
        <tr>
            <td class="meta-label">Department:</td>
            <td class="meta-value">{{ $entry->department->name ?? '-' }}</td>
            <td class="meta-label">Campus:</td>
            <td class="meta-value">{{ $entry->campus->name ?? '-' }}</td>
        </tr>
    </table>

    {{-- Description --}}
    @if($entry->description)
    <div class="description-box">
        <span class="label">Description:</span> {{ $entry->description }}
    </div>
    @endif

    {{-- Journal Lines --}}
    <table class="lines">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 12%;">Account Code</th>
                <th style="width: 25%;">Account Name</th>
                <th style="width: 25%;">Description</th>
                <th style="width: 10%;">Department</th>
                <th class="text-right" style="width: 11.5%;">Debit</th>
                <th class="text-right" style="width: 11.5%;">Credit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entry->lines as $i => $line)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $line->account->account_code ?? '-' }}</td>
                <td>{{ $line->account->account_name ?? '-' }}</td>
                <td>{{ $line->description ?? '-' }}</td>
                <td>{{ $line->department->name ?? '-' }}</td>
                <td class="text-right">{{ $line->debit > 0 ? '₱' . number_format($line->debit, 2) : '-' }}</td>
                <td class="text-right">{{ $line->credit > 0 ? '₱' . number_format($line->credit, 2) : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right">TOTAL</td>
                <td class="text-right">{{ '₱' . number_format($totalDebit, 2) }}</td>
                <td class="text-right">{{ '₱' . number_format($totalCredit, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Signature Section --}}
    <div class="signature-section">
        <table>
            <tr>
                <td>
                    <div class="signature-line">Prepared by</div>
                </td>
                <td>
                    <div class="signature-line">Checked/Reviewed by</div>
                </td>
                <td>
                    <div class="signature-line">Approved by</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        School Finance ERP &mdash; Journal Voucher &mdash; Printed on {{ $printedAt }} by {{ $printedBy }}
    </div>
</body>
</html>
