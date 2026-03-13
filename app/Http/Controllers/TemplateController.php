<?php

namespace App\Http\Controllers;

use App\Models\GroceryList;
use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function index(Request $request): View
    {
        $templates = Template::whereNull('user_id')->withCount('items')->get();
        return view('templates.index', compact('templates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'string'], // Comma-separated or newline-separated for simplicity in web form
        ]);

        $template = $request->user()->templates()->create(['name' => $data['name']]);
        
        $itemNames = preg_split('/[\n,]+/', $data['items']);
        foreach ($itemNames as $name) {
            $name = trim($name);
            if (!empty($name)) {
                $template->items()->create(['name' => $name, 'quantity' => 1]);
            }
        }

        return redirect()->route('templates.index')->with('success', 'Template created.');
    }

    public function destroy(Template $template): RedirectResponse
    {
        if ($template->user_id !== auth()->id()) {
            abort(403);
        }
        $template->delete();
        return redirect()->route('templates.index')->with('success', 'Template deleted.');
    }

    /**
     * Apply template items to a grocery list.
     */
    public function apply(Request $request, Template $template, GroceryList $list): RedirectResponse
    {
        // Allow if it's a global template (user_id is NULL), or if user owns it, or if it belongs to list owner
        $isGlobal = is_null($template->user_id);
        $isOwner = $request->user() && $template->user_id === $request->user()->id;
        $isListOwnerTemplate = $template->user_id === $list->user_id;

        if (!$isGlobal && !$isOwner && !$isListOwnerTemplate) {
            abort(403, 'Unauthorized access to template.');
        }

        // Check list access (collaborator or guest with session)
        // For guests, we check the session or the token, but usually the controller middleware or policies handle this.
        // However, we want to be explicit here if needed.

        foreach ($template->items as $tItem) {
            $list->items()->create([
                'name' => $tItem->name,
                'quantity' => $tItem->quantity,
                'position' => $list->items()->max('position') + 1,
            ]);
        }

        return redirect()->route('lists.show', $list)->with('success', "Applied template '{$template->name}'.");
    }
}
