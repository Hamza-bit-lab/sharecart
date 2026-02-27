<?php

namespace App\Policies;

use App\Models\ListItem;
use App\Models\User;

/**
 * Authorization for list item actions.
 * Delegates to GroceryListPolicy: if user can update the list, they can manage items.
 */
class ListItemPolicy
{
    public function view(User $user, ListItem $item): bool
    {
        return $user->can('view', $item->groceryList);
    }

    public function update(User $user, ListItem $item): bool
    {
        return $user->can('update', $item->groceryList);
    }

    public function delete(User $user, ListItem $item): bool
    {
        return $user->can('update', $item->groceryList);
    }
}
