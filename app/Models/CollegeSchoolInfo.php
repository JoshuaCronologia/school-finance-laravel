<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * CollegeSchoolInfo model — lives on branch College databases.
 *
 * Usage: CollegeSchoolInfo::on('pcc_college')->first()
 */
class CollegeSchoolInfo extends Model
{
    protected $table = 'school_infos';

    protected $guarded = [];

    public $timestamps = false;
}
