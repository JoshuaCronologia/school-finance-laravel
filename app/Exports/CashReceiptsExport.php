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

class CashReceiptsExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithEvents
{
    protected $entries;
    protected $tab;

    public function __construct($entries, string $tab = 'gl')
    {
        $this->entries = $entries;
        $this->tab     = $tab;
    }

    public function collection(): Collection
    {
        if ($this->tab === 'cashier') {
            $rows = new Collection();
            $currentOr = null;
            foreach ($this->entries as $r) {
                $typeMap = [1 => $r->student_name ?? '—', 2 => $r->employee_name ?? '—', 3 => trim($r->walkin_name) ?: '—'];
                $payor   = isset($typeMap[$r->customer_type]) ? $typeMap[$r->customer_type] : '—';
                $isNew   = $r->receipt_number !== $currentOr;
                $currentOr = $r->receipt_number;
                $rows->push([
                    $isNew ? $r->receipt_number : '',
                    $isNew ? $r->date_paid : '',
                    $isNew ? $payor : '',
                    $isNew ? ($r->remarks ?: '') : '',
                    $r->account,
                    (float) $r->amount,
                    $isNew ? (float) $r->batch_total : '',
                ]);
            }
            return $rows;
        }

        // GL tab
        return $this->entries->map(function ($e) {
            return [
                $e->posting_date,
                $e->entry_number,
                $e->reference_number ?? '',
                $e->je_description ?? $e->description ?? '',
                $e->account_code . ' - ' . $e->account_name,
                (float) $e->debit,
            ];
        });
    }

    public function headings(): array
    {
        if ($this->tab === 'cashier') {
            return ['OR #', 'Date', 'Payor', 'Remarks', 'Account', 'Amount', 'Total Amount'];
        }
        return ['Date', 'Entry #', 'Ref No.', 'Description', 'Cash/Bank Account', 'Amount'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setWidth(16);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(35);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(16);
        $sheet->getColumnDimension('G')->setWidth(16);

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
        $tab = $this->tab;
        $total = $this->tab === 'cashier'
            ? $this->entries->sum('amount')
            : $this->entries->sum('debit');

        return [
            AfterSheet::class => function (AfterSheet $event) use ($tab, $total) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $amtCol = $tab === 'cashier' ? 'F:G' : 'F';
                $sheet->getStyle("{$amtCol}2:{$amtCol}{$lastRow}")->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                // Totals row
                $nextRow = $lastRow + 1;
                $label   = $tab === 'cashier' ? 'Total Receipts' : 'Total Cash Receipts';
                $sheet->setCellValue("E{$nextRow}", $label);
                $sheet->setCellValue("F{$nextRow}", $total);
                $sheet->getStyle("A{$nextRow}:G{$nextRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => ['top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE]],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF0F4F8'],
                    ],
                ]);
                $sheet->getStyle("F{$nextRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            },
        ];
    }

    public function title(): string
    {
        return $this->tab === 'cashier' ? 'Cashier Receipts' : 'Cash Receipts Book';
    }
}
