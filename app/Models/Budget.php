<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_name',
        'school_year',
        'department_id',
        'category_id',
        'cost_center_id',
        'fund_source_id',
        'project',
        'campus',
        'annual_budget',
        'budget_owner',
        'status',
        'committed',
        'actual',
        'notes',
    ];

    protected $casts = [
        'annual_budget' => 'decimal:2',
        'committed' => 'decimal:2',
        'actual' => 'decimal:2',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(BudgetAllocation::class);
    }

    public function getRemainingAttribute(): float
    {
        return (float) ($this->annual_budget - $this->committed - $this->actual);
    }

    public function getUtilizationPercentAttribute(): float
    {
        if ($this->annual_budget == 0) {
            return 0.0;
        }

        return round(($this->actual / $this->annual_budget) * 100, 2);
    }
}
