@extends('layouts.print')
@section('title', 'OR #' . $collection->receipt_number)

@section('content')
<div style="max-width: 700px; margin: 0 auto; font-family: Arial, sans-serif;">

    {{-- Header --}}
    <div style="text-align: center; border-bottom: 3px solid #1e40af; padding-bottom: 15px; margin-bottom: 20px;">
        <h1 style="margin: 0; font-size: 22px; color: #1e293b;">{{ config('app.name', 'School Finance ERP') }}</h1>
        <p style="margin: 4px 0 0; font-size: 12px; color: #64748b;">Official Receipt</p>
    </div>

    {{-- Receipt Info --}}
    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
        <div>
            <table style="font-size: 13px;">
                <tr>
                    <td style="padding: 2px 10px 2px 0; color: #64748b;">Received From:</td>
                    <td style="font-weight: bold;">{{ $collection->customer->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 2px 10px 2px 0; color: #64748b;">Address:</td>
                    <td>{{ $collection->customer->billing_address ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 2px 10px 2px 0; color: #64748b;">TIN:</td>
                    <td>{{ $collection->customer->tin ?? '-' }}</td>
                </tr>
            </table>
        </div>
        <div style="text-align: right;">
            <table style="font-size: 13px;">
                <tr>
                    <td style="padding: 2px 10px 2px 0; color: #64748b;">OR Number:</td>
                    <td style="font-weight: bold; color: #1e40af;">{{ $collection->receipt_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 2px 10px 2px 0; color: #64748b;">Date:</td>
                    <td style="font-weight: bold;">{{ $collection->collection_date->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 2px 10px 2px 0; color: #64748b;">Payment Method:</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $collection->payment_method)) }}</td>
                </tr>
                @if($collection->check_number)
                <tr>
                    <td style="padding: 2px 10px 2px 0; color: #64748b;">Check No:</td>
                    <td>{{ $collection->check_number }}</td>
                </tr>
                @endif
                @if($collection->reference_number)
                <tr>
                    <td style="padding: 2px 10px 2px 0; color: #64748b;">Reference:</td>
                    <td>{{ $collection->reference_number }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Invoice Allocations --}}
    @if($collection->allocations && $collection->allocations->count() > 0)
    <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 20px;">
        <thead>
            <tr style="background: #f1f5f9;">
                <th style="text-align: left; padding: 8px; border: 1px solid #e2e8f0;">Invoice #</th>
                <th style="text-align: left; padding: 8px; border: 1px solid #e2e8f0;">Description</th>
                <th style="text-align: right; padding: 8px; border: 1px solid #e2e8f0;">Invoice Amount</th>
                <th style="text-align: right; padding: 8px; border: 1px solid #e2e8f0;">Amount Applied</th>
            </tr>
        </thead>
        <tbody>
            @foreach($collection->allocations as $alloc)
            <tr>
                <td style="padding: 8px; border: 1px solid #e2e8f0; font-weight: 600;">{{ $alloc->invoice->invoice_number ?? '-' }}</td>
                <td style="padding: 8px; border: 1px solid #e2e8f0;">{{ $alloc->invoice->description ?? '-' }}</td>
                <td style="text-align: right; padding: 8px; border: 1px solid #e2e8f0;">&#8369;{{ number_format($alloc->invoice->net_receivable ?? 0, 2) }}</td>
                <td style="text-align: right; padding: 8px; border: 1px solid #e2e8f0; font-weight: 600;">&#8369;{{ number_format($alloc->amount_applied ?? $alloc->amount ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Amount --}}
    <div style="display: flex; justify-content: flex-end; margin-bottom: 25px;">
        <table style="font-size: 14px; min-width: 280px;">
            <tr>
                <td style="padding: 5px 15px 5px 0; color: #64748b;">Amount Received:</td>
                <td style="text-align: right; font-weight: bold; font-size: 18px; color: #1e40af;">&#8369;{{ number_format($collection->amount_received, 2) }}</td>
            </tr>
            @if($collection->applied_amount > 0)
            <tr>
                <td style="padding: 5px 15px 5px 0; color: #64748b;">Applied:</td>
                <td style="text-align: right;">&#8369;{{ number_format($collection->applied_amount, 2) }}</td>
            </tr>
            @endif
            @if($collection->unapplied_amount > 0)
            <tr>
                <td style="padding: 5px 15px 5px 0; color: #64748b;">Unapplied:</td>
                <td style="text-align: right;">&#8369;{{ number_format($collection->unapplied_amount, 2) }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Amount in Words --}}
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 15px; margin-bottom: 25px; font-size: 13px;">
        <strong>Amount in Words:</strong>
        @php
            $amt = (float) $collection->amount_received;
            $pesos = floor($amt);
            $centavos = round(($amt - $pesos) * 100);
            $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
                     'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
                     'Seventeen', 'Eighteen', 'Nineteen'];
            $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

            $toWords = function($n) use (&$toWords, $ones, $tens) {
                if ($n == 0) return 'Zero';
                if ($n < 20) return $ones[$n];
                if ($n < 100) return $tens[intdiv($n, 10)] . ($n % 10 ? ' ' . $ones[$n % 10] : '');
                if ($n < 1000) return $ones[intdiv($n, 100)] . ' Hundred' . ($n % 100 ? ' ' . $toWords($n % 100) : '');
                if ($n < 1000000) return $toWords(intdiv($n, 1000)) . ' Thousand' . ($n % 1000 ? ' ' . $toWords($n % 1000) : '');
                if ($n < 1000000000) return $toWords(intdiv($n, 1000000)) . ' Million' . ($n % 1000000 ? ' ' . $toWords($n % 1000000) : '');
                return $toWords(intdiv($n, 1000000000)) . ' Billion' . ($n % 1000000000 ? ' ' . $toWords($n % 1000000000) : '');
            };

            $words = $toWords($pesos) . ' Pesos';
            if ($centavos > 0) $words .= ' and ' . $toWords($centavos) . ' Centavos';
            $words .= ' Only';
        @endphp
        {{ $words }}
    </div>

    @if($collection->remarks)
    <div style="font-size: 12px; margin-bottom: 25px;">
        <strong>Remarks:</strong> {{ $collection->remarks }}
    </div>
    @endif

    {{-- Signatures --}}
    <div style="display: flex; justify-content: space-between; margin-top: 50px; font-size: 12px;">
        <div style="text-align: center; width: 200px;">
            <div style="border-top: 1px solid #333; padding-top: 5px;">Received By</div>
        </div>
        <div style="text-align: center; width: 200px;">
            <div style="border-top: 1px solid #333; padding-top: 5px;">Approved By</div>
        </div>
        <div style="text-align: center; width: 200px;">
            <div style="border-top: 1px solid #333; padding-top: 5px;">Collected By</div>
        </div>
    </div>

    {{-- Footer --}}
    <div style="text-align: center; margin-top: 40px; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px;">
        {{ config('app.name') }} &mdash; Official Receipt &mdash; Printed on {{ now()->format('F d, Y h:i A') }}
    </div>
</div>

<script>window.onload = function() { window.print(); }</script>
@endsection
