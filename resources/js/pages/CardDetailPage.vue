<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"></div>
    </div>
    
    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
      <p class="text-red-600">{{ error }}</p>
    </div>
    
    <!-- Card Details -->
    <div v-else-if="card" class="bg-white rounded-lg shadow-lg overflow-hidden">
      <div class="md:flex">
        <!-- Card Image -->
        <div class="md:w-1/3 bg-gray-100 p-8">
          <div class="aspect-w-3 aspect-h-4">
            <img
              v-if="imageUrl"
              :src="imageUrl"
              :alt="card.name"
              class="w-full h-full object-contain rounded-lg"
              @error="handleImageError"
            />
            <div v-else class="w-full h-full flex items-center justify-center bg-gray-200 rounded-lg">
              <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          </div>
        </div>
        
        <!-- Card Information -->
        <div class="md:w-2/3 p-8">
          <!-- Header -->
          <div class="flex items-start justify-between mb-6">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">{{ card.name }}</h1>
              <p class="text-lg text-gray-600 mt-1">{{ card.set_name || card.set_code }}</p>
            </div>
            
            <!-- Favorite Button -->
            <button
              v-if="isAuthenticated"
              @click="toggleFavorite"
              class="p-2 rounded-full hover:bg-gray-100 transition-colors"
            >
              <svg
                class="w-6 h-6"
                :class="isFavorited ? 'text-red-500' : 'text-gray-400'"
                :fill="isFavorited ? 'currentColor' : 'none'"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
              </svg>
            </button>
          </div>
          
          <!-- Mana Cost and Type -->
          <div class="mb-6">
            <div v-if="card.mana_cost" class="mb-3">
              <span class="text-sm font-medium text-gray-700 mr-2">Mana Cost:</span>
              <ManaSymbols :mana-cost="card.mana_cost" />
            </div>
            
            <div v-if="card.type_line" class="mb-2">
              <span class="text-sm font-medium text-gray-700">Type:</span>
              <span class="ml-2 text-gray-900">{{ card.type_line }}</span>
            </div>
            
            <div v-if="card.rarity" class="mb-2">
              <span class="text-sm font-medium text-gray-700">Rarity:</span>
              <span
                :class="[
                  'ml-2 text-sm font-medium px-2 py-1 rounded',
                  rarityClass
                ]"
              >
                {{ card.rarity }}
              </span>
            </div>
          </div>
          
          <!-- Oracle Text -->
          <div v-if="card.oracle_text" class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Card Text</h3>
            <div class="text-gray-700 whitespace-pre-line bg-gray-50 p-4 rounded-lg">
              {{ card.oracle_text }}
            </div>
          </div>
          
          <!-- Power/Toughness or Loyalty -->
          <div v-if="card.power || card.loyalty" class="mb-6">
            <div v-if="card.power" class="text-lg">
              <span class="font-medium text-gray-700">Power/Toughness:</span>
              <span class="ml-2 font-bold text-gray-900">{{ card.power }}/{{ card.toughness }}</span>
            </div>
            <div v-if="card.loyalty" class="text-lg">
              <span class="font-medium text-gray-700">Loyalty:</span>
              <span class="ml-2 font-bold text-gray-900">{{ card.loyalty }}</span>
            </div>
          </div>
          
          <!-- Price Information -->
          <div class="border-t pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Market Price</h3>
            <div v-if="card.price" class="flex items-baseline">
              <span class="text-3xl font-bold text-gray-900">€{{ card.price }}</span>
              <span class="ml-2 text-gray-500">EUR</span>
              <span v-if="card.price_updated_at" class="ml-4 text-sm text-gray-500">
                Updated {{ formatDate(card.price_updated_at) }}
              </span>
            </div>
            <div v-else class="text-gray-500">
              No price data available
            </div>
            
            <!-- Update Price Button -->
            <button
              v-if="card.cardmarket_id"
              @click="updatePrice"
              :disabled="updatingPrice"
              class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ updatingPrice ? 'Updating...' : 'Update Price' }}
            </button>
          </div>
          
          <!-- Additional Information -->
          <div class="mt-6 pt-6 border-t">
            <dl class="grid grid-cols-2 gap-4 text-sm">
              <div v-if="card.artist">
                <dt class="font-medium text-gray-700">Artist</dt>
                <dd class="text-gray-900">{{ card.artist }}</dd>
              </div>
              <div v-if="card.collector_number">
                <dt class="font-medium text-gray-700">Collector Number</dt>
                <dd class="text-gray-900">{{ card.collector_number }}</dd>
              </div>
              <div v-if="card.cardmarket_id">
                <dt class="font-medium text-gray-700">Cardmarket ID</dt>
                <dd class="text-gray-900">{{ card.cardmarket_id }}</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';
import ManaSymbols from '../components/ManaSymbols.vue';

const route = useRoute();
const authStore = useAuthStore();

const card = ref(null);
const loading = ref(true);
const error = ref(null);
const isFavorited = ref(false);
const imageError = ref(false);
const updatingPrice = ref(false);

const isAuthenticated = computed(() => authStore.isAuthenticated);

const imageUrl = computed(() => {
  if (imageError.value || !card.value?.image_uris) return null;
  
  try {
    const uris = JSON.parse(card.value.image_uris);
    return uris.large || uris.normal || uris.small || null;
  } catch {
    return null;
  }
});

const rarityClass = computed(() => {
  const rarityClasses = {
    common: 'bg-gray-100 text-gray-800',
    uncommon: 'bg-gray-200 text-gray-900',
    rare: 'bg-yellow-100 text-yellow-800',
    mythic: 'bg-orange-100 text-orange-800',
  };
  
  return rarityClasses[card.value?.rarity] || 'bg-gray-100 text-gray-800';
});

const handleImageError = () => {
  imageError.value = true;
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString();
};

const loadCard = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    const response = await axios.get(`/api/cards/${route.params.id}`);
    card.value = response.data;
    
    // Check if favorited
    if (isAuthenticated.value) {
      const favResponse = await axios.get(`/api/favorites/check/${route.params.id}`);
      isFavorited.value = favResponse.data.is_favorited;
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load card';
  } finally {
    loading.value = false;
  }
};

const toggleFavorite = async () => {
  if (!isAuthenticated.value) return;
  
  try {
    if (isFavorited.value) {
      await axios.delete(`/api/favorites/${card.value.id}`);
      isFavorited.value = false;
    } else {
      await axios.post('/api/favorites', { card_id: card.value.id });
      isFavorited.value = true;
    }
  } catch (error) {
    console.error('Failed to toggle favorite:', error);
  }
};

const updatePrice = async () => {
  updatingPrice.value = true;
  
  try {
    await axios.post('/api/cards/update-prices', {
      card_ids: [card.value.id]
    });
    
    // Reload card to get updated price
    await loadCard();
  } catch (err) {
    console.error('Failed to update price:', err);
  } finally {
    updatingPrice.value = false;
  }
};

onMounted(() => {
  loadCard();
});
</script>

<style scoped>
.aspect-w-3 {
  position: relative;
  padding-bottom: 133.33%;
}

.aspect-w-3 > * {
  position: absolute;
  height: 100%;
  width: 100%;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}
</style>