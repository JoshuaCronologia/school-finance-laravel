<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>General Journal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #1a1a1a; }
        .header { text-align: center; margin-bottom: 14px; border-bottom: 2px solid #1a1a1a; padding-bottom: 8px; }
        .header h1 { font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .header .subtitle { font-size: 9px; color: #555; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: #2c3e50; color: #fff; font-size: 8px; padding: 4px 6px; text-align: left; text-transform: uppercase; letter-spacing: 0.2px; }
        thead th.text-right { text-align: right; }
        tbody td { font-size: 8px; padding: 3px 6px; border-bottom: 1px solid #f0f0f0; }
        tr.entry-header td { background: #e8edf5; font-weight: bold; font-size: 8px; border-top: 1px solid #ccc; }
        tr.debit-line td { }
        tr.credit-line td { color: #374151; }
        .text-right { text-align: right; }
        .font-mono { font-family: DejaVu Sans Mono, Courier, monospace; }
        .pl-4 { padding-left: 16px; }
        .pl-8 { padding-left: 32px; }
        .italic { font-style: italic; color: #555; }
        tfoot td { font-weight: bold; font-size: 9px; padding: 5px 6px; border-top: 2px solid #1a1a1a; background: #f0f4f8; }
        .footer { margin-top: 14px; font-size: 8px; color: #888; text-align: center; border-top: 1px solid #ccc; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>General Journal</h1>
        <div class="subtitle">
            {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:12%">Date</th>
                <th style="width:13%">Entry #</th>
                <th style="width:12%">Ref No.</th>
                <th style="width:39%">Account / Description</th>
                <th class="text-right" style="width:12%">Debit</th>
                <th class="text-right" style="width:12%">Credit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $je)
            <tr class="entry-header">
                <td>{{ $je->posting_date->format('m/d/Y') }}</td>
                <td class="font-mono">{{ $je->entry_number }}</td>
                <td class="font-mono">{{ $je->reference_number ?? '' }}</td>
                <td>{{ $je->description }}</td>
                <td></td>
                <td></td>
            </tr>
            @foreach($je->lines->where('debit', '>', 0) as $line)
            <tr class="debit-line">
                <td></td><td></td><td></td>
                <td class="pl-4">{{ $line->account->account_code ?? '' }} - {{ $line->account->account_name ?? '' }}</td>
                <td class="text-right font-mono">{{ number_format($line->debit, 2) }}</td>
                <td></td>
            </tr>
            @endforeach
            @foreach($je->lines->where('credit', '>', 0) as $line)
            <tr class="credit-line">
                <td></td><td></td><td></td>
                <td class="pl-8 italic">{{ $line->account->account_code ?? '' }} - {{ $line->account->account_name ?? '' }}</td>
                <td></td>
                <td class="text-right font-mono">{{ number_format($line->credit, 2) }}</td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
        @if($entries->count() > 0)
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">Grand Total:</td>
                <td class="text-right font-mono">{{ number_format($totalDebit, 2) }}</td>
                <td class="text-right font-mono">{{ number_format($totalCredit, 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">Generated: {{ now()->format('F d, Y h:i A') }}</div>
</body>
</html>
