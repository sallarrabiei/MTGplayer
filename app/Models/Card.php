<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'mana_cost',
        'converted_mana_cost',
        'type_line',
        'oracle_text',
        'power',
        'toughness',
        'colors',
        'color_identity',
        'keywords',
        'set_code',
        'set_name',
        'rarity',
        'collector_number',
        'artist',
        'flavor_text',
        'image_uris',
        'cardmarket_id',
        'multiverse_ids',
        'mtgo_id',
        'arena_id',
        'tcgplayer_id',
        'card_faces',
        'layout',
        'legalities',
        'reserved',
        'foil',
        'nonfoil',
        'oversized',
        'promo',
        'reprint',
        'variation',
        'frame',
        'full_art',
        'textless',
        'booster',
        'story_spotlight',
        'prices',
        'related_uris',
        'purchase_uris',
    ];

    protected $casts = [
        'colors' => 'array',
        'color_identity' => 'array',
        'keywords' => 'array',
        'image_uris' => 'array',
        'multiverse_ids' => 'array',
        'card_faces' => 'array',
        'legalities' => 'array',
        'prices' => 'array',
        'related_uris' => 'array',
        'purchase_uris' => 'array',
        'reserved' => 'boolean',
        'foil' => 'boolean',
        'nonfoil' => 'boolean',
        'oversized' => 'boolean',
        'promo' => 'boolean',
        'reprint' => 'boolean',
        'variation' => 'boolean',
        'full_art' => 'boolean',
        'textless' => 'boolean',
        'booster' => 'boolean',
        'story_spotlight' => 'boolean',
        'converted_mana_cost' => 'integer',
    ];

    /**
     * Get the favorites for this card.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Scope a query to search cards by name.
     */
    public function scopeSearchByName($query, $name)
    {
        return $query->where('name', 'LIKE', "%{$name}%");
    }

    /**
     * Scope a query to filter cards by set.
     */
    public function scopeFilterBySet($query, $setCode)
    {
        return $query->where('set_code', $setCode);
    }

    /**
     * Scope a query to filter cards by rarity.
     */
    public function scopeFilterByRarity($query, $rarity)
    {
        return $query->where('rarity', $rarity);
    }

    /**
     * Scope a query to filter cards by colors.
     */
    public function scopeFilterByColors($query, $colors)
    {
        if (is_string($colors)) {
            $colors = [$colors];
        }
        
        return $query->whereJsonContains('colors', $colors);
    }

    /**
     * Scope a query to filter cards by converted mana cost.
     */
    public function scopeFilterByConvertedManaCost($query, $cmc)
    {
        return $query->where('converted_mana_cost', $cmc);
    }

    /**
     * Scope a query to filter cards by type.
     */
    public function scopeFilterByType($query, $type)
    {
        return $query->where('type_line', 'LIKE', "%{$type}%");
    }

    /**
     * Get the card's main image URL.
     */
    public function getMainImageUrlAttribute()
    {
        if (isset($this->image_uris['normal'])) {
            return $this->image_uris['normal'];
        }
        
        if (isset($this->image_uris['large'])) {
            return $this->image_uris['large'];
        }
        
        if (isset($this->image_uris['small'])) {
            return $this->image_uris['small'];
        }
        
        return '/assets/images/card-placeholder.png';
    }

    /**
     * Get the card's price from Cardmarket if available.
     */
    public function getCardmarketPriceAttribute()
    {
        return $this->prices['cardmarket'] ?? null;
    }
}