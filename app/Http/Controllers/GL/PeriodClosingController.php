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

        // Batch-fetch checklist counts for all open periods in 2 queries instead of 2*N
        $openPeriods = $periods->where('status', 'open');
        if ($openPeriods->isNotEmpty()) {
            $unpostedCounts = [];
            $postedCounts = [];

            // Build a single query for unposted counts per period
            foreach (DB::select("
                SELECT ap.id as period_id, COUNT(je.id) as cnt
                FROM accounting_periods ap
                LEFT JOIN journal_entries je ON je.status = 'draft'
                    AND je.entry_date >= ap.start_date AND je.entry_date <= ap.end_date
                WHERE ap.status = 'open'
                GROUP BY ap.id
            ") as $row) {
                $unpostedCounts[$row->period_id] = (int) $row->cnt;
            }

            foreach (DB::select("
                SELECT ap.id as period_id, COUNT(je.id) as cnt
                FROM accounting_periods ap
                LEFT JOIN journal_entries je ON je.status = 'posted'
                    AND je.posting_date >= ap.start_date AND je.posting_date <= ap.end_date
                WHERE ap.status = 'open'
                GROUP BY ap.id
            ") as $row) {
                $postedCounts[$row->period_id] = (int) $row->cnt;
            }

            $openPeriods->each(function ($period) use ($unpostedCounts, $postedCounts) {
                $period->checklist = [
                    'unposted_entries' => $unpostedCounts[$period->id] ?? 0,
                    'total_entries' => $postedCounts[$period->id] ?? 0,
                ];
            });
        }

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
