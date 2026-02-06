<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => null,
        ];
    }

    public function withProduct(?Product $product = null): static
    {
        return $this->state(fn () => [
            'product_id' => $product?->id ?? Product::factory(),
        ]);
    }
}
