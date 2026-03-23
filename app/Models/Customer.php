<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_code',
        'customer_type',
        'name',
        'campus_id',
        'grade_level',
        'contact_person',
        'email',
        'phone',
        'billing_address',
        'tin',
        'default_ar_account_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(ArInvoice::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(ArCollection::class);
    }

    public function defaultARAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_ar_account_id');
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return (float) $this->invoices()
            ->whereNotIn('status', ['cancelled', 'voided', 'paid'])
            ->sum('balance');
    }
}
