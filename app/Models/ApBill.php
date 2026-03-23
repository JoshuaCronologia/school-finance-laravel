<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApBill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bill_number',
        'bill_date',
        'posting_date',
        'due_date',
        'vendor_id',
        'campus_id',
        'department_id',
        'cost_center_id',
        'category_id',
        'payment_terms_id',
        'reference_number',
        'description',
        'gross_amount',
        'vat_amount',
        'withholding_tax',
        'net_payable',
        'amount_paid',
        'balance',
        'journal_entry_id',
        'status',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'posting_date' => 'date',
        'due_date' => 'date',
        'gross_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ApBillLine::class, 'bill_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ApPaymentAllocation::class, 'bill_id');
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(
            ApPayment::class,
            ApPaymentAllocation::class,
            'bill_id',
            'id',
            'id',
            'payment_id'
        );
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function paymentTerms(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_terms_id');
    }
}
