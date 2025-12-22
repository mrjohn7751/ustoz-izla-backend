<?php
// app/Models/Elon.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Elon extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'elonlar';

    protected $fillable = [
        'ustoz_id',
        'fan_id',
        'sarlavha',
        'tavsif',
        'narx',
        'joylashuv',
        'markaz_nomi',
        'dars_kunlari',
        'dars_vaqti',
        'rasm',
        'status',
        'badge',
        'chegirma_foiz',
        'views_count',
        'favorites_count',
        'rad_sababi',
    ];

    protected $casts = [
        'dars_kunlari' => 'array',
        'narx' => 'decimal:2',
        'views_count' => 'integer',
        'favorites_count' => 'integer',
    ];

    // Relationships
    public function ustoz()
    {
        return $this->belongsTo(Ustoz::class, 'ustoz_id');
    }

    public function fan()
    {
        return $this->belongsTo(Fan::class, 'fan_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
