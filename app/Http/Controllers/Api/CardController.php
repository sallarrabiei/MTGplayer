<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Services\CardmarketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CardController extends Controller
{
    protected $cardmarketService;
    
    public function __construct(CardmarketService $cardmarketService)
    {
        $this->cardmarketService = $cardmarketService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|min:2',
            'set' => 'nullable|string',
            'rarity' => 'nullable|string|in:common,uncommon,rare,mythic',
            'colors' => 'nullable|string',
            'mana_cost' => 'nullable|string',
            'cmc' => 'nullable|numeric',
            'type' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort' => 'nullable|string|in:name,cmc,price,set_code',
            'order' => 'nullable|string|in:asc,desc'
        ]);
        
        // Build cache key from request parameters
        $cacheKey = 'cards_' . md5(json_encode($request->all()));
        
        // Try to get from cache
        $result = Cache::remember($cacheKey, 300, function () use ($request) {
            $query = Card::query();
            
            // Apply filters
            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }
            
            if ($request->filled('set')) {
                $query->where(function ($q) use ($request) {
                    $q->where('set_code', $request->set)
                      ->orWhere('set_name', 'like', '%' . $request->set . '%');
                });
            }
            
            if ($request->filled('rarity')) {
                $query->where('rarity', $request->rarity);
            }
            
            if ($request->filled('colors')) {
                $colors = explode(',', $request->colors);
                foreach ($colors as $color) {
                    $query->where('colors', 'like', '%' . $color . '%');
                }
            }
            
            if ($request->filled('mana_cost')) {
                $query->where('mana_cost', $request->mana_cost);
            }
            
            if ($request->filled('cmc')) {
                $query->where('cmc', $request->cmc);
            }
            
            if ($request->filled('type')) {
                $query->where('type_line', 'like', '%' . $request->type . '%');
            }
            
            // Apply sorting
            $sortField = $request->get('sort', 'name');
            $sortOrder = $request->get('order', 'asc');
            $query->orderBy($sortField, $sortOrder);
            
            // Paginate results
            $perPage = $request->get('per_page', 20);
            return $query->paginate($perPage);
        });
        
        return response()->json($result);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $card = Cache::remember("card_{$id}", 3600, function () use ($id) {
            return Card::findOrFail($id);
        });
        
        // Check if price needs updating (older than 1 hour)
        if ($card->cardmarket_id && 
            (!$card->price_updated_at || $card->price_updated_at->lt(now()->subHour()))) {
            
            $priceData = $this->cardmarketService->getCardPrice($card->cardmarket_id);
            
            if ($priceData && $priceData['price']) {
                $card->update([
                    'price' => $priceData['price'],
                    'price_currency' => $priceData['currency'],
                    'price_updated_at' => $priceData['updated_at']
                ]);
                
                // Clear cache for this card
                Cache::forget("card_{$id}");
                $card->refresh();
            }
        }
        
        return response()->json($card);
    }
    
    /**
     * Get distinct values for filters
     */
    public function filters()
    {
        $cacheKey = 'card_filters';
        
        $filters = Cache::remember($cacheKey, 3600, function () {
            return [
                'sets' => Card::select('set_code', 'set_name')
                    ->distinct()
                    ->whereNotNull('set_code')
                    ->orderBy('set_name')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'code' => $item->set_code,
                            'name' => $item->set_name ?: $item->set_code
                        ];
                    }),
                    
                'rarities' => Card::distinct()
                    ->whereNotNull('rarity')
                    ->pluck('rarity')
                    ->sort()
                    ->values(),
                    
                'colors' => ['W', 'U', 'B', 'R', 'G'],
                
                'types' => Card::selectRaw('SUBSTRING_INDEX(type_line, " — ", 1) as type')
                    ->distinct()
                    ->whereNotNull('type_line')
                    ->pluck('type')
                    ->sort()
                    ->values()
            ];
        });
        
        return response()->json($filters);
    }
    
    /**
     * Update card prices from Cardmarket
     */
    public function updatePrices(Request $request)
    {
        $request->validate([
            'card_ids' => 'nullable|array',
            'card_ids.*' => 'exists:cards,id'
        ]);
        
        $query = Card::whereNotNull('cardmarket_id');
        
        if ($request->filled('card_ids')) {
            $query->whereIn('id', $request->card_ids);
        } else {
            // Update only cards with outdated prices
            $query->where(function ($q) {
                $q->whereNull('price_updated_at')
                  ->orWhere('price_updated_at', '<', now()->subDay());
            });
        }
        
        $cards = $query->limit(100)->get(); // Limit to avoid timeout
        $updated = $this->cardmarketService->updateCardPrices($cards);
        
        // Clear relevant caches
        Cache::flush(); // In production, use more selective cache clearing
        
        return response()->json([
            'message' => "Updated prices for {$updated} cards",
            'updated' => $updated
        ]);
    }
}
