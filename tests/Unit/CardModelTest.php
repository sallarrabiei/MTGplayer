<?php

namespace Tests\Unit;

use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_card_has_fillable_attributes(): void
    {
        $card = new Card();
        
        $this->assertContains('name', $card->getFillable());
        $this->assertContains('mana_cost', $card->getFillable());
        $this->assertContains('type_line', $card->getFillable());
        $this->assertContains('set_code', $card->getFillable());
        $this->assertContains('rarity', $card->getFillable());
    }

    public function test_card_casts_arrays_properly(): void
    {
        $card = Card::factory()->create([
            'colors' => ['R', 'G'],
            'keywords' => ['Flying', 'Trample']
        ]);

        $this->assertIsArray($card->colors);
        $this->assertIsArray($card->keywords);
        $this->assertEquals(['R', 'G'], $card->colors);
        $this->assertEquals(['Flying', 'Trample'], $card->keywords);
    }

    public function test_search_by_name_scope(): void
    {
        Card::factory()->create(['name' => 'Lightning Bolt']);
        Card::factory()->create(['name' => 'Lightning Strike']);
        Card::factory()->create(['name' => 'Fireball']);

        $results = Card::searchByName('Lightning')->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('name', 'Lightning Bolt'));
        $this->assertTrue($results->contains('name', 'Lightning Strike'));
    }

    public function test_filter_by_set_scope(): void
    {
        Card::factory()->create(['set_code' => 'M21']);
        Card::factory()->create(['set_code' => 'M21']);
        Card::factory()->create(['set_code' => 'ZNR']);

        $results = Card::filterBySet('M21')->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn($card) => $card->set_code === 'M21'));
    }

    public function test_filter_by_rarity_scope(): void
    {
        Card::factory()->create(['rarity' => 'rare']);
        Card::factory()->create(['rarity' => 'common']);
        Card::factory()->create(['rarity' => 'rare']);

        $results = Card::filterByRarity('rare')->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn($card) => $card->rarity === 'rare'));
    }

    public function test_filter_by_colors_scope(): void
    {
        Card::factory()->create(['colors' => ['R']]);
        Card::factory()->create(['colors' => ['R', 'G']]);
        Card::factory()->create(['colors' => ['U']]);

        $results = Card::filterByColors(['R'])->get();

        $this->assertCount(2, $results);
    }

    public function test_filter_by_converted_mana_cost_scope(): void
    {
        Card::factory()->create(['converted_mana_cost' => 3]);
        Card::factory()->create(['converted_mana_cost' => 3]);
        Card::factory()->create(['converted_mana_cost' => 5]);

        $results = Card::filterByConvertedManaCost(3)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn($card) => $card->converted_mana_cost === 3));
    }

    public function test_main_image_url_attribute(): void
    {
        $card = Card::factory()->create([
            'image_uris' => [
                'normal' => 'https://example.com/normal.jpg',
                'large' => 'https://example.com/large.jpg'
            ]
        ]);

        $this->assertEquals('https://example.com/normal.jpg', $card->main_image_url);
    }

    public function test_main_image_url_fallback(): void
    {
        $card = Card::factory()->create(['image_uris' => null]);

        $this->assertEquals('/assets/images/card-placeholder.png', $card->main_image_url);
    }

    public function test_cardmarket_price_attribute(): void
    {
        $card = Card::factory()->create([
            'prices' => [
                'cardmarket' => '12.50'
            ]
        ]);

        $this->assertEquals('12.50', $card->cardmarket_price);
    }
}