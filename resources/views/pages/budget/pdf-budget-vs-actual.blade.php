<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Budget vs Actual Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1a1a1a; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1a1a1a; padding-bottom: 10px; }
        .header h1 { font-size: 16px; margin-bottom: 2px; }
        .header h2 { font-size: 13px; font-weight: normal; margin-bottom: 2px; }
        .header .subtitle { font-size: 10px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ccc; padding: 5px 6px; }
        th { background-color: #2c3e50; color: #fff; font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px; }
        td { font-size: 9px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .total-row { background-color: #ecf0f1; font-weight: bold; }
        .dept-header { background-color: #f0f4f8; font-weight: bold; font-size: 10px; }
        .negative { color: #c0392b; }
        .positive { color: #27ae60; }
        .footer { margin-top: 15px; font-size: 8px; color: #888; text-align: center; border-top: 1px solid #ccc; padding-top: 8px; }
        .summary-box { margin-bottom: 15px; padding: 10px; border: 1px solid #ccc; background: #f9fafb; }
        .summary-box table { border: none; margin: 0; }
        .summary-box td { border: none; padding: 2px 8px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Budget vs Actual Report</h1>
        @if($departmentName)
            <h2>{{ $departmentName }}</h2>
        @else
            <h2>All Departments</h2>
        @endif
        <div class="subtitle">School Year: {{ $schoolYear }} &nbsp;|&nbsp; Generated: {{ $generatedAt }}</div>
    </div>

    {{-- Summary Box --}}
    <div class="summary-box">
        <table>
            <tr>
                <td class="font-bold">Total Budget:</td>
                <td class="text-right">{{ '₱' . number_format($summary['total_budget'], 2) }}</td>
                <td class="font-bold">Total Actual:</td>
                <td class="text-right">{{ '₱' . number_format($summary['total_actual'], 2) }}</td>
                <td class="font-bold">Total Variance:</td>
                <td class="text-right {{ $summary['total_variance'] < 0 ? 'negative' : 'positive' }}">
                    {{ '₱' . number_format($summary['total_variance'], 2) }}
                </td>
            </tr>
            <tr>
                <td class="font-bold">Total Committed:</td>
                <td class="text-right">{{ '₱' . number_format($summary['total_committed'], 2) }}</td>
                <td class="font-bold">Total Remaining:</td>
                <td class="text-right">{{ '₱' . number_format($summary['total_remaining'], 2) }}</td>
                <td class="font-bold">Overall Utilization:</td>
                <td class="text-right">{{ number_format($summary['overall_utilization'], 1) }}%</td>
            </tr>
        </table>
    </div>

    {{-- Detail Table --}}
    <table>
        <thead>
            <tr>
                <th style="width: 18%;">Budget Name</th>
                <th style="width: 12%;">Department</th>
                <th style="width: 10%;">Category</th>
                <th class="text-right" style="width: 12%;">Annual Budget</th>
                <th class="text-right" style="width: 10%;">Committed</th>
                <th class="text-right" style="width: 10%;">Actual</th>
                <th class="text-right" style="width: 10%;">Remaining</th>
                <th class="text-right" style="width: 10%;">Variance</th>
                <th class="text-center" style="width: 8%;">Variance %</th>
            </tr>
        </thead>
        <tbody>
            @php $currentDept = null; @endphp
            @foreach($budgets as $budget)
                @if($budget->department_name !== $currentDept)
                    @php $currentDept = $budget->department_name; @endphp
                    @if(!$departmentName)
                    <tr class="dept-header">
                        <td colspan="9">{{ $currentDept ?: 'Unassigned' }}</td>
                    </tr>
                    @endif
                @endif
                <tr>
                    <td>{{ $budget->budget_name }}</td>
                    <td>{{ $budget->department_name ?: '-' }}</td>
                    <td>{{ $budget->category_name ?: '-' }}</td>
                    <td class="text-right">{{ '₱' . number_format($budget->annual_budget, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($budget->committed, 2) }}</td>
                    <td class="text-right">{{ '₱' . number_format($budget->actual, 2) }}</td>
                    <td class="text-right {{ $budget->remaining < 0 ? 'negative' : '' }}">
                        {{ '₱' . number_format($budget->remaining, 2) }}
                    </td>
                    <td class="text-right {{ $budget->variance < 0 ? 'negative' : 'positive' }}">
                        {{ '₱' . number_format($budget->variance, 2) }}
                    </td>
                    <td class="text-center {{ $budget->variance_pct < 0 ? 'negative' : '' }}">
                        {{ number_format($budget->variance_pct, 1) }}%
                    </td>
                </tr>
            @endforeach

            {{-- Grand Total Row --}}
            <tr class="total-row">
                <td colspan="3" class="text-right">GRAND TOTAL</td>
                <td class="text-right">{{ '₱' . number_format($summary['total_budget'], 2) }}</td>
                <td class="text-right">{{ '₱' . number_format($summary['total_committed'], 2) }}</td>
                <td class="text-right">{{ '₱' . number_format($summary['total_actual'], 2) }}</td>
                <td class="text-right">{{ '₱' . number_format($summary['total_remaining'], 2) }}</td>
                <td class="text-right {{ $summary['total_variance'] < 0 ? 'negative' : 'positive' }}">
                    {{ '₱' . number_format($summary['total_variance'], 2) }}
                </td>
                <td class="text-center">
                    {{ $summary['total_budget'] > 0 ? number_format((($summary['total_budget'] - $summary['total_actual']) / $summary['total_budget']) * 100, 1) : '0.0' }}%
                </td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        School Finance ERP &mdash; Budget vs Actual Report &mdash; Page 1 &mdash; Printed on {{ $generatedAt }}
    </div>
</body>
</html>
