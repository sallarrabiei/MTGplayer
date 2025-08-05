<?php

use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\FavoriteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Card routes
Route::prefix('cards')->group(function () {
    Route::get('/', [CardController::class, 'index'])->name('api.cards.index');
    Route::get('/sets', [CardController::class, 'getSets'])->name('api.cards.sets');
    Route::get('/stats', [CardController::class, 'getStats'])->name('api.cards.stats');
    Route::get('/{card}', [CardController::class, 'show'])->name('api.cards.show');
    Route::get('/{card}/price', [CardController::class, 'getPrice'])->name('api.cards.price');
});

// Favorite routes (require authentication)
Route::middleware('auth:sanctum')->prefix('favorites')->group(function () {
    Route::get('/', [FavoriteController::class, 'index'])->name('api.favorites.index');
    Route::post('/', [FavoriteController::class, 'store'])->name('api.favorites.store');
    Route::delete('/{card}', [FavoriteController::class, 'destroy'])->name('api.favorites.destroy');
    Route::get('/{card}/check', [FavoriteController::class, 'check'])->name('api.favorites.check');
});