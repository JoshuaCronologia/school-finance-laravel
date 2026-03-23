<?php

namespace App\Exports;

use App\Models\ApBill;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QAPExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithEvents
{
    protected int $year;
    protected int $quarter;

    public function __construct(int $year, int $quarter)
    {
        $this->year = $year;
        $this->quarter = $quarter;
    }

    public function collection(): Collection
    {
        $startMonth = (($this->quarter - 1) * 3) + 1;
        $endMonth = $startMonth + 2;

        $startDate = "{$this->year}-" . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . '-01';
        $endDate = date('Y-m-t', strtotime("{$this->year}-" . str_pad($endMonth, 2, '0', STR_PAD_LEFT) . '-01'));

        $bills = ApBill::with('vendor')
            ->where('status', '!=', 'void')
            ->where('withholding_tax', '>', 0)
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->orderBy('bill_date')
            ->get();

        // Group by vendor TIN
        $payees = $bills->groupBy(function ($bill) {
            return $bill->vendor?->tin ?? 'NO-TIN';
        });

        $rows = new Collection();
        $seq = 1;

        foreach ($payees as $tin => $vendorBills) {
            $vendor = $vendorBills->first()->vendor;
            $incomePayment = $vendorBills->sum('gross_amount');
            $taxWithheld = $vendorBills->sum('withholding_tax');

            $rows->push([
                $seq++,
                $tin !== 'NO-TIN' ? $tin : '',
                $vendor?->name ?? 'Unknown',
                $this->resolveAtc($vendor),
                $incomePayment,
                $taxWithheld,
            ]);
        }

        // Add totals
        $rows->push([
            '',
            '',
            'TOTAL',
            '',
            $rows->sum(4),
            $rows->sum(5),
        ]);

        return $rows;
    }

    protected function resolveAtc($vendor): string
    {
        if (!$vendor) {
            return 'WC010';
        }

        return match ($vendor->withholding_tax_type) {
            'professional' => 'WC010',
            'rental' => 'WC040',
            'contractor' => 'WC160',
            'supplier' => 'WI010',
            default => 'WC010',
        };
    }

    public function headings(): array
    {
        return [
            'Seq #',
            'TIN',
            'Name of Payee',
            'ATC',
            'Income Payment',
            'Tax Withheld',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        foreach (range('A', 'F') as $col) {
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

                $sheet->getStyle("E2:F{$lastRow}")->getNumberFormat()
                    ->setFormatCode('₱#,##0.00');

                $sheet->getStyle("A{$lastRow}:F{$lastRow}")->applyFromArray([
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
        return "QAP Q{$this->quarter} {$this->year}";
    }
}
