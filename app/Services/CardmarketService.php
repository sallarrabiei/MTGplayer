<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Card;
use App\Models\CardPrice;
use Exception;

class CardmarketService
{
    private string $baseUrl = 'https://api.cardmarket.com/ws/v2.0';
    private string $appToken;
    private string $appSecret;
    private string $accessToken;
    private string $accessTokenSecret;

    public function __construct()
    {
        $this->appToken = config('services.cardmarket.app_token');
        $this->appSecret = config('services.cardmarket.app_secret');
        $this->accessToken = config('services.cardmarket.access_token');
        $this->accessTokenSecret = config('services.cardmarket.access_token_secret');
    }

    /**
     * Get card prices from Cardmarket API
     */
    public function getCardPrices(string $cardmarketId): ?array
    {
        $cacheKey = "cardmarket_prices_{$cardmarketId}";
        
        // Check cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->getAuthorizationHeader(),
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/output.json/product/{$cardmarketId}");

            if ($response->successful()) {
                $data = $response->json();
                
                // Cache for 1 hour to respect rate limits
                Cache::put($cacheKey, $data, 3600);
                
                return $data;
            }

            Log::warning('Cardmarket API request failed', [
                'cardmarket_id' => $cardmarketId,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Cardmarket API exception', [
                'cardmarket_id' => $cardmarketId,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Search for cards by name
     */
    public function searchCards(string $name): ?array
    {
        $cacheKey = "cardmarket_search_" . md5($name);
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->getAuthorizationHeader(),
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/output.json/products/find", [
                'search' => $name,
                'game' => 1, // Magic: The Gathering
                'language' => 1, // English
                'maxResults' => 10
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Cache::put($cacheKey, $data, 1800); // Cache for 30 minutes
                return $data;
            }

            return null;
        } catch (Exception $e) {
            Log::error('Cardmarket search exception', [
                'search' => $name,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Update prices for a specific card
     */
    public function updateCardPrices(Card $card): bool
    {
        if (!$card->cardmarket_id) {
            return false;
        }

        $prices = $this->getCardPrices($card->cardmarket_id);
        
        if (!$prices || !isset($prices['product'])) {
            return false;
        }

        $product = $prices['product'];
        $priceGuide = $product['priceGuide'] ?? [];

        // Clear existing prices for this card
        $card->prices()->delete();

        // Store new prices
        $priceTypes = ['LOW', 'AVG', 'HIGH', 'MARKET'];
        $conditions = ['NM', 'LP', 'MP', 'HP', 'PO'];

        foreach ($priceTypes as $priceType) {
            if (isset($priceGuide[$priceType])) {
                $price = $priceGuide[$priceType];
                
                // Store non-foil prices
                if (isset($price['SELL'])) {
                    $card->prices()->create([
                        'cardmarket_id' => $card->cardmarket_id,
                        'price_type' => strtolower($priceType),
                        'price' => $price['SELL'],
                        'currency' => 'EUR',
                        'condition' => 'NM',
                        'foil' => false,
                        'price_updated_at' => now(),
                    ]);
                }

                // Store foil prices if available
                if (isset($price['SELLFOIL'])) {
                    $card->prices()->create([
                        'cardmarket_id' => $card->cardmarket_id,
                        'price_type' => strtolower($priceType),
                        'price' => $price['SELLFOIL'],
                        'currency' => 'EUR',
                        'condition' => 'NM',
                        'foil' => true,
                        'price_updated_at' => now(),
                    ]);
                }
            }
        }

        return true;
    }

    /**
     * Generate OAuth authorization header
     */
    private function getAuthorizationHeader(): string
    {
        // This is a simplified version. In production, you'd need proper OAuth 1.0a implementation
        // For now, we'll use the basic authentication method
        return "OAuth realm=\"\", oauth_consumer_key=\"{$this->appToken}\", oauth_token=\"{$this->accessToken}\"";
    }

    /**
     * Check if we're within rate limits
     */
    private function checkRateLimit(): bool
    {
        $requestsToday = Cache::get('cardmarket_requests_today', 0);
        
        if ($requestsToday >= 30000) { // 30,000 requests per day limit
            return false;
        }

        Cache::put('cardmarket_requests_today', $requestsToday + 1, 86400);
        return true;
    }

    /**
     * Handle rate limit errors
     */
    private function handleRateLimit(): void
    {
        Log::warning('Cardmarket API rate limit reached');
        Cache::put('cardmarket_rate_limited', true, 60); // Wait 1 minute
    }
}