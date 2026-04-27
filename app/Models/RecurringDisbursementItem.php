<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringDisbursementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id', 'description', 'quantity', 'unit_cost', 'amount',
        'account_id', 'account_code', 'tax_code_id', 'tax_code', 'remarks',
    ];

    protected $casts = [
        'quantity'  => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'amount'    => 'decimal:2',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(RecurringDisbursementTemplate::class, 'template_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
