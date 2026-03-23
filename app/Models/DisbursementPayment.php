<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisbursementPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'disbursement_id',
        'voucher_number',
        'payment_date',
        'payment_method',
        'bank_account',
        'check_number',
        'reference_number',
        'gross_amount',
        'withholding_tax',
        'net_amount',
        'status',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'gross_amount' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function disbursement(): BelongsTo
    {
        return $this->belongsTo(DisbursementRequest::class, 'disbursement_id');
    }
}
