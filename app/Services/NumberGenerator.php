<?php

namespace App\Services;

use App\Models\NumberingSequence;
use Illuminate\Support\Facades\DB;

class NumberGenerator
{
    /**
     * Generate the next sequential number for a given module.
     *
     * @param string $module Module code (e.g., 'DR', 'BILL', 'INV', 'JE', 'PAY', 'CR')
     * @return string Formatted number like "DR-2025-0001"
     */
    public static function generate(string $module): string
    {
        return DB::transaction(function () use ($module) {
            $sequence = NumberingSequence::where('module', $module)->lockForUpdate()->first();

            if ($sequence) {
                return $sequence::generate($module);
            }

            // Fallback: create a new sequence if it does not exist
            $sequence = NumberingSequence::create([
                'module' => $module,
                'prefix' => $module,
                'current_number' => 1,
                'pad_length' => 4,
            ]);

            $year = now()->year;
            $number = str_pad($sequence->current_number, $sequence->pad_length, '0', STR_PAD_LEFT);

            return "{$sequence->prefix}-{$year}-{$number}";
        });
    }
}
