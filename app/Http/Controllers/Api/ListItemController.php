<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\GroceryList;
use App\Models\ListItem;
use App\Services\SuggestionCategories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListItemController extends Controller
{
    /**
     * Add an item to a list. Allowed for Sanctum user or guest token.
     */
    public function store(Request $request, GroceryList $list): JsonResponse
    {
        if ($request->user()) {
            $this->authorize('update', $list);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ]);

        $item = $list->items()->create([
            'name' => $data['name'],
            'quantity' => $data['quantity'] ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'item' => $this->serializeItem($item),
            ],
        ], 201);
    }

    /**
     * Update an item (name, quantity, or completed).
     */
    public function update(Request $request, GroceryList $list, ListItem $item): JsonResponse
    {
        if ($item->list_id !== $list->id) {
            return ApiResponse::error('Item does not belong to this list.', 404);
        }

        if ($request->user()) {
            $this->authorize('update', $item);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'quantity' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:9999'],
            'completed' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('completed', $data)) {
            if ($data['completed']) {
                if ($request->user()) {
                    $data['completed_by'] = $request->user()->id;
                    $data['completed_by_name'] = null;
                } else {
                    $data['completed_by'] = null;
                    $data['completed_by_name'] = $request->attributes->get('guest_display_name');
                }
                $data['completed_at'] = now();
            } else {
                $data['completed_by'] = null;
                $data['completed_by_name'] = null;
                $data['completed_at'] = null;
            }
        }

        $item->update($data);

        return response()->json([
            'success' => true,
            'data' => [
                'item' => $this->serializeItem($item->fresh('completedBy')),
            ],
        ]);
    }

    /**
     * Delete an item.
     */
    public function destroy(Request $request, GroceryList $list, ListItem $item): JsonResponse
    {
        if ($item->list_id !== $list->id) {
            return ApiResponse::error('Item does not belong to this list.', 404);
        }

        if ($request->user()) {
            $this->authorize('delete', $item);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item deleted.',
        ]);
    }

    protected function serializeItem(ListItem $item): array
    {
        $item->loadMissing('completedBy');

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
    }
}

