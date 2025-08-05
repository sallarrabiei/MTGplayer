<?php

namespace Database\Factories;

use App\Models\Card;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $colors = ['W', 'U', 'B', 'R', 'G'];
        $rarities = ['common', 'uncommon', 'rare', 'mythic'];
        $sets = ['M21', 'ZNR', 'KHM', 'STX', 'AFR', 'MID', 'VOW', 'NEO', 'SNC', 'CLB'];
        
        return [
            'name' => $this->faker->words(2, true),
            'mana_cost' => $this->generateManaCost(),
            'converted_mana_cost' => $this->faker->numberBetween(0, 15),
            'type_line' => $this->generateTypeLine(),
            'oracle_text' => $this->faker->optional()->paragraph(),
            'power' => $this->faker->optional()->numberBetween(0, 20),
            'toughness' => $this->faker->optional()->numberBetween(0, 20),
            'colors' => $this->faker->optional()->randomElements($colors, $this->faker->numberBetween(1, 3)),
            'color_identity' => $this->faker->optional()->randomElements($colors, $this->faker->numberBetween(1, 3)),
            'keywords' => $this->faker->optional()->randomElements(['Flying', 'Trample', 'Vigilance', 'Haste', 'First Strike'], $this->faker->numberBetween(0, 3)),
            'set_code' => $this->faker->randomElement($sets),
            'set_name' => $this->faker->words(2, true),
            'rarity' => $this->faker->randomElement($rarities),
            'collector_number' => $this->faker->numberBetween(1, 300),
            'artist' => $this->faker->name(),
            'flavor_text' => $this->faker->optional()->sentence(),
            'image_uris' => $this->generateImageUris(),
            'cardmarket_id' => $this->faker->optional()->numberBetween(100000, 999999),
            'multiverse_ids' => $this->faker->optional()->randomElements(range(100000, 999999), $this->faker->numberBetween(1, 3)),
            'mtgo_id' => $this->faker->optional()->numberBetween(10000, 99999),
            'arena_id' => $this->faker->optional()->numberBetween(10000, 99999),
            'tcgplayer_id' => $this->faker->optional()->numberBetween(100000, 999999),
            'layout' => $this->faker->randomElement(['normal', 'transform', 'modal_dfc', 'split']),
            'legalities' => $this->generateLegalities(),
            'reserved' => $this->faker->boolean(5), // 5% chance
            'foil' => $this->faker->boolean(30),
            'nonfoil' => $this->faker->boolean(90),
            'oversized' => $this->faker->boolean(1),
            'promo' => $this->faker->boolean(10),
            'reprint' => $this->faker->boolean(40),
            'variation' => $this->faker->boolean(5),
            'frame' => $this->faker->randomElement(['1993', '1997', '2003', '2015', 'future']),
            'full_art' => $this->faker->boolean(5),
            'textless' => $this->faker->boolean(1),
            'booster' => $this->faker->boolean(80),
            'story_spotlight' => $this->faker->boolean(2),
            'prices' => $this->generatePrices(),
        ];
    }

    private function generateManaCost(): ?string
    {
        if ($this->faker->boolean(20)) {
            return null; // 20% chance of no mana cost (lands, etc.)
        }

        $cost = '';
        $genericCost = $this->faker->numberBetween(0, 10);
        
        if ($genericCost > 0) {
            $cost .= "{{$genericCost}}";
        }

        $colors = ['W', 'U', 'B', 'R', 'G'];
        $coloredMana = $this->faker->randomElements($colors, $this->faker->numberBetween(0, 3));
        
        foreach ($coloredMana as $color) {
            $cost .= "{{$color}}";
        }

        return $cost ?: null;
    }

    private function generateTypeLine(): string
    {
        $types = [
            'Creature — Human Wizard',
            'Instant',
            'Sorcery',
            'Enchantment',
            'Artifact',
            'Land',
            'Planeswalker — Jace',
            'Creature — Dragon',
            'Creature — Goblin Warrior',
            'Enchantment — Aura',
            'Artifact — Equipment',
        ];

        return $this->faker->randomElement($types);
    }

    private function generateImageUris(): ?array
    {
        if ($this->faker->boolean(20)) {
            return null; // 20% chance of no images
        }

        return [
            'small' => $this->faker->imageUrl(146, 204),
            'normal' => $this->faker->imageUrl(488, 680),
            'large' => $this->faker->imageUrl(672, 936),
        ];
    }

    private function generateLegalities(): array
    {
        $formats = ['standard', 'pioneer', 'modern', 'legacy', 'vintage', 'commander'];
        $statuses = ['legal', 'not_legal', 'banned', 'restricted'];
        
        $legalities = [];
        foreach ($formats as $format) {
            $legalities[$format] = $this->faker->randomElement($statuses);
        }

        return $legalities;
    }

    private function generatePrices(): ?array
    {
        if ($this->faker->boolean(30)) {
            return null; // 30% chance of no prices
        }

        return [
            'cardmarket' => $this->faker->randomFloat(2, 0.10, 100.00),
            'tcgplayer' => $this->faker->randomFloat(2, 0.10, 100.00),
        ];
    }
}