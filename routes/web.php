<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/home', 301);
});

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile');

    Route::post('/subscribe', [App\Http\Controllers\UserSubscriptionController::class, 'subscribe'])->name('subscribe');
    Route::post('/unsubscribe', [App\Http\Controllers\UserSubscriptionController::class, 'unsubscribe'])->name('unsubscribe');

    Route::get('/subscribe/success', [App\Http\Controllers\UserSubscriptionController::class, 'checkoutSuccess'])->name('checkout.success');
    Route::get('/subscribe/cancel', [App\Http\Controllers\UserSubscriptionController::class, 'checkoutCancel'])->name('checkout.success');
});
