<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BuyerMarketplaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@ukomp.test'],
            [
                'firebase_uid' => $this->firebaseUidFromEmail('admin@ukomp.test'),
                'name' => 'Marketplace Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_email_verified' => true,
            ]
        );

        $categories = collect([
            'Food',
            'Electronics',
            'Fashion',
            'Home & Living',
            'Beauty',
            'Sports',
            'Books',
            'Garden',
        ])->map(function (string $name): Category {
            return Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        });

        $sellers = collect(range(1, 5))->map(function (int $index): array {
            $email = "seller{$index}@ukomp.test";
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'firebase_uid' => $this->firebaseUidFromEmail($email),
                    'name' => "Seller {$index}",
                    'password' => Hash::make('password'),
                    'role' => 'seller',
                    'is_email_verified' => true,
                ]
            );

            $storeName = "Seller {$index} Store";
            $store = Store::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $storeName,
                    'slug' => Str::slug($storeName),
                    'description' => "Curated marketplace store #{$index}.",
                    'logo' => "https://picsum.photos/seed/store{$index}/300/300",
                ]
            );

            return ['user' => $user, 'store' => $store];
        });

        $buyers = collect(range(1, 12))->map(function (int $index): User {
            $email = "buyer{$index}@ukomp.test";

            return User::updateOrCreate(
                ['email' => $email],
                [
                    'firebase_uid' => $this->firebaseUidFromEmail($email),
                    'name' => "Buyer {$index}",
                    'password' => Hash::make('password'),
                    'role' => 'buyer',
                    'is_email_verified' => true,
                ]
            );
        });

        $productCount = 26;
        $products = collect(range(1, $productCount))->map(function (int $index) use ($sellers, $categories): Product {
            $sellerData = $sellers->random();
            $category = $categories->random();
            $name = "Marketplace Product {$index}";
            $slug = Str::slug($name);

            return Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'store_id' => $sellerData['store']->id,
                    'category_id' => $category->id,
                    'seller_id' => $sellerData['user']->id,
                    'name' => $name,
                    'description' => "Description for {$name} in {$category->name}.",
                    'price' => fake()->randomFloat(2, 10, 2000),
                    'stock' => fake()->numberBetween(5, 150),
                    'thumbnail' => "https://picsum.photos/seed/product{$index}/800/800",
                    'status' => 'published',
                ]
            );
        });

        foreach ($products as $product) {
            foreach (range(1, 3) as $imageIndex) {
                $imageUrl = "https://picsum.photos/seed/{$product->id}_{$imageIndex}/900/900";

                ProductImage::updateOrCreate(
                    ['product_id' => $product->id, 'image_url' => $imageUrl],
                    [
                        'url' => $imageUrl,
                        'is_primary' => $imageIndex === 1,
                    ]
                );
            }

            foreach (range(1, fake()->numberBetween(1, 4)) as $reviewIndex) {
                $buyer = $buyers->random();

                Review::create([
                    'user_id' => $buyer->id,
                    'product_id' => $product->id,
                    'rating' => fake()->numberBetween(3, 5),
                    'comment' => fake()->sentence(12),
                ]);
            }
        }

        // Keep admin in memory to avoid static analysis warnings on write-only variable.
        if (! $admin->exists) {
            throw new \RuntimeException('Admin user seeding failed.');
        }
    }

    private function firebaseUidFromEmail(string $email): string
    {
        return Str::substr(hash('sha256', $email), 0, 28);
    }
}
