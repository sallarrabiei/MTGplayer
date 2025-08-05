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
        Schema::create('card_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->onDelete('cascade');
            $table->string('cardmarket_id');
            $table->string('price_type'); // 'low', 'avg', 'high', 'market'
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('condition')->default('NM'); // NM, LP, MP, HP, PO
            $table->boolean('foil')->default(false);
            $table->integer('available_quantity')->nullable();
            $table->timestamp('price_updated_at');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['card_id', 'price_type', 'condition', 'foil']);
            $table->index('cardmarket_id');
            $table->index('price_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_prices');
    }
};
