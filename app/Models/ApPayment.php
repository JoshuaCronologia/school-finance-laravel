<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_number',
        'payment_date',
        'vendor_id',
        'payment_method',
        'bank_account',
        'check_number',
        'check_date',
        'reference_number',
        'gross_amount',
        'discount_amount',
        'withholding_tax',
        'net_amount',
        'journal_entry_id',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'check_date' => 'date',
        'gross_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ApPaymentAllocation::class, 'payment_id');
    }

    public function bills(): HasManyThrough
    {
        return $this->hasManyThrough(
            ApBill::class,
            ApPaymentAllocation::class,
            'payment_id',
            'id',
            'id',
            'bill_id'
        );
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
