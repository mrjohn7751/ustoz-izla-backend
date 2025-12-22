<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    protected $fillable = [
        'user_id',
        'ustoz_id',
        'elon_id',
        'chat_type',
        'last_message',
        'last_message_at',
        'user_unread_count',
        'ustoz_unread_count',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ustoz(): BelongsTo
    {
        return $this->belongsTo(Ustoz::class);
    }

    public function elon(): BelongsTo
    {
        return $this->belongsTo(Elon::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    // Get unread count for specific user
    public function getUnreadCountForUser($userId, $userType)
    {
        if ($userType === 'user') {
            return $this->user_unread_count;
        } elseif ($userType === 'ustoz') {
            return $this->ustoz_unread_count;
        }
        return 0;
    }

    // Mark messages as read
    public function markAsReadBy($userId, $userType)
    {
        // Update unread count
        if ($userType === 'user') {
            $this->update(['user_unread_count' => 0]);
        } elseif ($userType === 'ustoz') {
            $this->update(['ustoz_unread_count' => 0]);
        }

        // Mark messages as read
        $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}
