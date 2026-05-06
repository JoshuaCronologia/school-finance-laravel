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

class GeneralJournalExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithEvents
{
    protected $entries;
    protected $entryHeaderRows = [];

    public function __construct($entries)
    {
        $this->entries = $entries;
    }

    public function collection(): Collection
    {
        $rows = new Collection();
        $rowIndex = 2;

        foreach ($this->entries as $je) {
            // Entry header row
            $rows->push([
                $je->posting_date->format('Y-m-d'),
                $je->entry_number,
                $je->reference_number ?? '',
                $je->description,
                '',
                '',
            ]);
            $this->entryHeaderRows[] = $rowIndex;
            $rowIndex++;

            // Debit lines
            foreach ($je->lines->where('debit', '>', 0) as $line) {
                $rows->push([
                    '',
                    '',
                    '',
                    '    ' . ($line->account->account_code ?? '') . ' - ' . ($line->account->account_name ?? ''),
                    (float) $line->debit,
                    '',
                ]);
                $rowIndex++;
            }

            // Credit lines
            foreach ($je->lines->where('credit', '>', 0) as $line) {
                $rows->push([
                    '',
                    '',
                    '',
                    '        ' . ($line->account->account_code ?? '') . ' - ' . ($line->account->account_name ?? ''),
                    '',
                    (float) $line->credit,
                ]);
                $rowIndex++;
            }
        }

        // Totals row
        $totalDebit  = $this->entries->sum(function ($e) { return $e->lines->sum('debit'); });
        $totalCredit = $this->entries->sum(function ($e) { return $e->lines->sum('credit'); });
        $rows->push(['', '', '', 'GRAND TOTAL', $totalDebit, $totalCredit]);
        $this->entryHeaderRows['total'] = $rowIndex;

        return $rows;
    }

    public function headings(): array
    {
        return ['Date', 'Entry #', 'Ref No.', 'Account / Description', 'Debit', 'Credit'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(50);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2C3E50'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        $entryHeaderRows = $this->entryHeaderRows;

        return [
            AfterSheet::class => function (AfterSheet $event) use ($entryHeaderRows) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("E2:F{$lastRow}")->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                foreach ($entryHeaderRows as $key => $row) {
                    if ($key === 'total') {
                        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                            'font' => ['bold' => true],
                            'borders' => ['top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE]],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF0F4F8'],
                            ],
                        ]);
                    } else {
                        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFE8EDF5'],
                            ],
                        ]);
                    }
                }
            },
        ];
    }

    public function title(): string
    {
        return 'General Journal';
    }
}
