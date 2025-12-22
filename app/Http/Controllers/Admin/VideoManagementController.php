<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VideoManagementController extends Controller
{
    /**
     * Get all videos (with filters)
     */
    public function index(Request $request)
    {
        $query = Video::with('ustoz.user');

        // Filter by status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'approved':
                    $query->approved();
                    break;
                case 'pending':
                    $query->pending();
                    break;
                case 'rejected':
                    $query->rejected();
                    break;
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('subject', 'LIKE', "%{$search}%");
            });
        }

        $videos = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $videos
        ]);
    }

    /**
     * Get single video
     */
    public function show($id)
    {
        $video = Video::with(['ustoz.user', 'comments.user'])->find($id);

        if (!$video) {
            return response()->json([
                'success' => false,
                'message' => 'Video not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $video
        ]);
    }

    /**
     * Approve video
     */
    public function approve(Request $request, $id)
    {
        $video = Video::find($id);

        if (!$video) {
            return response()->json([
                'success' => false,
                'message' => 'Video not found'
            ], 404);
        }

        try {
            $adminNote = $request->input('admin_note', 'Video tasdiqlandi');
            $video->approve($adminNote);

            // Send notification to ustoz
            Notification::create([
                'user_id' => $video->ustoz->user_id,
                'type' => 'video_approved',
                'title' => 'Video tasdiqlandi',
                'message' => "Sizning '{$video->title}' videongiz admin tomonidan tasdiqlandi.",
                'data' => [
                    'video_id' => $video->id,
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Video approved successfully',
                'data' => $video
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject video
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'admin_note' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $video = Video::find($id);

        if (!$video) {
            return response()->json([
                'success' => false,
                'message' => 'Video not found'
            ], 404);
        }

        try {
            $video->reject($request->admin_note);

            // Send notification to ustoz
            Notification::create([
                'user_id' => $video->ustoz->user_id,
                'type' => 'video_rejected',
                'title' => 'Video rad etildi',
                'message' => "Sizning '{$video->title}' videongiz rad etildi. Sabab: {$request->admin_note}",
                'data' => [
                    'video_id' => $video->id,
                    'reason' => $request->admin_note,
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Video rejected successfully',
                'data' => $video
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete video
     */
    public function destroy($id)
    {
        $video = Video::find($id);

        if (!$video) {
            return response()->json([
                'success' => false,
                'message' => 'Video not found'
            ], 404);
        }

        try {
            // Delete files
            if ($video->video_url) {
                \Storage::disk('public')->delete($video->video_url);
            }
            if ($video->thumbnail) {
                \Storage::disk('public')->delete($video->thumbnail);
            }

            $video->delete();

            return response()->json([
                'success' => true,
                'message' => 'Video deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete video',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk approve videos
     */
    public function bulkApprove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video_ids' => 'required|array',
            'video_ids.*' => 'integer|exists:videos,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $videos = Video::whereIn('id', $request->video_ids)->get();

            foreach ($videos as $video) {
                $video->approve('Bulk approved');

                // Send notification
                Notification::create([
                    'user_id' => $video->ustoz->user_id,
                    'type' => 'video_approved',
                    'title' => 'Video tasdiqlandi',
                    'message' => "Sizning '{$video->title}' videongiz admin tomonidan tasdiqlandi.",
                    'data' => ['video_id' => $video->id],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Videos approved successfully',
                'count' => $videos->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve videos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
