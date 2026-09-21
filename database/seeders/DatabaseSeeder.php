<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::firstOrCreate(
            ['email' => 'admin@shopease.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Demo customer
        User::firstOrCreate(
            ['email' => 'customer@shopease.test'],
            [
                'name' => 'Rahul Sharma',
                'password' => Hash::make('password'),
                'role' => 'customer',
            ]
        );

        $categories = [
            'Fashion' => 'Clothing, footwear and accessories',
            'Electronics' => 'Gadgets and devices',
            'Home & Living' => 'Furniture and decor',
            'Beauty & Health' => 'Skincare, cosmetics and wellness',
            'Sports' => 'Fitness and sporting goods',
            'Toys & Games' => 'Fun for all ages',
            'Books' => 'Fiction and non-fiction',
            'Groceries' => 'Everyday essentials',
        ];

        foreach ($categories as $name => $description) {
            $category = Category::firstOrCreate(['name' => $name], [
                'description' => $description,
                'is_active' => true,
            ]);

            if ($category->products()->count() === 0) {
                for ($i = 1; $i <= 3; $i++) {
                    Product::create([
                        'category_id' => $category->id,
                        'name' => $name . ' Product ' . $i,
                        'description' => 'A great product from the ' . $name . ' category.',
                        'price' => rand(20, 200),
                        'discount_price' => rand(0, 1) ? rand(10, 19) : null,
                        'stock' => rand(5, 100),
                        'is_featured' => $i === 1,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
