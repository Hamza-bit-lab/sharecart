<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\GroceryList;
use App\Models\ListGuestToken;
use App\Models\User;
use App\Models\FcmToken;
use App\Services\FcmService;
use App\Services\SuggestionCategories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListController extends Controller
{
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
    public function icons(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'icons' => GroceryList::RECOMMENDED_ICONS,
            ],
        ]);
    }

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
            'icon' => ['nullable', 'string', 'max:10'],
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
            'icon' => ['nullable', 'string', 'max:10'],
        ]);

        $list->update($data);

        return response()->json([
            'success' => true,
            'data' => [
                'list' => $this->serializeListDetail($list->fresh(['items', 'sharedWith', 'user'])),
            ],
        ]);
    }

    public function archive(Request $request, GroceryList $list): JsonResponse
    {
        $this->authorize('update', $list);
        $list->update(['archived_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'List archived.',
            'data' => ['list' => $this->serializeListSummary($list->fresh())],
        ]);
    }

    public function restore(Request $request, GroceryList $list): JsonResponse
    {
        $this->authorize('update', $list);
        $list->update(['archived_at' => null]);

        return response()->json([
            'success' => true,
            'message' => 'List restored.',
            'data' => ['list' => $this->serializeListSummary($list->fresh())],
        ]);
    }

    public function destroy(Request $request, GroceryList $list): JsonResponse
    {
        $this->authorize('delete', $list);
        $list->delete();

        return response()->json(['message' => 'List deleted.']);
    }

    /**
     * Leave a shared list (for non-owners).
     */
    public function leave(Request $request, GroceryList $list): JsonResponse
    {
        if ($request->user()) {
            if ($list->user_id == $request->user()->id) {
                return ApiResponse::error('Owner cannot leave the list.', 403);
            }
            $list->sharedWith()->detach($request->user()->id);
        } else {
            // Guest leaving
            $tokenStr = $request->bearerToken();
            if ($tokenStr) {
                $tokenRecord = \App\Models\ListGuestToken::findValidByPlainToken($tokenStr);
                if ($tokenRecord && $tokenRecord->list_id === $list->id) {
                    $tokenRecord->delete();
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Left list.',
        ]);
    }

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
            'data' => ['list' => $this->serializeListDetail($list->fresh(['items' => ['completedBy'], 'sharedWith', 'user']))],
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

        // Notify the added user
        $this->fcmService->sendToUser(
            $user,
            "Added to list",
            "{$request->user()->name} added you to list {$list->name}",
            ['list_id' => $list->id, 'type' => 'added_to_list']
        );

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

        $user = null;
        if ($request->bearerToken()) {
            $user = auth('sanctum')->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }
        }

        if ($user) {
            if ($list->user_id === $user->id) {
                return response()->json([
                    'success' => true,
                    'message' => 'You own this list.',
                    'data' => ['list' => $this->serializeListDetail($list)],
                ]);
            }
            $list->sharedWith()->syncWithoutDetaching([$user->id]);
            $list->load(['items' => ['completedBy'], 'sharedWith', 'user']);

            // Notify the list owner
            $this->fcmService->sendToUser(
                $list->user,
                "Someone joined your list",
                "{$user->name} joined your list {$list->name} via code",
                ['list_id' => $list->id, 'type' => 'user_joined']
            );

            return response()->json([
                'success' => true,
                'message' => 'You have been added to this list.',
                'data' => ['list' => $this->serializeListDetail($list)],
            ]);
        }

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

    public function ping(Request $request, GroceryList $list): JsonResponse
    {
        if ($request->user()) {
            $this->authorize('update', $list);
            $list->update([
                'last_ping_at' => now(),
                'last_ping_by_id' => $request->user()->id,
                'last_ping_by_name' => $request->user()->name,
            ]);
        } else {
            // Guest ping (scoped by token display_name)
            $token = $request->attributes->get('list_access_token');
            $name = $token ? $token->display_name : 'Guest';
            $list->update([
                'last_ping_at' => now(),
                'last_ping_by_id' => null,
                'last_ping_by_name' => $name ?: 'Guest',
            ]);
        }

        $name = $request->user() ? $request->user()->name : ($list->last_ping_by_name ?: 'Someone');
         
        // Notify all other members
        $query = FcmToken::query()
            ->where(function($query) use ($list) {
                $query->whereIn('user_id', function($q) use ($list) {
                    $q->select('user_id')->from('list_user')->where('list_id', $list->id);
                })->orWhere('user_id', $list->user_id);
            });
            
        if ($user = $request->user()) {
            $query->where('user_id', '!=', $user->id);
        }

        $tokens = $query->pluck('token')
            ->filter(fn($token) => !empty($token))
            ->unique()
            ->values()
            ->toArray();
 
        if (!empty($tokens)) {
            $this->fcmService->sendToMany(
                $tokens,
                "Nudge!",
                "{$name} nudged the list {$list->name}",
                ['list_id' => $list->id, 'type' => 'nudge']
            );
        }
 
        return response()->json([
            'success' => true,
            'message' => 'Nudge sent!',
            'data' => [
                'last_ping_at' => $list->last_ping_at->toIso8601String(),
                'last_ping_by_name' => $list->last_ping_by_name,
            ],
        ]);
    }

    public function settlement(Request $request, GroceryList $list): JsonResponse
    {
        if ($request->user()) {
            $this->authorize('view', $list);
        }

        return response()->json([
            'success' => true,
            'data' => $this->calculateSettlement($list),
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
            'icon' => $list->icon,
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
            'icon' => $list->icon,
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
                    'claimed_by_user_id' => $item->claimed_by_user_id,
                    'claimed_by_name' => $item->claimed_by_name ?: ($item->claimedByUser ? $item->claimedByUser->name : null),
                    'claimed_at' => optional($item->claimed_at)->toIso8601String(),
                ];
            })->values(),
            'last_ping' => $list->last_ping_at ? [
                'at' => $list->last_ping_at->toIso8601String(),
                'by_name' => $list->last_ping_by_name,
            ] : null,
            'settlement' => $this->calculateSettlement($list),
        ];
    }

    /**
     * Calculate settlement balances.
     */
    protected function calculateSettlement(GroceryList $list): array
    {
        $payments = $list->payments()->with('user')->get();
        $totalSpent = (float) $payments->sum('amount');
        
        $participants = [];
        // Add Owner
        $participants['user_' . $list->user->id] = [
            'name' => $list->user->name,
            'spent' => 0
        ];
        // Add Shared Users
        foreach ($list->sharedWith as $u) {
            $participants['user_' . $u->id] = [
                'name' => $u->name,
                'spent' => 0
            ];
        }
        
        foreach ($payments as $p) {
            if ($p->user_id) {
                $key = 'user_' . $p->user_id;
                // If for some reason the payer is not in sharedWith (e.g. removed), still include them
                if (!isset($participants[$key])) {
                    $participants[$key] = [
                        'name' => $p->user ? $p->user->name : 'Unknown User',
                        'spent' => 0
                    ];
                }
                $participants[$key]['spent'] += $p->amount;
            } else {
                $guestKey = 'guest_' . $p->guest_name;
                if (!isset($participants[$guestKey])) {
                    $participants[$guestKey] = [
                        'name' => $p->guest_name ?: 'Guest',
                        'spent' => 0
                    ];
                }
                $participants[$guestKey]['spent'] += $p->amount;
            }
        }
        
        $participantCount = count($participants);
        $fairShare = $participantCount > 0 ? $totalSpent / $participantCount : 0;
        
        $settlementResult = [];
        foreach ($participants as $p) {
            $settlementResult[] = [
                'name' => $p['name'],
                'spent' => (float) $p['spent'],
                'balance' => (float) round($p['spent'] - $fairShare, 2),
            ];
        }

        return [
            'total_spent' => (float) round($totalSpent, 2),
            'fair_share' => (float) round($fairShare, 2),
            'participants' => $settlementResult,
        ];
    }
}

