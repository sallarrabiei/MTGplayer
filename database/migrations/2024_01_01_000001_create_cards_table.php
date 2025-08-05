<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('mana_cost')->nullable();
            $table->integer('converted_mana_cost')->nullable()->index();
            $table->string('type_line')->nullable()->index();
            $table->text('oracle_text')->nullable();
            $table->string('power')->nullable();
            $table->string('toughness')->nullable();
            $table->json('colors')->nullable();
            $table->json('color_identity')->nullable();
            $table->json('keywords')->nullable();
            $table->string('set_code', 10)->index();
            $table->string('set_name')->nullable();
            $table->string('rarity')->nullable()->index();
            $table->string('collector_number')->nullable();
            $table->string('artist')->nullable();
            $table->text('flavor_text')->nullable();
            $table->json('image_uris')->nullable();
            $table->string('cardmarket_id')->nullable()->index();
            $table->json('multiverse_ids')->nullable();
            $table->string('mtgo_id')->nullable();
            $table->string('arena_id')->nullable();
            $table->string('tcgplayer_id')->nullable();
            $table->json('card_faces')->nullable();
            $table->string('layout')->nullable();
            $table->json('legalities')->nullable();
            $table->boolean('reserved')->default(false);
            $table->boolean('foil')->default(false);
            $table->boolean('nonfoil')->default(true);
            $table->boolean('oversized')->default(false);
            $table->boolean('promo')->default(false);
            $table->boolean('reprint')->default(false);
            $table->boolean('variation')->default(false);
            $table->string('frame')->nullable();
            $table->boolean('full_art')->default(false);
            $table->boolean('textless')->default(false);
            $table->boolean('booster')->default(true);
            $table->boolean('story_spotlight')->default(false);
            $table->json('prices')->nullable();
            $table->json('related_uris')->nullable();
            $table->json('purchase_uris')->nullable();
            $table->timestamps();

            // Indexes for common search patterns
            $table->index(['name', 'set_code']);
            $table->index(['rarity', 'set_code']);
            $table->index(['converted_mana_cost', 'rarity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};