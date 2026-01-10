<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Seed 15 categories for the application.
     */
    public function run(): void
    {
        // Get admin user for created_by and updated_by
        $adminUser = User::where('email', 'admin@mail.com')->first() ?? User::first();

        // 15 Categories with diverse names
        $categories = [
            ['name' => 'Grocery', 'slug' => 'grocery', 'order' => 1, 'description' => 'Food items and daily essentials'],
            ['name' => 'Cosmetics', 'slug' => 'cosmetics', 'order' => 2, 'description' => 'Beauty and personal care products'],
            ['name' => 'Electronics', 'slug' => 'electronics', 'order' => 3, 'description' => 'Electronic devices and gadgets'],
            ['name' => 'Clothing', 'slug' => 'clothing', 'order' => 4, 'description' => 'Apparel and fashion items'],
            ['name' => 'Home & Kitchen', 'slug' => 'home-kitchen', 'order' => 5, 'description' => 'Household and kitchen essentials'],
            ['name' => 'Sports & Outdoors', 'slug' => 'sports-outdoors', 'order' => 6, 'description' => 'Sports equipment and outdoor gear'],
            ['name' => 'Books & Stationery', 'slug' => 'books-stationery', 'order' => 7, 'description' => 'Books, notebooks, and office supplies'],
            ['name' => 'Toys & Games', 'slug' => 'toys-games', 'order' => 8, 'description' => 'Children toys and board games'],
            ['name' => 'Health & Wellness', 'slug' => 'health-wellness', 'order' => 9, 'description' => 'Health supplements and wellness products'],
            ['name' => 'Automotive', 'slug' => 'automotive', 'order' => 10, 'description' => 'Car accessories and automotive parts'],
            ['name' => 'Pet Supplies', 'slug' => 'pet-supplies', 'order' => 11, 'description' => 'Pet food and accessories'],
            ['name' => 'Baby Products', 'slug' => 'baby-products', 'order' => 12, 'description' => 'Baby care and essentials'],
            ['name' => 'Furniture', 'slug' => 'furniture', 'order' => 13, 'description' => 'Home and office furniture'],
            ['name' => 'Jewelry & Accessories', 'slug' => 'jewelry-accessories', 'order' => 14, 'description' => 'Jewelry and fashion accessories'],
            ['name' => 'Garden & Tools', 'slug' => 'garden-tools', 'order' => 15, 'description' => 'Gardening supplies and tools'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                array_merge($category, [
                    'is_active' => true,
                    'created_by' => $adminUser?->id,
                    'updated_by' => $adminUser?->id,
                ])
            );
        }

        $this->command->info('15 categories seeded successfully!');
    }
}

































