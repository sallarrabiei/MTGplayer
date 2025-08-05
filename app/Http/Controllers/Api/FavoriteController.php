<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get user's favorite cards.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $favorites = $user->favoriteCards()
                         ->with('card')
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);

        return response()->json([
            'data' => $favorites->items(),
            'pagination' => [
                'current_page' => $favorites->currentPage(),
                'last_page' => $favorites->lastPage(),
                'per_page' => $favorites->perPage(),
                'total' => $favorites->total(),
            ]
        ]);
    }

    /**
     * Add a card to favorites.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'card_id' => 'required|integer|exists:cards,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $cardId = $request->input('card_id');

        // Check if already favorited
        $existingFavorite = Favorite::where('user_id', $user->id)
                                  ->where('card_id', $cardId)
                                  ->first();

        if ($existingFavorite) {
            return response()->json([
                'error' => 'Card is already in favorites'
            ], 409);
        }

        $favorite = Favorite::create([
            'user_id' => $user->id,
            'card_id' => $cardId
        ]);

        $favorite->load('card');

        return response()->json([
            'message' => 'Card added to favorites',
            'data' => $favorite
        ], 201);
    }

    /**
     * Remove a card from favorites.
     */
    public function destroy(Card $card): JsonResponse
    {
        $user = Auth::user();
        
        $favorite = Favorite::where('user_id', $user->id)
                          ->where('card_id', $card->id)
                          ->first();

        if (!$favorite) {
            return response()->json([
                'error' => 'Card is not in favorites'
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'message' => 'Card removed from favorites'
        ]);
    }

    /**
     * Check if a card is favorited by the user.
     */
    public function check(Card $card): JsonResponse
    {
        $user = Auth::user();
        
        $isFavorited = Favorite::where('user_id', $user->id)
                             ->where('card_id', $card->id)
                             ->exists();

        return response()->json([
            'data' => [
                'card_id' => $card->id,
                'is_favorited' => $isFavorited
            ]
        ]);
    }
}