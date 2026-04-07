<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Employee model — lives on branch SIS databases.
 *
 * Usage: Employee::on('main_kto12')->find($id)
 *
 * The connection is NOT hardcoded; it is set dynamically
 * by the controller's branchConnection() helper.
 */
class Employee extends Model
{
    protected $table = 'employee_db';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * Get the employee's full name.
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->firstname ?? $this->fname ?? $this->first_name ?? '',
            $this->middlename ?? $this->mname ?? $this->middle_name ?? '',
            $this->lastname ?? $this->lname ?? $this->last_name ?? '',
            $this->ext_name ?? $this->suffix ?? '',
        ]);

        return implode(' ', $parts);
    }
}
