<?php

namespace App\Exports;

use App\Models\JournalEntryLine;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncomeStatementExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithEvents
{
    protected string $startDate;
    protected string $endDate;
    protected ?int $campusId;

    public function __construct(string $startDate, string $endDate, ?int $campusId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->campusId = $campusId;
    }

    public function collection(): Collection
    {
        $query = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.posting_date', [$this->startDate, $this->endDate])
            ->whereIn('chart_of_accounts.account_type', ['Revenue', 'Expense'])
            ->selectRaw('
                chart_of_accounts.account_code,
                chart_of_accounts.account_name,
                chart_of_accounts.account_type,
                SUM(journal_entry_lines.credit) - SUM(journal_entry_lines.debit) as amount
            ')
            ->groupBy(
                'chart_of_accounts.account_code',
                'chart_of_accounts.account_name',
                'chart_of_accounts.account_type'
            )
            ->orderBy('chart_of_accounts.account_type')
            ->orderBy('chart_of_accounts.account_code');

        if ($this->campusId) {
            $query->where('journal_entries.campus_id', $this->campusId);
        }

        $accounts = $query->get();

        $totalRevenue = $accounts->where('account_type', 'Revenue')->sum('amount');
        $totalExpense = abs($accounts->where('account_type', 'Expense')->sum('amount'));
        $grandTotal = $totalRevenue + $totalExpense;

        $rows = new Collection();

        // Revenue section
        $rows->push(['', 'REVENUE', '', '', '']);
        foreach ($accounts->where('account_type', 'Revenue') as $account) {
            $amount = (float) $account->amount;
            $pct = $grandTotal > 0 ? round(($amount / $grandTotal) * 100, 2) : 0;
            $rows->push([
                $account->account_code,
                $account->account_name,
                'Revenue',
                $amount,
                $pct,
            ]);
        }
        $rows->push(['', 'Total Revenue', '', $totalRevenue, '']);

        // Expense section
        $rows->push(['', '', '', '', '']);
        $rows->push(['', 'EXPENSES', '', '', '']);
        foreach ($accounts->where('account_type', 'Expense') as $account) {
            $amount = abs((float) $account->amount);
            $pct = $grandTotal > 0 ? round(($amount / $grandTotal) * 100, 2) : 0;
            $rows->push([
                $account->account_code,
                $account->account_name,
                'Expense',
                $amount,
                $pct,
            ]);
        }
        $rows->push(['', 'Total Expenses', '', $totalExpense, '']);

        // Net Income
        $rows->push(['', '', '', '', '']);
        $netIncome = $totalRevenue - $totalExpense;
        $rows->push(['', 'NET INCOME', '', $netIncome, 100.00]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Account Code',
            'Account Name',
            'Type',
            'Amount',
            '% of Total',
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

                // Peso formatting for Amount column
                $sheet->getStyle("D2:D{$lastRow}")->getNumberFormat()
                    ->setFormatCode('₱#,##0.00');

                // Percentage formatting
                $sheet->getStyle("E2:E{$lastRow}")->getNumberFormat()
                    ->setFormatCode('0.00"%"');

                // Bold section headers and totals
                for ($row = 2; $row <= $lastRow; $row++) {
                    $cellValue = $sheet->getCell("B{$row}")->getValue();
                    if (in_array($cellValue, ['REVENUE', 'EXPENSES', 'Total Revenue', 'Total Expenses', 'NET INCOME'])) {
                        $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
                    }
                    if ($cellValue === 'NET INCOME') {
                        $sheet->getStyle("A{$row}:E{$row}")->getBorders()->getTop()
                            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE);
                    }
                }
            },
        ];
    }

    public function title(): string
    {
        return 'Income Statement';
    }
}
