<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArInvoiceLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'fee_code',
        'description',
        'quantity',
        'unit_amount',
        'amount',
        'revenue_account_id',
        'department_id',
        'project',
        'tax_code_id',
        'discount_type',
        'remarks',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_amount' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ArInvoice::class, 'invoice_id');
    }

    public function revenueAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'revenue_account_id');
    }

    public function taxCode(): BelongsTo
    {
        return $this->belongsTo(TaxCode::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
