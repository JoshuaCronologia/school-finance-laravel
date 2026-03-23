<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_code',
        'account_name',
        'account_type',
        'parent_id',
        'normal_balance',
        'fs_group',
        'is_active',
        'is_postable',
        'campus_id',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_postable' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAssets(Builder $query): Builder
    {
        return $query->where('account_type', 'asset');
    }

    public function scopeLiabilities(Builder $query): Builder
    {
        return $query->where('account_type', 'liability');
    }

    public function scopeEquity(Builder $query): Builder
    {
        return $query->where('account_type', 'equity');
    }

    public function scopeRevenue(Builder $query): Builder
    {
        return $query->where('account_type', 'revenue');
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('account_type', 'expense');
    }

    public function scopePostable(Builder $query): Builder
    {
        return $query->where('is_postable', true);
    }
}
