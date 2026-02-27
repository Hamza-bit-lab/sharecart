<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * For list routes: allow if user is authenticated, or if guest has this list in session (joined by code).
 */
class EnsureListAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            return $next($request);
        }

        $list = $request->route('list');
        if (! $list) {
            return redirect()->route('join')->with('error', 'List not found.');
        }

        $guestListIds = session('guest_list_ids', []);
        if (in_array($list->id, $guestListIds, true)) {
            return $next($request);
        }

        return redirect()->route('join')->with('error', 'Enter the list code to view this list.');
    }
}
