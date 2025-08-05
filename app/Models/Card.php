<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'cardmarket_id',
        'name',
        'set_code',
        'set_name',
        'collector_number',
        'rarity',
        'type_line',
        'oracle_text',
        'mana_cost',
        'cmc',
        'colors',
        'color_identity',
        'power',
        'toughness',
        'loyalty',
        'artist',
        'image_uris',
        'price',
        'price_currency',
        'price_updated_at',
        'raw_data'
    ];

    protected $casts = [
        'cmc' => 'integer',
        'price' => 'decimal:2',
        'price_updated_at' => 'datetime',
        'raw_data' => 'json'
    ];

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function getColorsArrayAttribute()
    {
        return $this->colors ? explode(',', $this->colors) : [];
    }

    public function getColorIdentityArrayAttribute()
    {
        return $this->color_identity ? explode(',', $this->color_identity) : [];
    }

    public function getImageUrisArrayAttribute()
    {
        return $this->image_uris ? json_decode($this->image_uris, true) : [];
    }
}
