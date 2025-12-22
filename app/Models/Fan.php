<?php
// app/Models/Fan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fan extends Model
{
    use HasFactory;

    protected $table = 'fanlar';

    protected $fillable = [
        'nomi',
        'kod',
        'rasm',
        'tavsif',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function elonlar()
    {
        return $this->hasMany(Elon::class, 'fan_id');
    }

    public function videos()
    {
        return $this->hasMany(Video::class, 'fan_id');
    }
}
