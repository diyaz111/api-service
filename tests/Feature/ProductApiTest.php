<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_products_requires_authentication(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(401)->assertJson(['success' => false]);
    }

    public function test_get_products_success(): void
    {
        Product::factory()->create(['name' => 'Product A']);
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->getJson('/api/products', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Products fetched successfully.',
            ])
            ->assertJsonStructure(['data' => ['products']]);
    }

    public function test_create_product_requires_authentication(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => 'New Product',
            'price' => 10.99,
        ]);

        $response->assertStatus(401)->assertJson(['success' => false]);
    }

    public function test_create_product_success(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson('/api/products', [
            'name' => 'New Product',
            'description' => 'A description',
            'price' => 19.99,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully.',
                'data' => [
                    'name' => 'New Product',
                    'description' => 'A description',
                    'price' => 19.99,
                ],
            ])
            ->assertJsonStructure(['data' => ['id', 'created_at']]);

        $this->assertDatabaseHas('products', ['name' => 'New Product']);
    }

    public function test_create_product_validation_empty_name(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson('/api/products', [
            'price' => 10,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_create_product_validation_negative_price(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson('/api/products', [
            'name' => 'Product',
            'price' => -5,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }
}
