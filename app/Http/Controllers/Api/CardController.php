<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Services\CardmarketService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CardController extends Controller
{
    private ?CardmarketService $cardmarketService;

    public function __construct(?CardmarketService $cardmarketService = null)
    {
        $this->cardmarketService = $cardmarketService;
    }

    /**
     * Display a listing of cards with search and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Card::query();

        // Search by name
        if ($request->has('name') && $request->name) {
            $query->searchByName($request->name);
        }

        // Filter by set
        if ($request->has('set') && $request->set) {
            $query->inSet($request->set);
        }

        // Filter by rarity
        if ($request->has('rarity') && $request->rarity) {
            $query->ofRarity($request->rarity);
        }

        // Filter by color
        if ($request->has('color') && $request->color) {
            $query->ofColor($request->color);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type_line', 'like', "%{$request->type}%");
        }

        // Filter by mana cost
        if ($request->has('cmc')) {
            $query->where('cmc', $request->cmc);
        }

        // Filter by power/toughness
        if ($request->has('power')) {
            $query->where('power', $request->power);
        }

        if ($request->has('toughness')) {
            $query->where('toughness', $request->toughness);
        }

        // Include prices if requested
        if ($request->boolean('with_prices')) {
            $query->with(['prices' => function ($query) {
                $query->where('price_type', 'market')
                      ->where('condition', 'NM')
                      ->where('foil', false)
                      ->latest('price_updated_at');
            }]);
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 100);
        $cards = $query->paginate($perPage);

        return response()->json([
            'data' => $cards->items(),
            'pagination' => [
                'current_page' => $cards->currentPage(),
                'last_page' => $cards->lastPage(),
                'per_page' => $cards->perPage(),
                'total' => $cards->total(),
            ]
        ]);
    }

    /**
     * Display the specified card with prices.
     */
    public function show(string $id): JsonResponse
    {
        $card = Card::with(['prices' => function ($query) {
            $query->latest('price_updated_at');
        }])->findOrFail($id);

        return response()->json([
            'data' => $card
        ]);
    }

    /**
     * Get card by Cardmarket ID
     */
    public function getByCardmarketId(string $cardmarketId): JsonResponse
    {
        $card = Card::where('cardmarket_id', $cardmarketId)->first();

        if (!$card) {
            return response()->json([
                'error' => 'Card not found'
            ], 404);
        }

        return response()->json([
            'data' => $card
        ]);
    }

    /**
     * Update card prices from Cardmarket API
     */
    public function updatePrices(string $id): JsonResponse
    {
        if (!$this->cardmarketService) {
            return response()->json([
                'error' => 'Cardmarket service not available'
            ], 503);
        }

        $card = Card::findOrFail($id);

        if (!$card->cardmarket_id) {
            return response()->json([
                'error' => 'Card has no Cardmarket ID'
            ], 400);
        }

        $success = $this->cardmarketService->updateCardPrices($card);

        if ($success) {
            return response()->json([
                'message' => 'Prices updated successfully'
            ]);
        }

        return response()->json([
            'error' => 'Failed to update prices'
        ], 500);
    }

    /**
     * Get all available sets
     */
    public function getSets(): JsonResponse
    {
        $sets = Card::select('set_name', 'set_code')
            ->distinct()
            ->orderBy('set_name')
            ->get();

        return response()->json([
            'data' => $sets
        ]);
    }

    /**
     * Get all available rarities
     */
    public function getRarities(): JsonResponse
    {
        $rarities = Card::select('rarity')
            ->distinct()
            ->orderBy('rarity')
            ->get()
            ->pluck('rarity');

        return response()->json([
            'data' => $rarities
        ]);
    }

    /**
     * Get all available colors
     */
    public function getColors(): JsonResponse
    {
        $colors = Card::select('colors')
            ->whereNotNull('colors')
            ->where('colors', '!=', '[]')
            ->get()
            ->flatMap(function ($card) {
                return $card->colors ?? [];
            })
            ->unique()
            ->sort()
            ->values();

        return response()->json([
            'data' => $colors
        ]);
    }
}
