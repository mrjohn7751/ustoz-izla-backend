<?php
// app/Models/Ustoz.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ustoz extends Model
{
    use HasFactory;

    protected $table = 'ustozlar';

    protected $fillable = [
        'user_id',
        'ism',
        'familiya',
        'telefon',
        'avatar',
        'bio',
        'joylashuv',
        'tajriba',
        'fanlar',
        'rating',
        'rating_count',
        'oquvchilar_soni',
        'sertifikatlar_soni',
        'is_verified',
        'status',
        'sertifikatlar',
    ];

    protected $casts = [
        'fanlar' => 'array',
        'sertifikatlar' => 'array',
        'rating' => 'decimal:2',
        'is_verified' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function elonlar()
    {
        return $this->hasMany(Elon::class, 'ustoz_id');
    }

    public function videos()
    {
        return $this->hasMany(Video::class, 'ustoz_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'ustoz_id');
    }

    // Helpers
    public function getFullNameAttribute()
    {
        return "{$this->ism} {$this->familiya}";
    }

    public function isVerified()
    {
        return $this->is_verified;
    }
}
