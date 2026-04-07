<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SchoolInfo model — lives on branch K-12 databases.
 *
 * Usage: SchoolInfo::on('pcc_kto12')->first()
 */
class SchoolInfo extends Model
{
    protected $table = 'school_info_db';

    protected $guarded = [];

    public $timestamps = false;
}
