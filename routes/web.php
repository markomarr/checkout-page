<?php

use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public checkout page routes — registered last so they do not conflict
// with Filament panel routes (/admin, /brand).
Route::get('/{brand:slug}/{product:slug}', [CheckoutController::class, 'show'])
    ->name('checkout.show');

Route::post('/{brand:slug}/{product:slug}', [CheckoutController::class, 'submit'])
    ->name('checkout.submit');

Route::get('/{brand:slug}/{product:slug}/sukses/{order:order_code}', [CheckoutController::class, 'success'])
    ->name('checkout.success');
