<?php

namespace App\Database;

use Illuminate\Database\PostgresConnection as BasePostgresConnection;

class PostgresConnection extends BasePostgresConnection
{
    /**
     * Prepare the query bindings for execution.
     *
     * Override to keep booleans as native PHP booleans instead of casting to int,
     * which PostgreSQL rejects when comparing against boolean columns.
     */
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            }
            // Don't convert booleans to int — PostgreSQL needs native booleans
        }

        return $bindings;
    }
}
