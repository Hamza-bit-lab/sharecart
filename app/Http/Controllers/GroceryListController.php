<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShareListRequest;
use App\Http\Requests\StoreGroceryListRequest;
use App\Http\Requests\UpdateGroceryListRequest;
use App\Models\GroceryList;
use App\Models\ListGuestToken;
use App\Models\User;
use App\Models\FcmToken;
use App\Services\FcmService;
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
    protected $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
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

    public function create(): View
    {
        return view('lists.create');
    }

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

        // Fetch System/Global Templates (user_id is NULL)
        $templates = \App\Models\Template::whereNull('user_id')->withCount('items')->get();

        // Settlement Calculation
        $payments = $list->payments()->with('user')->get();
        $totalSpent = $payments->sum('amount');
        
        $participants = [];
        // Add Owner
        $participants[$list->user->id] = [
            'name' => $list->user->name,
            'is_user' => true,
            'spent' => 0
        ];
        // Add Shared Users
        foreach ($list->sharedWith as $u) {
            $participants[$u->id] = [
                'name' => $u->name,
                'is_user' => true,
                'spent' => 0
            ];
        }
        
        // Track guest spending and identities
        foreach ($payments as $p) {
            if ($p->user_id) {
                if (isset($participants[$p->user_id])) {
                    $participants[$p->user_id]['spent'] += $p->amount;
                }
            } else {
                $guestKey = 'guest_' . $p->guest_name;
                if (!isset($participants[$guestKey])) {
                    $participants[$guestKey] = [
                        'name' => $p->guest_name,
                        'is_user' => false,
                        'spent' => 0
                    ];
                }
                $participants[$guestKey]['spent'] += $p->amount;
            }
        }
        
        $participantCount = count($participants);
        $fairShare = $participantCount > 0 ? $totalSpent / $participantCount : 0;
        
        foreach ($participants as $key => &$p) {
            $p['balance'] = $p['spent'] - $fairShare;
        }

        return view('lists.show', compact(
            'list', 'groupedItems', 'allItemsPurchased', 'sort', 
            'inviteLink', 'joinCode', 'guestName', 'guestCollaborators', 
            'templates', 'totalSpent', 'participants', 'fairShare'
        ));
    }

    /**
     * Nudge other collaborators (send a ping).
     */
    public function ping(Request $request, GroceryList $list): RedirectResponse
    {
        if ($request->user()) {
            $this->authorize('update', $list);
            $list->update([
                'last_ping_at' => now(),
                'last_ping_by_id' => $request->user()->id,
                'last_ping_by_name' => $request->user()->name,
            ]);
        } else {
            // Guest ping
            $guestNames = $request->session()->get('guest_names', []);
            $name = $guestNames[$list->id] ?? 'Guest';
            $list->update([
                'last_ping_at' => now(),
                'last_ping_by_id' => null,
                'last_ping_by_name' => $name,
            ]);
        }
        $name = $request->user() ? $request->user()->name : ($list->last_ping_by_name ?: 'Someone');
         
         // Notify all other members
         $query = FcmToken::query()
             ->where(function($query) use ($list) {
                 $query->whereIn('user_id', function($q) use ($list) {
                     $q->select('user_id')->from('list_user')->where('list_id', $list->id);
                 })->orWhere('user_id', $list->user_id);
             });
             
         if ($user = $request->user()) {
             $query->where('user_id', '!=', $user->id);
         }

         $tokens = $query->pluck('token')
             ->filter(fn($token) => !empty($token))
             ->unique()
             ->values()
             ->toArray();
 
         if (!empty($tokens)) {
             $this->fcmService->sendToMany(
                 $tokens,
                 "Nudge!",
                 "{$name} nudged the list {$list->name}",
                 ['list_id' => $list->id, 'type' => 'nudge']
             );
         }
 
         return redirect()->back()->with('success', 'Nudge sent to everyone!');
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
 
             // Notify the list owner
             $this->fcmService->sendToUser(
                 $list->user,
                 "Someone joined your list",
                 "{$request->user()->name} joined your list {$list->name} via code",
                 ['list_id' => $list->id, 'type' => 'user_joined']
             );
 
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
 
         // Notify the list owner
         $this->fcmService->sendToUser(
             $list->user,
             "Someone joined your list",
             "{$user->name} joined your list {$list->name} via invite",
             ['list_id' => $list->id, 'type' => 'user_joined']
         );
 
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

    public function edit(Request $request, GroceryList $list): View|RedirectResponse
    {
        $this->authorize('update', $list);

        return view('lists.edit', compact('list'));
    }

    public function update(UpdateGroceryListRequest $request, GroceryList $list): RedirectResponse
    {
        $list->update($request->validated());

        return redirect()->route('lists.show', $list)
            ->with('success', 'List updated.');
    }

    public function archive(Request $request, GroceryList $list): RedirectResponse
    {
        $this->authorize('update', $list);
        $list->update(['archived_at' => now()]);

        return redirect()->route('lists.index')->with('success', 'List archived.');
    }

    public function restore(Request $request, GroceryList $list): RedirectResponse
    {
        $this->authorize('update', $list);
        $list->update(['archived_at' => null]);

        return redirect()->route('lists.index')->with('success', 'List restored.');
    }

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
 
         // Notify the added user
         $this->fcmService->sendToUser(
             $user,
             "Added to list",
             "{$request->user()->name} added you to list {$list->name}",
             ['list_id' => $list->id, 'type' => 'added_to_list']
         );
 
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

    /**
     * Leave a shared list (for non-owners).
     */
    public function leave(Request $request, GroceryList $list): RedirectResponse
    {
        if ($request->user()) {
            if ($list->user_id === $request->user()->id) {
                return redirect()->back()->with('error', 'You cannot leave a list you own. Delete it instead.');
            }
            $list->sharedWith()->detach($request->user()->id);
            return redirect()->route('lists.index')->with('success', 'You have left the list.');
        } else {
            // Guest leaving
            $guestTokenStr = $request->session()->get('guest_token_' . $list->id);
            if ($guestTokenStr) {
                $tokenRecord = \App\Models\ListGuestToken::findValidByPlainToken($guestTokenStr);
                if ($tokenRecord && $tokenRecord->list_id === $list->id) {
                    $tokenRecord->delete();
                }
                $request->session()->forget('guest_token_' . $list->id);
                $guestNames = $request->session()->get('guest_names', []);
                if (isset($guestNames[$list->id])) {
                    unset($guestNames[$list->id]);
                    $request->session()->put('guest_names', $guestNames);
                }
            }
            return redirect()->route('join')->with('success', 'You have left the list.');
        }
    }
}
