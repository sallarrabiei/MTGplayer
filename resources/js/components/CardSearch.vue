<template>
  <div class="space-y-6">
    <!-- Search Header -->
    <div class="text-center">
      <h1 class="text-4xl font-bold text-gray-900 mb-4">
        Magic: The Gathering Search
      </h1>
      <p class="text-lg text-gray-600">
        Search and filter MTG cards with real-time pricing from Cardmarket
      </p>
    </div>

    <!-- Search Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Search Input -->
        <div class="md:col-span-2">
          <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
            Card Name
          </label>
          <input
            id="search"
            v-model="searchQuery"
            type="text"
            placeholder="Enter card name..."
            class="search-input"
            @input="debouncedSearch"
          />
        </div>

        <!-- Set Filter -->
        <div>
          <label for="set" class="block text-sm font-medium text-gray-700 mb-2">
            Set
          </label>
          <select
            id="set"
            v-model="filters.set"
            class="filter-select"
            @change="searchCards"
          >
            <option value="">All Sets</option>
            <option v-for="set in sets" :key="set.set_code" :value="set.set_code">
              {{ set.set_name }}
            </option>
          </select>
        </div>

        <!-- Rarity Filter -->
        <div>
          <label for="rarity" class="block text-sm font-medium text-gray-700 mb-2">
            Rarity
          </label>
          <select
            id="rarity"
            v-model="filters.rarity"
            class="filter-select"
            @change="searchCards"
          >
            <option value="">All Rarities</option>
            <option v-for="rarity in rarities" :key="rarity" :value="rarity">
              {{ rarity }}
            </option>
          </select>
        </div>
      </div>

      <!-- Additional Filters -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
        <div>
          <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
            Color
          </label>
          <select
            id="color"
            v-model="filters.color"
            class="filter-select"
            @change="searchCards"
          >
            <option value="">All Colors</option>
            <option v-for="color in colors" :key="color" :value="color">
              {{ color }}
            </option>
          </select>
        </div>

        <div>
          <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
            Type
          </label>
          <input
            id="type"
            v-model="filters.type"
            type="text"
            placeholder="e.g., Creature, Instant..."
            class="search-input"
            @input="debouncedSearch"
          />
        </div>

        <div>
          <label for="cmc" class="block text-sm font-medium text-gray-700 mb-2">
            CMC
          </label>
          <input
            id="cmc"
            v-model="filters.cmc"
            type="number"
            min="0"
            placeholder="0"
            class="search-input"
            @input="debouncedSearch"
          />
        </div>

        <div class="flex items-end">
          <button
            @click="clearFilters"
            class="btn-secondary w-full"
          >
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-flex items-center">
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-mtg-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-lg text-gray-600">Searching cards...</span>
      </div>
    </div>

    <!-- Results -->
    <div v-else-if="cards.length > 0">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">
          {{ pagination.total }} cards found
        </h2>
        <div class="flex items-center space-x-4">
          <span class="text-sm text-gray-600">
            Page {{ pagination.current_page }} of {{ pagination.last_page }}
          </span>
        </div>
      </div>

      <!-- Card Grid -->
      <div class="card-grid">
        <div
          v-for="card in cards"
          :key="card.id"
          class="card-item"
        >
          <router-link :to="{ name: 'card-detail', params: { id: card.id } }">
            <img
              :src="card.image_normal || card.image_small || '/images/card-placeholder.jpg'"
              :alt="card.name"
              class="card-image"
              @error="handleImageError"
            />
            <div class="p-4">
              <h3 class="font-semibold text-gray-900 mb-1">{{ card.name }}</h3>
              <p class="text-sm text-gray-600 mb-2">{{ card.set_name }}</p>
              <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">{{ card.rarity }}</span>
                <span v-if="card.prices && card.prices.length > 0" class="text-sm font-medium text-mtg-blue">
                  €{{ card.prices[0].price }}
                </span>
              </div>
            </div>
          </router-link>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex justify-center mt-8">
        <nav class="flex items-center space-x-2">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="btn-secondary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          
          <span class="px-4 py-2 text-sm text-gray-600">
            {{ pagination.current_page }} / {{ pagination.last_page }}
          </span>
          
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="btn-secondary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </nav>
      </div>
    </div>

    <!-- No Results -->
    <div v-else-if="!loading && searchQuery" class="text-center py-12">
      <div class="text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No cards found</h3>
        <p class="text-gray-600">Try adjusting your search criteria</p>
      </div>
    </div>

    <!-- Initial State -->
    <div v-else class="text-center py-12">
      <div class="text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Search for cards</h3>
        <p class="text-gray-600">Enter a card name to get started</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'CardSearch',
  setup() {
    const router = useRouter()
    
    // Reactive data
    const searchQuery = ref('')
    const loading = ref(false)
    const cards = ref([])
    const sets = ref([])
    const rarities = ref([])
    const colors = ref([])
    const pagination = reactive({
      current_page: 1,
      last_page: 1,
      per_page: 20,
      total: 0
    })

    const filters = reactive({
      set: '',
      rarity: '',
      color: '',
      type: '',
      cmc: ''
    })

    // Debounced search
    let searchTimeout = null
    const debouncedSearch = () => {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        searchCards()
      }, 300)
    }

    // Search cards
    const searchCards = async () => {
      if (!searchQuery.value && !Object.values(filters).some(v => v)) {
        cards.value = []
        return
      }

      loading.value = true
      
      try {
        const params = new URLSearchParams({
          page: pagination.current_page,
          per_page: pagination.per_page,
          with_prices: true
        })

        if (searchQuery.value) {
          params.append('name', searchQuery.value)
        }

        Object.entries(filters).forEach(([key, value]) => {
          if (value) {
            params.append(key, value)
          }
        })

        const response = await fetch(`/api/cards?${params}`)
        const data = await response.json()

        cards.value = data.data
        Object.assign(pagination, data.pagination)
      } catch (error) {
        console.error('Search failed:', error)
      } finally {
        loading.value = false
      }
    }

    // Change page
    const changePage = (page) => {
      if (page >= 1 && page <= pagination.last_page) {
        pagination.current_page = page
        searchCards()
      }
    }

    // Clear filters
    const clearFilters = () => {
      searchQuery.value = ''
      Object.keys(filters).forEach(key => {
        filters[key] = ''
      })
      searchCards()
    }

    // Load filter options
    const loadFilterOptions = async () => {
      try {
        const [setsResponse, raritiesResponse, colorsResponse] = await Promise.all([
          fetch('/api/cards/sets'),
          fetch('/api/cards/rarities'),
          fetch('/api/cards/colors')
        ])

        const setsData = await setsResponse.json()
        const raritiesData = await raritiesResponse.json()
        const colorsData = await colorsResponse.json()

        sets.value = setsData.data
        rarities.value = raritiesData.data
        colors.value = colorsData.data
      } catch (error) {
        console.error('Failed to load filter options:', error)
      }
    }

    // Handle image error
    const handleImageError = (event) => {
      event.target.src = '/images/card-placeholder.jpg'
    }

    // Initialize
    onMounted(() => {
      loadFilterOptions()
    })

    return {
      searchQuery,
      loading,
      cards,
      sets,
      rarities,
      colors,
      pagination,
      filters,
      debouncedSearch,
      searchCards,
      changePage,
      clearFilters,
      handleImageError
    }
  }
}
</script>