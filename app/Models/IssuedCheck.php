<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuedCheck extends Model
{
    protected $fillable = [
        'bank_account_id', 'check_date', 'check_number',
        'payee', 'amount', 'status', 'cleared_date',
        'disbursement_payment_id', 'remarks',
    ];

    protected $casts = [
        'check_date' => 'date',
        'cleared_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function disbursementPayment(): BelongsTo
    {
        return $this->belongsTo(DisbursementPayment::class);
    }
}
