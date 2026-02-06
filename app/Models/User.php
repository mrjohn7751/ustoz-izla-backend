<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',        // ✅ QO'SHILDI
        'password',
        'role',
        'is_active',    // ✅ QO'SHILDI
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'is_active' => 'boolean',  // ✅ QO'SHILDI
        ];
    }

    /**
     * Relationships
     */
    public function ustoz()
    {
        return $this->hasOne(Ustoz::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeUstozlar($query)
    {
        return $query->where('role', 'ustoz');
    }

    public function scopeFanlar($query)
    {
        return $query->where('role', 'fan');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Helper Methods
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isUstoz()
    {
        return $this->role === 'ustoz';
    }

    public function isFan()
    {
        return $this->role === 'fan';
    }

    /**
     * Check if user has ustoz profile
     */
    public function hasUstozProfile()
    {
        return $this->ustoz !== null;
    }

    /**
     * Get user's full name (from ustoz if exists)
     */
    public function getFullNameAttribute()
    {
        if ($this->hasUstozProfile()) {
            return $this->ustoz->full_name;
        }
        return $this->name;
    }
}
