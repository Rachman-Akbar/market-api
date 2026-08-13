<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Routing\Route as IlluminateRoute;
use Tests\TestCase;

final class SupportTicketContextRouteTest extends TestCase
{
    public function test_ticket_context_route_is_registered_before_dynamic_ticket_route(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes());

        $this->assertTrue($routes->contains(
            fn (IlluminateRoute $route): bool => in_array('GET', $route->methods(), true)
                && $route->uri() === 'api/v1/support/tickets/context'
        ));
    }
}
