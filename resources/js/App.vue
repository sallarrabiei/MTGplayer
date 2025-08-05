<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <router-link to="/" class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">🃏</span>
              </div>
              <span class="text-xl font-bold text-gray-900">MTG Player</span>
            </router-link>
          </div>
          
          <div class="flex items-center space-x-4">
            <router-link
              to="/"
              class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors"
              :class="{ 'text-blue-600 bg-blue-50': $route.name === 'home' }"
            >
              Search Cards
            </router-link>
            
            <router-link
              to="/favorites"
              class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors"
              :class="{ 'text-blue-600 bg-blue-50': $route.name === 'favorites' }"
            >
              Favorites
            </router-link>
            
            <router-link
              to="/stats"
              class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors"
              :class="{ 'text-blue-600 bg-blue-50': $route.name === 'stats' }"
            >
              Statistics
            </router-link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center text-gray-500 text-sm">
          <p>&copy; {{ new Date().getFullYear() }} MTG Player. Built with Laravel, Vue.js, and Tailwind CSS.</p>
          <p class="mt-2">
            Data powered by 
            <a href="https://mtgjson.com" target="_blank" class="text-blue-600 hover:text-blue-800">MTGJSON</a>
            and 
            <a href="https://www.cardmarket.com" target="_blank" class="text-blue-600 hover:text-blue-800">Cardmarket</a>
          </p>
        </div>
      </div>
    </footer>

    <!-- Loading Overlay -->
    <div
      v-if="isLoading"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="loading-spinner"></div>
        <span class="text-gray-700">Loading...</span>
      </div>
    </div>
  </div>
</template>

<script>
import { useAppStore } from './stores/app';
import { computed } from 'vue';

export default {
  name: 'App',
  setup() {
    const appStore = useAppStore();
    
    const isLoading = computed(() => appStore.isLoading);
    
    return {
      isLoading
    };
  }
};
</script>