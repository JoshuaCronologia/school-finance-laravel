<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * CollegeUser model — lives on branch College databases.
 *
 * Usage: CollegeUser::on('pcc_college')->where('email', $email)->get()
 */
class CollegeUser extends Model
{
    protected $table = 'users';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    public $timestamps = false;
}
