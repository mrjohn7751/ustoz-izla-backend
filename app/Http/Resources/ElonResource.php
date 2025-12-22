<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElonResource extends JsonResource
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
            'ustoz_id' => $this->ustoz_id,
            'title' => $this->title,
            'description' => $this->description,
            'subject' => $this->subject,
            'subject_image' => $this->subject_image ? asset('storage/' . $this->subject_image) : null,
            'price' => $this->price,
            'location' => $this->location,
            'center_name' => $this->center_name,
            'schedule' => $this->schedule,
            'duration_minutes' => $this->duration_minutes,
            'duration_formatted' => $this->getDurationFormatted(),
            'status' => $this->status,
            'badge' => $this->badge,
            'views_count' => $this->views_count,
            'favorites_count' => $this->favorites_count,
            'comments_count' => $this->comments_count,
            'admin_note' => $this->admin_note,
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Relationships
            'ustoz' => new UstozResource($this->whenLoaded('ustoz')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            
            // Additional computed fields
            'is_approved' => $this->isApproved(),
            'is_pending' => $this->isPending(),
            'is_rejected' => $this->isRejected(),
            'has_new_badge' => $this->hasNewBadge(),
            'has_top_badge' => $this->hasTopBadge(),
            'has_discount_badge' => $this->hasDiscountBadge(),
            'has_recommended_badge' => $this->hasRecommendedBadge(),
        ];
    }

    /**
     * Get formatted duration
     */
    private function getDurationFormatted(): string
    {
        if ($this->duration_minutes < 60) {
            return $this->duration_minutes . ' daqiqa';
        }
        
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($minutes == 0) {
            return $hours . ' soat';
        }
        
        return $hours . ' soat ' . $minutes . ' daqiqa';
    }
}
