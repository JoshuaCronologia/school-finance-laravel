<?php

namespace App\Services;

use App\Models\NumberingSequence;
use Illuminate\Support\Facades\DB;

class NumberingService
{
    /**
     * Generate the next sequential number for a given module.
     * Uses database locking to prevent duplicate numbers.
     *
     * @param string $module Module code (e.g., 'DR', 'BILL', 'INV', 'JE', 'PAY', 'CR', 'PV')
     * @return string Formatted number like "DR-0001"
     */
    public static function generate(string $module): string
    {
        return DB::transaction(function () use ($module) {
            $sequence = NumberingSequence::where('module', $module)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                // Auto-create sequence if it does not exist
                $sequence = NumberingSequence::create([
                    'module' => $module,
                    'prefix' => $module,
                    'current_number' => 0,
                    'pad_length' => 4,
                ]);
                $sequence = NumberingSequence::where('module', $module)
                    ->lockForUpdate()
                    ->first();
            }

            $sequence->increment('current_number');
            $sequence->refresh();

            $number = str_pad($sequence->current_number, $sequence->pad_length, '0', STR_PAD_LEFT);

            return "{$sequence->prefix}-{$number}";
        });
    }
}
