<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'commentable_id' => $this->commentable_id,
            'commentable_type' => $this->commentable_type,
            'content' => $this->content,
            'likes_count' => $this->likes_count,
            'is_approved' => $this->is_approved,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_human' => $this->getTimeAgo(),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // User relationship
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->avatar ? asset('storage/' . $this->user->avatar) : null,
                'role' => $this->user->role,
            ] when $this->relationLoaded('user'),
        ];
    }

    /**
     * Get human readable time ago
     */
    private function getTimeAgo(): string
    {
        $diff = $this->created_at->diffInSeconds(now());
        
        if ($diff < 60) {
            return 'Hozir';
        }
        
        $diff = $this->created_at->diffInMinutes(now());
        if ($diff < 60) {
            return $diff . ' daqiqa oldin';
        }
        
        $diff = $this->created_at->diffInHours(now());
        if ($diff < 24) {
            return $diff . ' soat oldin';
        }
        
        $diff = $this->created_at->diffInDays(now());
        if ($diff < 30) {
            return $diff . ' kun oldin';
        }
        
        $diff = $this->created_at->diffInMonths(now());
        if ($diff < 12) {
            return $diff . ' oy oldin';
        }
        
        $diff = $this->created_at->diffInYears(now());
        return $diff . ' yil oldin';
    }
}
