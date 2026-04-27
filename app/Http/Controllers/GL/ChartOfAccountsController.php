<?php

namespace App\Http\Controllers\GL;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use App\Services\AuditService;
use App\Services\FinanceFeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsController extends Controller
{
    public function index(Request $request)
    {
        $query = ChartOfAccount::with('parent');
        $isFiltered = $request->filled('account_type') || $request->filled('search') || $request->filled('status');

        if ($request->filled('account_type')) {
            $query->where('account_type', $request->account_type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_code', 'like', "%{$search}%")
                  ->orWhere('account_name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // When not filtered, show hierarchical (parent + children collapsible)
        if (!$isFiltered) {
            $query->whereNull('parent_id');
        }

        $accounts = $query->orderBy('account_code')->paginate(50);

        // Eager load children with counts for hierarchical view
        if (!$isFiltered) {
            $accounts->load('children');
        }

        // Collect ALL account IDs (parents + children) for balance query
        $allIds = $accounts->pluck('id')->all();
        if (!$isFiltered) {
            foreach ($accounts as $acct) {
                if ($acct->children) {
                    $allIds = array_merge($allIds, $acct->children->pluck('id')->all());
                }
            }
        }
        $allIds = array_unique($allIds);

        // Compute balances from JE lines
        $jeBalances = [];
        if (!empty($allIds)) {
            $rows = JournalEntryLine::select(
                    'journal_entry_lines.account_id',
                    DB::raw('COALESCE(SUM(journal_entry_lines.debit), 0) - COALESCE(SUM(journal_entry_lines.credit), 0) as balance')
                )
                ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
                ->where('je.status', 'posted')
                ->whereIn('journal_entry_lines.account_id', $allIds)
                ->groupBy('journal_entry_lines.account_id')
                ->get();

            foreach ($rows as $row) {
                $jeBalances[$row->account_id] = (float) $row->balance;
            }
        }

        // Add finance fee balances for mapped revenue accounts
        $feeBalances = [];
        try {
            $feeRevenue = FinanceFeeService::mappedRevenue('2000-01-01', now()->toDateString());
            foreach ($feeRevenue as $fee) {
                $feeBalances[$fee->account_id] = (float) $fee->total_amount;
            }
        } catch (\Exception $e) {
            // Finance DB unavailable
        }

        // Helper to compute balance for a single account
        $computeBalance = function ($account) use ($jeBalances, $feeBalances) {
            $jeBalance = $jeBalances[$account->id] ?? 0;
            $feeBalance = $feeBalances[$account->id] ?? 0;
            if ($account->normal_balance === 'credit') {
                return -$jeBalance + $feeBalance;
            }
            return $jeBalance + $feeBalance;
        };

        // Attach balances to accounts and children
        $accounts->getCollection()->transform(function ($account) use ($computeBalance) {
            $account->balance = $computeBalance($account);

            // Compute children balances + sum for parent total
            $childrenTotal = 0;
            if ($account->children && $account->children->count() > 0) {
                $account->children->transform(function ($child) use ($computeBalance, &$childrenTotal) {
                    $child->balance = $computeBalance($child);
                    $childrenTotal += $child->balance;
                    return $child;
                });
            }
            $account->balance += $childrenTotal;

            return $account;
        });

        $parentAccounts = ChartOfAccount::whereNull('parent_id')
            ->orWhere('is_postable', false)
            ->orderBy('account_code')
            ->get();

        $campuses = Campus::all();
        $accountTypes = ['asset', 'liability', 'equity', 'revenue', 'expense'];

        return view('pages.gl.accounts.index', compact('accounts', 'parentAccounts', 'campuses', 'accountTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:20|unique:chart_of_accounts',
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'normal_balance' => 'required|in:debit,credit',
            'fs_group' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'is_postable' => 'boolean',
            'campus_id' => 'nullable|exists:campuses,id',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['is_postable'] = $validated['is_postable'] ?? true;

        $account = ChartOfAccount::create($validated);

        app(AuditService::class)->log('create', 'chart_of_accounts', $account, null, 'Account created');

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'account' => $account, 'message' => 'Account created.']);
        }

        return redirect()->route('gl.accounts.index')->with('success', 'Account created successfully.');
    }

    public function show(ChartOfAccount $account)
    {
        $account->load('parent', 'children');

        // JE balance
        $jeData = JournalEntryLine::select(
                DB::raw('COALESCE(SUM(debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(credit), 0) as total_credit')
            )
            ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->where('je.status', 'posted')
            ->where('journal_entry_lines.account_id', $account->id)
            ->first();

        $totalDebit = (float) $jeData->total_debit;
        $totalCredit = (float) $jeData->total_credit;

        // Finance fee balance (for mapped revenue accounts)
        $feeBalance = 0;
        try {
            $feeRevenue = FinanceFeeService::mappedRevenue('2000-01-01', now()->toDateString());
            $match = $feeRevenue->firstWhere('account_id', $account->id);
            if ($match) {
                $feeBalance = (float) $match->total_amount;
            }
        } catch (\Exception $e) {}

        // Compute balance
        if ($account->normal_balance === 'credit') {
            $balance = ($totalCredit - $totalDebit) + $feeBalance;
        } else {
            $balance = ($totalDebit - $totalCredit) + $feeBalance;
        }

        // Recent transactions (last 20 from JE)
        $recentTransactions = JournalEntryLine::select(
                'journal_entry_lines.*',
                'je.entry_number', 'je.posting_date', 'je.description as je_description', 'je.reference_number'
            )
            ->join('journal_entries as je', 'journal_entry_lines.journal_entry_id', '=', 'je.id')
            ->where('je.status', 'posted')
            ->where('journal_entry_lines.account_id', $account->id)
            ->orderByDesc('je.posting_date')
            ->limit(20)
            ->get();

        // Recent finance fee entries (if mapped) — scope to current school year
        $feeTransactions = collect();
        $syLabel = 'Current SY';
        try {
            $sy = FinanceFeeService::currentSchoolYear(); // e.g. "2025-2026"
            $syStart = substr($sy, 0, 4) . '-06-01';      // June 1 of start year
            $syLabel = 'SY ' . $sy;

            $feeEntries = FinanceFeeService::glEntries(
                $syStart,
                now()->toDateString(),
                $account->id
            );
            if ($feeEntries->isNotEmpty()) {
                $feeTransactions = $feeEntries->first()->sortByDesc('posting_date')->take(20);
            }
        } catch (\Exception $e) {}

        return view('pages.gl.accounts.show', compact(
            'account', 'balance', 'totalDebit', 'totalCredit', 'feeBalance',
            'recentTransactions', 'feeTransactions', 'syLabel'
        ));
    }

    public function update(Request $request, ChartOfAccount $account)
    {
        $validated = $request->validate([
            'account_code' => "required|string|max:20|unique:chart_of_accounts,account_code,{$account->id}",
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'normal_balance' => 'required|in:debit,credit',
            'fs_group' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'is_postable' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $oldValues = $account->toArray();
        $account->update($validated);

        app(AuditService::class)->log('update', 'chart_of_accounts', $account, $oldValues, 'Account updated');

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'account' => $account, 'message' => 'Account updated.']);
        }

        return redirect()->route('gl.accounts.index')->with('success', 'Account updated successfully.');
    }
}
