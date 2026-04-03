<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\GroceryList;
use App\Models\ListItem;
use App\Models\ItemMessage;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemMessageController extends Controller
{
    /**
     * Get messages for an item.
     */
    public function index(Request $request, GroceryList $list, ListItem $item): JsonResponse
    {
        if ($item->list_id !== $list->id) {
            return ApiResponse::error('Item does not belong to this list.', 404);
        }

        $messages = $item->messages()->with('user:id,name')->orderBy('created_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'messages' => $messages->map(fn($msg) => [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'user_id' => $msg->user_id,
                    'user_name' => $msg->user_id ? $msg->user->name : clone($msg->guest_name),
                    'created_at' => $msg->created_at->toIso8601String(),
                ])
            ],
        ]);
    }

    /**
     * Post a message on an item.
     */
    public function store(Request $request, GroceryList $list, ListItem $item, FcmService $fcmService): JsonResponse
    {
        if ($item->list_id !== $list->id) {
            return ApiResponse::error('Item does not belong to this list.', 404);
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $userId = $request->user()?->id;
        $guestName = null;
        $displayName = 'Guest';

        if ($userId) {
            $displayName = $request->user()->name;
        } else {
            $guestName = $request->attributes->get('guest_display_name') ?? 'Guest';
            $displayName = $guestName;
        }

        $message = $item->messages()->create([
            'user_id' => $userId,
            'guest_name' => $guestName,
            'message' => $data['message'],
        ]);

        // Send FCM notification
        $userIds = $list->sharedWith()->pluck('users.id')->merge([$list->user_id])->unique();
        if ($userId) {
            $userIds = $userIds->reject(fn($id) => $id === $userId);
        }

        $usersToNotify = \App\Models\User::whereIn('id', $userIds)->get();

        $title = "Message about {$item->name}";
        $body = "{$displayName}: " . \Illuminate\Support\Str::limit($data['message'], 100);

        foreach ($usersToNotify as $user) {
            $fcmService->sendToUser($user, $title, $body, [
                'type' => 'item_message',
                'list_id' => (string) $list->id,
                'item_id' => (string) $item->id,
            ]);
        }

        $message->loadMissing('user:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Message sent.',
            'data' => [
                'message' => [
                    'id' => $message->id,
                    'message' => $message->message,
                    'user_id' => $message->user_id,
                    'user_name' => $displayName,
                    'created_at' => $message->created_at->toIso8601String(),
                ]
            ],
        ], 201);
    }
}
