<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Receipts Book</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #1a1a1a; }
        .header { text-align: center; margin-bottom: 14px; border-bottom: 2px solid #1a1a1a; padding-bottom: 8px; }
        .header h1 { font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .header .subtitle { font-size: 9px; color: #555; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: #2c3e50; color: #fff; font-size: 8px; padding: 4px 6px; text-align: left; text-transform: uppercase; }
        thead th.text-right { text-align: right; }
        tbody td { font-size: 8px; padding: 3px 6px; border-bottom: 1px solid #f0f0f0; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        .text-right { text-align: right; }
        .font-mono { font-family: DejaVu Sans Mono, Courier, monospace; }
        tfoot td { font-weight: bold; font-size: 9px; padding: 5px 6px; border-top: 2px solid #1a1a1a; background: #f0f4f8; }
        .footer { margin-top: 14px; font-size: 8px; color: #888; text-align: center; border-top: 1px solid #ccc; padding-top: 6px; }
        tr.new-or td { border-top: 1px solid #ccc; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $tab === 'cashier' ? 'Cashier Receipts' : 'Cash Receipts Book' }}</h1>
        <div class="subtitle">
            {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}
        </div>
    </div>

    @if($tab === 'gl')
    <table>
        <thead>
            <tr>
                <th style="width:13%">Date</th>
                <th style="width:14%">Entry #</th>
                <th style="width:13%">Ref No.</th>
                <th style="width:35%">Description</th>
                <th style="width:13%">Account</th>
                <th class="text-right" style="width:12%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
            <tr>
                <td>{{ \Carbon\Carbon::parse($entry->posting_date)->format('m/d/Y') }}</td>
                <td class="font-mono">{{ $entry->entry_number }}</td>
                <td class="font-mono">{{ $entry->reference_number ?? '' }}</td>
                <td>{{ $entry->je_description ?? $entry->description ?? '' }}</td>
                <td class="font-mono">{{ $entry->account_code }}</td>
                <td class="text-right font-mono">{{ number_format($entry->debit, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;padding:12px;color:#999;">No cash receipts for this period.</td></tr>
            @endforelse
        </tbody>
        @if($entries->count() > 0)
        <tfoot>
            <tr>
                <td colspan="5" class="text-right">Total Cash Receipts:</td>
                <td class="text-right font-mono">{{ number_format($totalAmount, 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
    @endif

    @if($tab === 'cashier')
    @php $currentOr = null; @endphp
    <table>
        <thead>
            <tr>
                <th style="width:13%">OR #</th>
                <th style="width:12%">Date</th>
                <th style="width:22%">Payor</th>
                <th style="width:20%">Remarks</th>
                <th style="width:18%">Account</th>
                <th class="text-right" style="width:8%">Amount</th>
                <th class="text-right" style="width:7%">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($finRecords as $r)
            @php
                $typeMap = [1 => $r->student_name ?? '—', 2 => $r->employee_name ?? '—', 3 => trim($r->walkin_name) ?: '—'];
                $payor   = isset($typeMap[$r->customer_type]) ? $typeMap[$r->customer_type] : '—';
                $isNew   = $r->receipt_number !== $currentOr;
                $currentOr = $r->receipt_number;
            @endphp
            <tr class="{{ $isNew ? 'new-or' : '' }}">
                <td class="font-mono">{{ $isNew ? $r->receipt_number : '' }}</td>
                <td>{{ $isNew ? \Carbon\Carbon::parse($r->date_paid)->format('m/d/Y') : '' }}</td>
                <td>{{ $isNew ? $payor : '' }}</td>
                <td>{{ $isNew ? ($r->remarks ?: '') : '' }}</td>
                <td>{{ $r->account }}</td>
                <td class="text-right font-mono">{{ number_format($r->amount, 2) }}</td>
                <td class="text-right font-mono">{{ $isNew ? number_format($r->batch_total, 2) : '' }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:12px;color:#999;">No records found.</td></tr>
            @endforelse
        </tbody>
        @if($finRecords->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="5" class="text-right">Total Receipts:</td>
                <td class="text-right font-mono">{{ number_format($finRecords->sum('amount'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>
    @endif

    <div class="footer">Generated: {{ now()->format('F d, Y h:i A') }}</div>
</body>
</html>
