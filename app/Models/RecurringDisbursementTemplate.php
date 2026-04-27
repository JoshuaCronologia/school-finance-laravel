<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringDisbursementTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_name', 'frequency', 'start_date', 'end_date', 'description',
        'payee_type', 'payee_id', 'payee_name',
        'department_id', 'category_id', 'cost_center_id', 'project', 'budget_id',
        'payment_method', 'amount', 'auto_create', 'is_active', 'last_generated_date',
    ];

    protected $casts = [
        'start_date'          => 'date',
        'end_date'            => 'date',
        'last_generated_date' => 'date',
        'auto_create'         => 'boolean',
        'is_active'           => 'boolean',
        'amount'              => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(RecurringDisbursementItem::class, 'template_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }
}
