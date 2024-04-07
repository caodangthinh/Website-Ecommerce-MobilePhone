<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::group([
    'as'     => 'product',
    'prefix' => '/api/v1/product',
], function () {
    Route::get('/get-product', [ProductController::class, 'getAllProduct'])->name('product.getall');
    
});