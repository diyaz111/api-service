<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_order_requires_authentication(): void
    {
        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(401)->assertJson(['success' => false]);
    }

    public function test_create_order_success_without_product_id(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson('/api/orders', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Order created successfully.',
                'data' => [
                    'user_id' => $user->id,
                    'product_id' => null,
                ],
            ])
            ->assertJsonStructure(['data' => ['id', 'created_at']]);

        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'product_id' => null]);
    }

    public function test_create_order_success_with_product_id(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ],
            ]);

        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    public function test_create_order_validation_invalid_product_id(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson('/api/orders', [
            'product_id' => 99999,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }
}
