<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="text-center">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Favorite Cards</h1>
      <p class="text-lg text-gray-600">Your collection of favorite Magic cards</p>
    </div>

    <!-- Authentication Notice -->
    <div v-if="!isAuthenticated" class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
      <div class="text-blue-600 mb-4">
        <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        <p class="text-lg font-medium">Authentication Required</p>
        <p class="text-sm">Please log in to view and manage your favorite cards.</p>
      </div>
      <button class="btn btn-primary">Sign In</button>
    </div>

    <!-- Loading State -->
    <div v-else-if="isLoading" class="text-center py-12">
      <div class="loading-spinner mx-auto mb-4"></div>
      <p class="text-gray-600">Loading your favorites...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <div class="text-red-600 mb-4">
        <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-lg font-medium">{{ error }}</p>
      </div>
      <button @click="loadFavorites" class="btn btn-primary">Try Again</button>
    </div>

    <!-- Favorites Content -->
    <div v-else>
      <!-- Empty State -->
      <div v-if="favorites.length === 0" class="text-center py-12">
        <div class="text-gray-400 mb-4">
          <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
          </svg>
          <p class="text-lg font-medium">No favorites yet</p>
          <p class="text-sm">Start exploring cards and add them to your favorites!</p>
        </div>
        <router-link to="/" class="btn btn-primary">Browse Cards</router-link>
      </div>

      <!-- Favorites Grid -->
      <div v-else>
        <!-- Stats -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
          <div class="flex justify-between items-center">
            <p class="text-gray-600">
              {{ favorites.length }} favorite card{{ favorites.length !== 1 ? 's' : '' }}
            </p>
            <button @click="clearAllFavorites" class="btn btn-danger text-sm">
              Clear All
            </button>
          </div>
        </div>

        <!-- Cards Grid -->
        <div class="cards-grid">
          <div
            v-for="favorite in favorites"
            :key="favorite.id"
            class="relative"
          >
            <CardComponent
              :card="favorite.card"
              @click="viewCard(favorite.card.id)"
            />
            
            <!-- Remove Button -->
            <button
              @click="removeFavorite(favorite.card)"
              class="absolute top-2 left-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 shadow-lg transition-colors"
              title="Remove from favorites"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="flex justify-center mt-8">
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
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAppStore } from '../stores/app';
import CardComponent from '../components/CardComponent.vue';
import axios from 'axios';

export default {
  name: 'Favorites',
  components: {
    CardComponent
  },
  setup() {
    const router = useRouter();
    const appStore = useAppStore();
    
    const favorites = ref([]);
    const pagination = ref(null);
    const isLoading = ref(false);
    const error = ref(null);

    // Computed properties
    const isAuthenticated = computed(() => appStore.isAuthenticated);

    // Methods
    const loadFavorites = async (page = 1) => {
      if (!isAuthenticated.value) return;

      isLoading.value = true;
      error.value = null;

      try {
        const response = await axios.get('/favorites', {
          params: { page }
        });
        
        favorites.value = response.data.data;
        pagination.value = response.data.pagination;
      } catch (err) {
        error.value = err.response?.data?.error || 'Failed to load favorites';
        console.error('Load favorites error:', err);
      } finally {
        isLoading.value = false;
      }
    };

    const removeFavorite = async (card) => {
      if (!confirm(`Remove "${card.name}" from favorites?`)) return;

      try {
        await axios.delete(`/favorites/${card.id}`);
        
        // Remove from local list
        favorites.value = favorites.value.filter(fav => fav.card.id !== card.id);
        
        // Show success message (you could use a toast notification here)
        console.log('Card removed from favorites');
      } catch (err) {
        console.error('Remove favorite error:', err);
        alert('Failed to remove card from favorites');
      }
    };

    const clearAllFavorites = async () => {
      if (!confirm('Remove all cards from favorites? This action cannot be undone.')) return;

      try {
        // Remove each favorite (in a real app, you might want a bulk delete endpoint)
        const promises = favorites.value.map(favorite => 
          axios.delete(`/favorites/${favorite.card.id}`)
        );
        
        await Promise.all(promises);
        favorites.value = [];
        
        console.log('All favorites cleared');
      } catch (err) {
        console.error('Clear favorites error:', err);
        alert('Failed to clear all favorites');
      }
    };

    const goToPage = (page) => {
      if (page >= 1 && page <= pagination.value.last_page) {
        loadFavorites(page);
      }
    };

    const viewCard = (cardId) => {
      router.push(`/card/${cardId}`);
    };

    // Lifecycle
    onMounted(() => {
      if (isAuthenticated.value) {
        loadFavorites();
      }
    });

    return {
      favorites,
      pagination,
      isLoading,
      error,
      isAuthenticated,
      loadFavorites,
      removeFavorite,
      clearAllFavorites,
      goToPage,
      viewCard
    };
  }
};
</script>