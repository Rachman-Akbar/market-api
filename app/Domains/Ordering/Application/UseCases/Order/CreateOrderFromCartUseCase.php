<?php

declare(strict_types=1);

namespace App\Domains\Ordering\Application\UseCases\Order;

use App\Domains\Ordering\Application\DTOs\CreateOrderData;
use App\Domains\Ordering\Application\DTOs\CreateOrderItemData;
use App\Domains\Ordering\Domain\Entities\Order;
use App\Domains\Ordering\Domain\Entities\OrderItem;
use App\Domains\Ordering\Domain\Repositories\CartForOrderReaderInterface;
use App\Domains\Ordering\Domain\Repositories\OrderRepositoryInterface;
use App\Domains\Ordering\Domain\Repositories\ProductStockRepositoryInterface;
use App\Domains\Ordering\Domain\Services\OrderNumberGenerator;
use App\Domains\Ordering\Domain\Services\OrderTotalCalculator;
use App\Domains\Ordering\Domain\ValueObjects\Money;
use App\Domains\Ordering\Domain\ValueObjects\OrderNumber;
use App\Domains\Ordering\Domain\ValueObjects\ShippingAddress;
use App\Domains\Ordering\Infrastructure\Services\OrderTransactionService;
use DomainException;

final readonly class CreateOrderFromCartUseCase
{
    public function __construct(
        private CartForOrderReaderInterface $cartReader,
        private ProductStockRepositoryInterface $stockRepository,
        private OrderRepositoryInterface $orderRepository,
        private OrderTotalCalculator $totalCalculator,
        private OrderNumberGenerator $orderNumberGenerator,
        private OrderTransactionService $transaction,
    ) {
    }

    public function execute(CreateOrderData $data): Order
    {
        return $this->transaction->run(function () use ($data): Order {
            $cart = $this->cartReader->getActiveCartForUser($data->userId);

            if (! $cart || empty($cart['items'])) {
                throw new DomainException('Cart is empty or not found.');
            }

            $itemData = array_map(
                static fn (array $item): CreateOrderItemData => CreateOrderItemData::fromArray($item),
                $cart['items'],
            );

            $stockPayload = array_map(
                static fn (CreateOrderItemData $item): array => $item->stockPayload(),
                $itemData,
            );

            $this->stockRepository->assertProductsAreAvailable($stockPayload);

            $items = array_map(
                static fn (CreateOrderItemData $item): OrderItem => OrderItem::create(
                    productId: $item->productId,
                    productName: $item->productName,
                    sku: $item->sku,
                    quantity: $item->quantity,
                    unitPrice: new Money($item->unitPrice, $item->currency),
                ),
                $itemData,
            );

            $subtotal = $this->totalCalculator->calculateSubtotal($items);

            $order = Order::create(
                orderNumber: new OrderNumber($this->orderNumberGenerator->generate()),
                userId: $data->userId,
                shippingAddress: ShippingAddress::fromData($data->shippingAddress),
                items: $items,
                subtotal: $subtotal,
                notes: $data->notes,
                paymentMethod: $data->paymentMethod,
            );

            $createdOrder = $this->orderRepository->create($order);
            $this->stockRepository->decreaseMany($stockPayload);
            $this->cartReader->markAsOrdered((int) $cart['id'], (int) $createdOrder->id());

            return $createdOrder;
        });
    }
}
