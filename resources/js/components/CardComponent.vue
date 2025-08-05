<template>
  <div class="card-container cursor-pointer" @click="$emit('click')">
    <!-- Card Image -->
    <div class="relative">
      <img
        :src="cardImage"
        :alt="card.name"
        class="card-image"
        @error="handleImageError"
        loading="lazy"
      />
      
      <!-- Rarity Indicator -->
      <div
        class="absolute top-2 right-2 w-3 h-3 rounded-full"
        :class="rarityClass"
        :title="card.rarity"
      ></div>
      
      <!-- Price Badge -->
      <div
        v-if="price"
        class="absolute bottom-2 right-2 bg-black bg-opacity-75 text-white text-xs px-2 py-1 rounded"
      >
        €{{ price.avg_price }}
      </div>
    </div>

    <!-- Card Content -->
    <div class="card-content">
      <!-- Card Name -->
      <h3 class="card-title">{{ card.name }}</h3>
      
      <!-- Mana Cost -->
      <div v-if="card.mana_cost" class="flex items-center mb-2">
        <span class="text-xs text-gray-500 mr-2">Mana Cost:</span>
        <ManaCost :cost="card.mana_cost" />
      </div>
      
      <!-- Type Line -->
      <p class="card-subtitle">{{ card.type_line }}</p>
      
      <!-- Set Info -->
      <div class="flex justify-between items-center text-xs text-gray-500 mb-2">
        <span>{{ card.set_name }}</span>
        <span class="uppercase">{{ card.set_code }}</span>
      </div>
      
      <!-- Colors -->
      <div v-if="card.colors && card.colors.length > 0" class="flex items-center mb-2">
        <span class="text-xs text-gray-500 mr-2">Colors:</span>
        <div class="flex space-x-1">
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
      <div
        v-if="card.power && card.toughness"
        class="text-sm text-gray-600 font-medium"
      >
        {{ card.power }}/{{ card.toughness }}
      </div>
      
      <!-- Oracle Text Preview -->
      <p
        v-if="card.oracle_text"
        class="card-text mt-2 line-clamp-3"
      >
        {{ card.oracle_text }}
      </p>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useCardsStore } from '../stores/cards';
import ManaCost from './ManaCost.vue';

export default {
  name: 'CardComponent',
  components: {
    ManaCost
  },
  props: {
    card: {
      type: Object,
      required: true
    }
  },
  emits: ['click'],
  setup(props) {
    const cardsStore = useCardsStore();
    const price = ref(null);
    const imageError = ref(false);

    // Computed properties
    const cardImage = computed(() => {
      if (imageError.value) {
        return '/assets/images/card-placeholder.png';
      }
      
      if (props.card.image_uris) {
        return props.card.image_uris.normal || 
               props.card.image_uris.large || 
               props.card.image_uris.small || 
               '/assets/images/card-placeholder.png';
      }
      
      return '/assets/images/card-placeholder.png';
    });

    const rarityClass = computed(() => {
      switch (props.card.rarity) {
        case 'common':
          return 'bg-gray-700';
        case 'uncommon':
          return 'bg-gray-400';
        case 'rare':
          return 'bg-yellow-500';
        case 'mythic':
          return 'bg-orange-500';
        default:
          return 'bg-gray-500';
      }
    });

    // Methods
    const handleImageError = () => {
      imageError.value = true;
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

    const loadPrice = async () => {
      if (props.card.cardmarket_id) {
        try {
          const priceData = await cardsStore.getCardPrice(props.card.id);
          if (priceData) {
            price.value = priceData.price;
          }
        } catch (error) {
          console.error('Failed to load price:', error);
        }
      }
    };

    // Lifecycle
    onMounted(() => {
      loadPrice();
    });

    return {
      price,
      cardImage,
      rarityClass,
      handleImageError,
      getManaColorClass
    };
  }
};
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>