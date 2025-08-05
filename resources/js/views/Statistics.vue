<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="text-center">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Collection Statistics</h1>
      <p class="text-lg text-gray-600">Insights into the Magic: The Gathering card database</p>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="loading-spinner mx-auto mb-4"></div>
      <p class="text-gray-600">Loading statistics...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <div class="text-red-600 mb-4">
        <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-lg font-medium">{{ error }}</p>
      </div>
      <button @click="loadStats" class="btn btn-primary">Try Again</button>
    </div>

    <!-- Statistics Content -->
    <div v-else-if="stats" class="space-y-6">
      <!-- Overview Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Total Cards -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Cards</p>
              <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(stats.total_cards) }}</p>
            </div>
          </div>
        </div>

        <!-- Total Sets -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Sets</p>
              <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(stats.total_sets) }}</p>
            </div>
          </div>
        </div>

        <!-- Average per Set -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Avg Cards/Set</p>
              <p class="text-2xl font-semibold text-gray-900">{{ averageCardsPerSet }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Rarity Distribution -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Rarity Distribution</h3>
          <div class="space-y-3">
            <div
              v-for="(count, rarity) in stats.rarities"
              :key="rarity"
              class="flex items-center justify-between"
            >
              <div class="flex items-center">
                <div
                  class="w-4 h-4 rounded-full mr-3"
                  :class="getRarityColorClass(rarity)"
                ></div>
                <span class="capitalize text-gray-700">{{ rarity }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-900 font-medium">{{ formatNumber(count) }}</span>
                <span class="text-sm text-gray-500">({{ getRarityPercentage(count) }}%)</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Color Distribution -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Color Distribution</h3>
          <div class="space-y-3">
            <div
              v-for="(count, color) in stats.colors"
              :key="color"
              class="flex items-center justify-between"
            >
              <div class="flex items-center">
                <div
                  class="w-4 h-4 rounded-full mr-3"
                  :class="getColorClass(color)"
                ></div>
                <span class="capitalize text-gray-700">{{ getColorName(color) }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-gray-900 font-medium">{{ formatNumber(count) }}</span>
                <span class="text-sm text-gray-500">({{ getColorPercentage(count) }}%)</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Additional Stats -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Database Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
          <div>
            <p class="text-gray-600">Most Common Rarity:</p>
            <p class="font-medium text-gray-900 capitalize">{{ mostCommonRarity }}</p>
          </div>
          <div>
            <p class="text-gray-600">Most Common Color:</p>
            <p class="font-medium text-gray-900">{{ mostCommonColor }}</p>
          </div>
          <div>
            <p class="text-gray-600">Last Updated:</p>
            <p class="font-medium text-gray-900">{{ lastUpdated }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useCardsStore } from '../stores/cards';

export default {
  name: 'Statistics',
  setup() {
    const cardsStore = useCardsStore();
    
    const stats = ref(null);
    const isLoading = ref(false);
    const error = ref(null);

    // Computed properties
    const averageCardsPerSet = computed(() => {
      if (!stats.value) return 0;
      return Math.round(stats.value.total_cards / stats.value.total_sets);
    });

    const mostCommonRarity = computed(() => {
      if (!stats.value?.rarities) return 'Unknown';
      
      let maxCount = 0;
      let mostCommon = '';
      
      Object.entries(stats.value.rarities).forEach(([rarity, count]) => {
        if (count > maxCount) {
          maxCount = count;
          mostCommon = rarity;
        }
      });
      
      return mostCommon;
    });

    const mostCommonColor = computed(() => {
      if (!stats.value?.colors) return 'Unknown';
      
      let maxCount = 0;
      let mostCommon = '';
      
      Object.entries(stats.value.colors).forEach(([color, count]) => {
        if (count > maxCount && color !== 'colorless') {
          maxCount = count;
          mostCommon = color;
        }
      });
      
      return getColorName(mostCommon);
    });

    const lastUpdated = computed(() => {
      return new Date().toLocaleDateString();
    });

    // Methods
    const loadStats = async () => {
      isLoading.value = true;
      error.value = null;

      try {
        const data = await cardsStore.getStats();
        stats.value = data;
      } catch (err) {
        error.value = 'Failed to load statistics';
        console.error('Load stats error:', err);
      } finally {
        isLoading.value = false;
      }
    };

    const formatNumber = (num) => {
      return num.toLocaleString();
    };

    const getRarityColorClass = (rarity) => {
      switch (rarity) {
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
    };

    const getColorClass = (color) => {
      switch (color) {
        case 'white':
          return 'bg-yellow-100 border border-yellow-300';
        case 'blue':
          return 'bg-blue-500';
        case 'black':
          return 'bg-gray-900';
        case 'red':
          return 'bg-red-500';
        case 'green':
          return 'bg-green-500';
        case 'colorless':
          return 'bg-gray-300';
        default:
          return 'bg-gray-400';
      }
    };

    const getColorName = (color) => {
      const names = {
        white: 'White',
        blue: 'Blue',
        black: 'Black',
        red: 'Red',
        green: 'Green',
        colorless: 'Colorless'
      };
      return names[color] || color;
    };

    const getRarityPercentage = (count) => {
      if (!stats.value) return 0;
      return Math.round((count / stats.value.total_cards) * 100);
    };

    const getColorPercentage = (count) => {
      if (!stats.value) return 0;
      return Math.round((count / stats.value.total_cards) * 100);
    };

    // Lifecycle
    onMounted(() => {
      loadStats();
    });

    return {
      stats,
      isLoading,
      error,
      averageCardsPerSet,
      mostCommonRarity,
      mostCommonColor,
      lastUpdated,
      loadStats,
      formatNumber,
      getRarityColorClass,
      getColorClass,
      getColorName,
      getRarityPercentage,
      getColorPercentage
    };
  }
};
</script>