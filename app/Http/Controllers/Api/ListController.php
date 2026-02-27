<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\GroceryList;
use App\Models\ListGuestToken;
use App\Models\User;
use App\Services\SuggestionCategories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListController extends Controller
{
    /**
     * Get all lists the authenticated user can access (owned + shared),
     * separated into active and archived.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $baseQuery = GroceryList::query()
            ->where('user_id', $user->id)
            ->orWhereIn('id', $user->sharedLists()->pluck('lists.id'));

        $active = (clone $baseQuery)
            ->active()
            ->withCount('items')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn (GroceryList $list) => $this->serializeListSummary($list))
            ->values();

        $archived = (clone $baseQuery)
            ->archived()
            ->withCount('items')
            ->orderBy('archived_at', 'desc')
            ->get()
            ->map(fn (GroceryList $list) => $this->serializeListSummary($list))
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'active' => $active,
                'archived' => $archived,
            ],
        ]);
    }

    /**
     * Create a new list for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
        ]);

        $data['join_code'] = GroceryList::generateUniqueJoinCode();

        $list = $request->user()->groceryLists()->create($data);

        return response()->json([
            'success' => true,
            'data' => [
                'list' => $this->serializeListDetail($list->fresh(['items', 'sharedWith', 'user'])),
            ],
        ], 201);
    }

    /**
     * Show a single list with items and collaborators.
     * Allowed for Sanctum user (policy) or guest token (list-scoped).
     */
    public function show(Request $request, GroceryList $list): JsonResponse
    {
        if ($request->user()) {
            $this->authorize('view', $list);
        }

        $list->load(['items' => ['completedBy'], 'sharedWith', 'user']);

        return response()->json([
            'success' => true,
            'data' => [
                'list' => $this->serializeListDetail($list),
            ],
        ]);
    }

    /**
     * Update list name or due date.
     */
    public function update(Request $request, GroceryList $list): JsonResponse
    {
        $this->authorize('update', $list);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
        ]);

        $list->update($data);

        return response()->json([
            'success' => true,
            'data' => [
                'list' => $this->serializeListDetail($list->fresh(['items', 'sharedWith', 'user'])),
            ],
        ]);
    }

    /**
     * Archive a list.
     */
    public function archive(Request $request, GroceryList $list): JsonResponse
    {
        $this->authorize('update', $list);

        $list->update(['archived_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'List archived.',
            'data' => [
                'list' => $this->serializeListSummary($list->fresh()),
            ],
        ]);
    }

    /**
     * Restore an archived list.
     */
    public function restore(Request $request, GroceryList $list): JsonResponse
    {
        $this->authorize('update', $list);

        $list->update(['archived_at' => null]);

        return response()->json([
            'success' => true,
            'message' => 'List restored.',
            'data' => [
                'list' => $this->serializeListSummary($list->fresh()),
            ],
        ]);
    }

    /**
     * Delete a list (owner only).
     */
    public function destroy(Request $request, GroceryList $list): JsonResponse
    {
        $this->authorize('delete', $list);

        $list->delete();

        return response()->json([
            'message' => 'List deleted.',
        ]);
    }

    /**
     * Reset all items on a list (uncheck all).
     * Allowed for Sanctum user or guest token.
     */
    public function resetItems(Request $request, GroceryList $list): JsonResponse
    {
        if ($request->user()) {
            $this->authorize('update', $list);
        }

        $list->items()->update([
            'completed' => false,
            'completed_by' => null,
            'completed_by_name' => null,
            'completed_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'List reset.',
            'data' => [
                'list' => $this->serializeListDetail($list->fresh(['items' => ['completedBy'], 'sharedWith', 'user'])),
            ],
        ]);
    }

    /**
     * Share list with another user by email.
     */
    public function share(Request $request, GroceryList $list): JsonResponse
    {
        $this->authorize('share', $list);

        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        /** @var User|null $user */
        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return ApiResponse::error('User not found.', 404);
        }

        if ($list->user_id === $user->id) {
            return ApiResponse::error('This user already owns the list.', 422);
        }

        $list->sharedWith()->syncWithoutDetaching([$user->id]);

        $list->load('sharedWith');

        return response()->json([
            'success' => true,
            'message' => 'List shared.',
            'data' => [
                'shared_with' => $list->sharedWith->map(fn (User $u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                ])->values(),
            ],
        ]);
    }

    /**
     * Remove a collaborator from a list.
     */
    public function unshare(Request $request, GroceryList $list, User $user): JsonResponse
    {
        $this->authorize('share', $list);

        if ($list->user_id === $user->id) {
            return ApiResponse::error('Cannot remove the list owner.', 422);
        }

        $list->sharedWith()->detach($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Access removed.',
        ]);
    }

    /**
     * Join a list using its 5-digit join_code.
     * No login required: send only the code to get list + guest access_token for that list.
     * With login (Bearer token): adds you as collaborator and returns list (no guest token).
     */
    public function joinByCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'size:5'],
            'name' => ['nullable', 'string', 'max:50'],
        ]);

        $code = strtoupper(trim($data['code']));

        $list = GroceryList::query()->where('join_code', $code)->first();

        if (! $list) {
            return ApiResponse::error('No list found with that code.', 404);
        }

        $list->load(['items' => ['completedBy'], 'sharedWith', 'user']);

        // Logged-in user: add as collaborator (or confirm owner)
        if ($request->user()) {
            $user = $request->user();
            if ($list->user_id === $user->id) {
                return response()->json([
                    'success' => true,
                    'message' => 'You own this list.',
                    'data' => ['list' => $this->serializeListDetail($list)],
                ]);
            }
            $list->sharedWith()->syncWithoutDetaching([$user->id]);
            $list->load(['items' => ['completedBy'], 'sharedWith', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'You have been added to this list.',
                'data' => ['list' => $this->serializeListDetail($list)],
            ]);
        }

        // Guest (no login): issue a list-scoped token so client can call list/item APIs without account
        $accessToken = ListGuestToken::createToken($list, 30, $data['name'] ?? null);
        $expiresAt = now()->addDays(30)->toIso8601String();

        return response()->json([
            'success' => true,
            'message' => 'You can access this list with the token below. No account required.',
            'data' => [
                'list' => $this->serializeListDetail($list),
                'access_token' => $accessToken,
                'token_type' => 'guest',
                'expires_at' => $expiresAt,
            ],
        ]);
    }

    /**
     * Summary representation for list cards.
     */
    protected function serializeListSummary(GroceryList $list): array
    {
        return [
            'id' => $list->id,
            'name' => $list->name,
            'due_date' => optional($list->due_date)->toDateString(),
            'archived_at' => optional($list->archived_at)->toIso8601String(),
            'items_count' => $list->items_count ?? $list->items()->count(),
            'join_code' => $list->join_code,
        ];
    }

    /**
     * Detailed representation including items and collaborators.
     */
    protected function serializeListDetail(GroceryList $list): array
    {
        $list->loadMissing(['items' => ['completedBy'], 'sharedWith', 'user']);

        return [
            'id' => $list->id,
            'name' => $list->name,
            'due_date' => optional($list->due_date)->toDateString(),
            'archived_at' => optional($list->archived_at)->toIso8601String(),
            'join_code' => $list->join_code,
            'owner' => [
                'id' => $list->user->id,
                'name' => $list->user->name,
                'email' => $list->user->email,
            ],
            'shared_with' => $list->sharedWith->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ])->values(),
            'joined_by_code' => ListGuestToken::query()
                ->where('list_id', $list->id)
                ->whereNotNull('display_name')
                ->where('display_name', '!=', '')
                ->where('expires_at', '>', now())
                ->get()
                ->pluck('display_name')
                ->unique()
                ->values()
                ->all(),
            'items' => $list->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'completed' => (bool) $item->completed,
                    'completed_by' => $item->completedBy ? [
                        'id' => $item->completedBy->id,
                        'name' => $item->completedBy->name,
                    ] : null,
                    'completed_by_name' => $item->completed_by_name,
                    'created_at' => $item->created_at->toIso8601String(),
                    'updated_at' => $item->updated_at->toIso8601String(),
                    'section' => SuggestionCategories::getCategoryForItem($item->name),
                ];
            })->values(),
        ];
    }
}

