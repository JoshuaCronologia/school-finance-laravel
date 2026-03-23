<?php

namespace App\Exports;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TrialBalanceExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithEvents
{
    protected string $asOfDate;
    protected ?int $campusId;

    public function __construct(string $asOfDate, ?int $campusId = null)
    {
        $this->asOfDate = $asOfDate;
        $this->campusId = $campusId;
    }

    public function collection(): Collection
    {
        $query = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.posting_date', '<=', $this->asOfDate)
            ->selectRaw('
                chart_of_accounts.account_code,
                chart_of_accounts.account_name,
                chart_of_accounts.account_type,
                SUM(journal_entry_lines.debit) as total_debit,
                SUM(journal_entry_lines.credit) as total_credit
            ')
            ->groupBy(
                'chart_of_accounts.account_code',
                'chart_of_accounts.account_name',
                'chart_of_accounts.account_type'
            )
            ->orderBy('chart_of_accounts.account_code');

        if ($this->campusId) {
            $query->where('journal_entries.campus_id', $this->campusId);
        }

        $rows = $query->get()->map(function ($row) {
            return [
                'Account Code' => $row->account_code,
                'Account Name' => $row->account_name,
                'Type' => $row->account_type,
                'Debit' => (float) $row->total_debit,
                'Credit' => (float) $row->total_credit,
            ];
        });

        // Add totals row
        $rows->push([
            'Account Code' => '',
            'Account Name' => 'TOTALS',
            'Type' => '',
            'Debit' => $rows->sum('Debit'),
            'Credit' => $rows->sum('Credit'),
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Account Code',
            'Account Name',
            'Type',
            'Debit',
            'Credit',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        foreach (range('A', 'E') as $col) {
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

                // Bold totals row
                $sheet->getStyle("A{$lastRow}:E{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'borders' => [
                        'top' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE,
                        ],
                    ],
                ]);

                // Peso formatting for Debit/Credit columns
                $sheet->getStyle("D2:E{$lastRow}")->getNumberFormat()
                    ->setFormatCode('₱#,##0.00');
            },
        ];
    }

    public function title(): string
    {
        return 'Trial Balance';
    }
}
