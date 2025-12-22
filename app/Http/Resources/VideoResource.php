<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
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
            'video_url' => $this->video_url ? asset('storage/' . $this->video_url) : null,
            'thumbnail' => $this->thumbnail ? asset('storage/' . $this->thumbnail) : null,
            'duration_seconds' => $this->duration_seconds,
            'duration_formatted' => $this->getDurationFormatted(),
            'status' => $this->status,
            'views_count' => $this->views_count,
            'likes_count' => $this->likes_count,
            'comments_count' => $this->comments_count,
            'admin_note' => $this->admin_note,
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Relationships
            'ustoz' => new UstozResource($this->whenLoaded('ustoz')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            
            // Computed fields
            'is_approved' => $this->isApproved(),
            'is_pending' => $this->isPending(),
            'is_rejected' => $this->isRejected(),
        ];
    }

    /**
     * Get formatted duration (MM:SS)
     */
    private function getDurationFormatted(): string
    {
        if (!$this->duration_seconds) {
            return '00:00';
        }

        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}
