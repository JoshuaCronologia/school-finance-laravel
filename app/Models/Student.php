<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Student model — lives on branch SIS databases.
 *
 * Usage: Student::on('main_kto12')->find($id)
 */
class Student extends Model
{
    protected $table = 'students_db';

    protected $guarded = [];

    public $timestamps = false;

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
