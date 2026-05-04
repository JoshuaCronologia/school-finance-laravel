<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPerformanceIndexesToFinanceDb extends Migration
{
    private $indexes = [
        // transaction_batch: date range + void filter used in every cashier report
        ['transaction_batch',             'idx_tb_date_paid',   'date_paid'],
        ['transaction_batch',             'idx_tb_voided_date', 'voided_by, date_paid'],
        // transaction_fees_distribution: join key from every fee-level report
        ['transaction_fees_distribution', 'idx_tfd_batch_id',  'transaction_batch_id'],
        ['transaction_fees_distribution', 'idx_tfd_fee_id',    'fee_id'],
        // transaction_payments: pivot subquery in summary of collection
        ['transaction_payments',          'idx_tp_batch_id',   'transaction_batch_id'],
        // enrollment_batch: joined in fee list report grouped by student + SY
        ['enrollment_batch',              'idx_eb_student_sy', 'student_id, acad_sy_school_year_id'],
    ];

    public function up()
    {
        foreach ($this->indexes as $idx) {
            list($table, $name, $columns) = $idx;
            try {
                DB::connection('finance')->statement(
                    "CREATE INDEX `{$name}` ON `{$table}` ({$columns})"
                );
            } catch (\Exception $e) {
                // Index already exists or table not found — skip
            }
        }
    }

    public function down()
    {
        foreach ($this->indexes as $idx) {
            list($table, $name) = $idx;
            try {
                DB::connection('finance')->statement(
                    "DROP INDEX `{$name}` ON `{$table}`"
                );
            } catch (\Exception $e) {}
        }
    }
}
