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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    /**
     * Budget dashboard with overall stats and utilization.
     */
    public function dashboard()
    {
        $budgets = Budget::with('department', 'category')->get();

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

        $budgetsByCategory = $budgets->groupBy('category_id')->map(function ($group) {
            return [
                'category' => $group->first()->category,
                'budget' => $group->sum('annual_budget'),
                'actual' => $group->sum('actual'),
            ];
        })->values();

        return view('pages.budget.dashboard', compact(
            'totalBudget', 'totalCommitted', 'totalActual', 'totalRemaining',
            'utilizationRate', 'budgetsByDepartment', 'budgetsByCategory', 'budgets'
        ));
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

        $validated['status'] = 'active';
        $validated['committed'] = 0;
        $validated['actual'] = 0;

        $budget = Budget::create($validated);

        app(AuditService::class)->log('create', 'budget', $budget, null, 'Budget created');

        return redirect()->route('budget.planning')->with('success', 'Budget created successfully.');
    }

    /**
     * Update a budget line.
     */
    public function update(Request $request, Budget $budget)
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

        $oldValues = $budget->toArray();
        $budget->update($validated);

        app(AuditService::class)->log('update', 'budget', $budget, $oldValues, 'Budget updated');

        return redirect()->route('budget.planning')->with('success', 'Budget updated successfully.');
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
            ->where('status', 'active')
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
                    $b->department?->name ?? '',
                    $b->category?->name ?? '',
                    $b->budget_name,
                    $b->costCenter?->name ?? '',
                    $b->fundSource?->name ?? '',
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

    // Alias methods for route compatibility
    public function storePlan(Request $request) { return $this->store($request); }
    public function updatePlan(Request $request, Budget $plan) { return $this->update($request, $plan); }
    public function storeAllocation(Request $request) { return $this->updateAllocation($request); }
}
