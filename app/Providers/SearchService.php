<?php

namespace App\Services;

use App\Models\Elon;
use App\Models\Ustoz;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SearchService
{
    /**
     * Global search across elonlar, ustozlar, and videos
     */
    public function globalSearch(string $query, int $limit = 20): array
    {
        $query = trim($query);
        
        if (empty($query)) {
            return [
                'elonlar' => [],
                'ustozlar' => [],
                'videos' => [],
            ];
        }

        return [
            'elonlar' => $this->searchElonlar($query, $limit),
            'ustozlar' => $this->searchUstozlar($query, $limit),
            'videos' => $this->searchVideos($query, $limit),
        ];
    }

    /**
     * Search elonlar
     */
    public function searchElonlar(string $query, int $limit = 20): Collection
    {
        return Elon::approved()
            ->with('ustoz.user')
            ->where(function (Builder $q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('subject', 'LIKE', "%{$query}%")
                  ->orWhere('location', 'LIKE', "%{$query}%");
            })
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Search ustozlar
     */
    public function searchUstozlar(string $query, int $limit = 20): Collection
    {
        return Ustoz::with('user')
            ->where(function (Builder $q) use ($query) {
                $q->where('full_name', 'LIKE', "%{$query}%")
                  ->orWhere('bio', 'LIKE', "%{$query}%")
                  ->orWhere('education', 'LIKE', "%{$query}%")
                  ->orWhere('location', 'LIKE', "%{$query}%")
                  ->orWhere('center_name', 'LIKE', "%{$query}%");
            })
            ->orderBy('average_rating', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Search videos
     */
    public function searchVideos(string $query, int $limit = 20): Collection
    {
        return Video::approved()
            ->with('ustoz.user')
            ->where(function (Builder $q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('subject', 'LIKE', "%{$query}%");
            })
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get filter options for elonlar
     */
    public function getElonFilterOptions(): array
    {
        return [
            'subjects' => Elon::approved()
                ->distinct()
                ->orderBy('subject')
                ->pluck('subject')
                ->toArray(),
            
            'locations' => Elon::approved()
                ->distinct()
                ->orderBy('location')
                ->pluck('location')
                ->toArray(),
            
            'price_range' => [
                'min' => Elon::approved()->min('price') ?? 0,
                'max' => Elon::approved()->max('price') ?? 0,
            ],
            
            'badges' => ['yangi', 'top', 'chegirma', 'tavsiya'],
        ];
    }

    /**
     * Get filter options for ustozlar
     */
    public function getUstozFilterOptions(): array
    {
        return [
            'locations' => Ustoz::distinct()
                ->orderBy('location')
                ->pluck('location')
                ->toArray(),
            
            'experience_range' => [
                'min' => Ustoz::min('experience_years') ?? 0,
                'max' => Ustoz::max('experience_years') ?? 0,
            ],
            
            'rating_range' => [
                'min' => 0,
                'max' => 5,
            ],
        ];
    }

    /**
     * Advanced elon search with filters
     */
    public function advancedElonSearch(array $filters): Collection
    {
        $query = Elon::approved()->with('ustoz.user');

        // Text search
        if (!empty($filters['query'])) {
            $searchQuery = $filters['query'];
            $query->where(function (Builder $q) use ($searchQuery) {
                $q->where('title', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('description', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('subject', 'LIKE', "%{$searchQuery}%");
            });
        }

        // Subject filter
        if (!empty($filters['subject'])) {
            $query->where('subject', $filters['subject']);
        }

        // Location filter
        if (!empty($filters['location'])) {
            $query->where('location', 'LIKE', "%{$filters['location']}%");
        }

        // Price range filter
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Badge filter
        if (!empty($filters['badge'])) {
            $query->withBadge($filters['badge']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'latest';
        switch ($sortBy) {
            case 'popular':
                $query->popular();
                break;
            case 'most_favorited':
                $query->mostFavorited();
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->latest();
        }

        // Pagination
        $perPage = $filters['per_page'] ?? 20;
        
        return $query->paginate($perPage);
    }

    /**
     * Get popular search queries
     */
    public function getPopularSearches(int $limit = 10): array
    {
        // This would require a search_logs table to track searches
        // For now, return most popular subjects
        return Elon::approved()
            ->select('subject')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('subject')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->pluck('subject')
            ->toArray();
    }

    /**
     * Get related elonlar based on subject and location
     */
    public function getRelatedElonlar(Elon $elon, int $limit = 5): Collection
    {
        return Elon::approved()
            ->with('ustoz.user')
            ->where('id', '!=', $elon->id)
            ->where(function (Builder $q) use ($elon) {
                $q->where('subject', $elon->subject)
                  ->orWhere('location', $elon->location);
            })
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending elonlar (most viewed in last 7 days)
     */
    public function getTrendingElonlar(int $limit = 10): Collection
    {
        return Elon::approved()
            ->with('ustoz.user')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
