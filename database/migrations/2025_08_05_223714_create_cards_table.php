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
            $table->string('cardmarket_id')->nullable()->index();
            $table->string('name')->index();
            $table->string('set_code')->index();
            $table->string('set_name')->nullable();
            $table->string('collector_number')->nullable();
            $table->string('rarity')->nullable()->index();
            $table->string('type_line')->nullable();
            $table->text('oracle_text')->nullable();
            $table->string('mana_cost')->nullable();
            $table->integer('cmc')->nullable()->index();
            $table->string('colors')->nullable();
            $table->string('color_identity')->nullable();
            $table->string('power')->nullable();
            $table->string('toughness')->nullable();
            $table->string('loyalty')->nullable();
            $table->string('artist')->nullable();
            $table->string('image_uris')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('price_currency')->default('EUR');
            $table->timestamp('price_updated_at')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();
            
            $table->index(['name', 'set_code']);
            $table->index(['cardmarket_id', 'name', 'set_code']);
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
