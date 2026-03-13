<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing global templates to ensure a clean state
        Template::whereNull('user_id')->delete();

        $templates = [
            [
                'name' => 'Monthly Grocery',
                'items' => [
                    ['name' => 'Milk', 'quantity' => 2],
                    ['name' => 'Eggs', 'quantity' => 1],
                    ['name' => 'Bread', 'quantity' => 2],
                    ['name' => 'Butter', 'quantity' => 1],
                    ['name' => 'Rice (5kg)', 'quantity' => 1],
                    ['name' => 'Flour', 'quantity' => 1],
                    ['name' => 'Cooking Oil', 'quantity' => 1],
                    ['name' => 'Sugar', 'quantity' => 1],
                    ['name' => 'Tea Bags', 'quantity' => 1],
                    ['name' => 'Salt', 'quantity' => 1],
                ]
            ],
            [
                'name' => 'Party Essentials',
                'items' => [
                    ['name' => 'Soft Drinks', 'quantity' => 6],
                    ['name' => 'Potato Chips', 'quantity' => 3],
                    ['name' => 'Dip/Salsa', 'quantity' => 2],
                    ['name' => 'Balloons', 'quantity' => 20],
                    ['name' => 'Plastic Cups', 'quantity' => 1],
                    ['name' => 'Disposable Plates', 'quantity' => 1],
                    ['name' => 'Cake', 'quantity' => 1],
                    ['name' => 'Pizza Bases', 'quantity' => 4],
                    ['name' => 'Cheese', 'quantity' => 2],
                    ['name' => 'Napkins', 'quantity' => 1],
                ]
            ],
            [
                'name' => 'Gym/Fitness Diet',
                'items' => [
                    ['name' => 'Oats', 'quantity' => 1],
                    ['name' => 'Peanut Butter', 'quantity' => 1],
                    ['name' => 'Bananas', 'quantity' => 1],
                    ['name' => 'Protein Bars', 'quantity' => 5],
                    ['name' => 'Chicken Breast', 'quantity' => 2],
                    ['name' => 'Spinach', 'quantity' => 1],
                    ['name' => 'Greek Yogurt', 'quantity' => 2],
                ]
            ],
            [
                'name' => 'Quick Breakfast',
                'items' => [
                    ['name' => 'Cereal', 'quantity' => 1],
                    ['name' => 'Milk', 'quantity' => 1],
                    ['name' => 'Coffee', 'quantity' => 1],
                    ['name' => 'Orange Juice', 'quantity' => 1],
                    ['name' => 'Jam', 'quantity' => 1],
                    ['name' => 'Whole Wheat Bread', 'quantity' => 1],
                ]
            ],
            [
                'name' => 'Weekend BBQ',
                'items' => [
                    ['name' => 'Beef Steaks', 'quantity' => 4],
                    ['name' => 'Burger Patties', 'quantity' => 8],
                    ['name' => 'Burger Buns', 'quantity' => 1],
                    ['name' => 'Charcoal', 'quantity' => 1],
                    ['name' => 'Ketchup', 'quantity' => 1],
                    ['name' => 'Mustard', 'quantity' => 1],
                    ['name' => 'Mix Salad', 'quantity' => 1],
                ]
            ]
        ];

        foreach ($templates as $tData) {
            $template = Template::create(['name' => $tData['name'], 'user_id' => null]);

            foreach ($tData['items'] as $item) {
                $template->items()->create($item);
            }
        }
    }
}
