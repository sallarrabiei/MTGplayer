<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="text-center">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Magic: The Gathering Search Engine</h1>
      <p class="text-lg text-gray-600">Search and discover Magic cards with real-time pricing</p>
    </div>

    <!-- Search Bar -->
    <div class="search-container max-w-2xl mx-auto">
      <div class="relative">
        <svg class="search-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="searchQuery"
          @input="debouncedSearch"
          type="text"
          placeholder="Search for cards by name..."
          class="search-input"
        />
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-grid">
      <!-- Set Filter -->
      <div class="filter-container">
        <label class="filter-label">Set</label>
        <select v-model="filters.set" @change="handleFilterChange" class="filter-select">
          <option value="">All Sets</option>
          <option v-for="set in sets" :key="set.set_code" :value="set.set_code">
            {{ set.set_name }} ({{ set.set_code }})
          </option>
        </select>
      </div>

      <!-- Rarity Filter -->
      <div class="filter-container">
        <label class="filter-label">Rarity</label>
        <select v-model="filters.rarity" @change="handleFilterChange" class="filter-select">
          <option value="">All Rarities</option>
          <option value="common">Common</option>
          <option value="uncommon">Uncommon</option>
          <option value="rare">Rare</option>
          <option value="mythic">Mythic Rare</option>
        </select>
      </div>

      <!-- Colors Filter -->
      <div class="filter-container">
        <label class="filter-label">Colors</label>
        <div class="flex flex-wrap gap-2">
          <label v-for="color in mtgColors" :key="color.code" class="flex items-center">
            <input
              type="checkbox"
              :value="color.code"
              v-model="filters.colors"
              @change="handleFilterChange"
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span class="ml-2 text-sm" :style="{ color: color.hex }">{{ color.name }}</span>
          </label>
        </div>
      </div>

      <!-- CMC Filter -->
      <div class="filter-container">
        <label class="filter-label">Converted Mana Cost</label>
        <input
          v-model.number="filters.cmc"
          @input="handleFilterChange"
          type="number"
          min="0"
          max="20"
          placeholder="Any"
          class="filter-input"
        />
      </div>
    </div>

    <!-- Clear Filters Button -->
    <div v-if="hasFilters" class="text-center">
      <button @click="clearAllFilters" class="btn btn-secondary">
        Clear All Filters
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="loading-spinner mx-auto mb-4"></div>
      <p class="text-gray-600">Searching cards...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <div class="text-red-600 mb-4">
        <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-lg font-medium">{{ error }}</p>
      </div>
      <button @click="retry" class="btn btn-primary">Try Again</button>
    </div>

    <!-- Results -->
    <div v-else>
      <!-- Results Info -->
      <div v-if="hasCards" class="flex justify-between items-center mb-6">
        <p class="text-gray-600">
          Showing {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }} cards
        </p>
        <div class="text-sm text-gray-500">
          Page {{ pagination.current_page }} of {{ pagination.last_page }}
        </div>
      </div>

      <!-- Cards Grid -->
      <div v-if="hasCards" class="cards-grid">
        <CardComponent
          v-for="card in cards"
          :key="card.id"
          :card="card"
          @click="viewCard(card.id)"
        />
      </div>

      <!-- No Results -->
      <div v-else-if="!isLoading" class="text-center py-12">
        <div class="text-gray-400 mb-4">
          <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.462-.881-6.065-2.328C5.934 12.672 5.934 12.328 6.065 11.672A7.962 7.962 0 0112 9c2.34 0 4.462.881 6.065 2.328.131.656.131 1.016 0 1.672z" />
          </svg>
          <p class="text-lg font-medium">No cards found</p>
          <p class="text-sm">Try adjusting your search criteria</p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="hasCards && pagination.last_page > 1" class="flex justify-center mt-8">
        <nav class="flex items-center space-x-2">
          <button
            @click="goToPage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="btn btn-secondary"
            :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === 1 }"
          >
            Previous
          </button>
          
          <span class="px-4 py-2 text-sm text-gray-700">
            Page {{ pagination.current_page }} of {{ pagination.last_page }}
          </span>
          
          <button
            @click="goToPage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="btn btn-secondary"
            :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === pagination.last_page }"
          >
            Next
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useCardsStore } from '../stores/cards';
import CardComponent from '../components/CardComponent.vue';

export default {
  name: 'Home',
  components: {
    CardComponent
  },
  setup() {
    const router = useRouter();
    const cardsStore = useCardsStore();
    
    // Reactive data
    const searchQuery = ref('');
    const filters = ref({
      set: '',
      rarity: '',
      colors: [],
      cmc: null,
      type: ''
    });
    
    const mtgColors = [
      { code: 'W', name: 'White', hex: '#FFFBD5' },
      { code: 'U', name: 'Blue', hex: '#0E68AB' },
      { code: 'B', name: 'Black', hex: '#150B00' },
      { code: 'R', name: 'Red', hex: '#D3202A' },
      { code: 'G', name: 'Green', hex: '#00733E' }
    ];

    // Computed properties
    const cards = computed(() => cardsStore.cards);
    const sets = computed(() => cardsStore.sets);
    const pagination = computed(() => cardsStore.pagination);
    const isLoading = computed(() => cardsStore.isLoading);
    const error = computed(() => cardsStore.error);
    const hasCards = computed(() => cardsStore.hasCards);
    const hasFilters = computed(() => cardsStore.hasFilters || searchQuery.value);

    // Debounced search
    let searchTimeout;
    const debouncedSearch = () => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        performSearch();
      }, 500);
    };

    // Methods
    const performSearch = () => {
      const searchFilters = {
        ...filters.value,
        name: searchQuery.value
      };
      cardsStore.searchCards(searchFilters, 1);
    };

    const handleFilterChange = () => {
      performSearch();
    };

    const clearAllFilters = () => {
      searchQuery.value = '';
      filters.value = {
        set: '',
        rarity: '',
        colors: [],
        cmc: null,
        type: ''
      };
      cardsStore.clearFilters();
      performSearch();
    };

    const goToPage = (page) => {
      if (page >= 1 && page <= pagination.value.last_page) {
        const searchFilters = {
          ...filters.value,
          name: searchQuery.value
        };
        cardsStore.searchCards(searchFilters, page);
      }
    };

    const viewCard = (cardId) => {
      router.push(`/card/${cardId}`);
    };

    const retry = () => {
      cardsStore.clearError();
      performSearch();
    };

    // Lifecycle
    onMounted(async () => {
      await cardsStore.getSets();
      performSearch();
    });

    return {
      searchQuery,
      filters,
      mtgColors,
      cards,
      sets,
      pagination,
      isLoading,
      error,
      hasCards,
      hasFilters,
      debouncedSearch,
      handleFilterChange,
      clearAllFilters,
      goToPage,
      viewCard,
      retry
    };
  }
};
</script>