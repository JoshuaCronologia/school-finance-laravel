<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * CollegeEmployee model — lives on branch College databases.
 *
 * Usage: CollegeEmployee::on('pcc_college')->find($id)
 */
class CollegeEmployee extends Model
{
    protected $table = 'employees';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    public $timestamps = false;

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->fname ?? '',
            $this->mname ?? '',
            $this->lname ?? '',
            $this->ext_name ?? '',
        ]);

        return implode(' ', $parts);
    }
}
