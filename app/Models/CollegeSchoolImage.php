<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * CollegeSchoolImage model — lives on branch College databases.
 *
 * Usage: CollegeSchoolImage::on('pcc_college')->where('image_type', 2)->first()
 */
class CollegeSchoolImage extends Model
{
    protected $table = 'school_images';

    protected $guarded = [];

    public $timestamps = false;
}
