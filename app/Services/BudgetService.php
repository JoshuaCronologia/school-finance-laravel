<?php

namespace App\Services;

use App\Models\Budget;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    /**
     * Check if a budget has sufficient funds for the requested amount.
     *
     * @return array{budget: Budget, committed: float, actual: float, remaining: float, requested: float, isOverBudget: bool}
     */
    public function checkBudget(int $budgetId, float $amount): array
    {
        $budget = Budget::findOrFail($budgetId);

        $remaining = (float) $budget->annual_budget - (float) $budget->committed - (float) $budget->actual;
        $isOverBudget = $amount > $remaining;

        return [
            'budget' => $budget,
            'committed' => (float) $budget->committed,
            'actual' => (float) $budget->actual,
            'remaining' => $remaining,
            'requested' => $amount,
            'isOverBudget' => $isOverBudget,
        ];
    }

    /**
     * Add amount to committed (when a disbursement is submitted/approved).
     */
    public function commitBudget(int $budgetId, float $amount)
    {
        DB::transaction(function () use ($budgetId, $amount) {
            $budget = Budget::lockForUpdate()->findOrFail($budgetId);
            $budget->increment('committed', $amount);
        });
    }

    /**
     * Release committed amount (when a disbursement is rejected/returned).
     */
    public function releaseCommitment(int $budgetId, float $amount)
    {
        DB::transaction(function () use ($budgetId, $amount) {
            $budget = Budget::lockForUpdate()->findOrFail($budgetId);
            $newCommitted = max(0, (float) $budget->committed - $amount);
            $budget->update(['committed' => $newCommitted]);
        });
    }

    /**
     * Move from committed to actual (when payment is processed).
     */
    public function recordActual(int $budgetId, float $amount)
    {
        DB::transaction(function () use ($budgetId, $amount) {
            $budget = Budget::lockForUpdate()->findOrFail($budgetId);
            $newCommitted = max(0, (float) $budget->committed - $amount);
            $budget->update([
                'committed' => $newCommitted,
                'actual' => DB::raw("actual + {$amount}"),
            ]);
        });
    }
}
