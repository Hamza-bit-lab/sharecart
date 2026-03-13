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
        $list->fresh()->checkAndAutoArchive();

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

    /**
     * Claim an item.
     */
    public function claim(Request $request, GroceryList $list, ListItem $item): JsonResponse
    {
        if ($item->list_id !== $list->id) {
            return ApiResponse::error('Item does not belong to this list.', 404);
        }

        if ($request->user()) {
            $this->authorize('update', $item);
            $item->update([
                'claimed_by_user_id' => $request->user()->id,
                'claimed_by_name' => null,
                'claimed_at' => now(),
            ]);
        } else {
            $displayName = $request->attributes->get('guest_display_name') ?? 'Guest';
            $item->update([
                'claimed_by_user_id' => null,
                'claimed_by_name' => $displayName,
                'claimed_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item claimed.',
            'data' => [
                'item' => $this->serializeItem($item->fresh('claimedByUser')),
            ],
        ]);
    }

    /**
     * Unclaim an item.
     */
    public function unclaim(Request $request, GroceryList $list, ListItem $item): JsonResponse
    {
        if ($item->list_id !== $list->id) {
            return ApiResponse::error('Item does not belong to this list.', 404);
        }

        if ($request->user()) {
            $this->authorize('update', $item);
        }

        $item->update([
            'claimed_by_user_id' => null,
            'claimed_by_name' => null,
            'claimed_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item unclaimed.',
            'data' => [
                'item' => $this->serializeItem($item->fresh()),
            ],
        ]);
    }

    /**
     * Reorder items in a list.
     */
    public function reorder(Request $request, GroceryList $list): JsonResponse
    {
        if ($request->user()) {
            $this->authorize('update', $list);
        }

        $data = $request->validate([
            'item_ids' => ['required', 'array'],
            'item_ids.*' => ['integer', 'exists:list_items,id'],
        ]);

        $itemIds = $data['item_ids'];

        // Ensure all IDs belong to this list
        $count = $list->items()->whereIn('id', $itemIds)->count();
        if ($count !== count(array_unique($itemIds))) {
            return ApiResponse::error('Invalid item IDs provided for this list.', 422);
        }

        // Update positions
        foreach ($itemIds as $index => $id) {
            ListItem::where('id', $id)->update(['position' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Items reordered.',
            'data' => [
                'list' => $this->serializeListDetail($list->fresh()),
            ],
        ]);
    }

    protected function serializeListDetail(GroceryList $list): array
    {
        // Reuse logic from ListController if possible, or duplicate for now.
        // Actually, let's just use the same structure as ListController@show.
        // I'll need to make sure I have access to serializeListDetail or duplicate it.
        // Looking at the existing file, it doesn't have serializeListDetail.
        // Ah, it was in ListController. I should move it to a trait or Helper if I want to reuse it properly.
        // For now, I'll just return a success message or re-implement it briefly.
        // Wait, the user asked to optionally return the updated list detail.
        // Let's implement a basic version or copy it.
        
        $list->load(['items' => ['completedBy'], 'sharedWith', 'user']);
        
        return [
            'id' => $list->id,
            'name' => $list->name,
            'items' => $list->items->map(fn($item) => $this->serializeItem($item))->values(),
        ];
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
            'claimed_by_user_id' => $item->claimed_by_user_id,
            'claimed_by_name' => $item->claimed_by_name ?: ($item->claimedByUser ? $item->claimedByUser->name : null),
            'claimed_at' => optional($item->claimed_at)->toIso8601String(),
        ];
    }
}

