<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArCollectionAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'collection_id',
        'invoice_id',
        'amount_applied',
    ];

    protected $casts = [
        'amount_applied' => 'decimal:2',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(ArCollection::class, 'collection_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ArInvoice::class, 'invoice_id');
    }
}
