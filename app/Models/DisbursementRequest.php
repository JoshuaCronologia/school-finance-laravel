<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class DisbursementRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_number',
        'request_date',
        'due_date',
        'payee_type',
        'payee_id',
        'payee_name',
        'department_id',
        'category_id',
        'cost_center_id',
        'project',
        'amount',
        'payment_method',
        'description',
        'budget_id',
        'requested_by',
        'status',
        'attachments',
    ];

    protected $casts = [
        'request_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DisbursementItem::class, 'disbursement_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(DisbursementApproval::class, 'disbursement_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(DisbursementPayment::class, 'disbursement_id');
    }

    /**
     * Polymorphic payee — returns the Vendor (or other payee model) if payee_type is set.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'payee_id');
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }
}
