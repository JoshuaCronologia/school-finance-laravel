<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Position model — lives on branch K-12 databases.
 *
 * Usage: Position::on('pcc_kto12')->find($id)
 */
class Position extends Model
{
    protected $table = 'positions';

    protected $guarded = [];

    public $timestamps = false;
}
