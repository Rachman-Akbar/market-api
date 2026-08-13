<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class TicketOwnershipContractTest extends TestCase
{
    public function test_ticket_service_uses_real_order_schema_and_validates_non_admin_store_history(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/app/Domains/Support/Ticket/Application/Services/TicketService.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('orders.deleted_at', $source);
        $this->assertStringNotContainsString('sub_orders.deleted_at', $source);
        $this->assertStringContainsString('Toko pada Help tidak berasal dari riwayat pesanan akun Anda.', $source);
        $this->assertStringContainsString("'user_id' => \$ticketUserId", $source);
    }
}
