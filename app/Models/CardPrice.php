<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardPrice extends Model
{
    protected $fillable = [
        'card_id',
        'cardmarket_id',
        'price_type',
        'price',
        'currency',
        'condition',
        'foil',
        'available_quantity',
        'price_updated_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'foil' => 'boolean',
        'price_updated_at' => 'datetime',
    ];

    /**
     * Get the card that owns this price.
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPrice(): string
    {
        return number_format($this->price, 2) . ' ' . $this->currency;
    }

    /**
     * Scope to filter by price type.
     */
    public function scopeOfType($query, string $priceType)
    {
        return $query->where('price_type', $priceType);
    }

    /**
     * Scope to filter by condition.
     */
    public function scopeOfCondition($query, string $condition)
    {
        return $query->where('condition', $condition);
    }

    /**
     * Scope to filter by foil status.
     */
    public function scopeFoil($query, bool $foil = true)
    {
        return $query->where('foil', $foil);
    }

    /**
     * Scope to get recent prices.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('price_updated_at', '>=', now()->subDays($days));
    }
}
