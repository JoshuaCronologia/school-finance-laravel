<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArCreditMemo extends Model
{
    use HasFactory;

    protected $fillable = [
        'memo_number',
        'memo_date',
        'customer_id',
        'invoice_id',
        'reason',
        'amount',
        'journal_entry_id',
        'status',
    ];

    protected $casts = [
        'memo_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ArInvoice::class, 'invoice_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
