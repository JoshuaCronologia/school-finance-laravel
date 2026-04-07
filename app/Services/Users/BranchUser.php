<?php

namespace App\Services\Users;

use App\Traits\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;

class BranchUser extends Model
{
    use HasUuids, HasPermissions;

    protected $table = 'branch_users';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guard_name = 'web';

    protected $fillable = [
        'parent_id',
        'parent_type',
        'branch_code',
        'name',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function isEmployee(): bool
    {
        return $this->parent_type === \App\Models\Employee::class;
    }

    public function isStudent(): bool
    {
        return $this->parent_type === \App\Models\Student::class;
    }

    public function isAdmin(): bool
    {
        return $this->parent_type === User::class;
    }

    /**
     * Get the SIS parent record from the branch database.
     */
    public function getSisRecord(string $connection)
    {
        if ($this->isEmployee()) {
            return \App\Models\Employee::on($connection)->find($this->parent_id);
        }
        if ($this->isStudent()) {
            return \App\Models\Student::on($connection)->find($this->parent_id);
        }
        return null;
    }

    /**
     * Get the branch database connection name.
     */
    public function branchConnection(string $platform = 'kto12'): string
    {
        return strtolower($this->branch_code) . '_' . strtolower($platform);
    }
}
