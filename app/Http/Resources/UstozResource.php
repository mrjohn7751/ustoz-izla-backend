<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UstozResource extends JsonResource
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
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'telegram' => $this->telegram,
            'bio' => $this->bio,
            'avatar' => $this->user->avatar ? asset('storage/' . $this->user->avatar) : null,
            'education' => $this->education,
            'experience_years' => $this->experience_years,
            'experience_text' => $this->getExperienceText(),
            'location' => $this->location,
            'center_name' => $this->center_name,
            'certificates' => $this->certificates,
            'students_count' => $this->students_count,
            'certified_students' => $this->certified_students,
            'average_rating' => round($this->average_rating, 2),
            'rating_text' => $this->getRatingText(),
            'total_ratings' => $this->total_ratings,
            'is_verified' => $this->is_verified,
            'is_top' => $this->is_top,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // User relationship
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'is_active' => $this->user->is_active,
            ] when $this->relationLoaded('user'),
            
            // Related data when loaded
            'elonlar' => ElonResource::collection($this->whenLoaded('elonlar')),
            'videos' => VideoResource::collection($this->whenLoaded('videos')),
            'ratings' => $this->whenLoaded('ratings', function() {
                return $this->ratings->map(function($rating) {
                    return [
                        'id' => $rating->id,
                        'rating' => $rating->rating,
                        'review' => $rating->review,
                        'user' => [
                            'name' => $rating->user->name,
                            'avatar' => $rating->user->avatar ? asset('storage/' . $rating->user->avatar) : null,
                        ],
                        'created_at' => $rating->created_at->format('Y-m-d H:i:s'),
                    ];
                });
            }),
        ];
    }

    /**
     * Get experience text
     */
    private function getExperienceText(): string
    {
        if ($this->experience_years == 1) {
            return '1 yil tajriba';
        }
        return $this->experience_years . ' yil tajriba';
    }

    /**
     * Get rating text
     */
    private function getRatingText(): string
    {
        $rating = round($this->average_rating, 1);
        return $rating . ' ⭐ (' . $this->total_ratings . ' baho)';
    }
}
