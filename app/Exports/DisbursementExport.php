<?php

namespace App\Exports;

use App\Models\DisbursementRequest;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormats;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DisbursementExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormats, WithTitle
{
    protected ?string $startDate;
    protected ?string $endDate;
    protected ?string $status;
    protected ?int $departmentId;

    public function __construct(
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $status = null,
        ?int $departmentId = null
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
        $this->departmentId = $departmentId;
    }

    public function collection(): Collection
    {
        $query = DisbursementRequest::with(['department', 'costCenter']);

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('request_date', [$this->startDate, $this->endDate]);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->departmentId) {
            $query->where('department_id', $this->departmentId);
        }

        return $query->orderBy('request_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Request #',
            'Date',
            'Payee',
            'Department',
            'Category',
            'Amount',
            'Status',
        ];
    }

    /**
     * @param DisbursementRequest $request
     */
    public function map($request): array
    {
        return [
            $request->request_number,
            $request->request_date?->format('m/d/Y'),
            $request->payee_name,
            $request->department?->name ?? '-',
            $request->costCenter?->name ?? '-',
            $request->amount,
            ucfirst($request->status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        foreach (range('A', 'G') as $col) {
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

    public function columnFormats(): array
    {
        return [
            'F' => '₱#,##0.00',
        ];
    }

    public function title(): string
    {
        return 'Disbursements';
    }
}
