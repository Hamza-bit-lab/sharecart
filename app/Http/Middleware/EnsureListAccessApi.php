<?php

namespace App\Http\Middleware;

use App\Models\GroceryList;
use App\Models\ListGuestToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * For API list routes: allow access if either (1) authenticated via Sanctum and can access list,
 * or (2) Bearer token is a valid guest token for this list (no login).
 */
class EnsureListAccessApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $list = $request->route('list');
        $listId = $list instanceof GroceryList ? $list->id : (int) $list;

        $bearer = $request->bearerToken();
        $bearer = $bearer !== null ? trim($bearer) : '';

        if ($bearer === '') {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        // 1) Try guest token (no login)
        $guestToken = ListGuestToken::findValidByPlainToken($bearer);
        if ($guestToken && (int) $guestToken->list_id === $listId) {
            $request->attributes->set('guest_list_id', $listId);
            $request->attributes->set('guest_display_name', $guestToken->display_name);

            return $next($request);
        }

        // 2) Try Sanctum (logged-in user)
        $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($bearer);
        if ($accessToken && $accessToken->tokenable) {
            $user = $accessToken->tokenable;
            if ($this->userCanAccessList($user, $listId)) {
                $request->setUserResolver(fn () => $user);
                Auth::setUser($user);

                return $next($request);
            }
        }

        return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
    }

    private function userCanAccessList($user, int $listId): bool
    {
        return GroceryList::query()
            ->where('id', $listId)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('sharedWith', fn ($q2) => $q2->where('user_id', $user->id));
            })
            ->exists();
    }
}
