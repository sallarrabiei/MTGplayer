<template>
  <div
    class="bg-white rounded-lg shadow-md overflow-hidden cursor-pointer transition-all duration-200 hover:shadow-xl hover:scale-105"
    @click="$emit('click')"
  >
    <!-- Card Image -->
    <div class="aspect-w-3 aspect-h-4 bg-gray-200">
      <img
        v-if="imageUrl"
        :src="imageUrl"
        :alt="card.name"
        class="w-full h-full object-cover"
        @error="handleImageError"
      />
      <div v-else class="w-full h-full flex items-center justify-center">
        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>
    </div>
    
    <!-- Card Info -->
    <div class="p-4">
      <h3 class="text-lg font-semibold text-gray-900 truncate">{{ card.name }}</h3>
      
      <div class="mt-2 flex items-center justify-between">
        <span class="text-sm text-gray-600">{{ card.set_name || card.set_code }}</span>
        <span
          v-if="card.rarity"
          :class="[
            'text-xs font-medium px-2 py-1 rounded',
            rarityClass
          ]"
        >
          {{ card.rarity }}
        </span>
      </div>
      
      <!-- Mana Cost -->
      <div v-if="card.mana_cost" class="mt-2">
        <ManaSymbols :mana-cost="card.mana_cost" />
      </div>
      
      <!-- Type Line -->
      <p v-if="card.type_line" class="mt-2 text-sm text-gray-600 truncate">
        {{ card.type_line }}
      </p>
      
      <!-- Price -->
      <div class="mt-3 flex items-center justify-between">
        <div v-if="card.price">
          <span class="text-lg font-bold text-gray-900">€{{ card.price }}</span>
          <span class="text-xs text-gray-500 ml-1">EUR</span>
        </div>
        <div v-else class="text-sm text-gray-500">
          No price data
        </div>
        
        <!-- Favorite Button -->
        <button
          v-if="isAuthenticated"
          @click.stop="toggleFavorite"
          class="p-1 rounded-full hover:bg-gray-100 transition-colors"
        >
          <svg
            class="w-5 h-5"
            :class="isFavorited ? 'text-red-500' : 'text-gray-400'"
            :fill="isFavorited ? 'currentColor' : 'none'"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';
import ManaSymbols from './ManaSymbols.vue';

const props = defineProps({
  card: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['click']);

const authStore = useAuthStore();
const isFavorited = ref(false);
const imageError = ref(false);

const isAuthenticated = computed(() => authStore.isAuthenticated);

const imageUrl = computed(() => {
  if (imageError.value || !props.card.image_uris) return null;
  
  try {
    const uris = JSON.parse(props.card.image_uris);
    return uris.normal || uris.small || uris.large || null;
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
  
  return rarityClasses[props.card.rarity] || 'bg-gray-100 text-gray-800';
});

const handleImageError = () => {
  imageError.value = true;
};

const toggleFavorite = async () => {
  if (!isAuthenticated.value) return;
  
  try {
    if (isFavorited.value) {
      await axios.delete(`/api/favorites/${props.card.id}`);
      isFavorited.value = false;
    } else {
      await axios.post('/api/favorites', { card_id: props.card.id });
      isFavorited.value = true;
    }
  } catch (error) {
    console.error('Failed to toggle favorite:', error);
  }
};

// Check if card is favorited on mount
if (isAuthenticated.value) {
  axios.get(`/api/favorites/check/${props.card.id}`)
    .then(response => {
      isFavorited.value = response.data.is_favorited;
    })
    .catch(() => {
      // Ignore errors
    });
}
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