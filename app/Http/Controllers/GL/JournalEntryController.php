<?php

namespace App\Http\Controllers\GL;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\Campus;
use App\Models\ChartOfAccount;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\FundSource;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\RecurringJournalLine;
use App\Models\RecurringJournalTemplate;
use App\Services\AuditService;
use App\Services\NumberingService;
use App\Services\PostingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalEntry::with('lines.account', 'campus', 'department');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('journal_type')) {
            $query->where('journal_type', $request->journal_type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('entry_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('entry_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('entry_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $journalEntries = $query->latest('entry_date')->paginate(20);

        $jeCounts = JournalEntry::selectRaw("
            COUNT(*) as total_entries,
            COUNT(CASE WHEN status = 'draft' THEN 1 END) as unposted,
            COUNT(CASE WHEN status = 'pending_approval' THEN 1 END) as pending_approval
        ")->first();
        $unpostedCount = (int) $jeCounts->unposted;
        $pendingApprovalCount = (int) $jeCounts->pending_approval;
        $totalEntries = (int) $jeCounts->total_entries;

        $accounts = ChartOfAccount::active()->where('is_postable', true)->orderBy('account_code')->get();

        return view('pages.gl.journal-entries.index', compact('journalEntries', 'unpostedCount', 'pendingApprovalCount', 'totalEntries', 'accounts'));
    }

    public function create()
    {
        $accounts = ChartOfAccount::active()->where('is_postable', true)->orderBy('account_code')->get();
        $departments = Department::where('is_active', true)->get();
        $campuses = Campus::where('is_active', true)->get();
        $costCenters = CostCenter::where('is_active', true)->get();
        $fundSources = FundSource::where('is_active', true)->get();

        return view('pages.gl.journal-entries.create', compact(
            'accounts', 'departments', 'campuses', 'costCenters', 'fundSources'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'journal_type' => 'required|in:general,adjusting,closing,reversing,revenue,expense,payroll',
            'description' => 'required|string',
            'reference_number' => 'nullable|string|max:100',
            'campus_id' => 'nullable|exists:campuses,id',
            'department_id' => 'nullable|exists:departments,id',
            'school_year' => 'nullable|string|max:20',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.description' => 'required|string',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
            'lines.*.department_id' => 'nullable|exists:departments,id',
            'lines.*.cost_center_id' => 'nullable|exists:cost_centers,id',
            'lines.*.fund_source_id' => 'nullable|exists:fund_sources,id',
            'lines.*.project' => 'nullable|string|max:100',
        ]);

        // Validate balanced entry
        $totalDebit = collect($validated['lines'])->sum('debit');
        $totalCredit = collect($validated['lines'])->sum('credit');

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return back()->withInput()->with('error',
                "Journal entry must be balanced. Debits (₱" . number_format($totalDebit, 2) . ") and credits (₱" . number_format($totalCredit, 2) . ") do not match.");
        }

        // Validate each line has either debit or credit (not both zero)
        foreach ($validated['lines'] as $i => $line) {
            if ($line['debit'] == 0 && $line['credit'] == 0) {
                return back()->withInput()->with('error', "Line " . ($i + 1) . " must have either a debit or credit amount.");
            }
        }

        try {
            $entry = DB::transaction(function () use ($validated, $request) {
                $entry = JournalEntry::create([
                    'entry_number' => NumberingService::generate('JE'),
                    'entry_date' => $validated['entry_date'],
                    'posting_date' => $validated['entry_date'],
                    'journal_type' => $validated['journal_type'],
                    'description' => $validated['description'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'campus_id' => $validated['campus_id'] ?? null,
                    'department_id' => $validated['department_id'] ?? null,
                    'school_year' => $validated['school_year'] ?? null,
                    'status' => 'draft',
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['lines'] as $i => $line) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'line_number' => $i + 1,
                        'account_id' => $line['account_id'],
                        'description' => $line['description'],
                        'debit' => $line['debit'],
                        'credit' => $line['credit'],
                        'department_id' => $line['department_id'] ?? null,
                        'cost_center_id' => $line['cost_center_id'] ?? null,
                        'fund_source_id' => $line['fund_source_id'] ?? null,
                        'project' => $line['project'] ?? null,
                    ]);
                }

                app(AuditService::class)->log('create', 'journal_entry', $entry, null, 'Journal entry created');

                // If user clicked "Submit for Approval", change status
                if ($request->input('action') === 'submit_approval') {
                    $entry->update(['status' => 'pending_approval']);
                    app(AuditService::class)->log('submit_approval', 'journal_entry', $entry, null, 'Submitted for approval');
                }

                return $entry;
            });

            return redirect()->route('gl.journal-entries.show', $entry)
                ->with('success', "Journal entry {$entry->entry_number} created successfully.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create journal entry: ' . $e->getMessage());
        }
    }

    public function show(JournalEntry $journalEntry)
    {
        $journalEntry->load('lines.account', 'lines.department', 'lines.costCenter', 'campus', 'department');

        $totalDebit = $journalEntry->lines->sum('debit');
        $totalCredit = $journalEntry->lines->sum('credit');

        return view('pages.gl.journal-entries.show', compact('journalEntry', 'totalDebit', 'totalCredit'));
    }

    /**
     * Submit a draft JE for approval.
     */
    public function submitForApproval(JournalEntry $journalEntry)
    {
        if ($journalEntry->status !== 'draft') {
            return back()->with('error', 'Only draft entries can be submitted for approval.');
        }

        $journalEntry->update(['status' => 'pending_approval']);

        app(AuditService::class)->log('submit_approval', 'journal_entry', $journalEntry, null, 'Submitted for approval');

        return back()->with('success', "Journal entry {$journalEntry->entry_number} submitted for approval.");
    }

    /**
     * Approve a JE (moves to approved status, ready for posting).
     */
    public function approve(JournalEntry $journalEntry)
    {
        if ($journalEntry->status !== 'pending_approval') {
            return back()->with('error', 'Only entries pending approval can be approved.');
        }

        $journalEntry->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        app(AuditService::class)->log('approve', 'journal_entry', $journalEntry, null, 'Journal entry approved');

        return back()->with('success', "Journal entry {$journalEntry->entry_number} approved.");
    }

    /**
     * Reject a JE (moves back to draft).
     */
    public function reject(Request $request, JournalEntry $journalEntry)
    {
        if ($journalEntry->status !== 'pending_approval') {
            return back()->with('error', 'Only entries pending approval can be rejected.');
        }

        $journalEntry->update([
            'status' => 'draft',
            'approved_by' => null,
        ]);

        $reason = $request->input('reason', 'Rejected');
        app(AuditService::class)->log('reject', 'journal_entry', $journalEntry, null, "Journal entry rejected: {$reason}");

        return back()->with('success', "Journal entry {$journalEntry->entry_number} rejected and returned to draft.");
    }

    /**
     * Post an approved journal entry.
     */
    public function post(JournalEntry $journalEntry)
    {
        if (!in_array($journalEntry->status, ['approved', 'draft'])) {
            return back()->with('error', 'Only approved or draft entries can be posted.');
        }

        // Check period is not closed
        $period = AccountingPeriod::where('start_date', '<=', $journalEntry->entry_date)
            ->where('end_date', '>=', $journalEntry->entry_date)
            ->first();

        if ($period && $period->status === 'closed') {
            return back()->with('error', 'Cannot post to a closed accounting period.');
        }

        // Verify entry is balanced
        $totalDebit = $journalEntry->lines()->sum('debit');
        $totalCredit = $journalEntry->lines()->sum('credit');

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return back()->with('error', 'Cannot post an unbalanced journal entry.');
        }

        $journalEntry->update([
            'status' => 'posted',
            'posting_date' => now(),
            'posted_by' => auth()->id(),
        ]);

        app(AuditService::class)->log('post', 'journal_entry', $journalEntry, null, 'Journal entry posted');

        return back()->with('success', "Journal entry {$journalEntry->entry_number} posted successfully.");
    }

    /**
     * Create a reversing entry.
     */
    public function reverse(JournalEntry $journalEntry)
    {
        if ($journalEntry->status !== 'posted') {
            return back()->with('error', 'Only posted entries can be reversed.');
        }

        try {
            $reversal = app(PostingService::class)->reverseEntry($journalEntry);

            app(AuditService::class)->log('reverse', 'journal_entry', $journalEntry, null,
                "Reversed by {$reversal->entry_number}");

            return redirect()->route('gl.journal-entries.show', $reversal)
                ->with('success', "Reversal entry {$reversal->entry_number} created.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reverse entry: ' . $e->getMessage());
        }
    }

    /**
     * Print Journal Voucher as PDF.
     */
    public function printVoucher(JournalEntry $journalEntry)
    {
        $journalEntry->load('lines.account', 'lines.department', 'campus', 'department');

        $totalDebit = $journalEntry->lines->sum('debit');
        $totalCredit = $journalEntry->lines->sum('credit');

        $data = [
            'entry'       => $journalEntry,
            'totalDebit'  => $totalDebit,
            'totalCredit' => $totalCredit,
            'printedAt'   => now()->format('F d, Y h:i A'),
            'printedBy'   => auth()->user()->name ?? 'System',
        ];

        $pdf = Pdf::loadView('pages.gl.journal-entries.print-voucher', $data)
            ->setPaper('letter', 'portrait');

        return $pdf->download("JV-{$journalEntry->entry_number}.pdf");
    }

    /**
     * Approval queue - list entries pending approval.
     */
    public function approvalQueue()
    {
        $pendingEntries = JournalEntry::with('lines.account', 'department')
            ->where('status', 'pending_approval')
            ->latest('entry_date')
            ->paginate(20);

        $approvedEntries = JournalEntry::with('lines.account', 'department')
            ->where('status', 'approved')
            ->latest('entry_date')
            ->paginate(20);

        return view('pages.gl.journal-entries.approval-queue', compact('pendingEntries', 'approvedEntries'));
    }

    /**
     * List recurring journal templates.
     */
    public function recurring()
    {
        $templates = RecurringJournalTemplate::with('lines.account')->get();
        $accounts = ChartOfAccount::active()->where('is_postable', true)->orderBy('account_code')->get();
        $departments = Department::where('is_active', true)->get();

        return view('pages.gl.recurring', compact('templates', 'accounts', 'departments'));
    }

    /**
     * Store a recurring journal template.
     */
    public function storeRecurring(Request $request)
    {
        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
            'frequency' => 'required|in:monthly,quarterly,semi-annually,annually',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'description' => 'nullable|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.description' => 'required|string',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
            'lines.*.department_id' => 'nullable|exists:departments,id',
        ]);

        $totalDebit = collect($validated['lines'])->sum('debit');
        $totalCredit = collect($validated['lines'])->sum('credit');

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return back()->withInput()->with('error', 'Recurring template lines must balance.');
        }

        DB::transaction(function () use ($validated) {
            $template = RecurringJournalTemplate::create([
                'template_name' => $validated['template_name'],
                'frequency' => $validated['frequency'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'description' => $validated['description'] ?? null,
                'auto_create' => false,
                'is_active' => true,
            ]);

            foreach ($validated['lines'] as $line) {
                RecurringJournalLine::create([
                    'template_id' => $template->id,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'department_id' => $line['department_id'] ?? null,
                ]);
            }
        });

        return redirect()->route('gl.recurring')->with('success', 'Recurring template created.');
    }
}
