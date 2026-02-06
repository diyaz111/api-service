<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return ApiResponse::success([
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => (float) $product->price,
            'created_at' => $product->created_at->toIso8601String(),
        ], 'Product created successfully.', 201);
    }

    public function index(): JsonResponse
    {
        $products = Product::query()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => (float) $product->price,
                'created_at' => $product->created_at->toIso8601String(),
            ]);

        return ApiResponse::success([
            'products' => $products,
        ], 'Products fetched successfully.');
    }
}
