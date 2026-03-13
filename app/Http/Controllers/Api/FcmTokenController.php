<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    /**
     * Register or update an FCM token for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'device_id' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        // Update existing token if it exists (for any user) or create new one for current user
        // The requirement says: "upsert/store this FCM token for the current user ... If using a single token per user, overwrite; if multiple devices, add/replace by device_id."
        // Actually, if we have a token, it should belong to ONE user. 
        // If another user logs in on the same device and gets the same token, we should move it to the new user.
        
        FcmToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => $user->id,
                'device_id' => $request->device_id,
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Token saved',
        ]);
    }
}
