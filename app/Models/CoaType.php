<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoaType extends Model
{
    protected $table = 'coa_types';

    protected $fillable = ['label', 'base_type', 'classification', 'normal_balance', 'sort_order', 'is_system'];

    protected $casts = ['is_system' => 'boolean'];
}
