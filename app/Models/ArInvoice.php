<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'posting_date',
        'due_date',
        'customer_id',
        'campus_id',
        'school_year',
        'semester',
        'billing_period',
        'reference_number',
        'description',
        'gross_amount',
        'discount_amount',
        'tax_amount',
        'net_receivable',
        'amount_paid',
        'balance',
        'journal_entry_id',
        'status',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'posting_date' => 'date',
        'due_date' => 'date',
        'gross_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'net_receivable' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ArInvoiceLine::class, 'invoice_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ArCollectionAllocation::class, 'invoice_id');
    }

    public function collections(): HasManyThrough
    {
        return $this->hasManyThrough(
            ArCollection::class,
            ArCollectionAllocation::class,
            'invoice_id',
            'id',
            'id',
            'collection_id'
        );
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
