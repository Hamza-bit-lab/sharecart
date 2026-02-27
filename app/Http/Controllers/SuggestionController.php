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
}
