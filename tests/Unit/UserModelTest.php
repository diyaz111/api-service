<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_orders_relationship(): void
    {
        $user = User::factory()->create();
        $this->assertCount(0, $user->orders);

        Order::factory()->create(['user_id' => $user->id]);
        $user->refresh();
        $this->assertCount(1, $user->orders);
    }

    public function test_password_is_hidden_in_array(): void
    {
        $user = User::factory()->create();
        $array = $user->toArray();
        $this->assertArrayNotHasKey('password', $array);
    }

    public function test_user_can_create_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $this->assertNotEmpty($token);
    }
}
