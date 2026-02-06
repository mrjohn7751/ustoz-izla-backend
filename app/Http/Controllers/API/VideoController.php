<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        try {
            Log::info('VIDEO INDEX - Request received', [
                'status' => $request->get('status'),
                'all_params' => $request->all()
            ]);

            $query = Video::with('fan');

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $videos = $query->orderBy('created_at', 'desc')->paginate(20);

            Log::info('VIDEO INDEX - Videos found', [
                'count' => $videos->count(),
                'total' => $videos->total()
            ]);

            $transformedData = $videos->getCollection()->map(function ($video) {
                return [
                    'id' => $video->id,
                    'ustoz_id' => $video->ustoz_id,
                    'title' => $video->sarlavha,
                    'description' => $video->tavsif,
                    'subject' => $video->fan ? $video->fan->nomi : 'Fan',
                    'video_url' => $video->video_url ? url('storage/' . $video->video_url) : '',
                    'thumbnail' => $video->thumbnail ? url('storage/' . $video->thumbnail) : '',
                    'duration_seconds' => $video->davomiyligi ?? 0,
                    'status' => $video->status,
                    'views_count' => $video->views_count,
                    'likes_count' => $video->likes_count,
                    'comments_count' => 0,
                    'created_at' => $video->created_at->toISOString(),
                    'updated_at' => $video->updated_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'current_page' => $videos->currentPage(),
                    'data' => $transformedData,
                    'total' => $videos->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('VIDEO INDEX ERROR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Videolarni yuklashda xatolik yuz berdi',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $video = Video::with('fan')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $video->id,
                    'ustoz_id' => $video->ustoz_id,
                    'title' => $video->sarlavha,
                    'description' => $video->tavsif,
                    'subject' => $video->fan ? $video->fan->nomi : 'Fan',
                    'video_url' => $video->video_url ? url('storage/' . $video->video_url) : '',
                    'thumbnail' => $video->thumbnail ? url('storage/' . $video->thumbnail) : '',
                    'duration_seconds' => $video->davomiyligi ?? 0,
                    'status' => $video->status,
                    'views_count' => $video->views_count,
                    'likes_count' => $video->likes_count,
                    'comments_count' => 0,
                    'created_at' => $video->created_at->toISOString(),
                    'updated_at' => $video->updated_at->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Video topilmadi',
            ], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('VIDEO STORE - Request received', $request->all());

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'subject' => 'required|integer',
                'video' => 'required|file|max:204800',
                'thumbnail' => 'nullable|image|max:5120',
            ]);

            $user = Auth::user();

            // Ustoz profilini tekshirish
            if (!$user->ustoz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siz ustoz sifatida ro\'yxatdan o\'tmagansiz',
                ], 403);
            }

            $videoPath = null;
            if ($request->hasFile('video')) {
                $videoPath = $request->file('video')->store('videos', 'public');
                Log::info('Video uploaded', ['path' => $videoPath]);
            }

            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
                Log::info('Thumbnail uploaded', ['path' => $thumbnailPath]);
            }

            $video = Video::create([
                'ustoz_id' => $user->ustoz->id,
                'fan_id' => $validated['subject'],
                'sarlavha' => $validated['title'],
                'tavsif' => $validated['description'],
                'video_url' => $videoPath,
                'thumbnail' => $thumbnailPath,
                'davomiyligi' => 0,
                'status' => 'pending', // Admin tasdiqlashi kerak
                'views_count' => 0,
                'likes_count' => 0,
            ]);

            Log::info('Video created', ['id' => $video->id, 'status' => $video->status]);

            return response()->json([
                'success' => true,
                'message' => 'Video yuklandi! Admin tasdiqlashini kuting.',
                'data' => [
                    'id' => $video->id,
                    'title' => $video->sarlavha,
                    'status' => 'pending',
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('VIDEO STORE ERROR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Video yuklashda xatolik yuz berdi',
            ], 500);
        }
    }

    public function incrementViews($id)
    {
        try {
            $video = Video::findOrFail($id);
            $video->increment('views_count');
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    public function likeVideo($id)
    {
        try {
            $video = Video::findOrFail($id);
            $video->increment('likes_count');
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    public function myVideos(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->ustoz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siz ustoz sifatida ro\'yxatdan o\'tmagansiz',
                ], 403);
            }

            $videos = Video::with('fan')
                ->where('ustoz_id', $user->ustoz->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $videos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $video = Video::findOrFail($id);

            if (!$user->ustoz || $video->ustoz_id !== $user->ustoz->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ruxsat yo\'q'
                ], 403);
            }

            if ($video->video_url) {
                Storage::disk('public')->delete($video->video_url);
            }
            if ($video->thumbnail) {
                Storage::disk('public')->delete($video->thumbnail);
            }

            $video->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }
}
