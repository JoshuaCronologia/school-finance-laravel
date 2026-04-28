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
     * Pass $table and $column to enable self-healing: if the generated number
     * already exists in that table, the counter keeps incrementing until a
     * free number is found. Prevents duplicate-key errors when the sequence
     * counter falls behind the actual data (e.g. after a DB restore/migration).
     *
     * @param string $module  Module code (e.g., 'DR', 'JE', 'BILL')
     * @param string|null $table   Table to check for existing numbers
     * @param string|null $column  Column to check (default: same as table's number column)
     * @return string Formatted number like "DR-0014"
     */
    public static function generate(string $module, string $table = null, string $column = null): string
    {
        return DB::transaction(function () use ($module, $table, $column) {
            $sequence = NumberingSequence::where('module', $module)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                $sequence = NumberingSequence::create([
                    'module'         => $module,
                    'prefix'         => $module,
                    'current_number' => 0,
                    'pad_length'     => 4,
                ]);
                $sequence = NumberingSequence::where('module', $module)
                    ->lockForUpdate()
                    ->first();
            }

            $maxAttempts = 100;
            $attempt = 0;

            do {
                $sequence->increment('current_number');
                $sequence->refresh();

                $number    = str_pad($sequence->current_number, $sequence->pad_length, '0', STR_PAD_LEFT);
                $generated = "{$sequence->prefix}-{$number}";

                // If caller provided a table, skip numbers already in use
                $exists = $table && $column
                    ? DB::table($table)->where($column, $generated)->exists()
                    : false;

                $attempt++;
            } while ($exists && $attempt < $maxAttempts);

            return $generated;
        });
    }
}
