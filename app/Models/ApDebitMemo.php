<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApDebitMemo extends Model
{
    use HasFactory;

    protected $fillable = [
        'memo_number',
        'memo_date',
        'vendor_id',
        'bill_id',
        'reason',
        'amount',
        'journal_entry_id',
        'status',
    ];

    protected $casts = [
        'memo_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(ApBill::class, 'bill_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
