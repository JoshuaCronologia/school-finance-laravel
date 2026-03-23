<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumberingSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'module',
        'prefix',
        'current_number',
        'pad_length',
    ];

    protected $casts = [
        'current_number' => 'integer',
        'pad_length' => 'integer',
    ];

    public static function generate(string $module): string
    {
        $sequence = static::where('module', $module)->lockForUpdate()->firstOrFail();

        $sequence->increment('current_number');

        $year = now()->year;
        $number = str_pad($sequence->current_number, $sequence->pad_length, '0', STR_PAD_LEFT);

        return "{$sequence->prefix}-{$year}-{$number}";
    }
}
