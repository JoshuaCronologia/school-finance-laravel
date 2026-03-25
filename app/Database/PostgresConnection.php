<?php

namespace App\Database;

use Illuminate\Database\PostgresConnection as BasePostgresConnection;

class PostgresConnection extends BasePostgresConnection
{
    /**
     * Prepare the query bindings for execution.
     *
     * With ATTR_EMULATE_PREPARES enabled (required for Supabase pooler),
     * PDO interpolates values into the SQL string. PHP true becomes 1,
     * which PostgreSQL rejects for boolean columns.
     *
     * Fix: convert booleans to string 'true'/'false' which PostgreSQL
     * can implicitly cast from text to boolean.
     */
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = $value ? 'true' : 'false';
            }
        }

        return $bindings;
    }
}
