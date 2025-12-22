<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Ustoz;
use App\Models\Elon;
use App\Models\Video;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_ustozlar' => Ustoz::count(),
            'total_fanlar' => User::where('role', 'fan')->count(),
            'total_elonlar' => Elon::count(),
            'approved_elonlar' => Elon::approved()->count(),
            'pending_elonlar' => Elon::pending()->count(),
            'rejected_elonlar' => Elon::rejected()->count(),
            'total_videos' => Video::count(),
            'approved_videos' => Video::approved()->count(),
            'pending_videos' => Video::pending()->count(),
            'rejected_videos' => Video::rejected()->count(),
            'new_users_this_month' => User::whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->count(),
        ];

        // Recent activities
        $recent_elonlar = Elon::with('ustoz.user')
            ->latest()
            ->limit(10)
            ->get();

        $recent_videos = Video::with('ustoz.user')
            ->latest()
            ->limit(10)
            ->get();

        $recent_users = User::latest()
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_elonlar' => $recent_elonlar,
                'recent_videos' => $recent_videos,
                'recent_users' => $recent_users,
            ]
        ]);
    }

    /**
     * Get charts data
     */
    public function charts()
    {
        // Users growth chart (last 12 months)
        $usersGrowth = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $usersGrowth[] = [
                'month' => $date->format('M Y'),
                'count' => User::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }

        // Elonlar by status
        $elonlarByStatus = [
            'approved' => Elon::approved()->count(),
            'pending' => Elon::pending()->count(),
            'rejected' => Elon::rejected()->count(),
        ];

        // Top subjects
        $topSubjects = Elon::approved()
            ->select('subject')
            ->selectRaw('count(*) as count')
            ->groupBy('subject')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Top locations
        $topLocations = Elon::approved()
            ->select('location')
            ->selectRaw('count(*) as count')
            ->groupBy('location')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'users_growth' => $usersGrowth,
                'elonlar_by_status' => $elonlarByStatus,
                'top_subjects' => $topSubjects,
                'top_locations' => $topLocations,
            ]
        ]);
    }
}
