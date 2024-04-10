<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::group([
    'as'     => 'product',
    'prefix' => '/api/v1/product',
], function () {
    Route::get('/get-product', [ProductController::class, 'getAllProduct'])->name('product.getall');
    Route::post('/create-product', [ProductController::class, 'createProduct'])
        ->name('product.create')
        ->middleware('auth:api', 'admin');
    Route::get('/image-product/{pid}', [ProductController::class, 'getProductImage'])->name('product.get-product-image');
});