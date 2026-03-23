<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringJournalTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_name',
        'frequency',
        'start_date',
        'end_date',
        'description',
        'auto_create',
        'is_active',
        'last_generated_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'last_generated_date' => 'date',
        'auto_create' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(RecurringJournalLine::class, 'template_id');
    }
}
