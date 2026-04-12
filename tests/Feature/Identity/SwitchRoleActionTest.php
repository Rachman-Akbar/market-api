<?php

namespace Tests\Feature\Identity;

use App\Domains\Identity\Application\Actions\SwitchRoleAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SwitchRoleActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_switches_role_when_role_belongs_to_user(): void
    {
        $user = User::factory()->create();

        $buyerRoleId = (int) \Illuminate\Support\Facades\DB::table('roles')->where('name', 'buyer')->value('id');
        $sellerRoleId = (int) \Illuminate\Support\Facades\DB::table('roles')->where('name', 'seller')->value('id');

        $user->roles()->attach([$buyerRoleId, $sellerRoleId]);

        $currentToken = $user->createToken('api-token', ['role:buyer']);
        $tokenModel = $user->tokens()->latest('id')->first();
        $user->setRelation('currentAccessToken', $tokenModel);

        $action = app(SwitchRoleAction::class);

        $payload = $action->execute($user, 'seller');

        $this->assertSame('seller', $payload['active_role']);
        $this->assertContains('seller', $payload['roles']);
        $this->assertNotSame($currentToken->plainTextToken, $payload['api_token']);
        $this->assertCount(1, $user->fresh()->tokens);
    }

    public function test_it_rejects_switch_when_role_not_owned_by_user(): void
    {
        $user = User::factory()->create();

        $buyerRoleId = (int) \Illuminate\Support\Facades\DB::table('roles')->where('name', 'buyer')->value('id');
        $user->roles()->attach($buyerRoleId);

        $action = app(SwitchRoleAction::class);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $action->execute($user, 'admin');
    }
}
