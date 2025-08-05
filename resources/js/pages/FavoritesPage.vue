<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
      <h1 class="text-3xl font-bold text-gray-900">My Favorite Cards</h1>
      <p class="mt-2 text-gray-600">Keep track of your favorite Magic cards</p>
    </div>
    
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"></div>
    </div>
    
    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-8">
      <p class="text-red-600">{{ error }}</p>
    </div>
    
    <!-- Empty State -->
    <div v-else-if="favorites.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
      </svg>
      <h3 class="mt-2 text-lg font-medium text-gray-900">No favorites yet</h3>
      <p class="mt-1 text-gray-500">Start adding cards to your favorites to see them here.</p>
      <router-link
        to="/"
        class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
      >
        Browse Cards
      </router-link>
    </div>
    
    <!-- Favorites Grid -->
    <div v-else>
      <div class="mb-4 text-gray-600">
        <span>{{ totalFavorites }} favorite{{ totalFavorites === 1 ? '' : 's' }}</span>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="favorite in favorites"
          :key="favorite.id"
          class="relative"
        >
          <CardItem
            :card="favorite.card"
            @click="goToCard(favorite.card.id)"
          />
          
          <!-- Remove Button -->
          <button
            @click="removeFavorite(favorite)"
            class="absolute top-2 right-2 p-2 bg-white rounded-full shadow-md hover:bg-gray-100 transition-colors"
            title="Remove from favorites"
          >
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
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
import { useAuthStore } from '../stores/auth';
import axios from 'axios';
import CardItem from '../components/CardItem.vue';

const router = useRouter();
const authStore = useAuthStore();

// Redirect if not authenticated
if (!authStore.isAuthenticated) {
  router.push('/');
}

const favorites = ref([]);
const loading = ref(true);
const error = ref(null);
const currentPage = ref(1);
const totalFavorites = ref(0);
const totalPages = ref(0);

const visiblePages = computed(() => {
  const pages = [];
  const start = Math.max(1, currentPage.value - 2);
  const end = Math.min(totalPages.value, currentPage.value + 2);
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  
  return pages;
});

const loadFavorites = async (page = 1) => {
  loading.value = true;
  error.value = null;
  
  try {
    const response = await axios.get('/api/favorites', {
      params: { page }
    });
    
    favorites.value = response.data.data;
    currentPage.value = response.data.current_page;
    totalFavorites.value = response.data.total;
    totalPages.value = response.data.last_page;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load favorites';
  } finally {
    loading.value = false;
  }
};

const removeFavorite = async (favorite) => {
  if (!confirm('Remove this card from favorites?')) return;
  
  try {
    await axios.delete(`/api/favorites/${favorite.card_id}`);
    
    // Remove from list
    favorites.value = favorites.value.filter(f => f.id !== favorite.id);
    totalFavorites.value--;
    
    // Reload if page is now empty
    if (favorites.value.length === 0 && currentPage.value > 1) {
      changePage(currentPage.value - 1);
    }
  } catch (err) {
    console.error('Failed to remove favorite:', err);
    alert('Failed to remove favorite');
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    loadFavorites(page);
  }
};

const goToCard = (id) => {
  router.push({ name: 'card-detail', params: { id } });
};

onMounted(() => {
  loadFavorites();
});
</script>