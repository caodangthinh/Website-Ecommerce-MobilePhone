<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::group([
    'as' => 'product',
    'prefix' => '/api/v1/product',
], function () {
    Route::get('/get-product', [ProductController::class, 'getAllProduct'])->name('product.getall');
    Route::post('/create-product', [ProductController::class, 'createProduct'])
        ->name('product.create')
        ->middleware('auth:api', 'admin');
    Route::get('/image-product/{pid}', [ProductController::class, 'getProductImage'])->name('product.get-product-image');
    Route::put('/update-product/{pid}', [ProductController::class, 'updateProduct'])
        ->name('product.update')
        ->middleware('auth:api', 'admin');
    Route::delete('/delete-product/{pid}', [ProductController::class, 'deleteProduct'])
        ->name('product.delete')
        ->middleware('auth:api', 'admin');
    Route::get('/get-product/{slug}', [ProductController::class, 'getSingleProduct'])
        ->name('product.get-product-single');
    Route::post('/product-filters', [ProductController::class, 'productFilters'])
        ->name('product.filter');
    Route::get('/count-products', [ProductController::class, 'countProduct'])
        ->name('product.count-product');
    Route::get('/product-list/{page}', [ProductController::class, 'listProduct'])
        ->name('product.list-product');
    Route::get('/search/{keyword}', [ProductController::class, 'searchProduct'])
        ->name('product.search-product');
    Route::get('/related-product/{pid}/{cid}', [ProductController::class, 'relatedProduct'])
        ->name('product.related-product');
    Route::get('product-category/{slug}', [ProductController::class, 'productCategory'])
        ->name('product.product-category');
    Route::get('/braintree/token', [ProductController::class, 'braintreeTokenController'])
        ->name('braintree.token');

});