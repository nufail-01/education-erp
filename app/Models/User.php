<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const ROLE_SUPER_ADMIN = 'Super Admin';
    public const ROLE_INSTITUTE_ADMIN = 'Institute Admin';

    /**
     * The attributes that are mass assignable.
     *
     * NOTE: institute_id aur status yahan intentionally nahi hain,
     * taaki form se mass-assignment karke koi institute ya status na badal sake.
     * Inhe controller/service mein explicitly set karenge.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile_no',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
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

    /**
     * Super Admin kisi institute ka nahi hota (institute_id = null).
     * Yahan role check nahi karte, taaki Spatie teams context par depend na kare.
     */
    public function isGlobalUser(): bool
    {
        return $this->institute_id === null;
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