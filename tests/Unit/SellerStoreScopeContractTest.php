<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class SellerStoreScopeContractTest extends TestCase
{
    public function test_advanced_seller_controllers_use_authenticated_store_resolver(): void
    {
        $root = dirname(__DIR__, 2) . '/app/Domains';
        $files = [
            '/Seller/Finance/Presentation/Http/Controllers/FinancialTransactionController.php',
            '/Seller/Stock/Presentation/Http/Controllers/StockMovementController.php',
            '/Seller/Showcase/Presentation/Http/Controllers/ShowcaseController.php',
            '/Seller/Customers/Presentation/Http/Controllers/CustomerController.php',
            '/Catalog/Promotion/Presentation/Http/Controllers/PromotionController.php',
            '/Catalog/Promotion/Presentation/Http/Controllers/PromotionPaymentController.php',
            '/Order/Review/Presentation/Http/Controllers/ProductReviewController.php',
            '/Order/Voucher/Presentation/Http/Controllers/VoucherController.php',
            '/Communication/Chat/Presentation/Http/Controllers/ConversationController.php',
            '/Order/Ordering/Presentation/Http/Controllers/OrderingController.php',
        ];

        foreach ($files as $file) {
            $source = file_get_contents($root . $file);
            $this->assertIsString($source, $file);
            $this->assertStringContainsString('ResolvesSellerStoreContext', $source, $file);
        }
    }
}
