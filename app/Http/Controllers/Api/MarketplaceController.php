<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketplaceController extends Controller
{
    public function products(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with([
                'store:id,name,slug,logo',
                'category:id,name,slug',
                'images:id,product_id,image_url,url,is_primary',
            ])
            ->when($request->filled('category'), function ($query) use ($request): void {
                $query->whereHas('category', function ($categoryQuery) use ($request): void {
                    $categoryQuery->where('slug', $request->string('category')->toString());
                });
            })
            ->latest('id')
            ->get();

        return response()->json([
            'data' => $products,
        ]);
    }

    public function productBySlug(string $slug): JsonResponse
    {
        $product = Product::query()
            ->with([
                'store:id,name,slug,description,logo',
                'category:id,name,slug',
                'images:id,product_id,image_url,url,is_primary',
                'reviews:id,user_id,product_id,rating,comment,created_at',
                'reviews.user:id,name,email',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'data' => $product,
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function storeBySlug(string $slug): JsonResponse
    {
        $store = Store::query()
            ->with([
                'products' => function ($query): void {
                    $query->with(['category:id,name,slug'])
                        ->orderByDesc('id');
                },
            ])
            ->where('slug', $slug)
            ->firstOrFail();

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
