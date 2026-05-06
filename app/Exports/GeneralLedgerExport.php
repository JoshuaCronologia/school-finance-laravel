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

class GeneralLedgerExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithEvents
{
    protected $accounts;
    protected $dateFrom;
    protected $dateTo;
    protected $sectionRows = [];

    public function __construct(Collection $accounts, string $dateFrom, string $dateTo)
    {
        $this->accounts = $accounts;
        $this->dateFrom = $dateFrom;
        $this->dateTo   = $dateTo;
    }

    public function collection(): Collection
    {
        $rows = new Collection();
        $rowIndex = 2; // 1 = heading row

        foreach ($this->accounts as $account) {
            // Account header row
            $rows->push([
                '',
                strtoupper($account->account_code . ' — ' . $account->account_name),
                ucfirst($account->account_type),
                '',
                '',
                '',
                number_format($account->opening_balance, 2),
            ]);
            $this->sectionRows[] = ['type' => 'account', 'row' => $rowIndex];
            $rowIndex++;

            // Transaction rows
            $runningBalance = $account->opening_balance;
            foreach ($account->transactions as $txn) {
                $debit  = (float) ($txn->debit ?? 0);
                $credit = (float) ($txn->credit ?? 0);
                if ($account->normal_balance === 'debit') {
                    $runningBalance += $debit - $credit;
                } else {
                    $runningBalance += $credit - $debit;
                }
                $date = isset($txn->posting_date) ? $txn->posting_date : (isset($txn->entry_date) ? $txn->entry_date : '');
                $rows->push([
                    $date,
                    $txn->je_description ?? $txn->description ?? '',
                    $txn->entry_number ?? '',
                    $txn->reference_number ?? '',
                    $debit > 0 ? $debit : '',
                    $credit > 0 ? $credit : '',
                    $runningBalance,
                ]);
                $rowIndex++;
            }

            // Subtotal row
            $rows->push([
                '',
                'Account Totals',
                '',
                '',
                $account->total_debit,
                $account->total_credit,
                $account->ending_balance,
            ]);
            $this->sectionRows[] = ['type' => 'subtotal', 'row' => $rowIndex];
            $rowIndex++;

            // Blank separator
            $rows->push(['', '', '', '', '', '', '']);
            $rowIndex++;
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['Date', 'Description', 'Entry #', 'Reference', 'Debit', 'Credit', 'Balance'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(16);
        $sheet->getColumnDimension('G')->setWidth(16);

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2C3E50'],
                ],
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        $sectionRows = $this->sectionRows;

        return [
            AfterSheet::class => function (AfterSheet $event) use ($sectionRows) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                // Number format for debit/credit/balance columns
                $sheet->getStyle("E2:G{$lastRow}")->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                foreach ($sectionRows as $meta) {
                    $row = $meta['row'];
                    if ($meta['type'] === 'account') {
                        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 10],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFE8EDF5'],
                            ],
                        ]);
                    } elseif ($meta['type'] === 'subtotal') {
                        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                            'font' => ['bold' => true],
                            'borders' => [
                                'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF0F4F8'],
                            ],
                        ]);
                    }
                }
            },
        ];
    }

    public function title(): string
    {
        return 'General Ledger';
    }
}
