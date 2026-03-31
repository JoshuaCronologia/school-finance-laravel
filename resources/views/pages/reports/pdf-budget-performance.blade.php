<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Budget Performance Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1a1a1a; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1a1a1a; padding-bottom: 10px; }
        .header h1 { font-size: 14px; margin-bottom: 2px; }
        .header h2 { font-size: 12px; font-weight: normal; margin-bottom: 2px; }
        .header h3 { font-size: 11px; margin-bottom: 2px; }
        .header .subtitle { font-size: 9px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ccc; padding: 5px 8px; }
        th { background-color: #2c3e50; color: #fff; font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px; }
        td { font-size: 9px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .total-row { background-color: #ecf0f1; font-weight: bold; }
        .negative { color: #c0392b; }
        .positive { color: #27ae60; }
        .footer { margin-top: 15px; font-size: 8px; color: #888; text-align: center; border-top: 1px solid #ccc; padding-top: 8px; }
        .summary-box { margin-bottom: 15px; }
        .summary-box table { border: none; margin: 0; }
        .summary-box td { border: none; padding: 3px 10px; font-size: 10px; }
        .summary-label { font-weight: bold; color: #555; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PERFORMANCE REPORT SY {{ $schoolYear }}</h1>
        <h2>ST. SCHOLASTICA'S COLLEGE, MANILA</h2>
        <h3>{{ $reportTitle }}</h3>
        <div class="subtitle">FOR THE PERIOD ENDED {{ strtoupper(\Carbon\Carbon::parse($asOfDate)->format('F d, Y')) }}</div>
    </div>

    {{-- Summary --}}
    <div class="summary-box">
        <table>
            <tr>
                <td class="summary-label">Approved Budget:</td>
                <td class="text-right">{{ '₱' . number_format($totals['approved_budget'], 2) }}</td>
                <td class="summary-label">Actual ({{ $periodLabel }}):</td>
                <td class="text-right">{{ '₱' . number_format($totals['actual'], 2) }}</td>
                <td class="summary-label">Variance:</td>
                <td class="text-right {{ $totals['variance'] < 0 ? 'negative' : 'positive' }}">
                    {{ $totals['variance'] < 0 ? '(' : '' }}₱{{ number_format(abs($totals['variance']), 2) }}{{ $totals['variance'] < 0 ? ')' : '' }}
                </td>
                <td class="summary-label">Utilization:</td>
                <td class="text-right">{{ $totals['approved_budget'] > 0 ? number_format(($totals['actual'] / $totals['approved_budget']) * 100, 1) : '0.0' }}%</td>
            </tr>
        </table>
    </div>

    {{-- Performance Table --}}
    <table>
        <thead>
            <tr>
                <th style="width: 30%;">Expense Category</th>
                <th class="text-right" style="width: 18%;">Approved Budget<br><span style="font-weight:normal;text-transform:none;">FY {{ $schoolYear }}</span></th>
                <th class="text-right" style="width: 18%;">Actual<br><span style="font-weight:normal;text-transform:none;">({{ $periodLabel }})</span></th>
                <th class="text-right" style="width: 18%;">Variance (B-A)</th>
                <th class="text-center" style="width: 10%;">Variance %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lineItems as $item)
            @php $isOver = $item['variance'] < 0; @endphp
            <tr>
                <td>{{ $item['category'] }}</td>
                <td class="text-right">{{ '₱' . number_format($item['approved_budget'], 2) }}</td>
                <td class="text-right">{{ '₱' . number_format($item['actual'], 2) }}</td>
                <td class="text-right {{ $isOver ? 'negative' : 'positive' }}">
                    {{ $isOver ? '(' : '' }}₱{{ number_format(abs($item['variance']), 2) }}{{ $isOver ? ')' : '' }}
                </td>
                <td class="text-center {{ $isOver ? 'negative' : 'positive' }}">
                    {{ $isOver ? '(' : '' }}{{ number_format(abs($item['variance_pct']), 1) }}%{{ $isOver ? ')' : '' }}
                </td>
            </tr>
            @endforeach

            <tr class="total-row">
                <td class="text-right">TOTAL</td>
                <td class="text-right">{{ '₱' . number_format($totals['approved_budget'], 2) }}</td>
                <td class="text-right">{{ '₱' . number_format($totals['actual'], 2) }}</td>
                <td class="text-right {{ $totals['variance'] < 0 ? 'negative' : 'positive' }}">
                    {{ $totals['variance'] < 0 ? '(' : '' }}₱{{ number_format(abs($totals['variance']), 2) }}{{ $totals['variance'] < 0 ? ')' : '' }}
                </td>
                <td class="text-center {{ $totals['variance'] < 0 ? 'negative' : 'positive' }}">
                    {{ $totals['variance'] < 0 ? '(' : '' }}{{ number_format(abs($totals['variance_pct']), 1) }}%{{ $totals['variance'] < 0 ? ')' : '' }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        School Finance ERP &mdash; Budget Performance Report &mdash; Generated: {{ now()->format('M d, Y h:i A') }}
    </div>
</body>
</html>
