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

class AlphalistQuarterlyExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithEvents
{
    protected $entries;
    protected $monthNames;
    protected $quarter;
    protected $year;
    protected $nameField;

    public function __construct(Collection $entries, array $monthNames, $quarter, $year, string $nameField = 'payee_name')
    {
        $this->entries    = $entries;
        $this->monthNames = $monthNames;
        $this->quarter    = $quarter;
        $this->year       = $year;
        $this->nameField  = $nameField;
    }

    public function collection(): Collection
    {
        $rows = new Collection();
        $nameField = $this->nameField;
        $seq = 1;

        foreach ($this->entries as $e) {
            $rows->push([
                $seq++,
                $e->tin ?? '',
                $e->{$nameField} ?? '',
                $e->atc ?? '',
                (float)($e->m1_income ?? 0),
                (float)($e->m2_income ?? 0),
                (float)($e->m3_income ?? 0),
                (float)($e->income_payment ?? 0),
                (float)($e->tax_withheld ?? 0),
            ]);
        }

        $rows->push([
            '', '', 'TOTAL', '',
            $this->entries->sum('m1_income'),
            $this->entries->sum('m2_income'),
            $this->entries->sum('m3_income'),
            $this->entries->sum('income_payment'),
            $this->entries->sum('tax_withheld'),
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Seq #',
            'TIN',
            'Name of Payee',
            'ATC',
            $this->monthNames[0],
            $this->monthNames[1],
            $this->monthNames[2],
            'Total Income',
            'Tax Withheld',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        foreach (range('A', 'I') as $col) {
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

                $sheet->getStyle("E2:I{$lastRow}")->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getStyle("A{$lastRow}:I{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => ['top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE]],
                ]);
            },
        ];
    }

    public function title(): string
    {
        return "Q{$this->quarter} {$this->year}";
    }
}
