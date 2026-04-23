<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\BudgetAllocation;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\ExpenseCategory;
use App\Models\FundSource;
use App\Services\AuditService;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    /**
     * Budget dashboard with overall stats and utilization.
     */
    public function dashboard(Request $request)
    {
        $deptFilter = $request->input('department_id');
        $cacheKey = 'budget:dashboard:' . ($deptFilter ?: 'all');

        $data = Cache::remember($cacheKey, 300, function () use ($deptFilter) {
            $query = Budget::with('department', 'category');

            if ($deptFilter) {
                $query->where('department_id', $deptFilter);
            }

            $budgets = $query->get();

            $totalBudget = $budgets->sum('annual_budget');
            $totalCommitted = $budgets->sum('committed');
            $totalActual = $budgets->sum('actual');
            $totalRemaining = $totalBudget - $totalCommitted - $totalActual;

            $utilizationRate = $totalBudget > 0 ? (($totalCommitted + $totalActual) / $totalBudget) * 100 : 0;

            $budgetsByDepartment = $budgets->groupBy('department_id')->map(function ($group) {
                return [
                    'department' => $group->first()->department,
                    'budget' => $group->sum('annual_budget'),
                    'committed' => $group->sum('committed'),
                    'actual' => $group->sum('actual'),
                    'remaining' => $group->sum('annual_budget') - $group->sum('committed') - $group->sum('actual'),
                ];
            })->values();

            $deptLabels = $budgetsByDepartment->map(function ($d) { return $d['department']->name ?? 'Unassigned'; });
            $deptDatasets = [
                ['label' => 'Budget',    'data' => $budgetsByDepartment->pluck('budget')->map(function ($v) { return (float) $v; })],
                ['label' => 'Actual',    'data' => $budgetsByDepartment->pluck('actual')->map(function ($v) { return (float) $v; })],
                ['label' => 'Committed', 'data' => $budgetsByDepartment->pluck('committed')->map(function ($v) { return (float) $v; })],
            ];

            $utilizationLabels = ['Actual Spent', 'Committed', 'Remaining'];
            $utilizationValues = [(float) $totalActual, (float) $totalCommitted, (float) max($totalRemaining, 0)];

            // Income YTD by account (for variance table)
            $incomeAccounts = DB::select("
                SELECT coa.account_name,
                    COALESCE(SUM(jel.credit - jel.debit), 0) as actual
                FROM chart_of_accounts coa
                LEFT JOIN journal_entry_lines jel ON jel.account_id = coa.id
                LEFT JOIN journal_entries je ON jel.journal_entry_id = je.id AND je.status = 'posted'
                WHERE coa.account_type = 'revenue'
                GROUP BY coa.id, coa.account_name
                HAVING COALESCE(SUM(jel.credit - jel.debit), 0) != 0
                ORDER BY actual DESC
            ");

            // Expense YTD by account
            $expenseAccounts = DB::select("
                SELECT coa.account_name,
                    COALESCE(SUM(jel.debit - jel.credit), 0) as actual
                FROM chart_of_accounts coa
                LEFT JOIN journal_entry_lines jel ON jel.account_id = coa.id
                LEFT JOIN journal_entries je ON jel.journal_entry_id = je.id AND je.status = 'posted'
                WHERE coa.account_type = 'expense'
                GROUP BY coa.id, coa.account_name
                HAVING COALESCE(SUM(jel.debit - jel.credit), 0) != 0
                ORDER BY actual DESC
            ");

            $totalIncome = collect($incomeAccounts)->sum('actual');
            $totalExpenses = collect($expenseAccounts)->sum('actual');
            $grossProfit = $totalIncome;
            $netIncome = $totalIncome - $totalExpenses;

            // Income budget total (revenue categories)
            $incomeBudget = (float) $totalBudget; // simplified: total budget = income target

            return compact(
                'totalBudget', 'totalCommitted', 'totalActual', 'totalRemaining',
                'utilizationRate', 'budgetsByDepartment', 'budgets',
                'deptLabels', 'deptDatasets',
                'utilizationLabels', 'utilizationValues',
                'incomeAccounts', 'expenseAccounts',
                'totalIncome', 'totalExpenses', 'grossProfit', 'netIncome', 'incomeBudget'
            );
        });

        $departments = Cache::remember('departments:active', 600, function () {
            return Department::where('is_active', true)->orderBy('name')->get();
        });

        return view('pages.budget.dashboard', array_merge($data, compact('departments')));
    }

    /**
     * Budget planning list.
     */
    public function planning()
    {
        $budgets = Budget::with('department', 'category', 'costCenter', 'fundSource')
            ->orderBy('school_year', 'desc')
            ->orderBy('department_id')
            ->paginate(25);

        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $costCenters = CostCenter::where('is_active', true)->get();
        $fundSources = FundSource::where('is_active', true)->get();

        // Ensure remaining is calculated for each budget
        $budgets->getCollection()->transform(function ($budget) {
            $budget->remaining = $budget->annual_budget - $budget->committed - $budget->actual;
            return $budget;
        });

        return view('pages.budget.planning', compact(
            'budgets', 'departments', 'categories', 'costCenters', 'fundSources'
        ));
    }

    /**
     * Create a new budget line.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'budget_name' => 'required|string|max:255',
            'school_year' => 'required|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'category_id' => 'required|exists:expense_categories,id',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'fund_source_id' => 'nullable|exists:fund_sources,id',
            'project' => 'nullable|string|max:100',
            'annual_budget' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'draft';
        $validated['committed'] = 0;
        $validated['actual'] = 0;

        $budget = Budget::create($validated);

        app(AuditService::class)->log('create', 'budget', $budget, null, 'Budget created');
        NotificationService::budgetCreated($budget);

        return redirect()->route('budget.planning')->with('success', 'Budget created successfully.');
    }

    /**
     * Update a budget line.
     */
    public function update(Request $request, Budget $budget)
    {
        // Lock approved budgets — cannot edit core fields once approved
        if (in_array($budget->status, ['approved', 'active'])) {
            return back()->with('error', 'Approved budgets cannot be edited. Only draft budgets can be modified.');
        }

        $validated = $request->validate([
            'budget_name' => 'required|string|max:255',
            'school_year' => 'required|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'category_id' => 'required|exists:expense_categories,id',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'fund_source_id' => 'nullable|exists:fund_sources,id',
            'project' => 'nullable|string|max:100',
            'annual_budget' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $oldValues = $budget->toArray();
        $budget->update($validated);

        app(AuditService::class)->log('update', 'budget', $budget, $oldValues, 'Budget updated');

        return redirect()->route('budget.planning')->with('success', 'Budget updated successfully.');
    }

    /**
     * Find matching budget by department + category for a disbursement form.
     * Returns id, budget, committed, actual, remaining — used to auto-link budget_id.
     */
    public function checkBudget(Request $request)
    {
        $deptId = $request->input('department_id');
        $catId = $request->input('category_id');

        if (!$deptId || !$catId) {
            return response()->json(['loaded' => false]);
        }

        $budget = Budget::where('department_id', $deptId)
            ->where('category_id', $catId)
            ->whereIn('status', ['draft', 'approved', 'active'])
            ->orderByDesc('school_year')
            ->first();

        if (!$budget) {
            return response()->json(['loaded' => false]);
        }

        $remaining = (float) $budget->annual_budget - (float) $budget->committed - (float) $budget->actual;

        return response()->json([
            'id' => $budget->id,
            'budget' => (float) $budget->annual_budget,
            'committed' => (float) $budget->committed,
            'actual' => (float) $budget->actual,
            'remaining' => $remaining,
        ]);
    }

    /**
     * Show budget detail with list of disbursements using this budget.
     */
    public function show(Budget $plan)
    {
        $budget = $plan;
        $budget->load('department', 'category', 'costCenter', 'allocations');

        // Get all disbursements that used this budget
        $disbursements = \App\Models\DisbursementRequest::with('department', 'category', 'payment')
            ->where('budget_id', $budget->id)
            ->orderByDesc('request_date')
            ->get();

        $approvedTotal = $disbursements->where('status', 'approved')->sum('amount');
        $paidTotal = $disbursements->where('status', 'paid')->sum('amount');
        $pendingTotal = $disbursements->whereIn('status', ['draft', 'pending_approval'])->sum('amount');

        $available = (float) $budget->annual_budget - (float) $budget->committed - (float) $budget->actual;

        return view('pages.budget.show', compact(
            'budget', 'disbursements', 'approvedTotal', 'paidTotal', 'pendingTotal', 'available'
        ));
    }

    /**
     * Approve a draft budget — locks it from further editing.
     */
    public function approve(Budget $plan)
    {
        if ($plan->status !== 'draft') {
            return back()->with('error', 'Only draft budgets can be approved.');
        }

        $plan->update(['status' => 'approved']);

        app(AuditService::class)->log('approve', 'budget', $plan, null, 'Budget approved');

        return back()->with('success', "Budget '{$plan->budget_name}' approved and locked.");
    }

    /**
     * Revert approved budget back to draft.
     */
    public function revertToDraft(Budget $plan)
    {
        if ($plan->status === 'draft') {
            return back()->with('error', 'Budget is already in draft status.');
        }

        if ($plan->actual > 0 || $plan->committed > 0) {
            return back()->with('error', 'Cannot revert budget with existing commitments or actual spending.');
        }

        $plan->update(['status' => 'draft']);

        app(AuditService::class)->log('revert', 'budget', $plan, null, 'Budget reverted to draft');

        return back()->with('success', "Budget '{$plan->budget_name}' reverted to draft.");
    }

    /**
     * Delete a budget if no committed or actual spending.
     */
    public function destroy(Budget $budget)
    {
        if ($budget->committed > 0 || $budget->actual > 0) {
            return back()->with('error', 'Cannot delete budget with committed or actual spending.');
        }

        $budget->allocations()->delete();
        $budget->delete();

        return redirect()->route('budget.planning')->with('success', 'Budget deleted successfully.');
    }

    /**
     * Budget allocation view with monthly breakdown.
     */
    public function allocation()
    {
        $budgets = Budget::with(['department', 'category', 'allocations'])
            ->whereIn('status', ['draft', 'approved'])
            ->orderBy('department_id')
            ->get()
            ->map(function ($budget) {
                $monthly = [];
                for ($m = 1; $m <= 12; $m++) {
                    $alloc = $budget->allocations->firstWhere('month', $m);
                    $monthly[$m] = $alloc ? (float) $alloc->amount : 0;
                }
                $budget->monthly = $monthly;
                return $budget;
            });

        return view('pages.budget.allocation', compact('budgets'));
    }

    /**
     * Update monthly allocation amounts (AJAX).
     */
    public function updateAllocation(Request $request)
    {
        $validated = $request->validate([
            'budget_id' => 'required|exists:budgets,id',
            'month' => 'required|integer|min:1|max:12',
            'amount' => 'required|numeric|min:0',
        ]);

        // Lock approved budget allocations
        $budget = Budget::find($validated['budget_id']);
        if ($budget && in_array($budget->status, ['approved', 'active'])) {
            return response()->json([
                'success' => false,
                'message' => 'Approved budgets cannot be modified.',
            ], 403);
        }

        $allocation = BudgetAllocation::updateOrCreate(
            ['budget_id' => $validated['budget_id'], 'month' => $validated['month']],
            ['amount' => $validated['amount']]
        );

        return response()->json([
            'success' => true,
            'allocation' => $allocation,
            'message' => 'Allocation updated.',
        ]);
    }

    /**
     * Copy budgets from the previous school year.
     */
    public function copyFromPreviousYear(Request $request)
    {
        $validated = $request->validate([
            'source_year' => 'required|string|max:20',
            'target_year' => 'required|string|max:20',
            'adjust_percentage' => 'nullable|numeric|min:-100|max:100',
        ]);

        $sourceBudgets = Budget::where('school_year', $validated['source_year'])->get();

        if ($sourceBudgets->isEmpty()) {
            return back()->with('error', 'No budgets found for source school year.');
        }

        $adjustment = 1 + (($validated['adjust_percentage'] ?? 0) / 100);
        $count = 0;

        DB::transaction(function () use ($sourceBudgets, $validated, $adjustment, &$count) {
            foreach ($sourceBudgets as $source) {
                $exists = Budget::where('school_year', $validated['target_year'])
                    ->where('department_id', $source->department_id)
                    ->where('category_id', $source->category_id)
                    ->exists();

                if (!$exists) {
                    Budget::create([
                        'budget_name' => $source->budget_name,
                        'school_year' => $validated['target_year'],
                        'department_id' => $source->department_id,
                        'category_id' => $source->category_id,
                        'cost_center_id' => $source->cost_center_id,
                        'fund_source_id' => $source->fund_source_id,
                        'project' => $source->project,
                        'annual_budget' => round($source->annual_budget * $adjustment, 2),
                        'committed' => 0,
                        'actual' => 0,
                        'status' => 'draft',
                        'notes' => "Copied from {$validated['source_year']}",
                    ]);
                    $count++;
                }
            }
        });

        return back()->with('success', "{$count} budgets copied from {$validated['source_year']} to {$validated['target_year']}.");
    }

    /**
     * Export budgets to CSV.
     */
    public function export()
    {
        (new AuditService)->logActivity('exported', 'budget', 'Exported budget data');

        $budgets = Budget::with('department', 'category', 'costCenter', 'fundSource')
            ->orderBy('school_year', 'desc')
            ->orderBy('department_id')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="budgets-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($budgets) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'School Year', 'Department', 'Category', 'Budget Name',
                'Cost Center', 'Fund Source', 'Project',
                'Annual Budget', 'Committed', 'Actual', 'Remaining', 'Status',
            ]);

            foreach ($budgets as $b) {
                fputcsv($file, [
                    $b->school_year,
                    optional($b->department)->name ?? '',
                    optional($b->category)->name ?? '',
                    $b->budget_name,
                    optional($b->costCenter)->name ?? '',
                    optional($b->fundSource)->name ?? '',
                    $b->project ?? '',
                    $b->annual_budget,
                    $b->committed,
                    $b->actual,
                    $b->annual_budget - $b->committed - $b->actual,
                    $b->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Budget vs Actual report as PDF.
     */
    public function budgetVsActualPdf(Request $request)
    {
        $departmentId = $request->input('department_id');
        $department = $departmentId ? Department::find($departmentId) : null;

        $query = Budget::with('department', 'category')->whereNotIn('status', ['cancelled']);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $budgets = $query->orderBy('department_id')->orderBy('budget_name')->get()
            ->map(function ($b) {
                $b->remaining = $b->annual_budget - $b->committed - $b->actual;
                $b->variance = $b->annual_budget - $b->actual;
                $b->variance_pct = $b->annual_budget > 0
                    ? (($b->annual_budget - $b->actual) / $b->annual_budget) * 100 : 0;
                $b->department_name = $b->department->name ?? null;
                $b->category_name = $b->category->name ?? null;
                return $b;
            });

        $summary = [
            'total_budget'       => $budgets->sum('annual_budget'),
            'total_committed'    => $budgets->sum('committed'),
            'total_actual'       => $budgets->sum('actual'),
            'total_remaining'    => $budgets->sum('remaining'),
            'total_variance'     => $budgets->sum('variance'),
        ];
        $summary['overall_utilization'] = $summary['total_budget'] > 0
            ? (($summary['total_committed'] + $summary['total_actual']) / $summary['total_budget']) * 100 : 0;

        $data = [
            'budgets'        => $budgets,
            'summary'        => $summary,
            'departmentName' => $department->name ?? null,
            'schoolYear'     => $budgets->first()->school_year ?? now()->format('Y'),
            'generatedAt'    => now()->format('F d, Y h:i A'),
        ];

        $pdf = Pdf::loadView('pages.budget.pdf-budget-vs-actual', $data)
            ->setPaper('legal', 'landscape');

        $filename = 'Budget-vs-Actual';
        if ($department) {
            $filename .= '-' . str_replace(' ', '_', $department->name);
        }
        $filename .= '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    // Alias methods for route compatibility
    public function storePlan(Request $request) { return $this->store($request); }
    public function updatePlan(Request $request, Budget $plan) { return $this->update($request, $plan); }
    public function storeAllocation(Request $request) { return $this->updateAllocation($request); }
}
