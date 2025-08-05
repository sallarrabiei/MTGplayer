<?php

namespace Tests\Feature;

use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CardApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create some test cards
        Card::factory()->count(10)->create();
    }

    public function test_can_get_cards_list(): void
    {
        $response = $this->getJson('/api/cards');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'mana_cost',
                            'type_line',
                            'set_code',
                            'rarity'
                        ]
                    ],
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total'
                    ]
                ]);
    }

    public function test_can_search_cards_by_name(): void
    {
        $card = Card::factory()->create(['name' => 'Lightning Bolt']);

        $response = $this->getJson('/api/cards?name=Lightning');

        $response->assertStatus(200);
        $this->assertTrue(
            collect($response->json('data'))->contains('name', 'Lightning Bolt')
        );
    }

    public function test_can_filter_cards_by_set(): void
    {
        $card = Card::factory()->create(['set_code' => 'TEST']);

        $response = $this->getJson('/api/cards?set=TEST');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals('TEST', $data[0]['set_code']);
    }

    public function test_can_filter_cards_by_rarity(): void
    {
        $card = Card::factory()->create(['rarity' => 'mythic']);

        $response = $this->getJson('/api/cards?rarity=mythic');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals('mythic', $data[0]['rarity']);
    }

    public function test_can_get_single_card(): void
    {
        $card = Card::factory()->create();

        $response = $this->getJson("/api/cards/{$card->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $card->id,
                        'name' => $card->name
                    ]
                ]);
    }

    public function test_can_get_card_sets(): void
    {
        $response = $this->getJson('/api/cards/sets');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'set_code',
                            'set_name'
                        ]
                    ]
                ]);
    }

    public function test_can_get_card_stats(): void
    {
        $response = $this->getJson('/api/cards/stats');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_cards',
                        'total_sets',
                        'rarities',
                        'colors'
                    ]
                ]);
    }

    public function test_validates_search_parameters(): void
    {
        $response = $this->getJson('/api/cards?rarity=invalid');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['rarity']);
    }

    public function test_returns_404_for_nonexistent_card(): void
    {
        $response = $this->getJson('/api/cards/99999');

        $response->assertStatus(404);
    }
}