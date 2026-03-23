<?php

namespace App\Services;

use App\Models\Budget;

class BudgetValidator
{
    /**
     * Validate if a budget has sufficient remaining funds for the given amount.
     *
     * @return array{available: float, is_over: bool, warning: string|null}
     */
    public function validate(Budget $budget, float $amount): array
    {
        $available = (float) $budget->annual_budget - (float) $budget->committed - (float) $budget->actual;

        $isOver = $amount > $available;

        $warning = null;

        if ($isOver) {
            $over = $amount - $available;
            $warning = "Budget exceeded by PHP " . number_format($over, 2)
                . ". Available: PHP " . number_format($available, 2)
                . ", Requested: PHP " . number_format($amount, 2);
        } elseif ($available > 0 && ($amount / $available) > 0.8) {
            $warning = "This request will consume " . number_format(($amount / $available) * 100, 1)
                . "% of the remaining budget (PHP " . number_format($available, 2) . ").";
        }

        return [
            'available' => $available,
            'is_over' => $isOver,
            'warning' => $warning,
        ];
    }
}
