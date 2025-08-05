<?php

namespace App\Services;

use App\Models\Card;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class CardImportService
{
    protected string $jsonUrl;
    protected int $batchSize = 500;

    public function __construct()
    {
        $this->jsonUrl = config('mtg.cards_json_url', 'https://mtgjson.com/api/v5/AllCards.json');
    }

    /**
     * Import cards from JSON URL.
     */
    public function importCards(): array
    {
        $stats = [
            'total_processed' => 0,
            'imported' => 0,
            'updated' => 0,
            'errors' => 0,
            'start_time' => now(),
        ];

        try {
            Log::info('Starting card import from: ' . $this->jsonUrl);
            
            $response = Http::timeout(300)->get($this->jsonUrl);
            
            if (!$response->successful()) {
                throw new Exception("Failed to fetch data from {$this->jsonUrl}. Status: " . $response->status());
            }

            $data = $response->json();
            
            if (!isset($data['data'])) {
                throw new Exception('Invalid JSON structure: missing data field');
            }

            $cards = $data['data'];
            $stats['total_processed'] = count($cards);
            
            Log::info("Processing {$stats['total_processed']} cards");

            // Process cards in batches
            $chunks = array_chunk($cards, $this->batchSize, true);
            
            foreach ($chunks as $chunk) {
                $batchStats = $this->processBatch($chunk);
                $stats['imported'] += $batchStats['imported'];
                $stats['updated'] += $batchStats['updated'];
                $stats['errors'] += $batchStats['errors'];
                
                // Memory cleanup
                gc_collect_cycles();
            }

        } catch (Exception $e) {
            Log::error('Card import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $stats['errors']++;
        }

        $stats['end_time'] = now();
        $stats['duration'] = $stats['end_time']->diffInSeconds($stats['start_time']);

        Log::info('Card import completed', $stats);

        return $stats;
    }

    /**
     * Process a batch of cards.
     */
    protected function processBatch(array $cards): array
    {
        $stats = ['imported' => 0, 'updated' => 0, 'errors' => 0];

        DB::beginTransaction();
        
        try {
            foreach ($cards as $cardName => $cardData) {
                try {
                    $result = $this->processCard($cardName, $cardData);
                    $stats[$result]++;
                } catch (Exception $e) {
                    Log::error('Error processing card', [
                        'card' => $cardName,
                        'error' => $e->getMessage()
                    ]);
                    $stats['errors']++;
                }
            }
            
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Batch processing failed', ['error' => $e->getMessage()]);
            $stats['errors'] += count($cards);
        }

        return $stats;
    }

    /**
     * Process a single card.
     */
    protected function processCard(string $cardName, array $cardData): string
    {
        // Handle cards with multiple printings
        if (isset($cardData[0]) && is_array($cardData[0])) {
            // Multiple printings - process the first one or most recent
            $cardData = $this->selectBestPrinting($cardData);
        }

        $cardAttributes = $this->mapCardAttributes($cardName, $cardData);
        
        // Try to find existing card by name and set
        $existingCard = Card::where('name', $cardAttributes['name'])
                          ->where('set_code', $cardAttributes['set_code'])
                          ->first();

        if ($existingCard) {
            $existingCard->update($cardAttributes);
            return 'updated';
        } else {
            Card::create($cardAttributes);
            return 'imported';
        }
    }

    /**
     * Select the best printing from multiple versions.
     */
    protected function selectBestPrinting(array $printings): array
    {
        // Prefer the most recent printing or the one with the most complete data
        usort($printings, function ($a, $b) {
            // Prefer cards with images
            if (isset($a['imageUris']) && !isset($b['imageUris'])) return -1;
            if (!isset($a['imageUris']) && isset($b['imageUris'])) return 1;
            
            // Prefer cards with Cardmarket ID
            if (isset($a['identifiers']['cardmarketId']) && !isset($b['identifiers']['cardmarketId'])) return -1;
            if (!isset($a['identifiers']['cardmarketId']) && isset($b['identifiers']['cardmarketId'])) return 1;
            
            // Prefer more recent sets (by release date if available)
            return strcmp($b['setCode'] ?? '', $a['setCode'] ?? '');
        });

        return $printings[0];
    }

    /**
     * Map JSON card data to database attributes.
     */
    protected function mapCardAttributes(string $cardName, array $cardData): array
    {
        return [
            'name' => $cardName,
            'mana_cost' => $cardData['manaCost'] ?? null,
            'converted_mana_cost' => $cardData['convertedManaCost'] ?? null,
            'type_line' => $cardData['type'] ?? null,
            'oracle_text' => $cardData['text'] ?? null,
            'power' => $cardData['power'] ?? null,
            'toughness' => $cardData['toughness'] ?? null,
            'colors' => $cardData['colors'] ?? [],
            'color_identity' => $cardData['colorIdentity'] ?? [],
            'keywords' => $cardData['keywords'] ?? [],
            'set_code' => $cardData['setCode'] ?? 'UNK',
            'set_name' => $cardData['setName'] ?? null,
            'rarity' => strtolower($cardData['rarity'] ?? 'common'),
            'collector_number' => $cardData['number'] ?? null,
            'artist' => $cardData['artist'] ?? null,
            'flavor_text' => $cardData['flavorText'] ?? null,
            'image_uris' => $cardData['imageUris'] ?? null,
            'cardmarket_id' => $cardData['identifiers']['cardmarketId'] ?? null,
            'multiverse_ids' => $cardData['identifiers']['multiverseId'] ? [$cardData['identifiers']['multiverseId']] : [],
            'mtgo_id' => $cardData['identifiers']['mtgoId'] ?? null,
            'arena_id' => $cardData['identifiers']['arenaId'] ?? null,
            'tcgplayer_id' => $cardData['identifiers']['tcgplayerId'] ?? null,
            'card_faces' => $cardData['cardFaces'] ?? null,
            'layout' => $cardData['layout'] ?? 'normal',
            'legalities' => $cardData['legalities'] ?? [],
            'reserved' => $cardData['isReserved'] ?? false,
            'foil' => $cardData['hasFoil'] ?? false,
            'nonfoil' => $cardData['hasNonFoil'] ?? true,
            'oversized' => $cardData['isOversized'] ?? false,
            'promo' => $cardData['isPromo'] ?? false,
            'reprint' => $cardData['isReprint'] ?? false,
            'variation' => $cardData['isVariation'] ?? false,
            'frame' => $cardData['frameVersion'] ?? null,
            'full_art' => $cardData['isFullArt'] ?? false,
            'textless' => $cardData['isTextless'] ?? false,
            'booster' => $cardData['boosterTypes'] ? count($cardData['boosterTypes']) > 0 : true,
            'story_spotlight' => $cardData['isStorySpotlight'] ?? false,
            'prices' => $cardData['prices'] ?? null,
            'related_uris' => $cardData['relatedUris'] ?? null,
            'purchase_uris' => $cardData['purchaseUris'] ?? null,
        ];
    }

    /**
     * Set batch size for processing.
     */
    public function setBatchSize(int $size): self
    {
        $this->batchSize = $size;
        return $this;
    }

    /**
     * Set JSON URL for import.
     */
    public function setJsonUrl(string $url): self
    {
        $this->jsonUrl = $url;
        return $this;
    }
}