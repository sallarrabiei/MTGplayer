<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Search Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Search Magic Cards</h1>
      
      <!-- Search Bar -->
      <div class="mb-6">
        <div class="relative">
          <input
            v-model="searchQuery"
            @input="debouncedSearch"
            type="text"
            placeholder="Search by card name..."
            class="w-full px-4 py-3 pl-10 pr-4 text-gray-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500"
          />
          <div class="absolute inset-y-0 left-0 flex items-center pl-3">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>
      
      <!-- Filters -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Set</label>
          <select
            v-model="filters.set"
            @change="searchCards"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">All Sets</option>
            <option v-for="set in filterOptions.sets" :key="set.code" :value="set.code">
              {{ set.name }}
            </option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Rarity</label>
          <select
            v-model="filters.rarity"
            @change="searchCards"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">All Rarities</option>
            <option v-for="rarity in filterOptions.rarities" :key="rarity" :value="rarity">
              {{ rarity.charAt(0).toUpperCase() + rarity.slice(1) }}
            </option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Colors</label>
          <div class="flex space-x-2">
            <button
              v-for="color in filterOptions.colors"
              :key="color"
              @click="toggleColor(color)"
              :class="[
                'w-8 h-8 rounded-full border-2 transition-all',
                selectedColors.includes(color)
                  ? 'border-gray-900 scale-110'
                  : 'border-gray-300 hover:border-gray-400'
              ]"
              :style="{ backgroundColor: getColorHex(color) }"
              :title="getColorName(color)"
            />
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
          <select
            v-model="sortBy"
            @change="searchCards"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="name">Name</option>
            <option value="cmc">Mana Cost</option>
            <option value="price">Price</option>
            <option value="set_code">Set</option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"></div>
    </div>
    
    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-8">
      <p class="text-red-600">{{ error }}</p>
    </div>
    
    <!-- Results -->
    <div v-else>
      <!-- Results Count -->
      <div class="mb-4 text-gray-600">
        <span v-if="cards.length > 0">
          Showing {{ cards.length }} of {{ totalCards }} cards
        </span>
        <span v-else>No cards found</span>
      </div>
      
      <!-- Card Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <CardItem
          v-for="card in cards"
          :key="card.id"
          :card="card"
          @click="goToCard(card.id)"
        />
      </div>
      
      <!-- Pagination -->
      <div v-if="totalPages > 1" class="mt-8 flex justify-center">
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
          <button
            @click="changePage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="changePage(page)"
            :class="[
              'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
              page === currentPage
                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
            ]"
          >
            {{ page }}
          </button>
          
          <button
            @click="changePage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash-es';
import axios from 'axios';
import CardItem from '../components/CardItem.vue';

const router = useRouter();

// State
const searchQuery = ref('');
const filters = ref({
  set: '',
  rarity: '',
  type: '',
});
const selectedColors = ref([]);
const sortBy = ref('name');
const cards = ref([]);
const loading = ref(false);
const error = ref(null);
const currentPage = ref(1);
const totalCards = ref(0);
const totalPages = ref(0);
const filterOptions = ref({
  sets: [],
  rarities: [],
  colors: ['W', 'U', 'B', 'R', 'G'],
  types: [],
});

// Computed
const visiblePages = computed(() => {
  const pages = [];
  const start = Math.max(1, currentPage.value - 2);
  const end = Math.min(totalPages.value, currentPage.value + 2);
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  
  return pages;
});

// Methods
const getColorHex = (color) => {
  const colors = {
    W: '#FFFBD5',
    U: '#0E68AB',
    B: '#150B00',
    R: '#D3202A',
    G: '#00733E',
  };
  return colors[color] || '#999';
};

const getColorName = (color) => {
  const names = {
    W: 'White',
    U: 'Blue',
    B: 'Black',
    R: 'Red',
    G: 'Green',
  };
  return names[color] || color;
};

const toggleColor = (color) => {
  const index = selectedColors.value.indexOf(color);
  if (index > -1) {
    selectedColors.value.splice(index, 1);
  } else {
    selectedColors.value.push(color);
  }
  searchCards();
};

const searchCards = async (page = 1) => {
  loading.value = true;
  error.value = null;
  
  try {
    const params = {
      page,
      per_page: 20,
      sort: sortBy.value,
      order: 'asc',
    };
    
    if (searchQuery.value) {
      params.name = searchQuery.value;
    }
    
    if (filters.value.set) {
      params.set = filters.value.set;
    }
    
    if (filters.value.rarity) {
      params.rarity = filters.value.rarity;
    }
    
    if (selectedColors.value.length > 0) {
      params.colors = selectedColors.value.join(',');
    }
    
    const response = await axios.get('/api/cards', { params });
    
    cards.value = response.data.data;
    currentPage.value = response.data.current_page;
    totalCards.value = response.data.total;
    totalPages.value = response.data.last_page;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load cards';
  } finally {
    loading.value = false;
  }
};

const debouncedSearch = debounce(() => {
  searchCards();
}, 500);

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    searchCards(page);
  }
};

const goToCard = (id) => {
  router.push({ name: 'card-detail', params: { id } });
};

const loadFilters = async () => {
  try {
    const response = await axios.get('/api/cards/filters');
    filterOptions.value = response.data;
  } catch (err) {
    console.error('Failed to load filters:', err);
  }
};

// Lifecycle
onMounted(() => {
  loadFilters();
  searchCards();
});
</script>