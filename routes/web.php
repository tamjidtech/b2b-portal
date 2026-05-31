<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImageFetchController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ResearchController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('products',  ProductController::class);
    Route::resource('trips',     TripController::class);
    Route::resource('purchases', PurchaseController::class);
    Route::resource('sales',     SaleController::class);

    Route::get('/research',                         [ResearchController::class, 'index'])->name('research.index');
    Route::post('/research',                        [ResearchController::class, 'store'])->name('research.store');
    Route::post('/research/import-preview',         [ResearchController::class, 'importPreview'])->name('research.import-preview');
    Route::post('/research/bulk-fetch-images',      [ResearchController::class, 'bulkFetchImages'])->name('research.bulk-fetch-images');
    Route::post('/research/{product}/activate',     [ResearchController::class, 'activate'])->name('research.activate');
    Route::delete('/research/{product}',            [ResearchController::class, 'destroy'])->name('research.destroy');
    Route::post('/products/{product}/fetch-image',  [ImageFetchController::class, 'fetch'])->name('products.fetch-image');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
