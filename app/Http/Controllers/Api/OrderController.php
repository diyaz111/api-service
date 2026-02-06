<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $user = $request->user('sanctum');
        $validated = $request->validated();

        $order = Order::create([
            'user_id' => $user->id,
            'product_id' => $validated['product_id'] ?? null,
        ]);

        $payload = [
            'id' => $order->id,
            'user_id' => $order->user_id,
            'product_id' => $order->product_id,
            'created_at' => $order->created_at->toIso8601String(),
        ];

        return ApiResponse::success($payload, 'Order created successfully.', 201);
    }
}
