<?php

namespace App\Console\Commands;

use App\Models\Card;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ImportCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cards:import {url : The URL to fetch card data from} {--chunk=1000 : Number of cards to process in each chunk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import MTG card data from a JSON URL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');
        $chunkSize = (int) $this->option('chunk');

        $this->info("Starting card import from: {$url}");
        $this->info("Chunk size: {$chunkSize}");

        try {
            // Fetch JSON data
            $this->info('Fetching card data...');
            $response = Http::timeout(300)->get($url);

            if (!$response->successful()) {
                $this->error("Failed to fetch data from URL. Status: {$response->status()}");
                return 1;
            }

            $data = $response->json();
            
            if (!is_array($data)) {
                $this->error('Invalid JSON data received');
                return 1;
            }

            $totalCards = count($data);
            $this->info("Found {$totalCards} cards to import");

            // Process cards in chunks
            $chunks = array_chunk($data, $chunkSize);
            $imported = 0;
            $skipped = 0;
            $errors = 0;

            $progressBar = $this->output->createProgressBar($totalCards);
            $progressBar->start();

            foreach ($chunks as $chunk) {
                foreach ($chunk as $cardData) {
                    try {
                        $card = $this->processCard($cardData);
                        
                        if ($card) {
                            $imported++;
                        } else {
                            $skipped++;
                        }
                    } catch (Exception $e) {
                        $errors++;
                        Log::error('Card import error', [
                            'card_data' => $cardData,
                            'error' => $e->getMessage()
                        ]);
                    }

                    $progressBar->advance();
                }

                // Clear memory after each chunk
                gc_collect_cycles();
            }

            $progressBar->finish();
            $this->newLine();

            $this->info("Import completed!");
            $this->info("Imported: {$imported}");
            $this->info("Skipped: {$skipped}");
            $this->info("Errors: {$errors}");

            return 0;

        } catch (Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");
            Log::error('Card import failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }

    /**
     * Process a single card
     */
    private function processCard(array $cardData): ?Card
    {
        // Check if card already exists
        $existingCard = null;
        
        if (isset($cardData['cardmarket_id'])) {
            $existingCard = Card::where('cardmarket_id', $cardData['cardmarket_id'])->first();
        } elseif (isset($cardData['scryfall_id'])) {
            $existingCard = Card::where('scryfall_id', $cardData['scryfall_id'])->first();
        }

        if ($existingCard) {
            // Update existing card
            $existingCard->update($this->mapCardData($cardData));
            return $existingCard;
        }

        // Create new card
        return Card::create($this->mapCardData($cardData));
    }

    /**
     * Map card data to database fields
     */
    private function mapCardData(array $data): array
    {
        return [
            'name' => $data['name'] ?? '',
            'set_name' => $data['set_name'] ?? $data['set'] ?? '',
            'set_code' => $data['set_code'] ?? $data['set'] ?? '',
            'rarity' => $data['rarity'] ?? '',
            'type_line' => $data['type_line'] ?? $data['type'] ?? '',
            'oracle_text' => $data['oracle_text'] ?? $data['text'] ?? null,
            'mana_cost' => $data['mana_cost'] ?? $data['cost'] ?? null,
            'cmc' => $data['cmc'] ?? $data['converted_mana_cost'] ?? null,
            'colors' => $data['colors'] ?? null,
            'color_identity' => $data['color_identity'] ?? null,
            'cardmarket_id' => $data['cardmarket_id'] ?? $data['id'] ?? null,
            'scryfall_id' => $data['scryfall_id'] ?? $data['id'] ?? null,
            'image_url' => $data['image_url'] ?? $data['image'] ?? null,
            'image_art_crop' => $data['image_art_crop'] ?? null,
            'image_normal' => $data['image_normal'] ?? null,
            'image_small' => $data['image_small'] ?? null,
            'power' => $data['power'] ?? null,
            'toughness' => $data['toughness'] ?? null,
            'loyalty' => $data['loyalty'] ?? null,
            'keywords' => $data['keywords'] ?? null,
            'layout' => $data['layout'] ?? 'normal',
            'reserved' => $data['reserved'] ?? false,
            'foil' => $data['foil'] ?? false,
            'nonfoil' => $data['nonfoil'] ?? true,
            'border_color' => $data['border_color'] ?? 'black',
            'frame' => $data['frame'] ?? '2015',
            'frame_effects' => $data['frame_effects'] ?? null,
            'full_art' => $data['full_art'] ?? false,
            'textless' => $data['textless'] ?? false,
            'oversized' => $data['oversized'] ?? false,
            'promo' => $data['promo'] ?? false,
            'reprint' => $data['reprint'] ?? false,
            'variation' => $data['variation'] ?? false,
            'variation_of' => $data['variation_of'] ?? null,
            'printed_name' => $data['printed_name'] ?? null,
            'printed_type_line' => $data['printed_type_line'] ?? null,
            'printed_text' => $data['printed_text'] ?? null,
        ];
    }
}
