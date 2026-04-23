<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Check Voucher - {{ $payment->voucher_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { margin: 0.5in 0.6in; size: letter portrait; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #000; }

        /* School Header - compact */
        .school-header { text-align: center; margin-bottom: 8px; }
        .school-name { font-size: 13px; font-weight: bold; font-style: italic; }
        .school-address { font-size: 9px; color: #000; margin-top: 1px; }

        .voucher-title { font-size: 12px; font-weight: bold; text-align: center; margin: 6px 0 10px; letter-spacing: 1px; }

        /* Top info section - compact */
        .top-info { width: 100%; margin-bottom: 8px; }
        .top-info td { padding: 2px 0; font-size: 10px; vertical-align: bottom; }
        .top-info .value { border-bottom: 1px solid #000; padding: 0 4px 1px; }
        .top-info .right-col { text-align: right; padding-right: 4px; }

        /* Main accounts table */
        table.accounts { width: 100%; border-collapse: collapse; margin: 8px 0; }
        table.accounts th { padding: 3px 4px; font-size: 10px; font-weight: bold; text-align: center; border-bottom: 1px solid #000; border-top: 1px solid #000; }
        table.accounts td { padding: 2px 4px; font-size: 10px; vertical-align: top; }
        table.accounts .account-col { width: 56%; }
        table.accounts .debit-col, table.accounts .credit-col { width: 22%; text-align: right; font-family: "Courier New", monospace; }

        /* Received section - compact */
        .received-section { margin-top: 10px; border-top: 1px solid #000; padding-top: 6px; font-size: 10px; line-height: 1.6; }
        .received-section .amount-line { border-bottom: 1px solid #000; display: inline-block; min-width: 120px; padding: 0 4px; font-family: "Courier New", monospace; text-align: right; }

        /* Signature grid - boxed layout like sample */
        .signatures { width: 100%; margin-top: 15px; border-collapse: collapse; border: 1px solid #000; }
        .signatures td { padding: 22px 14px 4px; vertical-align: bottom; text-align: center; font-size: 9px; width: 25%; border-right: 1px solid #000; }
        .signatures td:last-child { border-right: none; }
        .signatures .date-received-row td { padding: 22px 14px 4px; border-top: 1px solid #000; }

        /* Text utilities */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
    </style>
</head>
<body>
    @php
        $d = $payment->disbursement;
        $schoolName = \App\Models\Setting::where('key', 'school_name')->value('value') ?? 'OrangeApps School Finance ERP';
        $schoolAddress = \App\Models\Setting::where('key', 'school_address')->value('value') ?? '';

        // Compute total debit and credit lines
        $totalDebit = $payment->gross_amount;
        $wht = (float) ($payment->withholding_tax ?? 0);
        $netAmount = $payment->net_amount;
    @endphp

    {{-- School Header --}}
    <div class="school-header">
        <div class="school-name">{{ $schoolName }}</div>
        @if($schoolAddress)
        <div class="school-address">{{ $schoolAddress }}</div>
        @endif
    </div>

    <div class="voucher-title">CHECK VOUCHER</div>

    {{-- Top Info Section --}}
    <table class="top-info">
        <tr>
            <td style="width: 13%;">PAY TO:</td>
            <td style="width: 57%;" class="value text-bold uppercase">{{ $d->payee_name ?? '' }}</td>
            <td style="width: 10%;" class="right-col">NO.</td>
            <td style="width: 20%;" class="value text-bold text-center">{{ $payment->voucher_number }}</td>
        </tr>
        <tr>
            <td>EXPLANATION:</td>
            <td class="value">{{ $d->description ?? '' }}</td>
            <td class="right-col">DATE:</td>
            <td class="value text-center">{{ \Carbon\Carbon::parse($payment->payment_date)->format('m/d/Y') }}</td>
        </tr>
    </table>

    {{-- Accounts Table --}}
    <table class="accounts">
        <thead>
            <tr>
                <th class="account-col">ACCOUNT TITLE</th>
                <th class="debit-col">DEBIT</th>
                <th class="credit-col">CREDIT</th>
            </tr>
        </thead>
        <tbody>
            {{-- Debit: Each expense account from line items --}}
            @foreach($d->items ?? [] as $item)
            <tr>
                <td class="account-col">
                    @if($item->account_code)
                        {{ $item->account_code }} - {{ $item->description ?? '' }}
                    @else
                        {{ $item->description ?? '' }}
                    @endif
                </td>
                <td class="debit-col">{{ number_format($item->amount, 2) }}</td>
                <td class="credit-col"></td>
            </tr>
            @endforeach

            {{-- Credit: WHT (if applicable) --}}
            @if($wht > 0)
            <tr>
                <td class="account-col">2502-00: WTAX Expanded</td>
                <td class="debit-col"></td>
                <td class="credit-col">{{ number_format($wht, 2) }}</td>
            </tr>
            @endif

            {{-- Credit: Cash/Bank --}}
            <tr>
                <td class="account-col">
                    @if($payment->bank_account)
                        1105-00: {{ $payment->bank_account }}
                    @else
                        1100-00: Cash in Bank
                    @endif
                </td>
                <td class="debit-col"></td>
                <td class="credit-col">{{ number_format($netAmount, 2) }}</td>
            </tr>

            {{-- Totals --}}
            <tr style="border-top: 1px solid #000;">
                <td class="account-col text-bold" style="padding-top: 6px;">TOTAL</td>
                <td class="debit-col text-bold" style="padding-top: 6px; border-top: 1px solid #000; border-bottom: 2px solid #000;">{{ number_format($totalDebit, 2) }}</td>
                <td class="credit-col text-bold" style="padding-top: 6px; border-top: 1px solid #000; border-bottom: 2px solid #000;">{{ number_format($totalDebit, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Received Section --}}
    <div class="received-section">
        <p style="margin-bottom: 8px;">
            RECEIVED FROM <strong>{{ strtoupper($schoolName) }}</strong> THE SUM OF PESOS
            <span class="amount-line">{{ number_format($netAmount, 2) }}</span>
            &nbsp;IN PAYMENT OF
        </p>
        <p>
            THE ABOVE DESCRIBED ACCOUNT AS PER CHECK NO.
            <span class="amount-line" style="min-width: 100px;">{{ $payment->check_number ?? $payment->reference_number ?? '' }}</span>
        </p>
    </div>

    {{-- Signatures --}}
    <table class="signatures">
        <tr>
            <td>Prepared by</td>
            <td>Checked by</td>
            <td>Approved by</td>
            <td>Signature over Printed Name</td>
        </tr>
        <tr class="date-received-row">
            <td colspan="3"></td>
            <td>Date Received</td>
        </tr>
    </table>
</body>
</html>
