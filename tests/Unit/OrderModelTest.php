<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $this->assertInstanceOf(User::class, $order->user);
        $this->assertSame($user->id, $order->user->id);
    }

    public function test_order_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        $this->assertInstanceOf(Product::class, $order->product);
        $this->assertSame($product->id, $order->product->id);
    }

    public function test_order_can_have_null_product_id(): void
    {
        $user = User::factory()->create();
        $order = Order::create(['user_id' => $user->id, 'product_id' => null]);
        $this->assertNull($order->product_id);
    }
}
