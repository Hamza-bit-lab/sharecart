<?php

namespace App\Http\Controllers;

use App\Services\SuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Suggests commonly added items from all users' lists (aggregate data).
 */
class SuggestionController extends Controller
{
    public function __construct(
        private SuggestionService $suggestions
    ) {}

    /**
     * Return suggested item names. Uses categories (beauty, cooking, etc.) when possible.
     * Query params: q (search, optional), context (comma-separated item names on current list), limit (default 5).
     */
    public function index(Request $request): JsonResponse
    {
        $q = $request->input('q', '');
        $limit = (int) $request->input('limit', 5);
        $limit = min(max($limit, 1), 50);

        $context = $request->input('context', '');
        $contextItems = $context !== '' ? array_map('trim', explode(',', $context)) : [];

        $suggestions = $this->suggestions->getSuggestions($q, $limit, $contextItems);

        return response()->json([
            'success' => true,
            'data' => ['suggestions' => $suggestions],
        ]);
    }

    /**
     * Predictive suggestions based on the user's past habits.
     */
    public function predictiveSuggestions(Request $request, \App\Models\GroceryList $list): JsonResponse
    {
        $userId = $request->user()?->id;

        if (!$userId) {
            return response()->json([
                'success' => true,
                'data' => ['suggestions' => []],
            ]);
        }

        $currentListNames = $list->items()->pluck('name')->map(fn($name) => strtolower(trim($name)))->toArray();

        $frequentItems = \App\Models\ListItem::whereHas('groceryList', function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereHas('sharedWith', function($sq) use ($userId) {
                  $sq->where('users.id', $userId);
              });
        })
        ->where('created_at', '>=', now()->subDays(60))
        ->select('name')
        ->selectRaw('COUNT(*) as count')
        ->groupBy('name')
        ->orderBy('count', 'desc')
        ->limit(20)
        ->get()
        ->pluck('name')
        ->reject(function ($name) use ($currentListNames) {
            return in_array(strtolower(trim($name)), $currentListNames);
        })
        ->take(5)
        ->values()
        ->toArray();

        return response()->json([
            'success' => true,
            'data' => ['suggestions' => $frequentItems],
        ]);
    }
}
