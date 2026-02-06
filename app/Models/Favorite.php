<?php
// app/Models/Favorite.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'favoritable_type',
        'favoritable_id',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function favoritable()
    {
        return $this->morphTo();
    }

    /**
     * Get elon (backward compatibility)
     * Faqat Elon tipidagi favoritalar uchun
     */
    public function elon()
    {
        return $this->belongsTo(Elon::class, 'favoritable_id')
            ->where('favoritable_type', Elon::class);
    }
}
