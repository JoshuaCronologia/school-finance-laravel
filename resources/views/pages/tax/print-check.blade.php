@extends('layouts.print')
@section('title', 'Check - ' . ($payment->check_number ?? $payment->voucher_number))

@section('content')
<div style="width: 8in; margin: 0 auto; font-family: 'Courier New', monospace;">
    {{-- Check Top Stub --}}
    <div style="border: 1px solid #ccc; padding: 20px; margin-bottom: 10px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
            <div>
                <strong style="font-size: 14px;">{{ config('app.name') }}</strong><br>
                <span style="font-size: 11px;">School Finance ERP</span>
            </div>
            <div style="text-align: right;">
                <strong>Check No:</strong> {{ $payment->check_number ?? 'N/A' }}<br>
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($payment->payment_date)->format('F d, Y') }}
            </div>
        </div>

        <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
            <tr>
                <td style="padding: 3px 0;"><strong>Pay to:</strong></td>
                <td>{{ $payment->disbursement->payee_name ?? '-' }}</td>
                <td style="text-align: right;"><strong>Amount:</strong></td>
                <td style="text-align: right; font-size: 14px; font-weight: bold;">&#8369;{{ number_format($payment->net_amount, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 3px 0;"><strong>Voucher #:</strong></td>
                <td>{{ $payment->voucher_number }}</td>
                <td style="text-align: right;"><strong>Gross:</strong></td>
                <td style="text-align: right;">&#8369;{{ number_format($payment->gross_amount, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 3px 0;"><strong>Department:</strong></td>
                <td>{{ $payment->disbursement->department->name ?? '-' }}</td>
                <td style="text-align: right;"><strong>WHT:</strong></td>
                <td style="text-align: right;">(&#8369;{{ number_format($payment->withholding_tax ?? 0, 2) }})</td>
            </tr>
            <tr>
                <td style="padding: 3px 0;"><strong>Description:</strong></td>
                <td colspan="3">{{ $payment->disbursement->description ?? '-' }}</td>
            </tr>
        </table>
    </div>

    {{-- Check Body (actual check portion) --}}
    <div style="border: 2px solid #333; padding: 25px; min-height: 200px; position: relative;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <div>
                <strong style="font-size: 16px;">{{ config('app.name') }}</strong><br>
                <span style="font-size: 11px; color: #666;">School Finance ERP</span>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 12px;">No. <strong>{{ $payment->check_number ?? '________' }}</strong></span><br>
                <span style="font-size: 12px;">Date: <strong>{{ \Carbon\Carbon::parse($payment->payment_date)->format('m/d/Y') }}</strong></span>
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <span style="font-size: 12px;">PAY TO THE ORDER OF: </span>
            <strong style="font-size: 14px; border-bottom: 1px solid #333; padding-bottom: 2px;">
                {{ $payment->disbursement->payee_name ?? '________________________________' }}
            </strong>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <div style="flex: 1; font-size: 12px; border-bottom: 1px solid #333; padding-bottom: 2px;">
                @php
                    $f = new NumberFormatter('en', NumberFormatter::SPELLOUT);
                    $amount = $payment->net_amount;
                    $pesos = floor($amount);
                    $centavos = round(($amount - $pesos) * 100);
                    $words = ucwords($f->format($pesos)) . ' Pesos';
                    if ($centavos > 0) $words .= ' and ' . ucwords($f->format($centavos)) . ' Centavos';
                    $words .= ' Only';
                @endphp
                {{ $words }}
            </div>
            <div style="margin-left: 20px; border: 2px solid #333; padding: 5px 15px; font-size: 16px; font-weight: bold;">
                &#8369;{{ number_format($payment->net_amount, 2) }}
            </div>
        </div>

        <div style="margin-top: 40px; display: flex; justify-content: space-between;">
            <div style="text-align: center; width: 200px;">
                <div style="border-top: 1px solid #333; padding-top: 5px; font-size: 10px;">Authorized Signature</div>
            </div>
            <div style="text-align: center; width: 200px;">
                <div style="border-top: 1px solid #333; padding-top: 5px; font-size: 10px;">Authorized Signature</div>
            </div>
        </div>
    </div>
</div>

<script>window.onload = function() { window.print(); }</script>
@endsection
