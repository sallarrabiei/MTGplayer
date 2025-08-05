<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\FavoriteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Card routes (public)
Route::get('/cards', [CardController::class, 'index']);
Route::get('/cards/filters', [CardController::class, 'filters']);
Route::get('/cards/{id}', [CardController::class, 'show']);
Route::post('/cards/update-prices', [CardController::class, 'updatePrices']);

// Favorite routes (authenticated)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{cardId}', [FavoriteController::class, 'destroy']);
    Route::get('/favorites/check/{cardId}', [FavoriteController::class, 'check']);
});
