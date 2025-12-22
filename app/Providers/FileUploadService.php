<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class FileUploadService
{
    /**
     * Upload avatar image
     */
    public function uploadAvatar(UploadedFile $file): string
    {
        $filename = $this->generateFilename($file, 'avatar');
        $path = 'uploads/avatars/' . $filename;
        
        // Resize image to 500x500
        $image = Image::make($file)->fit(500, 500);
        Storage::disk('public')->put($path, (string) $image->encode());
        
        return $path;
    }

    /**
     * Upload elon subject image
     */
    public function uploadElonImage(UploadedFile $file): string
    {
        $filename = $this->generateFilename($file, 'elon');
        $path = 'uploads/elon_images/' . $filename;
        
        // Resize image to 800x600
        $image = Image::make($file)->fit(800, 600);
        Storage::disk('public')->put($path, (string) $image->encode());
        
        return $path;
    }

    /**
     * Upload video file
     */
    public function uploadVideo(UploadedFile $file): array
    {
        $filename = $this->generateFilename($file, 'video');
        $path = 'uploads/videos/' . $filename;
        
        // Store video
        $storedPath = $file->storeAs('uploads/videos', $filename, 'public');
        
        // Get video duration (requires FFmpeg)
        $duration = $this->getVideoDuration(Storage::disk('public')->path($storedPath));
        
        return [
            'path' => $storedPath,
            'duration' => $duration,
        ];
    }

    /**
     * Upload video thumbnail
     */
    public function uploadVideoThumbnail(UploadedFile $file): string
    {
        $filename = $this->generateFilename($file, 'thumbnail');
        $path = 'uploads/videos/thumbnails/' . $filename;
        
        // Resize image to 1280x720 (16:9)
        $image = Image::make($file)->fit(1280, 720);
        Storage::disk('public')->put($path, (string) $image->encode());
        
        return $path;
    }

    /**
     * Delete file from storage
     */
    public function deleteFile(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Generate unique filename
     */
    private function generateFilename(UploadedFile $file, string $prefix = 'file'): string
    {
        $extension = $file->getClientOriginalExtension();
        $randomString = Str::random(20);
        $timestamp = time();
        
        return "{$prefix}_{$timestamp}_{$randomString}.{$extension}";
    }

    /**
     * Get video duration in seconds
     * Requires FFmpeg to be installed
     */
    private function getVideoDuration(string $filePath): int
    {
        try {
            // Try to get duration using getID3 library if available
            if (class_exists('\getID3')) {
                $getID3 = new \getID3;
                $file = $getID3->analyze($filePath);
                return (int) ($file['playtime_seconds'] ?? 0);
            }

            // Fallback: Try using FFmpeg command
            $command = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($filePath);
            $duration = shell_exec($command);
            
            return (int) floatval($duration);
        } catch (\Exception $e) {
            // If can't get duration, return 0
            return 0;
        }
    }

    /**
     * Get file size in MB
     */
    public function getFileSizeMB(string $path): float
    {
        if (Storage::disk('public')->exists($path)) {
            $bytes = Storage::disk('public')->size($path);
            return round($bytes / 1048576, 2); // Convert to MB
        }

        return 0;
    }

    /**
     * Check if file exists
     */
    public function fileExists(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }
}
