<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Ana López',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'ana@example.com')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);
    }

    public function test_user_can_login(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Carlos Ruiz',
            'email' => 'carlos@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'carlos@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'carlos@example.com')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);
    }

    public function test_user_requires_authentication_for_profile(): void
    {
        $response = $this->getJson('/api/v1/auth/user');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_users(): void
    {
        $user = User::factory()->create([
            'name' => 'Laura Gómez',
            'email' => 'laura@example.com',
        ]);

        $token = $user->createToken('api-test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/users');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_authenticated_user_can_update_own_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Pedro Morales',
            'email' => 'pedro@example.com',
        ]);

        $token = $user->createToken('api-test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/users/' . $user->id, [
                'name' => 'Pedro Actualizado',
                'email' => 'pedro.nuevo@example.com',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Pedro Actualizado')
            ->assertJsonPath('data.email', 'pedro.nuevo@example.com');
    }

    public function test_authenticated_user_can_delete_own_account(): void
    {
        $user = User::factory()->create([
            'name' => 'Marta Silva',
            'email' => 'marta@example.com',
        ]);

        $token = $user->createToken('api-test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/v1/users/' . $user->id);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Usuario eliminado correctamente');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_user_can_access_paginated_admin_users_list(): void
    {
        User::factory()->count(15)->create();

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $token = $admin->createToken('api-test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/admin/users?page=2');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.current_page', 2)
            ->assertJsonPath('data.per_page', 10)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'current_page',
                    'data' => [],
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_regular_user_cannot_access_admin_users_list(): void
    {
        $user = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'role' => 'user',
        ]);

        $token = $user->createToken('api-test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_login_is_rate_limited_after_too_many_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'nope@example.com',
                'password' => 'wrongpass',
            ]);
        }

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nope@example.com',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(429);
    }
}
