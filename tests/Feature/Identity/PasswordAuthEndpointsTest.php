<?php

namespace Tests\Feature\Identity;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordAuthEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_endpoint_creates_user_and_returns_token(): void
    {
        $response = $this->postJson('/api/v1/identity/auth/register', [
            'name' => 'Local User',
            'email' => 'local-user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'user' => ['id', 'email', 'name'],
            'roles',
            'active_role',
            'api_token',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'local-user@example.com',
            'name' => 'Local User',
        ]);

        $this->assertDatabaseHas('user_roles', [
            'user_id' => User::query()->where('email', 'local-user@example.com')->value('id'),
        ]);
    }

    public function test_login_endpoint_returns_token_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'buyer-login@example.com',
            'password' => Hash::make('password123'),
            'is_email_verified' => true,
            'role' => 'buyer',
        ]);

        $buyerRoleId = (int) \Illuminate\Support\Facades\DB::table('roles')->where('name', 'buyer')->value('id');
        $user->roles()->attach($buyerRoleId);

        $response = $this->postJson('/api/v1/identity/auth/login', [
            'email' => 'buyer-login@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJsonPath('user.email', 'buyer-login@example.com');
        $response->assertJsonStructure(['api_token']);
    }
}
