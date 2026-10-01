<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const ROLE_SUPER_ADMIN = 'Super Admin';
    public const ROLE_INSTITUTE_ADMIN = 'Institute Admin';

    private ?bool $superAdminCache = null;

    /**
    
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile_no',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    
    public function isGlobalUser(): bool
    {
        return $this->institute_id === null;
    }

    
    public function isSuperAdmin(): bool
    {
        if ($this->institute_id !== null) {
            return false;
        }

        return $this->superAdminCache ??= DB::table(config('permission.table_names.model_has_roles') . ' as mhr')
            ->join('roles', 'roles.id', '=', 'mhr.role_id')
            ->where('mhr.model_id', $this->getKey())
            ->where('mhr.model_type', $this->getMorphClass())
            ->where('roles.name', self::ROLE_SUPER_ADMIN)
            ->whereNull('roles.institute_id')
            ->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForInstitute($query, int $instituteId)
    {
        return $query->where('institute_id', $instituteId);
    }
}