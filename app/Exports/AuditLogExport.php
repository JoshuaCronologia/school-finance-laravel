<?php

namespace App\Exports;

use App\Models\AuditLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditLogExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected ?string $startDate;
    protected ?string $endDate;
    protected ?string $module;
    protected ?string $action;
    protected ?int $userId;

    public function __construct(
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $module = null,
        ?string $action = null,
        ?int $userId = null
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->module = $module;
        $this->action = $action;
        $this->userId = $userId;
    }

    public function collection(): Collection
    {
        $query = AuditLog::with('user');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [
                $this->startDate . ' 00:00:00',
                $this->endDate . ' 23:59:59',
            ]);
        }

        if ($this->module) {
            $query->where('module', $this->module);
        }

        if ($this->action) {
            $query->where('action', $this->action);
        }

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Date & Time',
            'User',
            'Action',
            'Module',
            'Record Type',
            'Record ID',
            'Old Values',
            'New Values',
            'IP Address',
            'User Agent',
            'Remarks',
        ];
    }

    /**
     * @param AuditLog $log
     */
    public function map($log): array
    {
        return [
            $log->created_at->format('m/d/Y h:i:s A'),
            $log->user_name ?? ($log->user?->name ?? 'System'),
            ucfirst($log->action),
            $log->module,
            $log->record_type,
            $log->record_id,
            $log->old_values ? json_encode($log->old_values, JSON_PRETTY_PRINT) : '',
            $log->new_values ? json_encode($log->new_values, JSON_PRETTY_PRINT) : '',
            $log->ip_address,
            $log->user_agent,
            $log->remarks,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        foreach (range('A', 'K') as $col) {
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

    public function title(): string
    {
        return 'Audit Log';
    }
}
