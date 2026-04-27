<?php

namespace App\Console\Commands;

use App\Models\DisbursementItem;
use App\Models\DisbursementRequest;
use App\Models\RecurringDisbursementTemplate;
use App\Services\NumberingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessRecurringDisbursements extends Command
{
    protected $signature   = 'disbursements:process-recurring';
    protected $description = 'Auto-generate disbursement requests from recurring templates with auto_create enabled';

    public function handle()
    {
        $templates = RecurringDisbursementTemplate::where('is_active', true)
            ->where('auto_create', true)
            ->with('items.account')
            ->get();

        $generated = 0;

        foreach ($templates as $template) {
            if (!$this->shouldGenerate($template)) {
                continue;
            }

            DB::transaction(function () use ($template) {
                $dr = DisbursementRequest::create([
                    'request_number' => NumberingService::generate('DR'),
                    'request_date'   => now()->toDateString(),
                    'due_date'       => null,
                    'payee_type'     => $template->payee_type,
                    'payee_id'       => $template->payee_id,
                    'payee_name'     => $template->payee_name,
                    'description'    => $template->description ?? "Auto-generated from: {$template->template_name}",
                    'amount'         => $template->amount,
                    'department_id'  => $template->department_id,
                    'category_id'    => $template->category_id,
                    'cost_center_id' => $template->cost_center_id,
                    'project'        => $template->project,
                    'budget_id'      => $template->budget_id,
                    'payment_method' => $template->payment_method,
                    'status'         => 'draft',
                    'requested_by'   => null,
                ]);

                foreach ($template->items as $item) {
                    DisbursementItem::create([
                        'disbursement_id' => $dr->id,
                        'description'     => $item->description,
                        'quantity'        => $item->quantity,
                        'unit_cost'       => $item->unit_cost,
                        'amount'          => $item->amount,
                        'account_id'      => $item->account_id,
                        'account_code'    => $item->account_code ?? ($item->account ? $item->account->account_code : null),
                        'tax_code_id'     => $item->tax_code_id,
                        'tax_code'        => $item->tax_code,
                        'remarks'         => $item->remarks,
                    ]);
                }

                $template->update(['last_generated_date' => now()]);

                app(\App\Services\AuditService::class)->log('create', 'disbursement_request', $dr, null,
                    "Auto-generated from recurring template: {$template->template_name}");
            });

            $generated++;
            $this->info("Generated DR from template: {$template->template_name}");
        }

        $this->info("Done. Generated {$generated} disbursement request" . ($generated === 1 ? '' : 's') . '.');

        return 0;
    }

    private function shouldGenerate(RecurringDisbursementTemplate $template): bool
    {
        $today     = now()->startOfDay();
        $startDate = $template->start_date->startOfDay();

        if ($today->lt($startDate)) {
            return false;
        }

        if ($template->end_date && $today->gt($template->end_date->endOfDay())) {
            return false;
        }

        $lastGenerated = $template->last_generated_date;

        if (!$lastGenerated) {
            return true;
        }

        $lastGenerated = $lastGenerated->startOfDay();

        switch ($template->frequency) {
            case 'monthly':      return $today->diffInMonths($lastGenerated) >= 1;
            case 'quarterly':    return $today->diffInMonths($lastGenerated) >= 3;
            case 'semi-annually': return $today->diffInMonths($lastGenerated) >= 6;
            case 'annually':     return $today->diffInYears($lastGenerated) >= 1;
            default:             return false;
        }
    }
}
