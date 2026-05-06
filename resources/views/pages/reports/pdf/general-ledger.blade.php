<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>General Ledger</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #1a1a1a; }
        .header { text-align: center; margin-bottom: 14px; border-bottom: 2px solid #1a1a1a; padding-bottom: 8px; }
        .header h1 { font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .header .subtitle { font-size: 9px; color: #555; margin-top: 3px; }
        .account-block { margin-bottom: 14px; page-break-inside: avoid; }
        .account-header { background: #2c3e50; color: #fff; padding: 5px 7px; font-size: 9px; font-weight: bold; }
        .account-header .balance { float: right; }
        .account-meta { font-size: 8px; color: #ccc; }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: #f0f4f8; font-size: 8px; padding: 4px 6px; text-align: left; border-bottom: 1px solid #ccc; font-weight: bold; text-transform: uppercase; letter-spacing: 0.2px; }
        thead th.text-right { text-align: right; }
        tbody td { font-size: 8px; padding: 3px 6px; border-bottom: 1px solid #f0f0f0; }
        tbody tr.opening-row td { background: #eff6ff; color: #1d4ed8; font-weight: bold; }
        .text-right { text-align: right; }
        .font-mono { font-family: DejaVu Sans Mono, Courier, monospace; }
        tfoot td { font-weight: bold; font-size: 9px; padding: 4px 6px; border-top: 1px solid #aaa; background: #f0f4f8; }
        .footer { margin-top: 14px; font-size: 8px; color: #888; text-align: center; border-top: 1px solid #ccc; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>General Ledger Report</h1>
        <div class="subtitle">
            Period: {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}
        </div>
    </div>

    @foreach($accounts as $account)
    <div class="account-block">
        <div class="account-header">
            {{ $account->account_code }} — {{ $account->account_name }}
            <span class="balance">Balance: {{ number_format($account->ending_balance, 2) }}</span>
            <br>
            <span class="account-meta">{{ ucfirst($account->account_type) }} | Normal: {{ ucfirst($account->normal_balance) }}</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width:13%">Date</th>
                    <th style="width:38%">Description</th>
                    <th style="width:13%">Entry #</th>
                    <th style="width:12%">Ref</th>
                    <th class="text-right" style="width:12%">Debit</th>
                    <th class="text-right" style="width:12%">Credit</th>
                </tr>
            </thead>
            <tbody>
                <tr class="opening-row">
                    <td colspan="4">Opening Balance (as of {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }})</td>
                    <td class="text-right font-mono"></td>
                    <td class="text-right font-mono">{{ number_format($account->opening_balance, 2) }}</td>
                </tr>
                @foreach($account->transactions as $txn)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($txn->posting_date ?? $txn->entry_date)->format('m/d/Y') }}</td>
                    <td>{{ $txn->je_description ?? $txn->description ?? '' }}</td>
                    <td class="font-mono">{{ $txn->entry_number ?? '' }}</td>
                    <td class="font-mono">{{ $txn->reference_number ?? '' }}</td>
                    <td class="text-right font-mono">{{ ($txn->debit ?? 0) > 0 ? number_format($txn->debit, 2) : '' }}</td>
                    <td class="text-right font-mono">{{ ($txn->credit ?? 0) > 0 ? number_format($txn->credit, 2) : '' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right">Account Totals</td>
                    <td class="text-right font-mono">{{ number_format($account->total_debit, 2) }}</td>
                    <td class="text-right font-mono">{{ number_format($account->total_credit, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endforeach

    <div class="footer">Generated: {{ now()->format('F d, Y h:i A') }}</div>
</body>
</html>
