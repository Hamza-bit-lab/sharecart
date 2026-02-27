<?php

namespace App\Services;

/**
 * Defines suggestion categories (beauty, cooking, etc.) with keywords to detect
 * the category and items to suggest. Used so that e.g. adding "lotion" shows
 * shampoo, face wash; adding "cooking oil" shows flour, spices, etc.
 */
class SuggestionCategories
{
    /**
     * Category definitions: keyword phrases (lowercase) to detect category,
     * and item names to suggest for that category.
     *
     * @return array<string, array{keywords: list<string>, items: list<string>}>
     */
    public static function all(): array
    {
        return [
            'beauty' => [
                'keywords' => [
                    'lotion', 'shampoo', 'face wash', 'cream', 'moisturizer', 'soap', 'conditioner',
                    'body wash', 'sunscreen', 'serum', 'toner', 'cleanser', 'lip balm', 'hand cream',
                    'body lotion', 'face cream', 'hair oil', 'mask', 'scrub', 'gel', 'deodorant',
                ],
                'items' => [
                    'Shampoo', 'Face wash', 'Lotion', 'Body lotion', 'Moisturizer', 'Soap',
                    'Conditioner', 'Body wash', 'Sunscreen', 'Face cream', 'Hand cream',
                    'Cleanser', 'Toner', 'Serum', 'Lip balm', 'Hair oil', 'Face mask',
                    'Body scrub', 'Deodorant', 'Shaving cream', 'Toothpaste', 'Mouthwash',
                ],
            ],
            'fruits' => [
                'keywords' => [
                    'banana', 'apple', 'orange', 'mango', 'grapes', 'strawberry', 'watermelon',
                    'papaya', 'pineapple', 'pomegranate', 'guava', 'kiwi', 'melon', 'pear', 'peach',
                    'plum', 'cherry', 'blueberry', 'blackberry', 'raspberry', 'coconut', 'litchi',
                    'apricot', 'fig', 'dates', 'avocado', 'lemon', 'lime', 'cranberry', 'dragon fruit',
                ],
                'items' => [
                    'Banana', 'Apple', 'Orange', 'Mango', 'Grapes', 'Strawberry', 'Watermelon',
                    'Papaya', 'Pineapple', 'Pomegranate', 'Guava', 'Kiwi', 'Pear', 'Peach',
                    'Coconut', 'Lemon', 'Lime', 'Avocado', 'Dates', 'Berries',
                ],
            ],
            'vegetables' => [
                'keywords' => [
                    'potato', 'tomato', 'onion', 'carrot', 'spinach', 'cabbage', 'cauliflower',
                    'broccoli', 'beans', 'peas', 'cucumber', 'ladyfinger', 'okra', 'brinjal',
                    'eggplant', 'capsicum', 'bell pepper', 'ginger', 'garlic', 'pumpkin',
                    'radish', 'beetroot', 'lettuce', 'coriander', 'mint', 'green beans',
                    'corn', 'sweet potato', 'bottle gourd', 'bitter gourd', 'ridge gourd',
                    'zucchini', 'mushroom', 'turnip', 'leek', 'celery',
                ],
                'items' => [
                    'Potato', 'Tomato', 'Onion', 'Carrot', 'Spinach', 'Cabbage', 'Cauliflower',
                    'Broccoli', 'Beans', 'Peas', 'Cucumber', 'Ladyfinger', 'Brinjal',
                    'Capsicum', 'Ginger', 'Garlic', 'Pumpkin', 'Radish', 'Beetroot',
                    'Lettuce', 'Coriander', 'Mint', 'Green beans', 'Corn', 'Sweet potato',
                    'Zucchini', 'Mushroom',
                ],
            ],
            'cooking' => [
                'keywords' => [
                    'cooking oil', 'oil', 'flour', 'spices', 'rice', 'salt', 'sugar', 'vinegar',
                    'soy sauce', 'pasta', 'noodles', 'dal', 'lentils', 'beans', 'atta', 'bread',
                    'butter', 'ghee', 'mustard oil', 'olive oil', 'turmeric', 'cumin', 'coriander',
                    'garam masala', 'chilli', 'pepper', 'baking soda', 'yeast',
                ],
                'items' => [
                    'Cooking oil', 'Flour', 'Rice', 'Salt', 'Sugar', 'Spices', 'Vinegar',
                    'Soy sauce', 'Pasta', 'Noodles', 'Dal', 'Lentils', 'Butter', 'Ghee',
                    'Turmeric', 'Cumin', 'Coriander powder', 'Chilli powder', 'Garam masala',
                    'Black pepper', 'Baking soda', 'Yeast', 'Bread', 'Atta', 'Beans',
                    'Olive oil', 'Mustard oil', 'Honey', 'Jelly', 'Peanut butter',
                ],
            ],
            'dairy_bakery' => [
                'keywords' => [
                    'milk', 'bread', 'eggs', 'cheese', 'curd', 'yogurt', 'butter',
                    'paneer', 'toast', 'bun', 'croissant',
                ],
                'items' => [
                    'Milk', 'Bread', 'Eggs', 'Cheese', 'Curd', 'Yogurt', 'Butter',
                    'Paneer', 'Cream', 'Toast', 'Bun', 'Croissant',
                ],
            ],
            'beverages' => [
                'keywords' => [
                    'tea', 'coffee', 'juice', 'water', 'cold drink', 'soda', 'energy drink',
                ],
                'items' => [
                    'Tea', 'Coffee', 'Juice', 'Water', 'Cold drink', 'Milk', 'Green tea',
                    'Fruit juice', 'Soda', 'Energy drink',
                ],
            ],
            'snacks' => [
                'keywords' => [
                    'chips', 'biscuits', 'cookies', 'nuts', 'chocolate', 'candy', 'crackers',
                ],
                'items' => [
                    'Chips', 'Biscuits', 'Cookies', 'Nuts', 'Chocolate', 'Candy', 'Crackers',
                    'Popcorn', 'Dry fruits', 'Peanuts',
                ],
            ],
            'electronics' => [
                'keywords' => [
                    'mobile', 'phone', 'charger', 'earphones', 'headphones', 'laptop', 'tablet',
                    'cable', 'adapter', 'power bank', 'screen guard', 'case', 'cover',
                ],
                'items' => [
                    'Charger', 'Earphones', 'Mobile case', 'Screen guard', 'Power bank',
                    'Data cable', 'Adapter', 'Headphones', 'Memory card', 'Back cover',
                ],
            ],
        ];
    }

    /**
     * Detect category from a search string or a list of item names (context).
     * When context is used, last item in the list is checked first (most recently added wins).
     * Returns the first matching category key or null.
     */
    public static function detectCategory(string $search = '', array $contextItems = []): ?string
    {
        $toCheck = [];
        if ($search !== '') {
            $toCheck[] = strtolower(trim($search));
        }
        // Reverse so most recently added (last in list) drives the category
        foreach (array_reverse($contextItems) as $item) {
            $item = trim((string) $item);
            if ($item !== '') {
                $toCheck[] = strtolower($item);
            }
        }

        $categories = self::all();
        foreach ($toCheck as $term) {
            foreach ($categories as $key => $def) {
                foreach ($def['keywords'] as $keyword) {
                    // Word-boundary match so "mobile" does not match "oil", but "cooking oil" matches "oil"
                    $kwEscaped = preg_quote($keyword, '/');
                    $termEscaped = preg_quote($term, '/');
                    if ((strlen($keyword) > 0 && preg_match('/\b' . $kwEscaped . '\b/i', $term))
                        || (strlen($term) > 0 && preg_match('/\b' . $termEscaped . '\b/i', $keyword))) {
                        return $key;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get the category key for a single item name (for grouping list items into sections).
     * Returns 'other' if no category matches.
     */
    public static function getCategoryForItem(string $itemName): string
    {
        $cat = self::detectCategory(trim($itemName), []);
        return $cat ?? 'other';
    }

    /**
     * Display labels for section headers. Keys must match getCategoryForItem / category keys.
     *
     * @return array<string, string>
     */
    public static function sectionLabels(): array
    {
        return [
            'fruits' => 'Fruits',
            'vegetables' => 'Vegetables',
            'beauty' => 'Beauty',
            'cooking' => 'Cooking',
            'dairy_bakery' => 'Dairy & Bakery',
            'beverages' => 'Beverages',
            'snacks' => 'Snacks',
            'electronics' => 'Electronics',
            'other' => 'Other',
        ];
    }

    /**
     * Ordered list of section keys for consistent display order.
     *
     * @return list<string>
     */
    public static function sectionOrder(): array
    {
        return ['fruits', 'vegetables', 'cooking', 'beauty', 'dairy_bakery', 'beverages', 'snacks', 'electronics', 'other'];
    }

    /**
     * Get suggestion items for a category, optionally filtered by search.
     * Excludes items that are in the exclude list (e.g. already on list).
     *
     * @return array<int, array{name: string, count: int}>
     */
    public static function getItemsForCategory(string $category, string $search = '', array $exclude = [], int $limit = 5): array
    {
        $categories = self::all();
        if (! isset($categories[$category])) {
            return [];
        }

        $items = $categories[$category]['items'];
        $search = strtolower(trim($search));
        $excludeLower = array_map('strtolower', array_map('trim', $exclude));

        $matched = [];
        $rest = [];
        foreach ($items as $name) {
            $nameTrim = trim($name);
            if (in_array(strtolower($nameTrim), $excludeLower, true)) {
                continue;
            }
            if ($search !== '' && str_contains(strtolower($nameTrim), $search)) {
                $matched[] = $nameTrim;
            } else {
                $rest[] = $nameTrim;
            }
        }

        $result = array_merge($matched, $rest);
        $result = array_slice($result, 0, $limit);

        return array_map(fn ($name) => ['name' => $name, 'count' => 0], $result);
    }
}
