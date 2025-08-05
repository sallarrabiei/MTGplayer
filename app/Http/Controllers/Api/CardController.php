<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Services\CardmarketService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CardController extends Controller
{
    protected CardmarketService $cardmarketService;

    public function __construct(CardmarketService $cardmarketService)
    {
        $this->cardmarketService = $cardmarketService;
    }

    /**
     * Get cards with search and filter options.
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'set' => 'sometimes|string|max:10',
            'rarity' => 'sometimes|string|in:common,uncommon,rare,mythic',
            'colors' => 'sometimes|array',
            'colors.*' => 'string|in:W,U,B,R,G',
            'cmc' => 'sometimes|integer|min:0',
            'type' => 'sometimes|string|max:255',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $cacheKey = 'cards_search_' . md5(serialize($request->all()));
        
        return Cache::remember($cacheKey, 300, function () use ($request) {
            $query = Card::query();

            // Apply filters
            if ($request->filled('name')) {
                $query->searchByName($request->input('name'));
            }

            if ($request->filled('set')) {
                $query->filterBySet($request->input('set'));
            }

            if ($request->filled('rarity')) {
                $query->filterByRarity($request->input('rarity'));
            }

            if ($request->filled('colors')) {
                $query->filterByColors($request->input('colors'));
            }

            if ($request->filled('cmc')) {
                $query->filterByConvertedManaCost($request->input('cmc'));
            }

            if ($request->filled('type')) {
                $query->filterByType($request->input('type'));
            }

            // Pagination
            $perPage = $request->input('per_page', 20);
            $cards = $query->orderBy('name')
                          ->paginate($perPage);

            return response()->json([
                'data' => $cards->items(),
                'pagination' => [
                    'current_page' => $cards->currentPage(),
                    'last_page' => $cards->lastPage(),
                    'per_page' => $cards->perPage(),
                    'total' => $cards->total(),
                    'from' => $cards->firstItem(),
                    'to' => $cards->lastItem(),
                ]
            ]);
        });
    }

    /**
     * Get a specific card by ID.
     */
    public function show(Card $card): JsonResponse
    {
        $cacheKey = "card_{$card->id}";
        
        return Cache::remember($cacheKey, 600, function () use ($card) {
            return response()->json([
                'data' => $card
            ]);
        });
    }

    /**
     * Get card price from Cardmarket API.
     */
    public function getPrice(Card $card): JsonResponse
    {
        if (!$card->cardmarket_id) {
            return response()->json([
                'error' => 'Card has no Cardmarket ID'
            ], 404);
        }

        $cacheKey = "cardmarket_price_{$card->cardmarket_id}";
        
        $price = Cache::remember($cacheKey, 1800, function () use ($card) {
            return $this->cardmarketService->getCardPrice($card->cardmarket_id);
        });

        if ($price === null) {
            return response()->json([
                'error' => 'Unable to fetch price from Cardmarket'
            ], 503);
        }

        return response()->json([
            'data' => [
                'card_id' => $card->id,
                'cardmarket_id' => $card->cardmarket_id,
                'price' => $price
            ]
        ]);
    }

    /**
     * Get unique sets for filtering.
     */
    public function getSets(): JsonResponse
    {
        $cacheKey = 'card_sets';
        
        return Cache::remember($cacheKey, 3600, function () {
            $sets = Card::select('set_code', 'set_name')
                       ->distinct()
                       ->orderBy('set_name')
                       ->get();

            return response()->json([
                'data' => $sets
            ]);
        });
    }

    /**
     * Get card statistics.
     */
    public function getStats(): JsonResponse
    {
        $cacheKey = 'card_stats';
        
        return Cache::remember($cacheKey, 1800, function () {
            $stats = [
                'total_cards' => Card::count(),
                'total_sets' => Card::distinct('set_code')->count(),
                'rarities' => Card::selectRaw('rarity, COUNT(*) as count')
                                 ->groupBy('rarity')
                                 ->pluck('count', 'rarity'),
                'colors' => [
                    'white' => Card::whereJsonContains('colors', 'W')->count(),
                    'blue' => Card::whereJsonContains('colors', 'U')->count(),
                    'black' => Card::whereJsonContains('colors', 'B')->count(),
                    'red' => Card::whereJsonContains('colors', 'R')->count(),
                    'green' => Card::whereJsonContains('colors', 'G')->count(),
                    'colorless' => Card::whereJsonLength('colors', 0)->count(),
                ]
            ];

            return response()->json([
                'data' => $stats
            ]);
        });
    }
}