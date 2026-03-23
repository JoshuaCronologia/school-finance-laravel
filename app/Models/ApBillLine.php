<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApBillLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'account_id',
        'description',
        'quantity',
        'unit_cost',
        'amount',
        'tax_code_id',
        'withholding_tax_code_id',
        'department_id',
        'project',
        'fund_source_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(ApBill::class, 'bill_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function taxCode(): BelongsTo
    {
        return $this->belongsTo(TaxCode::class);
    }

    public function withholdingTaxCode(): BelongsTo
    {
        return $this->belongsTo(TaxCode::class, 'withholding_tax_code_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class);
    }
}
