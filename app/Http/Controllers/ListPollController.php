<?php

namespace App\Http\Controllers;

use App\Models\GroceryList;
use App\Services\SuggestionCategories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Returns list and items as JSON for Ajax polling (real-time sync).
 * Items include section key (cooking, beauty, etc.) for grouped collapsible display.
 */
class ListPollController extends Controller
{
    public function show(Request $request, GroceryList $list): JsonResponse
    {
        if ($request->user()) {
            $this->authorize('view', $list);
        }

        $list->load(['items' => ['completedBy']]);

        $sort = $request->input('sort', 'section');
        if (! in_array($sort, ['section', 'name', 'date'], true)) {
            $sort = 'section';
        }
        $items = $list->items;
        if ($sort === 'name') {
            $items = $items->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
        } elseif ($sort === 'date') {
            $items = $items->sortByDesc('created_at')->values();
        }

        return response()->json([
            'list' => [
                'id' => $list->id,
                'name' => $list->name,
                'updated_at' => $list->updated_at->toIso8601String(),
            ],
            'items' => $items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'quantity' => $item->quantity,
                'completed' => $item->completed,
                'created_at' => $item->created_at->toIso8601String(),
                'updated_at' => $item->updated_at->toIso8601String(),
                'section' => SuggestionCategories::getCategoryForItem($item->name),
                'completed_by' => $item->completedBy ? ['id' => $item->completedBy->id, 'name' => $item->completedBy->name] : null,
                'completed_by_name' => $item->completed_by_name,
            ]),
            'section_labels' => SuggestionCategories::sectionLabels(),
            'section_order' => SuggestionCategories::sectionOrder(),
        ]);
    }
}
