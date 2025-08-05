<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class CardmarketService
{
    protected string $apiUrl;
    protected string $appToken;
    protected string $appSecret;
    protected string $accessToken;
    protected string $accessSecret;

    public function __construct()
    {
        $this->apiUrl = config('services.cardmarket.api_url', 'https://api.cardmarket.com/ws/v2.0');
        $this->appToken = config('services.cardmarket.app_token', '');
        $this->appSecret = config('services.cardmarket.app_secret', '');
        $this->accessToken = config('services.cardmarket.access_token', '');
        $this->accessSecret = config('services.cardmarket.access_secret', '');
    }

    /**
     * Get card price from Cardmarket API.
     */
    public function getCardPrice(string $cardmarketId): ?array
    {
        if (!$this->isConfigured()) {
            Log::warning('Cardmarket API not configured');
            return null;
        }

        try {
            $url = "{$this->apiUrl}/cards/{$cardmarketId}";
            
            $response = Http::withHeaders($this->getAuthHeaders('GET', $url))
                          ->timeout(10)
                          ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['card'])) {
                    return [
                        'avg_price' => $data['card']['priceGuide']['AVG'] ?? null,
                        'low_price' => $data['card']['priceGuide']['LOW'] ?? null,
                        'trend_price' => $data['card']['priceGuide']['TREND'] ?? null,
                        'german_pro_low' => $data['card']['priceGuide']['LOWFOIL'] ?? null,
                        'suggested_price' => $data['card']['priceGuide']['SELL'] ?? null,
                        'last_updated' => now()->toISOString()
                    ];
                }
            } elseif ($response->status() === 503) {
                Log::warning('Cardmarket API rate limit exceeded');
                return null;
            } else {
                Log::error('Cardmarket API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (Exception $e) {
            Log::error('Cardmarket API exception', [
                'message' => $e->getMessage(),
                'cardmarket_id' => $cardmarketId
            ]);
        }

        return null;
    }

    /**
     * Search for cards on Cardmarket.
     */
    public function searchCards(string $name, int $limit = 10): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $url = "{$this->apiUrl}/cards/find";
            $params = [
                'search' => $name,
                'exact' => 'false',
                'start' => '0',
                'maxResults' => $limit
            ];

            $response = Http::withHeaders($this->getAuthHeaders('GET', $url))
                          ->timeout(10)
                          ->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                return $data['card'] ?? [];
            }
        } catch (Exception $e) {
            Log::error('Cardmarket search error', [
                'message' => $e->getMessage(),
                'search' => $name
            ]);
        }

        return null;
    }

    /**
     * Check if the service is properly configured.
     */
    protected function isConfigured(): bool
    {
        return !empty($this->appToken) && 
               !empty($this->appSecret) && 
               !empty($this->accessToken) && 
               !empty($this->accessSecret);
    }

    /**
     * Generate OAuth 1.0 authorization headers for Cardmarket API.
     */
    protected function getAuthHeaders(string $method, string $url): array
    {
        $timestamp = time();
        $nonce = bin2hex(random_bytes(16));

        $oauthParams = [
            'oauth_consumer_key' => $this->appToken,
            'oauth_token' => $this->accessToken,
            'oauth_nonce' => $nonce,
            'oauth_timestamp' => $timestamp,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_version' => '1.0'
        ];

        // Create signature base string
        $baseString = $method . '&' . rawurlencode($url) . '&' . rawurlencode(http_build_query($oauthParams));
        
        // Create signing key
        $signingKey = rawurlencode($this->appSecret) . '&' . rawurlencode($this->accessSecret);
        
        // Generate signature
        $signature = base64_encode(hash_hmac('sha1', $baseString, $signingKey, true));
        $oauthParams['oauth_signature'] = $signature;

        // Build authorization header
        $authHeader = 'OAuth ';
        $headerParts = [];
        foreach ($oauthParams as $key => $value) {
            $headerParts[] = $key . '="' . rawurlencode($value) . '"';
        }
        $authHeader .= implode(', ', $headerParts);

        return [
            'Authorization' => $authHeader,
            'Accept' => 'application/json',
            'User-Agent' => 'MTGPlayer/1.0'
        ];
    }
}