<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'role',
        'organization',
        'host_name_en',
        'host_name_gu',
        'host_image_path',
        'is_active',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * The tenant (admin) that owns shared settings — message templates, the
     * host profile, etc. Members inherit these from their parent admin;
     * everyone else owns their own.
     */
    public function tenant(): User
    {
        return $this->isMember() && $this->parent_id ? ($this->parent ?? $this) : $this;
    }

    /** Public URL for the admin's host/organizer image, if any. */
    public function getHostImageUrlAttribute(): ?string
    {
        return $this->host_image_path ? '/file/' . $this->host_image_path : null;
    }

    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function members()
    {
        return $this->hasMany(User::class, 'parent_id')->where('role', 'member');
    }

    public function admins()
    {
        return $this->hasMany(User::class, 'parent_id')->where('role', 'admin');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
}
