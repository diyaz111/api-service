<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Auth
Route::post('/login', [AuthController::class, 'login']);

// Users
Route::post('/users', [UserController::class, 'store']);
Route::get('/users', [UserController::class, 'index'])->middleware('auth:sanctum');

// Products
Route::post('/products', [ProductController::class, 'store'])->middleware('auth:sanctum');
Route::get('/products', [ProductController::class, 'index'])->middleware('auth:sanctum');

// Orders
Route::post('/orders', [OrderController::class, 'store'])->middleware('auth:sanctum');

