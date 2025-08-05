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
            $table->string('name');
            $table->string('set_name');
            $table->string('set_code');
            $table->string('rarity');
            $table->string('type_line');
            $table->text('oracle_text')->nullable();
            $table->string('mana_cost')->nullable();
            $table->integer('cmc')->nullable();
            $table->json('colors')->nullable();
            $table->json('color_identity')->nullable();
            $table->string('cardmarket_id')->nullable()->unique();
            $table->string('scryfall_id')->nullable()->unique();
            $table->string('image_url')->nullable();
            $table->string('image_art_crop')->nullable();
            $table->string('image_normal')->nullable();
            $table->string('image_small')->nullable();
            $table->decimal('power', 5, 2)->nullable();
            $table->decimal('toughness', 5, 2)->nullable();
            $table->string('loyalty')->nullable();
            $table->json('keywords')->nullable();
            $table->string('layout')->default('normal');
            $table->boolean('reserved')->default(false);
            $table->boolean('foil')->default(false);
            $table->boolean('nonfoil')->default(true);
            $table->string('border_color')->default('black');
            $table->string('frame')->default('2015');
            $table->string('frame_effects')->nullable();
            $table->boolean('full_art')->default(false);
            $table->boolean('textless')->default(false);
            $table->boolean('oversized')->default(false);
            $table->boolean('promo')->default(false);
            $table->boolean('reprint')->default(false);
            $table->boolean('variation')->default(false);
            $table->string('variation_of')->nullable();
            $table->string('printed_name')->nullable();
            $table->string('printed_type_line')->nullable();
            $table->text('printed_text')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('name');
            $table->index('set_code');
            $table->index('rarity');
            $table->index('cardmarket_id');
            $table->index(['name', 'set_code']);
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
