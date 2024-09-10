<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Repositories\ProductController as RepositoryProductController;
use App\Http\Controllers\Services\ProductController as ServicesProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

/*
    Prefix the routes with repositories to avoid conflicts with the default routes
*/
Route::prefix('repositories')->group(function () {
    // Add a new route to search for a product by name
    Route::get('products/search', [RepositoryProductController::class, 'search'])->name('products.search');

    // create resource routes for products
    Route::resource('products', RepositoryProductController::class);
});

/*
    Prefix the routes with services to avoid conflicts with the default routes
*/
Route::prefix('services')->group(function () {
    // Add a new route to search for a product by name
    // Route::get('products/search', [ServicesProductController::class, 'search'])->name('products.search');

    // create resource routes for products
    Route::post('products/store', [ServicesProductController::class,'store'])->name('services.products.store');
    Route::get('products/create', [ServicesProductController::class,'create'])->name('services.products.create');

});
