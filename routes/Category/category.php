<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

oute::group([
    'as'     => 'category',
    'prefix' => '/api/v1/category',
], function () {
    Route::post('/create-category', [CategoryController::class, 'createCategory'])
        ->name('category.create')
        ->middleware('auth:api', 'admin');
    Route::post('/update-category/{id}', [CategoryController::class, 'updateCategory'])
        ->name('category.create')
        ->middleware('auth:api', 'admin');
});