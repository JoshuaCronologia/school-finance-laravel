<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trial Balance</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1a1a1a; }
        .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #1a1a1a; padding-bottom: 10px; }
        .header h1 { font-size: 15px; font-weight: bold; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px; }
        .header .subtitle { font-size: 9px; color: #555; margin-top: 3px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; margin-top: 4px; }
        .badge-balanced { background: #d1fae5; color: #065f46; }
        .badge-unbalanced { background: #fee2e2; color: #991b1b; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead th { background: #2c3e50; color: #fff; font-size: 9px; padding: 5px 7px; text-align: left; text-transform: uppercase; letter-spacing: 0.3px; }
        thead th.text-right { text-align: right; }
        tbody td { font-size: 9px; padding: 4px 7px; border-bottom: 1px solid #e5e7eb; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        .text-right { text-align: right; }
        .font-mono { font-family: DejaVu Sans Mono, Courier, monospace; }
        tfoot td { font-weight: bold; font-size: 10px; padding: 6px 7px; border-top: 2px solid #1a1a1a; background: #f0f4f8; }
        .footer { margin-top: 16px; font-size: 8px; color: #888; text-align: center; border-top: 1px solid #ccc; padding-top: 8px; }
        .type-badge { font-size: 8px; padding: 1px 5px; border-radius: 8px; background: #e8edf5; color: #374151; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Trial Balance</h1>
        <div class="subtitle">As of {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}</div>
        <div>
            <span class="badge {{ $isBalanced ? 'badge-balanced' : 'badge-unbalanced' }}">
                {{ $isBalanced ? 'Balanced' : 'Unbalanced' }}
            </span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:15%">Account Code</th>
                <th style="width:45%">Account Name</th>
                <th style="width:12%">Type</th>
                <th class="text-right" style="width:14%">Debit</th>
                <th class="text-right" style="width:14%">Credit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accounts as $account)
            <tr>
                <td class="font-mono">{{ $account->account_code }}</td>
                <td>{{ $account->account_name }}</td>
                <td><span class="type-badge">{{ ucfirst($account->account_type) }}</span></td>
                <td class="text-right font-mono">{{ $account->total_debit > 0 ? number_format($account->total_debit, 2) : '' }}</td>
                <td class="text-right font-mono">{{ $account->total_credit > 0 ? number_format($account->total_credit, 2) : '' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">TOTALS</td>
                <td class="text-right font-mono">{{ number_format($totals['total_debit'], 2) }}</td>
                <td class="text-right font-mono">{{ number_format($totals['total_credit'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated: {{ now()->format('F d, Y h:i A') }}
        @if(!$isBalanced)
            &nbsp;|&nbsp; Difference: {{ number_format(abs($totals['difference']), 2) }}
        @endif
    </div>
</body>
</html>
