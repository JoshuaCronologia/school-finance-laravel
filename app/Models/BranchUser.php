<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
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

    // ─── Helpers ────────────────────────────────────────────────

    public function isEmployee(): bool
    {
        return $this->parent_type === Employee::class;
    }

    public function isStudent(): bool
    {
        return $this->parent_type === Student::class;
    }

    public function isAdmin(): bool
    {
        return $this->parent_type === User::class;
    }

    /**
     * Get the SIS parent record (Employee or Student) from the branch database.
     */
    public function getSisRecord(string $connection)
    {
        if ($this->isEmployee()) {
            return Employee::on($connection)->find($this->parent_id);
        }
        if ($this->isStudent()) {
            return Student::on($connection)->find($this->parent_id);
        }
        return null;
    }

    /**
     * Get the branch database connection name.
     * e.g., 'main_kto12' or 'pcc_college'
     */
    public function branchConnection(string $platform = 'kto12'): string
    {
        return strtolower($this->branch_code) . '_' . strtolower($platform);
    }
}
