<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ustoz_id',
        'fan_id',
        'sarlavha',
        'tavsif',
        'video_url',
        'thumbnail',
        'davomiyligi',
        'views_count',
        'likes_count',
        'status',
        'rad_sababi',
    ];

    protected $casts = [
        'davomiyligi' => 'integer',
        'views_count' => 'integer',
        'likes_count' => 'integer',
    ];

    // Relationships
    public function ustoz()
    {
        return $this->belongsTo(User::class, 'ustoz_id');
    }

    public function fan()
    {
        return $this->belongsTo(Fan::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
