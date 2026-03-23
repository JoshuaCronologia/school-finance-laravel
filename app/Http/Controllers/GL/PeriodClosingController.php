<?php

namespace App\Http\Controllers\GL;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeriodClosingController extends Controller
{
    public function index()
    {
        $periods = AccountingPeriod::orderBy('start_date', 'desc')->get();

        // Pre-closing checklist for each open period
        $periods->each(function ($period) {
            if ($period->status === 'open') {
                $period->checklist = [
                    'unposted_entries' => JournalEntry::where('status', 'draft')
                        ->whereDate('entry_date', '>=', $period->start_date)
                        ->whereDate('entry_date', '<=', $period->end_date)
                        ->count(),
                    'total_entries' => JournalEntry::where('status', 'posted')
                        ->whereDate('posting_date', '>=', $period->start_date)
                        ->whereDate('posting_date', '<=', $period->end_date)
                        ->count(),
                ];
            }
        });

        return view('pages.gl.period-closing', compact('periods'));
    }

    public function close(Request $request, AccountingPeriod $period)
    {
        if ($period->status === 'closed') {
            return back()->with('error', 'Period is already closed.');
        }

        // Validation checks
        $unpostedCount = JournalEntry::where('status', 'draft')
            ->whereDate('entry_date', '>=', $period->start_date)
            ->whereDate('entry_date', '<=', $period->end_date)
            ->count();

        if ($unpostedCount > 0 && !$request->boolean('force')) {
            return back()->with('error', "Cannot close period: {$unpostedCount} unposted journal entries exist. Post or delete them first, or force close.");
        }

        DB::transaction(function () use ($period) {
            $period->update([
                'status' => 'closed',
                'closed_by' => auth()->id(),
                'closed_at' => now(),
            ]);

            app(AuditService::class)->log('close', 'accounting_period', $period, null,
                "Period closed: {$period->name}");
        });

        return back()->with('success', "Period '{$period->name}' closed successfully.");
    }

    public function reopen(AccountingPeriod $period)
    {
        if ($period->status !== 'closed') {
            return back()->with('error', 'Only closed periods can be reopened.');
        }

        DB::transaction(function () use ($period) {
            $period->update([
                'status' => 'open',
                'closed_by' => null,
                'closed_at' => null,
            ]);

            app(AuditService::class)->log('reopen', 'accounting_period', $period, null,
                "Period reopened: {$period->name}");
        });

        return back()->with('success', "Period '{$period->name}' reopened.");
    }
}
