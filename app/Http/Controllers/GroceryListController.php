<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShareListRequest;
use App\Http\Requests\StoreGroceryListRequest;
use App\Http\Requests\UpdateGroceryListRequest;
use App\Models\GroceryList;
use App\Models\ListGuestToken;
use App\Models\User;
use App\Services\SuggestionCategories;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CRUD for grocery lists.
 * Users see only their own lists and lists shared with them.
 */
class GroceryListController extends Controller
{
    /**
     * List all lists the user can access (owned + shared). Supports active vs archived tab.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $baseQuery = GroceryList::query()
            ->where('user_id', $user->id)
            ->orWhereIn('id', $user->sharedLists()->pluck('lists.id'));

        $activeLists = (clone $baseQuery)->active()->withCount('items')->orderBy('updated_at', 'desc')->get();
        $archivedLists = (clone $baseQuery)->archived()->withCount('items')->orderBy('archived_at', 'desc')->get();

        return view('lists.index', compact('activeLists', 'archivedLists'));
    }

    /**
     * Show form to create a new list.
     */
    public function create(): View
    {
        return view('lists.create');
    }

    /**
     * Store a new list.
     */
    public function store(StoreGroceryListRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['join_code'] = GroceryList::generateUniqueJoinCode();
        $list = $request->user()->groceryLists()->create($data);

        return redirect()->route('lists.show', $list)
            ->with('success', 'List created.');
    }

    /**
     * Show a single list with its items, grouped by section (Cooking, Beauty, etc.).
     * Access: owner/collaborator (auth) or guest who joined by code (session).
     */
    public function show(Request $request, GroceryList $list): View|RedirectResponse
    {
        if ($request->user()) {
            $this->authorize('view', $list);
        }

        $list->load(['items' => ['completedBy'], 'sharedWith']);

        $sort = $request->input('sort', 'section');
        if (! in_array($sort, ['section', 'name', 'date'], true)) {
            $sort = 'section';
        }

        $items = $list->items;
        if ($sort === 'name') {
            $items = $items->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
        } elseif ($sort === 'date') {
            $items = $items->sortByDesc('created_at');
        }

        $groupedItems = [];
        foreach ($items as $item) {
            $section = SuggestionCategories::getCategoryForItem($item->name);
            $groupedItems[$section][] = $item;
        }
        $ordered = [];
        foreach (SuggestionCategories::sectionOrder() as $key) {
            if (! empty($groupedItems[$key])) {
                $ordered[$key] = $groupedItems[$key];
            }
        }
        $groupedItems = $ordered;

        $totalItems = $list->items->count();
        $completedCount = $list->items->filter(fn ($item) => (bool) $item->completed)->count();
        $allItemsPurchased = $totalItems > 0 && $completedCount === $totalItems;

        $inviteLink = null;
        $joinCode = null;
        if ($request->user() && $request->user()->can('share', $list)) {
            $list->ensureInviteToken();
            $joinCode = $list->ensureJoinCode();
            $inviteLink = url(route('lists.join', ['token' => $list->invite_token]));
        }

        $guestNames = $request->session()->get('guest_names', []);
        $guestName = $guestNames[$list->id] ?? null;

        $guestCollaborators = ListGuestToken::query()
            ->where('list_id', $list->id)
            ->whereNotNull('display_name')
            ->where('display_name', '!=', '')
            ->where('expires_at', '>', now())
            ->get()
            ->pluck('display_name')
            ->unique()
            ->values();

        return view('lists.show', compact('list', 'groupedItems', 'allItemsPurchased', 'sort', 'inviteLink', 'joinCode', 'guestName', 'guestCollaborators'));
    }

    /**
     * Set a display name for a guest who joined by code (web, session-based).
     * Persists the name so it appears in Collaborators and is used for "completed by".
     */
    public function setGuestName(Request $request, GroceryList $list): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);

        $names = $request->session()->get('guest_names', []);
        $names[$list->id] = $data['name'];
        $request->session()->put('guest_names', $names);

        $tokens = $request->session()->get('guest_list_tokens', []);
        $plain = $tokens[$list->id] ?? null;
        if ($plain) {
            $token = ListGuestToken::findValidByPlainToken($plain);
            if ($token) {
                $token->update(['display_name' => $data['name']]);
            } else {
                $plain = ListGuestToken::createToken($list, 30, $data['name']);
                $tokens[$list->id] = $plain;
                $request->session()->put('guest_list_tokens', $tokens);
            }
        } else {
            $plain = ListGuestToken::createToken($list, 30, $data['name']);
            $tokens[$list->id] = $plain;
            $request->session()->put('guest_list_tokens', $tokens);
        }

        return redirect()->route('lists.show', $list)->with('success', 'Name saved. You’ll show up in the list’s collaborators.');
    }

    /**
     * Show form to join a list by 5-digit code (public, no login).
     */
    public function joinByCodeForm(): View
    {
        return view('lists.join-by-code');
    }

    /**
     * Process join-by-code: add list to session (guest) or as collaborator (auth), redirect to list.
     */
    public function joinByCode(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:5']]);
        $code = strtoupper($request->input('code'));

        $list = GroceryList::query()->where('join_code', $code)->first();
        if (! $list) {
            return redirect()->back()->with('error', 'No list found with that code.')->withInput();
        }

        if ($request->user()) {
            if ($list->user_id === $request->user()->id) {
                return redirect()->route('lists.show', $list)->with('success', 'You own this list.');
            }
            $list->sharedWith()->syncWithoutDetaching([$request->user()->id]);

            return redirect()->route('lists.show', $list)->with('success', 'You have been added to this list.');
        }

        $ids = session('guest_list_ids', []);
        if (! in_array($list->id, $ids, true)) {
            $ids[] = $list->id;
            session(['guest_list_ids' => $ids]);
        }

        return redirect()->route('lists.show', $list)->with('success', 'You joined the list. You can view and edit without an account.');
    }

    /**
     * Join a list via invite link (token). Guests are redirected to login, then back here.
     */
    public function joinByInvite(Request $request, string $token): View|RedirectResponse
    {
        $list = GroceryList::query()->where('invite_token', $token)->first();

        if (! $list) {
            abort(404, 'Invite link invalid or expired.');
        }

        if (! $request->user()) {
            return redirect()->guest(route('login'));
        }

        $user = $request->user();
        if ($list->user_id === $user->id) {
            return redirect()->route('lists.show', $list)->with('success', 'You own this list.');
        }

        $list->sharedWith()->syncWithoutDetaching([$user->id]);

        return redirect()->route('lists.show', $list)->with('success', 'You have been added to this list.');
    }

    /**
     * Uncheck all items (reset list for next shopping trip). Any collaborator or guest (by code) can do this.
     */
    public function resetItems(Request $request, GroceryList $list): RedirectResponse
    {
        if ($request->user()) {
            $this->authorize('update', $list);
        }

        $list->items()->update([
            'completed' => false,
            'completed_by' => null,
            'completed_at' => null,
        ]);

        return redirect()->back()->with('success', 'List reset. Ready for your next trip!');
    }

    /**
     * Show form to edit list name.
     */
    public function edit(Request $request, GroceryList $list): View|RedirectResponse
    {
        $this->authorize('update', $list);

        return view('lists.edit', compact('list'));
    }

    /**
     * Update list name.
     */
    public function update(UpdateGroceryListRequest $request, GroceryList $list): RedirectResponse
    {
        $list->update($request->validated());

        return redirect()->route('lists.show', $list)
            ->with('success', 'List updated.');
    }

    /**
     * Archive the list (move to archived). Requires update permission.
     */
    public function archive(Request $request, GroceryList $list): RedirectResponse
    {
        $this->authorize('update', $list);

        $list->update(['archived_at' => now()]);

        return redirect()->route('lists.index')
            ->with('success', 'List archived.');
    }

    /**
     * Restore an archived list. Requires update permission.
     */
    public function restore(Request $request, GroceryList $list): RedirectResponse
    {
        $this->authorize('update', $list);

        $list->update(['archived_at' => null]);

        return redirect()->route('lists.index')
            ->with('success', 'List restored.');
    }

    /**
     * Delete the list (owner only).
     */
    public function destroy(Request $request, GroceryList $list): RedirectResponse
    {
        $this->authorize('delete', $list);

        $list->delete();

        return redirect()->route('lists.index', ['tab' => 'archived'])
            ->with('success', 'List deleted.');
    }

    /**
     * Share list with another user (by email). Owner only.
     */
    public function share(ShareListRequest $request, GroceryList $list): RedirectResponse
    {
        $user = User::where('email', $request->validated('email'))->firstOrFail();
        $list->sharedWith()->syncWithoutDetaching([$user->id]);

        return redirect()->back()->with('success', 'List shared with ' . $user->email . '.');
    }

  
    public function unshare(Request $request, GroceryList $list, User $user): RedirectResponse
    {
        $this->authorize('share', $list);
        if ($list->user_id === $user->id) {
            return redirect()->back()->with('error', 'Cannot remove the list owner.');
        }
        $list->sharedWith()->detach($user->id);

        return redirect()->back()->with('success', 'Access removed.');
    }
}
