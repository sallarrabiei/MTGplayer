<template>
  <div v-if="isLoading" class="text-center py-12">
    <div class="loading-spinner mx-auto mb-4"></div>
    <p class="text-gray-600">Loading card details...</p>
  </div>

  <div v-else-if="error" class="text-center py-12">
    <div class="text-red-600 mb-4">
      <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="text-lg font-medium">{{ error }}</p>
    </div>
    <router-link to="/" class="btn btn-primary">Back to Search</router-link>
  </div>

  <div v-else-if="card" class="max-w-4xl mx-auto">
    <!-- Back Button -->
    <div class="mb-6">
      <button @click="$router.go(-1)" class="btn btn-secondary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Card Image -->
      <div class="space-y-4">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
          <img
            :src="cardImage"
            :alt="card.name"
            class="w-full h-auto"
            @error="handleImageError"
          />
        </div>
        
        <!-- Price Information -->
        <div v-if="price" class="bg-white rounded-lg shadow-sm p-4">
          <h3 class="font-semibold text-gray-900 mb-3">Cardmarket Prices</h3>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div v-if="price.avg_price">
              <span class="text-gray-600">Average:</span>
              <span class="font-medium ml-2">€{{ price.avg_price }}</span>
            </div>
            <div v-if="price.low_price">
              <span class="text-gray-600">Low:</span>
              <span class="font-medium ml-2">€{{ price.low_price }}</span>
            </div>
            <div v-if="price.trend_price">
              <span class="text-gray-600">Trend:</span>
              <span class="font-medium ml-2">€{{ price.trend_price }}</span>
            </div>
            <div v-if="price.suggested_price">
              <span class="text-gray-600">Suggested:</span>
              <span class="font-medium ml-2">€{{ price.suggested_price }}</span>
            </div>
          </div>
          <p class="text-xs text-gray-500 mt-2">
            Last updated: {{ formatDate(price.last_updated) }}
          </p>
        </div>
      </div>

      <!-- Card Details -->
      <div class="space-y-6">
        <!-- Basic Info -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ card.name }}</h1>
          
          <!-- Mana Cost -->
          <div v-if="card.mana_cost" class="flex items-center mb-4">
            <span class="text-sm font-medium text-gray-700 mr-3">Mana Cost:</span>
            <ManaCost :cost="card.mana_cost" />
            <span class="text-sm text-gray-500 ml-3">(CMC: {{ card.converted_mana_cost }})</span>
          </div>
          
          <!-- Type Line -->
          <div class="mb-4">
            <span class="text-sm font-medium text-gray-700">Type:</span>
            <span class="ml-2 text-gray-900">{{ card.type_line }}</span>
          </div>
          
          <!-- Set Information -->
          <div class="mb-4">
            <span class="text-sm font-medium text-gray-700">Set:</span>
            <span class="ml-2 text-gray-900">{{ card.set_name }} ({{ card.set_code }})</span>
          </div>
          
          <!-- Rarity -->
          <div class="mb-4">
            <span class="text-sm font-medium text-gray-700">Rarity:</span>
            <span class="ml-2 capitalize" :class="getRarityClass(card.rarity)">
              {{ card.rarity }}
            </span>
          </div>
          
          <!-- Colors -->
          <div v-if="card.colors && card.colors.length > 0" class="mb-4">
            <span class="text-sm font-medium text-gray-700">Colors:</span>
            <div class="flex items-center ml-2 space-x-2">
              <span
                v-for="color in card.colors"
                :key="color"
                class="mana-symbol"
                :class="getManaColorClass(color)"
              >
                {{ color }}
              </span>
            </div>
          </div>
          
          <!-- Power/Toughness -->
          <div v-if="card.power && card.toughness" class="mb-4">
            <span class="text-sm font-medium text-gray-700">Power/Toughness:</span>
            <span class="ml-2 text-gray-900 font-mono text-lg">
              {{ card.power }}/{{ card.toughness }}
            </span>
          </div>
          
          <!-- Artist -->
          <div v-if="card.artist" class="mb-4">
            <span class="text-sm font-medium text-gray-700">Artist:</span>
            <span class="ml-2 text-gray-900">{{ card.artist }}</span>
          </div>
        </div>

        <!-- Oracle Text -->
        <div v-if="card.oracle_text" class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="font-semibold text-gray-900 mb-3">Oracle Text</h3>
          <div class="prose prose-sm max-w-none">
            <p class="whitespace-pre-line text-gray-700">{{ card.oracle_text }}</p>
          </div>
        </div>

        <!-- Flavor Text -->
        <div v-if="card.flavor_text" class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="font-semibold text-gray-900 mb-3">Flavor Text</h3>
          <p class="italic text-gray-600">{{ card.flavor_text }}</p>
        </div>

        <!-- Legalities -->
        <div v-if="card.legalities" class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="font-semibold text-gray-900 mb-3">Format Legalities</h3>
          <div class="grid grid-cols-2 gap-2 text-sm">
            <div
              v-for="(legality, format) in card.legalities"
              :key="format"
              class="flex justify-between"
            >
              <span class="capitalize text-gray-700">{{ format }}:</span>
              <span
                class="capitalize font-medium"
                :class="getLegalityClass(legality)"
              >
                {{ legality }}
              </span>
            </div>
          </div>
        </div>

        <!-- Additional Info -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="font-semibold text-gray-900 mb-3">Additional Information</h3>
          <div class="space-y-2 text-sm">
            <div v-if="card.collector_number">
              <span class="text-gray-600">Collector Number:</span>
              <span class="ml-2">{{ card.collector_number }}</span>
            </div>
            <div>
              <span class="text-gray-600">Layout:</span>
              <span class="ml-2 capitalize">{{ card.layout }}</span>
            </div>
            <div v-if="card.keywords && card.keywords.length > 0">
              <span class="text-gray-600">Keywords:</span>
              <span class="ml-2">{{ card.keywords.join(', ') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useCardsStore } from '../stores/cards';
import ManaCost from '../components/ManaCost.vue';

export default {
  name: 'CardDetail',
  components: {
    ManaCost
  },
  setup() {
    const route = useRoute();
    const cardsStore = useCardsStore();
    
    const price = ref(null);
    const imageError = ref(false);

    // Computed properties
    const card = computed(() => cardsStore.currentCard);
    const isLoading = computed(() => cardsStore.isLoading);
    const error = computed(() => cardsStore.error);

    const cardImage = computed(() => {
      if (!card.value || imageError.value) {
        return '/assets/images/card-placeholder.png';
      }
      
      if (card.value.image_uris) {
        return card.value.image_uris.large || 
               card.value.image_uris.normal || 
               card.value.image_uris.small || 
               '/assets/images/card-placeholder.png';
      }
      
      return '/assets/images/card-placeholder.png';
    });

    // Methods
    const handleImageError = () => {
      imageError.value = true;
    };

    const getRarityClass = (rarity) => {
      switch (rarity) {
        case 'common':
          return 'rarity-common';
        case 'uncommon':
          return 'rarity-uncommon';
        case 'rare':
          return 'rarity-rare';
        case 'mythic':
          return 'rarity-mythic';
        default:
          return 'text-gray-700';
      }
    };

    const getManaColorClass = (color) => {
      switch (color) {
        case 'W':
          return 'mana-white';
        case 'U':
          return 'mana-blue';
        case 'B':
          return 'mana-black';
        case 'R':
          return 'mana-red';
        case 'G':
          return 'mana-green';
        default:
          return 'mana-colorless';
      }
    };

    const getLegalityClass = (legality) => {
      switch (legality) {
        case 'legal':
          return 'text-green-600';
        case 'banned':
          return 'text-red-600';
        case 'restricted':
          return 'text-yellow-600';
        default:
          return 'text-gray-600';
      }
    };

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString();
    };

    const loadCardData = async () => {
      const cardId = route.params.id;
      
      try {
        await cardsStore.getCard(cardId);
        
        // Load price data if available
        if (card.value?.cardmarket_id) {
          const priceData = await cardsStore.getCardPrice(cardId);
          if (priceData) {
            price.value = priceData.price;
          }
        }
      } catch (err) {
        console.error('Failed to load card:', err);
      }
    };

    // Lifecycle
    onMounted(() => {
      loadCardData();
    });

    return {
      card,
      price,
      isLoading,
      error,
      cardImage,
      handleImageError,
      getRarityClass,
      getManaColorClass,
      getLegalityClass,
      formatDate
    };
  }
};
</script>