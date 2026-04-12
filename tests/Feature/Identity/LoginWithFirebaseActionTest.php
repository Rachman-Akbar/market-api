<?php

namespace Tests\Feature\Identity;

use App\Domains\Identity\Application\Actions\LoginWithFirebaseAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginWithFirebaseActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_new_user_and_assigns_default_buyer_role(): void
    {
        $action = app(LoginWithFirebaseAction::class);

        $payload = $action->execute([
            'uid' => 'firebase_uid_new_1234567890',
            'email' => 'new-user@example.com',
            'name' => 'New User',
            'picture' => 'https://example.com/avatar.png',
            'email_verified' => true,
        ]);

        $this->assertSame('new-user@example.com', $payload['user']['email']);
        $this->assertTrue($payload['user']['is_email_verified']);
        $this->assertContains('buyer', $payload['roles']);
        $this->assertSame('buyer', $payload['active_role']);
        $this->assertNotEmpty($payload['api_token']);

        $user = User::query()->where('firebase_uid', 'firebase_uid_new_1234567890')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->roles()->where('name', 'buyer')->exists());
    }

    public function test_it_rejects_login_when_email_is_not_verified(): void
    {
        $action = app(LoginWithFirebaseAction::class);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $action->execute([
            'uid' => 'firebase_uid_unverified_123',
            'email' => 'unverified@example.com',
            'name' => 'Unverified User',
            'picture' => null,
            'email_verified' => false,
        ]);
    }

    public function test_it_logs_in_existing_user_and_syncs_profile_fields(): void
    {
        $user = User::factory()->create([
            'firebase_uid' => 'firebase_uid_existing_123',
            'email' => 'old-email@example.com',
            'name' => 'Old Name',
            'avatar' => null,
            'is_email_verified' => true,
        ]);

        $buyerRoleId = (int) \Illuminate\Support\Facades\DB::table('roles')->where('name', 'buyer')->value('id');
        $user->roles()->attach($buyerRoleId);

        $action = app(LoginWithFirebaseAction::class);

        $payload = $action->execute([
            'uid' => 'firebase_uid_existing_123',
            'email' => 'updated-email@example.com',
            'name' => 'Updated Name',
            'picture' => 'https://example.com/new-avatar.png',
            'email_verified' => true,
        ]);

        $this->assertSame('updated-email@example.com', $payload['user']['email']);
        $this->assertSame('Updated Name', $payload['user']['name']);
        $this->assertSame('https://example.com/new-avatar.png', $payload['user']['avatar']);
        $this->assertContains('buyer', $payload['roles']);
    }
}
