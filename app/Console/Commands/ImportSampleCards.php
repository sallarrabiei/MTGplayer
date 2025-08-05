<?php

namespace App\Console\Commands;

use App\Models\Card;
use Illuminate\Console\Command;

class ImportSampleCards extends Command
{
    protected $signature = 'cards:sample';
    protected $description = 'Import sample MTG card data for testing';

    public function handle()
    {
        $this->info('Importing sample MTG cards...');

        $sampleCards = [
            [
                'name' => 'Black Lotus',
                'set_name' => 'Alpha',
                'set_code' => 'lea',
                'rarity' => 'rare',
                'type_line' => 'Artifact',
                'oracle_text' => '{T}, Sacrifice Black Lotus: Add three mana of any one color.',
                'mana_cost' => '{0}',
                'cmc' => 0,
                'colors' => [],
                'color_identity' => [],
                'cardmarket_id' => '12345',
                'scryfall_id' => 'scryfall-123',
                'image_url' => 'https://cards.scryfall.io/normal/front/0/0/00000000-0000-0000-0000-000000000000.jpg',
                'image_art_crop' => 'https://cards.scryfall.io/art_crop/front/0/0/00000000-0000-0000-0000-000000000000.jpg',
                'image_normal' => 'https://cards.scryfall.io/normal/front/0/0/00000000-0000-0000-0000-000000000000.jpg',
                'image_small' => 'https://cards.scryfall.io/small/front/0/0/00000000-0000-0000-0000-000000000000.jpg',
                'power' => null,
                'toughness' => null,
                'loyalty' => null,
                'keywords' => [],
                'layout' => 'normal',
                'reserved' => true,
                'foil' => true,
                'nonfoil' => true,
                'border_color' => 'black',
                'frame' => '1993',
                'frame_effects' => null,
                'full_art' => false,
                'textless' => false,
                'oversized' => false,
                'promo' => false,
                'reprint' => false,
                'variation' => false,
                'variation_of' => null,
                'printed_name' => null,
                'printed_type_line' => null,
                'printed_text' => null,
            ],
            [
                'name' => 'Lightning Bolt',
                'set_name' => 'Alpha',
                'set_code' => 'lea',
                'rarity' => 'common',
                'type_line' => 'Instant',
                'oracle_text' => 'Lightning Bolt deals 3 damage to any target.',
                'mana_cost' => '{R}',
                'cmc' => 1,
                'colors' => ['R'],
                'color_identity' => ['R'],
                'cardmarket_id' => '12346',
                'scryfall_id' => 'scryfall-124',
                'image_url' => 'https://cards.scryfall.io/normal/front/0/0/00000000-0000-0000-0000-000000000001.jpg',
                'image_art_crop' => 'https://cards.scryfall.io/art_crop/front/0/0/00000000-0000-0000-0000-000000000001.jpg',
                'image_normal' => 'https://cards.scryfall.io/normal/front/0/0/00000000-0000-0000-0000-000000000001.jpg',
                'image_small' => 'https://cards.scryfall.io/small/front/0/0/00000000-0000-0000-0000-000000000001.jpg',
                'power' => null,
                'toughness' => null,
                'loyalty' => null,
                'keywords' => [],
                'layout' => 'normal',
                'reserved' => false,
                'foil' => true,
                'nonfoil' => true,
                'border_color' => 'black',
                'frame' => '1993',
                'frame_effects' => null,
                'full_art' => false,
                'textless' => false,
                'oversized' => false,
                'promo' => false,
                'reprint' => false,
                'variation' => false,
                'variation_of' => null,
                'printed_name' => null,
                'printed_type_line' => null,
                'printed_text' => null,
            ],
            [
                'name' => 'Counterspell',
                'set_name' => 'Alpha',
                'set_code' => 'lea',
                'rarity' => 'common',
                'type_line' => 'Instant',
                'oracle_text' => 'Counter target spell.',
                'mana_cost' => '{U}{U}',
                'cmc' => 2,
                'colors' => ['U'],
                'color_identity' => ['U'],
                'cardmarket_id' => '12347',
                'scryfall_id' => 'scryfall-125',
                'image_url' => 'https://cards.scryfall.io/normal/front/0/0/00000000-0000-0000-0000-000000000002.jpg',
                'image_art_crop' => 'https://cards.scryfall.io/art_crop/front/0/0/00000000-0000-0000-0000-000000000002.jpg',
                'image_normal' => 'https://cards.scryfall.io/normal/front/0/0/00000000-0000-0000-0000-000000000002.jpg',
                'image_small' => 'https://cards.scryfall.io/small/front/0/0/00000000-0000-0000-0000-000000000002.jpg',
                'power' => null,
                'toughness' => null,
                'loyalty' => null,
                'keywords' => [],
                'layout' => 'normal',
                'reserved' => false,
                'foil' => true,
                'nonfoil' => true,
                'border_color' => 'black',
                'frame' => '1993',
                'frame_effects' => null,
                'full_art' => false,
                'textless' => false,
                'oversized' => false,
                'promo' => false,
                'reprint' => false,
                'variation' => false,
                'variation_of' => null,
                'printed_name' => null,
                'printed_type_line' => null,
                'printed_text' => null,
            ],
        ];

        $bar = $this->output->createProgressBar(count($sampleCards));
        $bar->start();

        foreach ($sampleCards as $cardData) {
            Card::updateOrCreate(
                ['name' => $cardData['name'], 'set_code' => $cardData['set_code']],
                $cardData
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Sample cards imported successfully!');
    }
}