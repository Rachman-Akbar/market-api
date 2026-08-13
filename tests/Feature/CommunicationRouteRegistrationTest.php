<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Communication\Chat\Presentation\Http\Controllers\ConversationController;
use Illuminate\Routing\Route as IlluminateRoute;
use Tests\TestCase;

final class CommunicationRouteRegistrationTest extends TestCase
{
    public function test_communication_routes_are_registered_under_api_v1(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes());

        $this->assertTrue($this->hasRoute($routes, 'GET', 'api/v1/communication/conversations'));
        $this->assertTrue($this->hasRoute($routes, 'POST', 'api/v1/communication/conversations'));
        $this->assertTrue($this->hasRoute($routes, 'POST', 'api/v1/communication/conversations/{id}/messages'));
        $this->assertTrue($this->hasRoute($routes, 'PATCH', 'api/v1/communication/conversations/{id}/read'));
        $this->assertTrue($this->hasRoute($routes, 'POST', 'api/v1/communication/announcements'));
        $this->assertTrue($this->hasRoute($routes, 'POST', 'api/broadcasting/auth'));
    }

    public function test_conversation_controller_can_be_resolved_from_container(): void
    {
        $this->assertInstanceOf(ConversationController::class, app(ConversationController::class));
    }

    private function hasRoute($routes, string $method, string $uri): bool
    {
        return $routes->contains(
            fn (IlluminateRoute $route): bool => in_array($method, $route->methods(), true) && $route->uri() === $uri
        );
    }
}
