<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_code',
        'name',
        'vendor_type',
        'contact_person',
        'phone',
        'email',
        'address',
        'tin',
        'vat_type',
        'withholding_tax_type',
        'payment_terms_id',
        'credit_limit',
        'bank_name',
        'account_name',
        'account_number',
        'default_ap_account_id',
        'default_expense_account_id',
        'is_active',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function paymentTerms(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_terms_id');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(ApBill::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ApPayment::class);
    }

    public function defaultAPAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_ap_account_id');
    }

    public function defaultExpenseAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'default_expense_account_id');
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return (float) $this->bills()
            ->whereNotIn('status', ['cancelled', 'voided', 'paid'])
            ->sum('balance');
    }
}
