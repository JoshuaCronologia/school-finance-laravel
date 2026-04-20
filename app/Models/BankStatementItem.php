<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStatementItem extends Model
{
    protected $fillable = [
        'bank_statement_id', 'transaction_date', 'description',
        'reference_number', 'debit', 'credit', 'running_balance',
        'is_matched', 'matched_check_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'running_balance' => 'decimal:2',
        'is_matched' => 'boolean',
    ];

    public function bankStatement(): BelongsTo
    {
        return $this->belongsTo(BankStatement::class);
    }

    public function matchedCheck(): BelongsTo
    {
        return $this->belongsTo(IssuedCheck::class, 'matched_check_id');
    }
}
