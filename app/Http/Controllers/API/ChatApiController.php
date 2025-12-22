<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Ustoz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ChatApiController extends Controller
{
    /**
     * Get user's chats list
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userType = $user->role; // 'user', 'ustoz', 'admin'

        $query = Chat::with(['user', 'ustoz', 'elon', 'latestMessage']);

        if ($userType === 'user') {
            $query->where('user_id', $user->id);
        } elseif ($userType === 'ustoz') {
            $ustoz = Ustoz::where('user_id', $user->id)->first();
            if (!$ustoz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ustoz profili topilmadi',
                ], 404);
            }
            $query->where('ustoz_id', $ustoz->id)
                  ->orWhere(function($q) use ($user) {
                      $q->where('chat_type', 'ustoz_admin')
                        ->where('user_id', $user->id);
                  });
        } elseif ($userType === 'admin') {
            $query->where('chat_type', 'ustoz_admin');
        }

        $chats = $query->orderBy('last_message_at', 'desc')->get();

        $chats = $chats->map(function($chat) use ($user, $userType) {
            return [
                'id' => $chat->id,
                'user' => $chat->user ? [
                    'id' => $chat->user->id,
                    'name' => $chat->user->name,
                    'email' => $chat->user->email,
                ] : null,
                'ustoz' => $chat->ustoz ? [
                    'id' => $chat->ustoz->id,
                    'full_name' => $chat->ustoz->full_name,
                    'fan' => $chat->ustoz->fan,
                ] : null,
                'elon' => $chat->elon ? [
                    'id' => $chat->elon->id,
                    'sarlavha' => $chat->elon->sarlavha,
                ] : null,
                'chat_type' => $chat->chat_type,
                'last_message' => $chat->last_message,
                'last_message_at' => $chat->last_message_at,
                'unread_count' => $chat->getUnreadCountForUser($user->id, $userType),
                'created_at' => $chat->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $chats,
        ]);
    }

    /**
     * Create or get existing chat
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ustoz_id' => 'required_without:chat_type|exists:ustozlar,id',
            'elon_id' => 'nullable|exists:elonlar,id',
            'chat_type' => 'nullable|in:user_ustoz,ustoz_admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $chatType = $request->chat_type ?? 'user_ustoz';

        if ($chatType === 'ustoz_admin') {
            // Ustoz → Admin chat
            $chat = Chat::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'chat_type' => 'ustoz_admin',
                ],
                [
                    'ustoz_id' => null,
                    'elon_id' => null,
                ]
            );
        } else {
            // User → Ustoz chat
            $chat = Chat::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'ustoz_id' => $request->ustoz_id,
                ],
                [
                    'elon_id' => $request->elon_id,
                    'chat_type' => 'user_ustoz',
                ]
            );
        }

        return response()->json([
            'success' => true,
            'data' => $chat,
        ]);
    }

    /**
     * Get specific chat details
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $chat = Chat::with(['user', 'ustoz', 'elon'])->find($id);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Chat topilmadi',
            ], 404);
        }

        // Check permission
        $userType = $user->role;
        if ($userType === 'user' && $chat->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ruxsat yo\'q',
            ], 403);
        }

        if ($userType === 'ustoz') {
            $ustoz = Ustoz::where('user_id', $user->id)->first();
            if (!$ustoz || ($chat->ustoz_id !== $ustoz->id && $chat->user_id !== $user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ruxsat yo\'q',
                ], 403);
            }
        }

        // Mark as read
        $chat->markAsReadBy($user->id, $userType);

        return response()->json([
            'success' => true,
            'data' => $chat,
        ]);
    }

    /**
     * Get messages for a chat
     */
    public function messages(Request $request, $id)
    {
        $user = $request->user();
        $chat = Chat::find($id);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Chat topilmadi',
            ], 404);
        }

        // Pagination
        $perPage = $request->input('per_page', 50);
        $messages = Message::where('chat_id', $id)
            ->with('sender')
            ->orderBy('created_at', 'asc')  // Oldest first for chat
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'message_type' => 'required|in:text,image,file',
            'content' => 'required_if:message_type,text|string',
            'file' => 'required_if:message_type,image,file|file|max:10240', // 10MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $chat = Chat::find($id);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Chat topilmadi',
            ], 404);
        }

        $messageType = $request->message_type;
        $content = $request->content;
        $fileName = null;
        $fileSize = null;
        $fileType = null;

        // Handle file upload
        if ($messageType !== 'text' && $request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $fileType = $file->getMimeType();

            // Determine folder
            $folder = $messageType === 'image' ? 'chat_images' : 'chat_files';

            // Store file
            $path = $file->store($folder, 'public');
            $content = $path;
        }

        // Determine sender type
        $senderType = 'user';
        if ($user->role === 'ustoz') {
            $senderType = 'ustoz';
        } elseif ($user->role === 'admin') {
            $senderType = 'admin';
        }

        // Create message
        $message = Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'sender_type' => $senderType,
            'message_type' => $messageType,
            'content' => $content,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'file_type' => $fileType,
        ]);

        // Update chat
        $lastMessage = $messageType === 'text'
            ? $content
            : ($messageType === 'image' ? '📷 Rasm' : '📎 Fayl: ' . $fileName);

        $chat->update([
            'last_message' => $lastMessage,
            'last_message_at' => now(),
        ]);

        // Increment unread count
        if ($senderType === 'user') {
            $chat->increment('ustoz_unread_count');
        } else {
            $chat->increment('user_unread_count');
        }

        // Load sender relationship
        $message->load('sender');

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $chat = Chat::find($id);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Chat topilmadi',
            ], 404);
        }

        $userType = $user->role;
        $chat->markAsReadBy($user->id, $userType);

        return response()->json([
            'success' => true,
            'message' => 'Xabarlar o\'qildi deb belgilandi',
        ]);
    }

    /**
     * Delete a chat
     */
    public function destroy($id)
    {
        $chat = Chat::find($id);

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Chat topilmadi',
            ], 404);
        }

        // Delete associated files
        $messages = Message::where('chat_id', $id)
            ->whereIn('message_type', ['image', 'file'])
            ->get();

        foreach ($messages as $message) {
            if ($message->content) {
                Storage::disk('public')->delete($message->content);
            }
        }

        $chat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chat o\'chirildi',
        ]);
    }
}
