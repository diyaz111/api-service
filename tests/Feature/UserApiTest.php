<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_create_user_success(): void
    {
        $response = $this->postJson('/api/users', [
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'name' => 'New User',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User created successfully.',
                'data' => [
                    'email' => 'newuser@example.com',
                    'name' => 'New User',
                ],
            ])
            ->assertJsonStructure(['data' => ['created_at']]);

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    public function test_create_user_validation_empty_payload(): void
    {
        $response = $this->postJson('/api/users', []);

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonStructure(['errors' => ['email', 'password', 'name']]);
    }

    public function test_create_user_validation_invalid_email(): void
    {
        $response = $this->postJson('/api/users', [
            'email' => 'invalid-email',
            'password' => 'password123',
            'name' => 'John Doe',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_create_user_validation_short_password(): void
    {
        $response = $this->postJson('/api/users', [
            'email' => 'john@example.com',
            'password' => 'short',
            'name' => 'John Doe',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_create_user_validation_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/users', [
            'email' => 'existing@example.com',
            'password' => 'password123',
            'name' => 'John Doe',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_get_users_requires_authentication(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_get_users_success_with_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->getJson('/api/users', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Users fetched successfully.',
            ])
            ->assertJsonStructure(['data' => ['page', 'users']]);
    }

    public function test_get_users_with_search_and_sort(): void
    {
        User::factory()->create(['name' => 'Alice', 'email' => 'alice@example.com']);
        User::factory()->create(['name' => 'Bob', 'email' => 'bob@example.com']);
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->getJson('/api/users?search=alice&sortBy=name&page=1', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_get_users_can_edit_for_administrator(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        User::factory()->create(['role' => 'user']);
        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->getJson('/api/users', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $users = $response->json('data.users');
        $this->assertNotEmpty($users);
        foreach ($users as $u) {
            $this->assertTrue($u['can_edit']);
        }
    }

    public function test_get_users_can_edit_only_self_for_role_user(): void
    {
        $regularUser = User::factory()->create(['role' => 'user', 'email' => 'me@example.com']);
        User::factory()->create(['role' => 'user', 'email' => 'other@example.com']);
        $token = $regularUser->createToken('test')->plainTextToken;

        $response = $this->getJson('/api/users', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $users = $response->json('data.users');
        $me = collect($users)->firstWhere('email', 'me@example.com');
        $other = collect($users)->firstWhere('email', 'other@example.com');
        $this->assertTrue($me['can_edit']);
        $this->assertFalse($other['can_edit']);
    }
}
