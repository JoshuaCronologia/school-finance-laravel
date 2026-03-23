<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArCollection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'receipt_number',
        'collection_date',
        'customer_id',
        'payment_method',
        'bank_account',
        'check_number',
        'reference_number',
        'amount_received',
        'applied_amount',
        'unapplied_amount',
        'journal_entry_id',
        'collected_by',
        'status',
        'remarks',
    ];

    protected $casts = [
        'collection_date' => 'date',
        'amount_received' => 'decimal:2',
        'applied_amount' => 'decimal:2',
        'unapplied_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ArCollectionAllocation::class, 'collection_id');
    }

    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(
            ArInvoice::class,
            ArCollectionAllocation::class,
            'collection_id',
            'id',
            'id',
            'invoice_id'
        );
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
