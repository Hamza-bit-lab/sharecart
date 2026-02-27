<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreListItemRequest;
use App\Http\Requests\UpdateListItemRequest;
use App\Models\GroceryList;
use App\Models\ListItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * CRUD for items within a grocery list.
 * All actions require update permission on the parent list.
 */
class ListItemController extends Controller
{
    /**
     * Add an item to a list. Allowed for owner/collaborator (auth) or guest (joined by code).
     */
    public function store(StoreListItemRequest $request, GroceryList $list): RedirectResponse
    {
        if ($request->user()) {
            $this->authorize('update', $list);
        }

        $list->items()->create([
            'name' => $request->validated('name'),
            'quantity' => $request->validated('quantity', 1),
        ]);

        return redirect()->back()->with('success', 'Item added.');
    }

    /**
     * Update an item (name, quantity, or completed/purchased).
     * When marking completed, stores the current user as the purchaser (null for guests).
     */
    public function update(UpdateListItemRequest $request, GroceryList $list, ListItem $item): RedirectResponse
    {
        if ($request->user()) {
            $this->authorize('update', $item);
        }
        if ($item->list_id !== $list->id) {
            abort(404);
        }
        $data = $request->validated();
        if (array_key_exists('completed', $data)) {
            if ($data['completed']) {
                if ($request->user()) {
                    $data['completed_by'] = $request->user()->id;
                    $data['completed_by_name'] = null;
                } else {
                    $guestNames = $request->session()->get('guest_names', []);
                    $data['completed_by'] = null;
                    $data['completed_by_name'] = $guestNames[$list->id] ?? null;
                }
                $data['completed_at'] = now();
            } else {
                $data['completed_by'] = null;
                $data['completed_by_name'] = null;
                $data['completed_at'] = null;
            }
        }
        $item->update($data);

        return redirect()->back()->with('success', 'Item updated.');
    }

    /**
     * Delete an item. Allowed for owner/collaborator (auth) or guest (joined by code).
     */
    public function destroy(Request $request, GroceryList $list, ListItem $item): RedirectResponse
    {
        if ($item->list_id !== $list->id) {
            abort(404);
        }
        if ($request->user()) {
            $this->authorize('delete', $item);
        }

        $item->delete();

        return redirect()->back()->with('success', 'Item removed.');
    }
}
