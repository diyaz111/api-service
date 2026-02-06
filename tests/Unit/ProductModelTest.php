<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_has_orders_relationship(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();
        Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $product->refresh();
        $this->assertCount(1, $product->orders);
    }

    public function test_product_fillable_attributes(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'Desc',
            'price' => 9.99,
        ]);
        $this->assertSame('Test Product', $product->name);
        $this->assertSame(9.99, (float) $product->price);
    }
}
