<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProductController, CartController, CheckoutController, OrderController, ProfileController
};

// Accueil = liste produits
Route::get('/', [ProductController::class, 'index'])->name('home');

Route::get('/test-products', function() {
    return \App\Models\Product::all();
});


// Détail produit (binding sur le slug)
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('product.show');

// Panier (visiteur ou connecté)
Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::post('/cart/add/{product:slug}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{product:slug}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{product:slug}', [CartController::class, 'remove'])->name('cart.remove');

// Zone authentifiée : checkout + commandes + dashboard/profil
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');  
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show'); 

    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';