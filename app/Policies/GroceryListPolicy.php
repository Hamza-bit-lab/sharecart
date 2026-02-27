<?php

namespace App\Policies;

use App\Models\GroceryList;
use App\Models\User;

/**
 * Authorization for grocery list actions.
 * Users can manage lists they own or that are shared with them.
 */
class GroceryListPolicy
{
    /**
     * User can view if owner or shared with.
     */
    public function view(User $user, GroceryList $list): bool
    {
        return $list->user_id === $user->id
            || $list->sharedWith()->where('user_id', $user->id)->exists();
    }

    /**
     * User can update if owner or shared with.
     */
    public function update(User $user, GroceryList $list): bool
    {
        return $this->view($user, $list);
    }

    /**
     * Only the owner can delete the list.
     */
    public function delete(User $user, GroceryList $list): bool
    {
        return $list->user_id === $user->id;
    }

    /**
     * Only the owner can share the list.
     */
    public function share(User $user, GroceryList $list): bool
    {
        return $list->user_id === $user->id;
    }
}
