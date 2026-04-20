<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    protected $fillable = [
        'bank_name', 'account_type', 'account_number',
        'account_label', 'chart_account_id', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function chartAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_account_id');
    }

    public function issuedChecks(): HasMany
    {
        return $this->hasMany(IssuedCheck::class);
    }

    public function bankStatements(): HasMany
    {
        return $this->hasMany(BankStatement::class);
    }

    public function getFullLabelAttribute(): string
    {
        return $this->bank_name . ' - ' . ($this->account_type === 'SA' ? 'Savings' : 'Checking') . ($this->account_number ? ' (' . $this->account_number . ')' : '');
    }
}
