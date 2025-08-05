<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Card extends Model
{
    protected $fillable = [
        'name',
        'set_name',
        'set_code',
        'rarity',
        'type_line',
        'oracle_text',
        'mana_cost',
        'cmc',
        'colors',
        'color_identity',
        'cardmarket_id',
        'scryfall_id',
        'image_url',
        'image_art_crop',
        'image_normal',
        'image_small',
        'power',
        'toughness',
        'loyalty',
        'keywords',
        'layout',
        'reserved',
        'foil',
        'nonfoil',
        'border_color',
        'frame',
        'frame_effects',
        'full_art',
        'textless',
        'oversized',
        'promo',
        'reprint',
        'variation',
        'variation_of',
        'printed_name',
        'printed_type_line',
        'printed_text',
    ];

    protected $casts = [
        'colors' => 'array',
        'color_identity' => 'array',
        'keywords' => 'array',
        'reserved' => 'boolean',
        'foil' => 'boolean',
        'nonfoil' => 'boolean',
        'full_art' => 'boolean',
        'textless' => 'boolean',
        'oversized' => 'boolean',
        'promo' => 'boolean',
        'reprint' => 'boolean',
        'variation' => 'boolean',
        'power' => 'decimal:2',
        'toughness' => 'decimal:2',
    ];

    /**
     * Get the card prices for this card.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(CardPrice::class);
    }

    /**
     * Get the latest price for a specific type and condition.
     */
    public function getLatestPrice(string $priceType = 'market', string $condition = 'NM', bool $foil = false)
    {
        return $this->prices()
            ->where('price_type', $priceType)
            ->where('condition', $condition)
            ->where('foil', $foil)
            ->latest('price_updated_at')
            ->first();
    }

    /**
     * Get the display name with set information.
     */
    public function getDisplayName(): string
    {
        return "{$this->name} ({$this->set_code})";
    }

    /**
     * Get the primary image URL.
     */
    public function getImageUrl(): ?string
    {
        return $this->image_normal ?? $this->image_small ?? $this->image_url;
    }

    /**
     * Scope to filter by set.
     */
    public function scopeInSet($query, string $setCode)
    {
        return $query->where('set_code', $setCode);
    }

    /**
     * Scope to filter by rarity.
     */
    public function scopeOfRarity($query, string $rarity)
    {
        return $query->where('rarity', $rarity);
    }

    /**
     * Scope to filter by color.
     */
    public function scopeOfColor($query, string $color)
    {
        return $query->whereJsonContains('colors', $color);
    }

    /**
     * Scope to search by name.
     */
    public function scopeSearchByName($query, string $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }
}
