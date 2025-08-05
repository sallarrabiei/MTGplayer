<?php

namespace App\Console\Commands;

use App\Models\Card;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ImportMTGCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mtg:import {url : The JSON URL to import cards from}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import MTG cards from a JSON URL into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');
        
        $this->info('Fetching cards from: ' . $url);
        
        try {
            $response = Http::timeout(300)->get($url);
            
            if (!$response->successful()) {
                $this->error('Failed to fetch data from URL. Status: ' . $response->status());
                return 1;
            }
            
            $data = $response->json();
            
            if (!is_array($data)) {
                $this->error('Invalid JSON format. Expected an array of cards.');
                return 1;
            }
            
            $this->info('Found ' . count($data) . ' cards to import');
            
            $progressBar = $this->output->createProgressBar(count($data));
            $progressBar->start();
            
            $imported = 0;
            $skipped = 0;
            
            DB::transaction(function () use ($data, &$imported, &$skipped, $progressBar) {
                foreach ($data as $cardData) {
                    try {
                        $card = $this->processCard($cardData);
                        if ($card) {
                            $imported++;
                        } else {
                            $skipped++;
                        }
                    } catch (\Exception $e) {
                        $this->error("\nError processing card: " . ($cardData['name'] ?? 'Unknown') . ' - ' . $e->getMessage());
                        $skipped++;
                    }
                    
                    $progressBar->advance();
                }
            });
            
            $progressBar->finish();
            
            $this->newLine(2);
            $this->info("Import completed!");
            $this->info("Imported: $imported cards");
            $this->info("Skipped: $skipped cards");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error during import: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Process a single card data
     */
    protected function processCard(array $cardData): ?Card
    {
        // Extract required fields
        $name = $cardData['name'] ?? null;
        $setCode = $cardData['set'] ?? $cardData['set_code'] ?? null;
        
        if (!$name || !$setCode) {
            return null;
        }
        
        // Prepare card data
        $attributes = [
            'name' => $name,
            'set_code' => $setCode,
            'set_name' => $cardData['set_name'] ?? null,
            'collector_number' => $cardData['collector_number'] ?? $cardData['number'] ?? null,
            'rarity' => $cardData['rarity'] ?? null,
            'type_line' => $cardData['type_line'] ?? $cardData['type'] ?? null,
            'oracle_text' => $cardData['oracle_text'] ?? $cardData['text'] ?? null,
            'mana_cost' => $cardData['mana_cost'] ?? null,
            'cmc' => $cardData['cmc'] ?? $cardData['converted_mana_cost'] ?? null,
            'colors' => $this->processColors($cardData['colors'] ?? []),
            'color_identity' => $this->processColors($cardData['color_identity'] ?? []),
            'power' => $cardData['power'] ?? null,
            'toughness' => $cardData['toughness'] ?? null,
            'loyalty' => $cardData['loyalty'] ?? null,
            'artist' => $cardData['artist'] ?? null,
            'cardmarket_id' => $cardData['cardmarket_id'] ?? $cardData['identifiers']['cardmarket_id'] ?? null,
            'image_uris' => $this->processImageUris($cardData),
            'raw_data' => $cardData
        ];
        
        // Update or create the card
        return Card::updateOrCreate(
            [
                'name' => $name,
                'set_code' => $setCode,
                'collector_number' => $attributes['collector_number']
            ],
            $attributes
        );
    }
    
    /**
     * Process colors array into comma-separated string
     */
    protected function processColors($colors): ?string
    {
        if (empty($colors)) {
            return null;
        }
        
        if (is_array($colors)) {
            return implode(',', $colors);
        }
        
        return $colors;
    }
    
    /**
     * Process image URIs
     */
    protected function processImageUris(array $cardData): ?string
    {
        $imageUris = [];
        
        // Check for image_uris object
        if (isset($cardData['image_uris'])) {
            $imageUris = $cardData['image_uris'];
        }
        // Check for card_faces (double-faced cards)
        elseif (isset($cardData['card_faces']) && is_array($cardData['card_faces'])) {
            foreach ($cardData['card_faces'] as $face) {
                if (isset($face['image_uris'])) {
                    $imageUris['face_' . ($face['name'] ?? 'unknown')] = $face['image_uris'];
                }
            }
        }
        
        return !empty($imageUris) ? json_encode($imageUris) : null;
    }
}
