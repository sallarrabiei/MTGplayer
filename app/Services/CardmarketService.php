<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CardmarketService
{
    protected $baseUrl = 'https://api.cardmarket.com/ws/v2.0';
    protected $appToken;
    protected $appSecret;
    protected $accessToken;
    protected $accessSecret;
    
    public function __construct()
    {
        $this->appToken = config('services.cardmarket.app_token');
        $this->appSecret = config('services.cardmarket.app_secret');
        $this->accessToken = config('services.cardmarket.access_token');
        $this->accessSecret = config('services.cardmarket.access_secret');
    }
    
    /**
     * Get price for a specific card by ID
     */
    public function getCardPrice($cardmarketId)
    {
        if (!$cardmarketId) {
            return null;
        }
        
        // Cache key for this specific card
        $cacheKey = "cardmarket_price_{$cardmarketId}";
        
        // Check cache first (cache for 1 hour)
        return Cache::remember($cacheKey, 3600, function () use ($cardmarketId) {
            try {
                $response = $this->makeRequest("GET", "/products/{$cardmarketId}");
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    return [
                        'price' => $data['product']['priceGuide']['avg'] ?? null,
                        'low_price' => $data['product']['priceGuide']['low'] ?? null,
                        'trend_price' => $data['product']['priceGuide']['trend'] ?? null,
                        'foil_price' => $data['product']['priceGuide']['foilAvg'] ?? null,
                        'currency' => 'EUR',
                        'updated_at' => now()
                    ];
                }
                
                return null;
            } catch (\Exception $e) {
                Log::error('Cardmarket API error: ' . $e->getMessage());
                return null;
            }
        });
    }
    
    /**
     * Search for cards by name and set
     */
    public function searchCards($name, $set = null)
    {
        $cacheKey = "cardmarket_search_" . md5($name . $set);
        
        return Cache::remember($cacheKey, 3600, function () use ($name, $set) {
            try {
                $params = [
                    'search' => $name,
                    'game' => 1, // Magic: The Gathering
                    'language' => 1, // English
                ];
                
                if ($set) {
                    $params['expansion'] = $set;
                }
                
                $response = $this->makeRequest("GET", "/products/find", $params);
                
                if ($response->successful()) {
                    return $response->json()['product'] ?? [];
                }
                
                return [];
            } catch (\Exception $e) {
                Log::error('Cardmarket search error: ' . $e->getMessage());
                return [];
            }
        });
    }
    
    /**
     * Update prices for multiple cards
     */
    public function updateCardPrices($cards)
    {
        $updated = 0;
        
        foreach ($cards as $card) {
            if (!$card->cardmarket_id) {
                continue;
            }
            
            $priceData = $this->getCardPrice($card->cardmarket_id);
            
            if ($priceData && $priceData['price']) {
                $card->update([
                    'price' => $priceData['price'],
                    'price_currency' => $priceData['currency'],
                    'price_updated_at' => $priceData['updated_at']
                ]);
                $updated++;
            }
            
            // Rate limiting - Cardmarket has strict limits
            usleep(100000); // 100ms delay between requests
        }
        
        return $updated;
    }
    
    /**
     * Make authenticated request to Cardmarket API
     */
    protected function makeRequest($method, $endpoint, $params = [])
    {
        if (!$this->appToken || !$this->appSecret) {
            throw new \Exception('Cardmarket API credentials not configured');
        }
        
        $url = $this->baseUrl . $endpoint;
        
        // OAuth 1.0 signature generation
        $oauth = [
            'oauth_consumer_key' => $this->appToken,
            'oauth_token' => $this->accessToken,
            'oauth_nonce' => $this->generateNonce(),
            'oauth_timestamp' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_version' => '1.0',
        ];
        
        // Build signature base string
        $signatureBase = $this->buildSignatureBase($method, $url, array_merge($oauth, $params));
        
        // Generate signature
        $signingKey = rawurlencode($this->appSecret) . '&' . rawurlencode($this->accessSecret ?? '');
        $oauth['oauth_signature'] = base64_encode(hash_hmac('sha1', $signatureBase, $signingKey, true));
        
        // Build Authorization header
        $authHeader = 'OAuth ' . $this->buildAuthHeader($oauth);
        
        // Make request
        $request = Http::withHeaders([
            'Authorization' => $authHeader,
            'Accept' => 'application/json',
        ]);
        
        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $request->send($method, $url, $method !== 'GET' ? ['json' => $params] : []);
    }
    
    /**
     * Generate OAuth nonce
     */
    protected function generateNonce()
    {
        return md5(microtime() . mt_rand());
    }
    
    /**
     * Build OAuth signature base string
     */
    protected function buildSignatureBase($method, $url, $params)
    {
        // Sort parameters
        ksort($params);
        
        // Build parameter string
        $paramString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        
        // Build signature base
        return strtoupper($method) . '&' . rawurlencode($url) . '&' . rawurlencode($paramString);
    }
    
    /**
     * Build OAuth Authorization header
     */
    protected function buildAuthHeader($oauth)
    {
        $header = '';
        foreach ($oauth as $key => $value) {
            $header .= rawurlencode($key) . '="' . rawurlencode($value) . '", ';
        }
        return rtrim($header, ', ');
    }
}