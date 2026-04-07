<?php

namespace App\Exports;

use App\Models\ApBill;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class APAgingExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithEvents
{
    protected $asOfDate;

    public function __construct(string $asOfDate)
    {
        $this->asOfDate = $asOfDate;
    }

    public function collection(): Collection
    {
        $asOf = Carbon::parse($this->asOfDate);

        $bills = ApBill::with('vendor')
            ->where('status', '!=', 'paid')
            ->where('balance', '>', 0)
            ->get();

        $vendorBuckets = [];

        foreach ($bills as $bill) {
            $vendorName = $billoptional($bill->vendor)->name ?? 'Unknown';
            $vendorCode = $billoptional($bill->vendor)->vendor_code ?? '-';
            $key = $vendorCode . '|' . $vendorName;

            if (!isset($vendorBuckets[$key])) {
                $vendorBuckets[$key] = [
                    'current' => 0,
                    '1_30' => 0,
                    '31_60' => 0,
                    '61_90' => 0,
                    'over_90' => 0,
                    'total' => 0,
                ];
            }

            $daysOverdue = $asOf->diffInDays($bill->due_date, false) * -1;
            $balance = (float) $bill->balance;

            if ($daysOverdue <= 0) {
                $vendorBuckets[$key]['current'] += $balance;
            } elseif ($daysOverdue <= 30) {
                $vendorBuckets[$key]['1_30'] += $balance;
            } elseif ($daysOverdue <= 60) {
                $vendorBuckets[$key]['31_60'] += $balance;
            } elseif ($daysOverdue <= 90) {
                $vendorBuckets[$key]['61_90'] += $balance;
            } else {
                $vendorBuckets[$key]['over_90'] += $balance;
            }

            $vendorBuckets[$key]['total'] += $balance;
        }

        $rows = new Collection();

        foreach ($vendorBuckets as $key => $buckets) {
            [$code, $name] = explode('|', $key, 2);
            $rows->push([
                $code,
                $name,
                $buckets['current'],
                $buckets['1_30'],
                $buckets['31_60'],
                $buckets['61_90'],
                $buckets['over_90'],
                $buckets['total'],
            ]);
        }

        // Sort by vendor name
        $rows = $rows->sortBy(1)->values();

        // Add totals row
        $rows->push([
            '',
            'TOTALS',
            $rows->sum(2),
            $rows->sum(3),
            $rows->sum(4),
            $rows->sum(5),
            $rows->sum(6),
            $rows->sum(7),
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Vendor Code',
            'Vendor Name',
            'Current',
            '1-30 Days',
            '31-60 Days',
            '61-90 Days',
            'Over 90 Days',
            'Total',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE8EDF5'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("C2:H{$lastRow}")->getNumberFormat()
                    ->setFormatCode('₱#,##0.00');

                $sheet->getStyle("A{$lastRow}:H{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'top' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE,
                        ],
                    ],
                ]);
            },
        ];
    }

    public function title(): string
    {
        return 'AP Aging';
    }
}
