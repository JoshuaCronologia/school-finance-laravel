<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Statement of Account - {{ $customer->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; margin: 30px; }
        h1 { font-size: 18px; margin: 0 0 5px; }
        .header { text-align: center; border-bottom: 3px solid #1e40af; padding-bottom: 15px; margin-bottom: 20px; }
        .header p { margin: 3px 0; color: #666; font-size: 11px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 3px 10px 3px 0; vertical-align: top; }
        .info-label { color: #888; font-size: 10px; text-transform: uppercase; }
        .info-value { font-weight: bold; font-size: 12px; }
        table.transactions { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.transactions th { background: #1e3a5f; color: #fff; padding: 8px 6px; text-align: left; font-size: 10px; text-transform: uppercase; }
        table.transactions td { padding: 6px; border-bottom: 1px solid #e5e7eb; }
        table.transactions tr:nth-child(even) { background: #f9fafb; }
        .text-right { text-align: right; }
        .text-danger { color: #dc2626; }
        .text-success { color: #16a34a; }
        .summary { margin-top: 15px; }
        .summary td { padding: 5px 10px; font-size: 12px; }
        .summary .total { font-size: 14px; font-weight: bold; border-top: 2px solid #333; }
        .footer { text-align: center; margin-top: 30px; font-size: 9px; color: #999; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name', 'School Finance ERP') }}</h1>
        <p>Statement of Account</p>
        <p>As of {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="50%">
                <span class="info-label">Customer</span><br>
                <span class="info-value">{{ $customer->name }}</span><br>
                <span style="font-size:10px; color:#666;">{{ $customer->customer_code }} &middot; {{ ucfirst($customer->customer_type ?? '') }}</span>
            </td>
            <td width="25%">
                <span class="info-label">Campus</span><br>
                <span class="info-value">{{ optional($customer->campus)->name ?? '-' }}</span>
            </td>
            <td width="25%">
                <span class="info-label">Contact</span><br>
                <span class="info-value">{{ $customer->email ?? '-' }}</span><br>
                <span style="font-size:10px;">{{ $customer->phone ?? '' }}</span>
            </td>
        </tr>
        @if($customer->billing_address)
        <tr>
            <td colspan="3">
                <span class="info-label">Billing Address</span><br>
                <span class="info-value">{{ $customer->billing_address }}</span>
            </td>
        </tr>
        @endif
    </table>

    <table class="transactions">
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Description</th>
                <th class="text-right">Charges</th>
                <th class="text-right">Payments</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $txn)
            <tr>
                <td>{{ \Carbon\Carbon::parse($txn->date)->format('M d, Y') }}</td>
                <td style="font-weight:600;">{{ $txn->reference }}</td>
                <td>{{ $txn->description }}</td>
                <td class="text-right">{{ $txn->debit > 0 ? '₱' . number_format($txn->debit, 2) : '' }}</td>
                <td class="text-right text-success">{{ $txn->credit > 0 ? '₱' . number_format($txn->credit, 2) : '' }}</td>
                <td class="text-right {{ $txn->balance > 0 ? 'text-danger' : '' }}" style="font-weight:600;">₱{{ number_format($txn->balance, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center; padding:20px; color:#999;">No transactions found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary" style="width:auto; margin-left:auto;">
        <tr>
            <td style="color:#888;">Total Charges:</td>
            <td class="text-right" style="font-weight:bold;">₱{{ number_format($totalInvoiced, 2) }}</td>
        </tr>
        <tr>
            <td style="color:#888;">Total Payments:</td>
            <td class="text-right text-success" style="font-weight:bold;">₱{{ number_format($totalCollected, 2) }}</td>
        </tr>
        <tr class="total">
            <td style="font-size:13px;">Outstanding Balance:</td>
            <td class="text-right {{ $balance > 0 ? 'text-danger' : 'text-success' }}" style="font-size:14px; font-weight:bold;">₱{{ number_format($balance, 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        {{ config('app.name') }} &mdash; Statement of Account &mdash; Generated on {{ now()->format('F d, Y h:i A') }}
    </div>
</body>
</html>
