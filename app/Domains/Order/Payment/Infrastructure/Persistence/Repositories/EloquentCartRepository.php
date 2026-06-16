<?php

declare(strict_types=1);

namespace App\Domains\Cart\Infrastructure\Persistence\Repositories;

use App\Domains\Cart\Domain\Entities\Cart;
use App\Domains\Cart\Domain\Entities\CartItem;
use App\Domains\Cart\Domain\Repositories\CartRepositoryInterface;
use App\Domains\Cart\Domain\ValueObjects\CartStatus;
use App\Domains\Cart\Infrastructure\Persistence\Mappers\CartMapper;
use App\Domains\Cart\Infrastructure\Persistence\Models\CartItemModel;
use App\Domains\Cart\Infrastructure\Persistence\Models\CartModel;

final readonly class EloquentCartRepository implements CartRepositoryInterface
{
    public function __construct(private CartMapper $mapper)
    {
    }

    public function findActiveByUserId(string $userId, bool $lock = false): ?Cart
    {
        $query = CartModel::query()
            ->where('user_id', $userId)
            ->where('status', CartStatus::ACTIVE->value)
            ->with('items')
            ->latest('id');

        if ($lock) {
            $query->lockForUpdate();
        }

        $model = $query->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function getOrCreateActiveByUserId(string $userId, bool $lock = false): Cart
    {
        $cart = $this->findActiveByUserId($userId, $lock);

        if ($cart !== null) {
            return $cart;
        }

        $model = CartModel::query()->create([
            'user_id' => $userId,
            'active_user_id' => $userId,
            'status' => CartStatus::ACTIVE->value,
        ]);

        $model->load('items');

        return $this->mapper->toDomain($model);
    }

    public function save(Cart $cart): Cart
    {
        $model = $cart->id() !== null
            ? CartModel::query()->lockForUpdate()->findOrFail($cart->id())
            : new CartModel();

        $model->fill([
            'user_id' => $cart->userId(),
            'active_user_id' => $cart->status() === CartStatus::ACTIVE ? $cart->userId() : null,
            'status' => $cart->status()->value,
        ]);
        $model->save();

        $this->syncItems($model, $cart->items());

        $model->load('items');

        return $this->mapper->toDomain($model);
    }

    /** @param CartItem[] $items */
    private function syncItems(CartModel $cartModel, array $items): void
    {
        $productIds = array_map(
            static fn (CartItem $item): int => $item->productId(),
            $items,
        );

        if ($productIds === []) {
            CartItemModel::query()
                ->where('cart_id', $cartModel->id)
                ->delete();
            return;
        }

        CartItemModel::query()
            ->where('cart_id', $cartModel->id)
            ->whereNotIn('product_id', $productIds)
            ->delete();

        foreach ($items as $item) {
            CartItemModel::query()->updateOrCreate(
                [
                    'cart_id' => $cartModel->id,
                    'product_id' => $item->productId(),
                ],
                [
                    'quantity' => $item->quantity()->value(),
                    'price_snapshot' => $item->priceSnapshot()->value(),
                    'product_name_snapshot' => $item->productNameSnapshot(),
                    'product_image_snapshot' => $item->productImageSnapshot(),
                ],
            );
        }
    }
}
