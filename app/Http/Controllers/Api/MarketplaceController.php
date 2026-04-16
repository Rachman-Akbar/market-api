<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketplaceController extends Controller
{
    private function sourceDb(): string
    {
        return (string) config('database.connections.mysql.database', 'kisha_api');
    }

    public function products(Request $request): JsonResponse
    {
        $sourceDb = $this->sourceDb();

        $rows = DB::table("{$sourceDb}.products as p")
            ->leftJoin("{$sourceDb}.product_categories as c", 'c.id', '=', 'p.category_id')
            ->leftJoin("{$sourceDb}.sellers as s", 's.id', '=', 'p.seller_id')
            ->where('p.status', 'active')
            ->select([
                'p.id',
                'p.name',
                'p.slug',
                'p.description',
                'p.price',
                'p.stock',
                'p.main_image_path',
                'p.category_id',
                'c.name as category_name',
                's.id as seller_id',
                's.store_name',
                's.store_slug',
                's.store_logo',
            ])
            ->orderByDesc('p.id')
            ->get();

        $products = $rows->map(function ($row) {
            $categorySlug = $row->category_name ? Str::slug($row->category_name) : null;

            return [
                'id' => (int) $row->id,
                'name' => $row->name,
                'slug' => $row->slug,
                'description' => $row->description,
                'price' => (float) $row->price,
                'stock' => (int) ($row->stock ?? 0),
                'thumbnail' => $row->main_image_path,
                'category' => $row->category_id ? [
                    'id' => (int) $row->category_id,
                    'name' => $row->category_name,
                    'slug' => $categorySlug,
                ] : null,
                'store' => $row->seller_id ? [
                    'id' => (int) $row->seller_id,
                    'name' => $row->store_name,
                    'slug' => $row->store_slug,
                    'logo' => $row->store_logo,
                ] : null,
                'images' => [],
            ];
        });

        if ($request->filled('category')) {
            $categorySlug = $request->string('category')->toString();

            $products = $products->filter(function (array $item) use ($categorySlug): bool {
                return isset($item['category']['slug']) && $item['category']['slug'] === $categorySlug;
            })->values();
        }

        return response()->json([
            'data' => $products,
        ]);
    }

    public function productBySlug(string $slug): JsonResponse
    {
        $sourceDb = $this->sourceDb();

        $row = DB::table("{$sourceDb}.products as p")
            ->leftJoin("{$sourceDb}.product_categories as c", 'c.id', '=', 'p.category_id')
            ->leftJoin("{$sourceDb}.sellers as s", 's.id', '=', 'p.seller_id')
            ->where('p.slug', $slug)
            ->where('p.status', 'active')
            ->select([
                'p.id',
                'p.name',
                'p.slug',
                'p.description',
                'p.price',
                'p.stock',
                'p.main_image_path',
                'p.category_id',
                'c.name as category_name',
                's.id as seller_id',
                's.store_name',
                's.store_slug',
                's.store_logo',
                's.store_description',
            ])
            ->first();

        abort_unless($row, 404);

        $product = [
            'id' => (int) $row->id,
            'name' => $row->name,
            'slug' => $row->slug,
            'description' => $row->description,
            'price' => (float) $row->price,
            'stock' => (int) ($row->stock ?? 0),
            'thumbnail' => $row->main_image_path,
            'category' => $row->category_id ? [
                'id' => (int) $row->category_id,
                'name' => $row->category_name,
                'slug' => Str::slug((string) $row->category_name),
            ] : null,
            'store' => $row->seller_id ? [
                'id' => (int) $row->seller_id,
                'name' => $row->store_name,
                'slug' => $row->store_slug,
                'logo' => $row->store_logo,
                'description' => $row->store_description,
            ] : null,
            'images' => [],
            'reviews' => [],
        ];

        return response()->json([
            'data' => $product,
        ]);
    }

    public function categories(): JsonResponse
    {
        $sourceDb = $this->sourceDb();

        $categories = DB::table("{$sourceDb}.product_categories as c")
            ->leftJoin("{$sourceDb}.products as p", function ($join): void {
                $join->on('p.category_id', '=', 'c.id')
                    ->where('p.status', '=', 'active');
            })
            ->where('c.is_active', 1)
            ->groupBy('c.id', 'c.name')
            ->orderBy('c.name')
            ->selectRaw('c.id, c.name, COUNT(p.id) as products_count')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => (int) $category->id,
                    'name' => $category->name,
                    'slug' => Str::slug($category->name),
                    'products_count' => (int) $category->products_count,
                ];
            })
            ->values();

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function storeBySlug(string $slug): JsonResponse
    {
        $sourceDb = $this->sourceDb();

        $seller = DB::table("{$sourceDb}.sellers")
            ->where('store_slug', $slug)
            ->select([
                'id',
                'store_name',
                'store_slug',
                'store_description',
                'store_logo',
            ])
            ->first();

        abort_unless($seller, 404);

        $products = DB::table("{$sourceDb}.products as p")
            ->leftJoin("{$sourceDb}.product_categories as c", 'c.id', '=', 'p.category_id')
            ->where('p.seller_id', $seller->id)
            ->where('p.status', 'active')
            ->select([
                'p.id',
                'p.name',
                'p.slug',
                'p.description',
                'p.price',
                'p.stock',
                'p.main_image_path',
                'p.category_id',
                'c.name as category_name',
            ])
            ->orderByDesc('p.id')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'name' => $row->name,
                    'slug' => $row->slug,
                    'description' => $row->description,
                    'price' => (float) $row->price,
                    'stock' => (int) ($row->stock ?? 0),
                    'thumbnail' => $row->main_image_path,
                    'category' => $row->category_id ? [
                        'id' => (int) $row->category_id,
                        'name' => $row->category_name,
                        'slug' => Str::slug((string) $row->category_name),
                    ] : null,
                ];
            })
            ->values();

        $store = [
            'id' => (int) $seller->id,
            'name' => $seller->store_name,
            'slug' => $seller->store_slug,
            'description' => $seller->store_description,
            'logo' => $seller->store_logo,
            'products' => $products,
        ];

        return response()->json([
            'data' => $store,
        ]);
    }

    public function upsertCart(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = Cart::firstOrCreate([
            'user_id' => $payload['user_id'],
        ]);

        foreach ($payload['items'] as $item) {
            CartItem::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'product_id' => $item['product_id'],
                ],
                [
                    'quantity' => $item['quantity'],
                    'qty' => $item['quantity'],
                ]
            );
        }

        $cart->load(['items.product']);

        return response()->json([
            'message' => 'Cart has been updated.',
            'data' => $cart,
        ]);
    }

    public function createOrder(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = DB::transaction(function () use ($payload): Order {
            $products = Product::query()
                ->whereIn('id', collect($payload['items'])->pluck('product_id')->all())
                ->get()
                ->keyBy('id');

            $firstSellerId = optional($products->first())->seller_id;
            $totalPrice = 0;

            foreach ($payload['items'] as $item) {
                $product = $products->get($item['product_id']);
                if (! $product) {
                    continue;
                }

                $totalPrice += ((float) $product->price) * $item['quantity'];
            }

            $order = Order::create([
                'user_id' => $payload['user_id'],
                'buyer_id' => $payload['user_id'],
                'seller_id' => $firstSellerId ?? $payload['user_id'],
                'status' => 'pending',
                'total_price' => $totalPrice,
            ]);

            foreach ($payload['items'] as $item) {
                $product = $products->get($item['product_id']);
                if (! $product) {
                    continue;
                }

                $order->items()->create([
                    'product_id' => $product->id,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'qty' => $item['quantity'],
                ]);

                if ($product->stock >= $item['quantity']) {
                    $product->decrement('stock', $item['quantity']);
                }
            }

            return $order->load('items.product');
        });

        return response()->json([
            'message' => 'Order created successfully.',
            'data' => $order,
        ], 201);
    }
}
