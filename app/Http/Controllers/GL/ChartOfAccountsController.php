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
        $isFiltered = $request->filled('account_type') || $request->filled('search') || $request->filled('status');

        $query = ChartOfAccount::query();

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

        // Hierarchical default: top-level only, children lazy-loaded via AJAX
        if (!$isFiltered) {
            $query->whereNull('parent_id');
        }

        $accounts = $query->orderBy('account_code')->paginate(50);

        // Child counts per parent — one query, no loading actual children
        $parentIds = $accounts->pluck('id')->all();
        $childCounts = ChartOfAccount::whereIn('parent_id', $parentIds)
            ->select('parent_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('parent_id')
            ->pluck('cnt', 'parent_id');

        // Parent balances = sum of children's JE lines (one query, no child eager load)
        $parentJeBalances = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'jel.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as c', 'jel.account_id', '=', 'c.id')
            ->where('je.status', 'posted')
            ->whereIn('c.parent_id', $parentIds)
            ->select('c.parent_id', DB::raw('SUM(COALESCE(jel.debit,0)) - SUM(COALESCE(jel.credit,0)) as balance'))
            ->groupBy('c.parent_id')
            ->pluck('balance', 'parent_id');

        // Standalone account (no children) own JE balances
        $standaloneIds = $accounts->filter(function ($a) use ($childCounts) {
            return !isset($childCounts[$a->id]);
        })->pluck('id')->all();

        $standaloneJeBalances = [];
        if (!empty($standaloneIds)) {
            $standaloneJeBalances = DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'jel.journal_entry_id', '=', 'je.id')
                ->where('je.status', 'posted')
                ->whereIn('jel.account_id', $standaloneIds)
                ->select('jel.account_id', DB::raw('SUM(COALESCE(jel.debit,0)) - SUM(COALESCE(jel.credit,0)) as balance'))
                ->groupBy('jel.account_id')
                ->pluck('balance', 'account_id')
                ->all();
        }

        // Fee balances for mapped revenue accounts (cached)
        $feeBalances = [];
        try {
            $feeRevenue = FinanceFeeService::mappedRevenue('2000-01-01', now()->toDateString());
            foreach ($feeRevenue as $fee) {
                $feeBalances[$fee->account_id] = (float) $fee->total_amount;
            }
        } catch (\Exception $e) {}

        // Attach balance + child count to each account
        $accounts->getCollection()->transform(function ($account) use ($childCounts, $parentJeBalances, $standaloneJeBalances, $feeBalances) {
            $childCount = $childCounts[$account->id] ?? 0;
            $account->child_count = $childCount;

            if ($childCount > 0) {
                $raw = (float) ($parentJeBalances[$account->id] ?? 0);
                $fee = $feeBalances[$account->id] ?? 0;
                $account->balance = $account->normal_balance === 'credit' ? -$raw + $fee : $raw + $fee;
            } else {
                $raw = (float) ($standaloneJeBalances[$account->id] ?? 0);
                $fee = $feeBalances[$account->id] ?? 0;
                $account->balance = $account->normal_balance === 'credit' ? -$raw + $fee : $raw + $fee;
            }

            return $account;
        });

        // Parent accounts for modal dropdown — top-level only
        $parentAccounts = ChartOfAccount::whereNull('parent_id')
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name']);

        $accountTypes = ['asset', 'liability', 'equity', 'revenue', 'expense'];

        return view('pages.gl.accounts.index', compact('accounts', 'parentAccounts', 'accountTypes'));
    }

    /**
     * AJAX: return children rows for a parent account.
     */
    public function children($id)
    {
        $parent = ChartOfAccount::findOrFail($id);

        $children = ChartOfAccount::where('parent_id', $id)
            ->orderBy('account_code')
            ->get();

        $childIds = $children->pluck('id')->all();

        // JE balances for children
        $jeBalances = [];
        if (!empty($childIds)) {
            $jeBalances = DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'jel.journal_entry_id', '=', 'je.id')
                ->where('je.status', 'posted')
                ->whereIn('jel.account_id', $childIds)
                ->select('jel.account_id', DB::raw('SUM(COALESCE(jel.debit,0)) - SUM(COALESCE(jel.credit,0)) as balance'))
                ->groupBy('jel.account_id')
                ->pluck('balance', 'account_id')
                ->all();
        }

        // Fee balances for children
        $feeBalances = [];
        try {
            $feeRevenue = FinanceFeeService::mappedRevenue('2000-01-01', now()->toDateString());
            foreach ($feeRevenue as $fee) {
                $feeBalances[$fee->account_id] = (float) $fee->total_amount;
            }
        } catch (\Exception $e) {}

        $children->transform(function ($child) use ($jeBalances, $feeBalances) {
            $raw = (float) ($jeBalances[$child->id] ?? 0);
            $fee = $feeBalances[$child->id] ?? 0;
            $child->balance = $child->normal_balance === 'credit' ? -$raw + $fee : $raw + $fee;
            return $child;
        });

        return view('pages.gl.accounts.partials.children-rows', compact('children', 'parent'));
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
