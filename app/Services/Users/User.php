<?php

namespace App\Services\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $keyType = 'string';

    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'fname',
        'mname',
        'lname',
        'ext_name',
        'email',
        'password',
        'phone',
        'department_id',
        'campus_id',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function setPasswordAttribute($value)
    {
        // Skip if already hashed (prevents double-hashing)
        if (strlen($value) === 60 && strpos($value, '$2y$') === 0) {
            $this->attributes['password'] = $value;
        } else {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    public function getKey()
    {
        return (string) parent::getKey();
    }

    public function getNameAttribute($value)
    {
        if ($value) return $value;
        return trim(sprintf('%s %s %s %s', $this->fname, $this->mname, $this->lname, $this->ext_name));
    }

    public function isAdmin(): bool
    {
        foreach ($this->roles as $role) {
            if ($role->name === Acl::ROLE_ADMIN) {
                return true;
            }
        }
        return false;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Campus::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(\App\Models\AuditLog::class);
    }
}
