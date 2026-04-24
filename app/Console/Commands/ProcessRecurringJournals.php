<?php

namespace App\Console\Commands;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\RecurringJournalTemplate;
use App\Services\NumberingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessRecurringJournals extends Command
{
    protected $signature = 'journal-entries:process-recurring';
    protected $description = 'Auto-generate journal entries from recurring templates with auto_create enabled';

    public function handle()
    {
        $templates = RecurringJournalTemplate::where('is_active', true)
            ->where('auto_create', true)
            ->with('lines')
            ->get();

        $generated = 0;

        foreach ($templates as $template) {
            if (!$this->shouldGenerate($template)) {
                continue;
            }

            $totalDebit = $template->lines->sum('debit');
            $totalCredit = $template->lines->sum('credit');

            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                $this->warn("Template {$template->template_name} lines are not balanced. Skipping.");
                continue;
            }

            DB::transaction(function () use ($template) {
                $je = JournalEntry::create([
                    'entry_number' => NumberingService::generate('JE'),
                    'entry_date' => now()->toDateString(),
                    'posting_date' => now()->toDateString(),
                    'journal_type' => 'general',
                    'description' => $template->description ?? "Auto-generated from: {$template->template_name}",
                    'status' => 'draft',
                    'source_module' => 'recurring',
                    'source_id' => $template->id,
                    'created_by' => null,
                ]);

                foreach ($template->lines as $i => $line) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $je->id,
                        'line_number' => $i + 1,
                        'account_id' => $line->account_id,
                        'description' => $line->description,
                        'debit' => $line->debit,
                        'credit' => $line->credit,
                        'department_id' => $line->department_id,
                    ]);
                }

                $template->update(['last_generated_date' => now()]);

                app(\App\Services\AuditService::class)->log('create', 'journal_entry', $je, null,
                    "Auto-generated from recurring template: {$template->template_name}");
            });

            $generated++;
            $this->info("Generated JE from template: {$template->template_name}");
        }

        $this->info("Done. Generated {$generated} journal entr" . ($generated === 1 ? 'y' : 'ies') . '.');

        return 0;
    }

    private function shouldGenerate(RecurringJournalTemplate $template): bool
    {
        $today = now()->startOfDay();
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
            case 'monthly':
                return $today->diffInMonths($lastGenerated) >= 1;
            case 'quarterly':
                return $today->diffInMonths($lastGenerated) >= 3;
            case 'semi-annually':
                return $today->diffInMonths($lastGenerated) >= 6;
            case 'annually':
                return $today->diffInYears($lastGenerated) >= 1;
            default:
                return false;
        }
    }
}

