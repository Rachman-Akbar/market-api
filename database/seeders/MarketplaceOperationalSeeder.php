<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class MarketplaceOperationalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buyers = User::query()->where('role', 'buyer')->get();
        $products = Product::query()->where('status', 'published')->get();

        if ($buyers->isEmpty() || $products->isEmpty()) {
            return;
        }

        if (Stock::query()->count() === 0) {
            foreach ($products as $product) {
                Stock::query()->create([
                    'product_id' => $product->id,
                    'quantity' => max(0, (int) $product->stock),
                    'reserved_quantity' => 0,
                ]);
            }
        }

        if (StockMovement::query()->count() === 0) {
            $stocks = Stock::query()->get();

            foreach ($stocks as $stock) {
                StockMovement::query()->create([
                    'product_id' => $stock->product_id,
                    'type' => 'initial_stock',
                    'quantity' => $stock->quantity,
                    'reference_type' => 'seeder',
                    'reference_id' => 'marketplace-operational-seeder',
                ]);
            }
        }

        if (Address::query()->count() === 0) {
            foreach ($buyers->take(3) as $index => $buyer) {
                Address::query()->create([
                    'user_id' => $buyer->id,
                    'label' => 'Alamat Utama',
                    'address' => 'Jl. Marketplace No. '.($index + 1).', Jakarta',
                    'lat' => -6.2000 + ($index * 0.0010),
                    'lng' => 106.8166 + ($index * 0.0010),
                ]);
            }
        }

        $buyer = $buyers->first();
        $selectedProducts = $products->take(2)->values();

        if (Cart::query()->count() === 0) {
            Cart::query()->create([
                'user_id' => $buyer->id,
            ]);
        }

        $cart = Cart::query()->first();

        if ($cart && CartItem::query()->count() === 0) {
            foreach ($selectedProducts as $product) {
                CartItem::query()->create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'qty' => 1,
                ]);
            }
        }

        if (Order::query()->count() === 0 && $selectedProducts->isNotEmpty()) {
            $firstProduct = $selectedProducts->first();
            $totalPrice = $selectedProducts->sum(fn (Product $product): float => (float) $product->price);

            $order = Order::query()->create([
                'user_id' => $buyer->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $firstProduct->seller_id,
                'status' => 'pending',
                'total_price' => $totalPrice,
            ]);

            foreach ($selectedProducts as $product) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'price' => $product->price,
                    'quantity' => 1,
                    'qty' => 1,
                ]);
            }

            if (Payment::query()->count() === 0) {
                Payment::query()->create([
                    'order_id' => $order->id,
                    'status' => 'pending',
                    'payment_method' => 'manual_transfer',
                ]);
            }
        }
    }
}
