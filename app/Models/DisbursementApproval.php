<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisbursementApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'disbursement_id',
        'approver_role',
        'approver_name',
        'action',
        'comments',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function disbursement(): BelongsTo
    {
        return $this->belongsTo(DisbursementRequest::class, 'disbursement_id');
    }
}
