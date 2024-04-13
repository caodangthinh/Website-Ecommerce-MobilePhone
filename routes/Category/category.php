<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

Route::group([
    'as' => 'category',
    'prefix' => '/api/v1/category',
], function () {
    Route::post('/create-category', [CategoryController::class, 'createCategory'])
        ->name('category.create')
        ->middleware('auth:api', 'admin');
    Route::put('/update-category/{id}', [CategoryController::class, 'updateCategory'])
        ->name('category.create')
        ->middleware('auth:api', 'admin');
    Route::delete('/delete-category/{pid}', [CategoryController::class, 'deleteCategory'])
        ->name('category.delete')
        ->middleware('auth:api', 'admin');
    Route::get('/list-category', [CategoryController::class, 'listCategoryController'])
        ->name('category.list');

});