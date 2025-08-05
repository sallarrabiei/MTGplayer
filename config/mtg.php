<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MTG Card Data Source
    |--------------------------------------------------------------------------
    |
    | The URL from which to fetch MTG card data. This should be a JSON
    | endpoint that provides comprehensive card information.
    |
    */

    'cards_json_url' => env('MTG_CARDS_JSON_URL', 'https://mtgjson.com/api/v5/AllCards.json'),

    /*
    |--------------------------------------------------------------------------
    | Card Import Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the card import process.
    |
    */

    'import' => [
        'batch_size' => env('MTG_IMPORT_BATCH_SIZE', 500),
        'timeout' => env('MTG_IMPORT_TIMEOUT', 300),
        'memory_limit' => env('MTG_IMPORT_MEMORY_LIMIT', '2G'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Cache durations for various MTG-related data.
    |
    */

    'cache' => [
        'cards_search' => env('MTG_CACHE_SEARCH', 300), // 5 minutes
        'card_details' => env('MTG_CACHE_DETAILS', 600), // 10 minutes
        'card_sets' => env('MTG_CACHE_SETS', 3600), // 1 hour
        'card_stats' => env('MTG_CACHE_STATS', 1800), // 30 minutes
        'cardmarket_prices' => env('MTG_CACHE_PRICES', 1800), // 30 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Colors Configuration
    |--------------------------------------------------------------------------
    |
    | MTG color definitions and their representations.
    |
    */

    'colors' => [
        'W' => ['name' => 'White', 'hex' => '#FFFBD5'],
        'U' => ['name' => 'Blue', 'hex' => '#0E68AB'],
        'B' => ['name' => 'Black', 'hex' => '#150B00'],
        'R' => ['name' => 'Red', 'hex' => '#D3202A'],
        'G' => ['name' => 'Green', 'hex' => '#00733E'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rarity Configuration
    |--------------------------------------------------------------------------
    |
    | MTG rarity definitions and their colors.
    |
    */

    'rarities' => [
        'common' => ['name' => 'Common', 'color' => '#1a1a1a'],
        'uncommon' => ['name' => 'Uncommon', 'color' => '#c0c0c0'],
        'rare' => ['name' => 'Rare', 'color' => '#ffd700'],
        'mythic' => ['name' => 'Mythic Rare', 'color' => '#ff8c00'],
    ],

];