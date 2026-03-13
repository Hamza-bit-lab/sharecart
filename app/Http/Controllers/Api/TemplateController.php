<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\GroceryList;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $templates = Template::whereNull('user_id')->with('items')->get();
        return response()->json([
            'success' => true,
            'data' => ['templates' => $templates],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $template = $request->user()->templates()->create(['name' => $data['name']]);
        
        foreach ($data['items'] as $item) {
            $template->items()->create([
                'name' => $item['name'],
                'quantity' => $item['quantity'] ?? 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => ['template' => $template->load('items')],
        ], 201);
    }

    public function show(Request $request, Template $template): JsonResponse
    {
        if ($template->user_id !== $request->user()->id) {
            return ApiResponse::error('Unauthorized.', 403);
        }

        return response()->json([
            'success' => true,
            'data' => ['template' => $template->load('items')],
        ]);
    }

    public function destroy(Request $request, Template $template): JsonResponse
    {
        if ($template->user_id !== $request->user()->id) {
            return ApiResponse::error('Unauthorized.', 403);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted.',
        ]);
    }

    /**
     * Apply template items to a grocery list.
     */
    public function apply(Request $request, Template $template, GroceryList $list): JsonResponse
    {
        // Allow if it's a global template (user_id is NULL), or if user owns it, or if it belongs to list owner
        $isGlobal = is_null($template->user_id);
        $isOwner = $request->user() && $template->user_id === $request->user()->id;
        $isListOwnerTemplate = $template->user_id === $list->user_id;

        if (!$isGlobal && !$isOwner && !$isListOwnerTemplate) {
            return ApiResponse::error('Unauthorized access to template.', 403);
        }

        // List access check (policy usually handled in middleware or manually)
        $this->authorize('update', $list);

        foreach ($template->items as $tItem) {
            $list->items()->create([
                'name' => $tItem->name,
                'quantity' => $tItem->quantity,
                'position' => $list->items()->max('position') + 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Applied template '{$template->name}' to list '{$list->name}'.",
            'data' => [
                'items' => $list->items()->orderBy('position')->get(),
            ],
        ]);
    }
}
