<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlphalistAnnualExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithEvents
{
    protected $entries;
    protected $year;

    public function __construct(Collection $entries, $year)
    {
        $this->entries = $entries;
        $this->year    = $year;
    }

    public function collection(): Collection
    {
        $rows = new Collection();
        $seq = 1;

        foreach ($this->entries as $e) {
            $rows->push([
                $seq++,
                $e->tin ?? '',
                $e->payee_name ?? '',
                $e->atc ?? '',
                (float)($e->q1_income ?? 0),
                (float)($e->q2_income ?? 0),
                (float)($e->q3_income ?? 0),
                (float)($e->q4_income ?? 0),
                (float)($e->income_payment ?? 0),
                (float)($e->tax_withheld ?? 0),
            ]);
        }

        $rows->push([
            '', '', 'TOTAL', '',
            $this->entries->sum('q1_income'),
            $this->entries->sum('q2_income'),
            $this->entries->sum('q3_income'),
            $this->entries->sum('q4_income'),
            $this->entries->sum('income_payment'),
            $this->entries->sum('tax_withheld'),
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Seq #', 'TIN', 'Name of Payee', 'ATC',
            'Q1 (Jan-Mar)', 'Q2 (Apr-Jun)', 'Q3 (Jul-Sep)', 'Q4 (Oct-Dec)',
            'Total Income', 'Tax Withheld',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFDBEAFE'],
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

                $sheet->getStyle("E2:J{$lastRow}")->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getStyle("A{$lastRow}:J{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => ['top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE]],
                ]);
            },
        ];
    }

    public function title(): string
    {
        return "Annual {$this->year}";
    }
}
