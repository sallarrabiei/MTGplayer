<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CardController;

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

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});

// Card routes
Route::prefix('cards')->group(function () {
    Route::get('/', [CardController::class, 'index']);
    Route::get('/sets', [CardController::class, 'getSets']);
    Route::get('/rarities', [CardController::class, 'getRarities']);
    Route::get('/colors', [CardController::class, 'getColors']);
    Route::get('/{id}', [CardController::class, 'show']);
    Route::get('/cardmarket/{cardmarketId}', [CardController::class, 'getByCardmarketId']);
    Route::post('/{id}/update-prices', [CardController::class, 'updatePrices']);
});