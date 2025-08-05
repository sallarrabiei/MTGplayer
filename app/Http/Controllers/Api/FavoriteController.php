<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $favorites = Auth::user()
            ->favorites()
            ->with('card')
            ->paginate(20);
            
        return response()->json($favorites);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'card_id' => 'required|exists:cards,id'
        ]);
        
        $userId = Auth::id();
        
        // Check if already favorited
        $exists = Favorite::where('user_id', $userId)
            ->where('card_id', $request->card_id)
            ->exists();
            
        if ($exists) {
            return response()->json([
                'message' => 'Card already in favorites'
            ], 409);
        }
        
        $favorite = Favorite::create([
            'user_id' => $userId,
            'card_id' => $request->card_id
        ]);
        
        $favorite->load('card');
        
        return response()->json($favorite, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $cardId)
    {
        $userId = Auth::id();
        
        $favorite = Favorite::where('user_id', $userId)
            ->where('card_id', $cardId)
            ->first();
            
        if (!$favorite) {
            return response()->json([
                'message' => 'Favorite not found'
            ], 404);
        }
        
        $favorite->delete();
        
        return response()->json([
            'message' => 'Card removed from favorites'
        ]);
    }
    
    /**
     * Check if a card is favorited by the current user
     */
    public function check($cardId)
    {
        $isFavorited = Favorite::where('user_id', Auth::id())
            ->where('card_id', $cardId)
            ->exists();
            
        return response()->json([
            'is_favorited' => $isFavorited
        ]);
    }
}
