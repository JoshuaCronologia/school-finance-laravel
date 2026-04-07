<?php

namespace App\Exports;

use App\Models\Budget;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormats;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BudgetExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormats, WithTitle
{
    protected $schoolYear;
    protected $departmentId;
    protected $status;

    public function __construct(?string $schoolYear = null, ?int $departmentId = null, ?string $status = null)
    {
        $this->schoolYear = $schoolYear;
        $this->departmentId = $departmentId;
        $this->status = $status;
    }

    public function collection(): Collection
    {
        $query = Budget::with(['department', 'costCenter']);

        if ($this->schoolYear) {
            $query->where('school_year', $this->schoolYear);
        }

        if ($this->departmentId) {
            $query->where('department_id', $this->departmentId);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->orderBy('budget_name')->get();
    }

    public function headings(): array
    {
        return [
            'Budget Name',
            'Department',
            'Category',
            'Annual Budget',
            'Committed',
            'Actual',
            'Remaining',
            'Utilization %',
        ];
    }

    /**
     * @param Budget $budget
     */
    public function map($budget): array
    {
        return [
            $budget->budget_name,
            $budgetoptional($budget->department)->name ?? '-',
            $budgetoptional($budget->costCenter)->name ?? '-',
            $budget->annual_budget,
            $budget->committed,
            $budget->actual,
            $budget->remaining,
            $budget->utilization_percent,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        // Auto-size columns
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
            "D2:G{$lastRow}" => [
                'numberFormat' => [
                    'formatCode' => '₱#,##0.00',
                ],
            ],
            "H2:H{$lastRow}" => [
                'numberFormat' => [
                    'formatCode' => '0.00"%"',
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => '₱#,##0.00',
            'E' => '₱#,##0.00',
            'F' => '₱#,##0.00',
            'G' => '₱#,##0.00',
            'H' => '0.00"%"',
        ];
    }

    public function title(): string
    {
        return 'Budget Report';
    }
}
