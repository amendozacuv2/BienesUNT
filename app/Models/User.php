<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;
    use HasRoles;

    protected $fillable = [
        'uuid',
        'name',
        'username',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    public function userAreas()
    {
        return $this->hasMany(UserArea::class);
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class, 'user_area')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function activeAreas()
    {
        return $this->areas()->wherePivot('is_active', true);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLiveSearch($query, ?string $search)
    {
        $search = trim((string) $search);

        if (mb_strlen($search) < 2) {
            return $query;
        }

        return $query->where(function ($query) use ($search) {
            $query->whereRaw('LOWER(name) ILIKE ?', ['%' . mb_strtolower($search) . '%'])
                ->orWhereRaw('LOWER(username) ILIKE ?', ['%' . mb_strtolower($search) . '%']);
        });
    }
}
