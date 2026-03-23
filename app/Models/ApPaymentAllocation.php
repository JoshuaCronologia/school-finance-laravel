<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApPaymentAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'bill_id',
        'amount_applied',
    ];

    protected $casts = [
        'amount_applied' => 'decimal:2',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(ApPayment::class, 'payment_id');
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(ApBill::class, 'bill_id');
    }
}
