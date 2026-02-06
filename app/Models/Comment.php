<?php
// app/Models/Comment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'commentable_type',
        'commentable_id',
        'comment',
        'likes_count',
        'parent_id',
    ];

    protected $casts = [
        'likes_count' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Increment likes count
     */
    public function incrementLikes()
    {
        $this->increment('likes_count');
        return $this;
    }

    /**
     * Decrement likes count
     */
    public function decrementLikes()
    {
        if ($this->likes_count > 0) {
            $this->decrement('likes_count');
        }
        return $this;
    }
}
