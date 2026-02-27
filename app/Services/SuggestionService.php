<?php

namespace App\Services;

use App\Models\ListItem;
use Illuminate\Support\Facades\DB;

/**
 * Smart suggestions: blend of
 * 1) Category-based (beauty, cooking, etc.) when the item matches.
 * 2) Co-occurrence from DB: "what other people add together with this item" (e.g. Mobile → Charger, Case).
 * 3) Popular from DB: "what everyone adds most often".
 * So even for items like "Mobile" we suggest related and popular items from real usage.
 */
class SuggestionService
{
    /**
     * Get suggestions: category-based (beauty, cooking, fruits, etc.), co-occurrence from DB,
     * and popular items. No external API required.
     *
     * @param  array<int, string>  $contextItemNames  Names of items already on the list.
     * @return array<int, array{name: string, count: int}>
     */
    public function getSuggestions(string $search = '', int $limit = 8, array $contextItemNames = []): array
    {
        $search = trim($search);
        $limit = min(max($limit, 1), 50);
        $contextItemNames = array_values(array_filter(array_map('trim', $contextItemNames)));

        $triggers = $contextItemNames;
        if ($search !== '') {
            $triggers[] = $search;
        }
        $exclude = array_unique(array_merge($contextItemNames, $search !== '' ? [$search] : []));

        // 1) Category-based suggestions (when item matches a known category)
        $categorySuggestions = [];
        if ($search !== '') {
            $category = SuggestionCategories::detectCategory($search, []);
            if ($category !== null) {
                $categorySuggestions = SuggestionCategories::getItemsForCategory(
                    $category,
                    $search,
                    $exclude,
                    min(4, $limit)
                );
            }
        }
        if (empty($categorySuggestions) && ! empty($contextItemNames)) {
            $category = SuggestionCategories::detectCategory('', $contextItemNames);
            if ($category !== null) {
                $categorySuggestions = SuggestionCategories::getItemsForCategory(
                    $category,
                    '',
                    $exclude,
                    min(4, $limit)
                );
            }
        }

        // 2) Co-occurrence: what other people frequently add in the same lists (e.g. Mobile → Charger, Case)
        $coOccurrence = $this->getCoOccurrenceSuggestions($triggers, $exclude, $limit);

        // 3) Popular: what everyone adds most often across all lists
        $popular = $this->getPopularSuggestions($limit * 2);
        $popularFiltered = [];
        $excludeLower = array_map('strtolower', $exclude);
        foreach ($popular as $item) {
            if (! in_array(strtolower($item['name']), $excludeLower, true)) {
                $popularFiltered[] = $item;
            }
        }

        // 4) Merge: category, co-occurrence, popular; no duplicates, up to limit
        $seen = [];
        $out = [];
        foreach ($categorySuggestions as $item) {
            $name = $item['name'];
            if (! isset($seen[strtolower($name)])) {
                $seen[strtolower($name)] = true;
                $out[] = $item;
                if (count($out) >= $limit) {
                    return $out;
                }
            }
        }
        foreach ($coOccurrence as $item) {
            $name = $item['name'];
            if (! isset($seen[strtolower($name)])) {
                $seen[strtolower($name)] = true;
                $out[] = $item;
                if (count($out) >= $limit) {
                    return $out;
                }
            }
        }
        foreach ($popularFiltered as $item) {
            $name = $item['name'];
            if (! isset($seen[strtolower($name)])) {
                $seen[strtolower($name)] = true;
                $out[] = $item;
                if (count($out) >= $limit) {
                    return $out;
                }
            }
        }

        return $out;
    }

    /**
     * Items that frequently appear in the same lists as the trigger terms (what others add together).
     * E.g. triggers = ["Mobile"] → lists containing "Mobile" → other items in those lists (Charger, Case, etc.).
     *
     * @param  array<int, string>  $triggerTerms  Search or context item names.
     * @param  array<int, string>  $excludeNames  Do not suggest these (already on list / same as search).
     * @return array<int, array{name: string, count: int}>
     */
    private function getCoOccurrenceSuggestions(array $triggerTerms, array $excludeNames, int $limit): array
    {
        if (empty($triggerTerms)) {
            return [];
        }

        $listIds = collect();
        foreach ($triggerTerms as $term) {
            $term = trim($term);
            if ($term === '') {
                continue;
            }
            $escaped = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $term);
            $like = '%' . $escaped . '%';
            $ids = ListItem::query()
                ->where('name', 'like', $like)
                ->distinct()
                ->pluck('list_id');
            $listIds = $listIds->merge($ids);
        }
        $listIds = $listIds->unique()->filter()->values();
        if ($listIds->isEmpty()) {
            return [];
        }

        // Exclude exact names only (so we still suggest "Mobile Charger" when they typed "Mobile")
        $exactExclude = array_unique(array_filter(array_map(function ($x) {
            return strtolower(trim($x));
        }, array_merge($triggerTerms, $excludeNames))));

        $query = ListItem::query()
            ->whereIn('list_id', $listIds)
            ->select('name', DB::raw('COUNT(*) as count'))
            ->groupBy('name')
            ->orderByDesc('count')
            ->limit($limit);

        if (! empty($exactExclude)) {
            $placeholders = implode(',', array_fill(0, count($exactExclude), '?'));
            $query->whereRaw('LOWER(TRIM(name)) NOT IN (' . $placeholders . ')', array_values($exactExclude));
        }

        $results = $query->get();

        return $results->map(fn ($row) => [
            'name' => $row->name,
            'count' => (int) $row->count,
        ])->values()->all();
    }

    /**
     * Top popular items by frequency across all lists (what people add most often).
     *
     * @return array<int, array{name: string, count: int}>
     */
    private function getPopularSuggestions(int $limit): array
    {
        $results = ListItem::query()
            ->select('name', DB::raw('COUNT(*) as count'))
            ->groupBy('name')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();

        return $results->map(fn ($row) => [
            'name' => $row->name,
            'count' => (int) $row->count,
        ])->values()->all();
    }
}
