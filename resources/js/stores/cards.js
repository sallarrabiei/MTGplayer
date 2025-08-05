import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useCardsStore = defineStore('cards', () => {
  // State
  const cards = ref([]);
  const currentCard = ref(null);
  const sets = ref([]);
  const stats = ref(null);
  const searchFilters = ref({
    name: '',
    set: '',
    rarity: '',
    colors: [],
    cmc: null,
    type: ''
  });
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 20,
    total: 0
  });
  const isLoading = ref(false);
  const error = ref(null);

  // Actions
  async function searchCards(filters = null, page = 1) {
    isLoading.value = true;
    error.value = null;

    try {
      const params = {
        page,
        per_page: pagination.value.perPage,
        ...(filters || searchFilters.value)
      };

      // Remove empty values
      Object.keys(params).forEach(key => {
        if (params[key] === '' || params[key] === null || 
            (Array.isArray(params[key]) && params[key].length === 0)) {
          delete params[key];
        }
      });

      const response = await axios.get('/cards', { params });
      
      cards.value = response.data.data;
      pagination.value = response.data.pagination;
      
      if (filters) {
        searchFilters.value = { ...searchFilters.value, ...filters };
      }
    } catch (err) {
      error.value = err.response?.data?.error || 'Failed to search cards';
      console.error('Search error:', err);
    } finally {
      isLoading.value = false;
    }
  }

  async function getCard(id) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await axios.get(`/cards/${id}`);
      currentCard.value = response.data.data;
      return response.data.data;
    } catch (err) {
      error.value = err.response?.data?.error || 'Failed to fetch card';
      console.error('Get card error:', err);
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function getCardPrice(id) {
    try {
      const response = await axios.get(`/cards/${id}/price`);
      return response.data.data;
    } catch (err) {
      console.error('Get price error:', err);
      return null;
    }
  }

  async function getSets() {
    try {
      const response = await axios.get('/cards/sets');
      sets.value = response.data.data;
      return response.data.data;
    } catch (err) {
      console.error('Get sets error:', err);
      return [];
    }
  }

  async function getStats() {
    try {
      const response = await axios.get('/cards/stats');
      stats.value = response.data.data;
      return response.data.data;
    } catch (err) {
      console.error('Get stats error:', err);
      return null;
    }
  }

  function updateFilters(newFilters) {
    searchFilters.value = { ...searchFilters.value, ...newFilters };
  }

  function clearFilters() {
    searchFilters.value = {
      name: '',
      set: '',
      rarity: '',
      colors: [],
      cmc: null,
      type: ''
    };
  }

  function clearError() {
    error.value = null;
  }

  // Getters
  const hasCards = computed(() => cards.value.length > 0);
  const totalPages = computed(() => pagination.value.lastPage);
  const currentPage = computed(() => pagination.value.currentPage);
  const hasFilters = computed(() => {
    return searchFilters.value.name || 
           searchFilters.value.set || 
           searchFilters.value.rarity || 
           searchFilters.value.colors.length > 0 || 
           searchFilters.value.cmc !== null || 
           searchFilters.value.type;
  });

  return {
    // State
    cards,
    currentCard,
    sets,
    stats,
    searchFilters,
    pagination,
    isLoading,
    error,

    // Actions
    searchCards,
    getCard,
    getCardPrice,
    getSets,
    getStats,
    updateFilters,
    clearFilters,
    clearError,

    // Getters
    hasCards,
    totalPages,
    currentPage,
    hasFilters
  };
});