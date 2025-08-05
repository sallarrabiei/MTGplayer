<template>
  <div v-if="card" class="space-y-6">
    <!-- Back Button -->
    <div>
      <router-link 
        to="/" 
        class="inline-flex items-center text-mtg-blue hover:text-blue-600"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Search
      </router-link>
    </div>

    <!-- Card Details -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-6">
        <!-- Card Image -->
        <div class="space-y-4">
          <img
            :src="card.image_normal || card.image_small || '/images/card-placeholder.jpg'"
            :alt="card.name"
            class="w-full rounded-lg shadow-md"
            @error="handleImageError"
          />
          
          <!-- Additional Images -->
          <div v-if="card.image_art_crop" class="space-y-2">
            <h4 class="text-sm font-medium text-gray-700">Art Crop</h4>
            <img
              :src="card.image_art_crop"
              :alt="`${card.name} art`"
              class="w-full rounded-lg shadow-sm"
              @error="handleImageError"
            />
          </div>
        </div>

        <!-- Card Information -->
        <div class="space-y-6">
          <!-- Basic Info -->
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ card.name }}</h1>
            <p class="text-lg text-gray-600 mb-4">{{ card.set_name }} ({{ card.set_code }})</p>
            
            <div class="flex items-center space-x-4 text-sm text-gray-500">
              <span class="bg-gray-100 px-2 py-1 rounded">{{ card.rarity }}</span>
              <span v-if="card.mana_cost" class="font-mono">{{ card.mana_cost }}</span>
              <span v-if="card.cmc !== null">CMC: {{ card.cmc }}</span>
            </div>
          </div>

          <!-- Type Line -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Type</h3>
            <p class="text-gray-700">{{ card.type_line }}</p>
          </div>

          <!-- Oracle Text -->
          <div v-if="card.oracle_text">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Oracle Text</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
              <p class="text-gray-700 whitespace-pre-line">{{ card.oracle_text }}</p>
            </div>
          </div>

          <!-- Power/Toughness -->
          <div v-if="card.power || card.toughness">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Power/Toughness</h3>
            <p class="text-gray-700">{{ card.power }}/{{ card.toughness }}</p>
          </div>

          <!-- Colors -->
          <div v-if="card.colors && card.colors.length > 0">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Colors</h3>
            <div class="flex space-x-2">
              <span
                v-for="color in card.colors"
                :key="color"
                class="px-3 py-1 rounded-full text-sm font-medium"
                :class="getColorClass(color)"
              >
                {{ color }}
              </span>
            </div>
          </div>

          <!-- Keywords -->
          <div v-if="card.keywords && card.keywords.length > 0">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Keywords</h3>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="keyword in card.keywords"
                :key="keyword"
                class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm"
              >
                {{ keyword }}
              </span>
            </div>
          </div>

          <!-- Additional Info -->
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div v-if="card.layout">
              <span class="font-medium text-gray-700">Layout:</span>
              <span class="ml-2 text-gray-600">{{ card.layout }}</span>
            </div>
            <div v-if="card.frame">
              <span class="font-medium text-gray-700">Frame:</span>
              <span class="ml-2 text-gray-600">{{ card.frame }}</span>
            </div>
            <div v-if="card.border_color">
              <span class="font-medium text-gray-700">Border:</span>
              <span class="ml-2 text-gray-600">{{ card.border_color }}</span>
            </div>
            <div v-if="card.loyalty">
              <span class="font-medium text-gray-700">Loyalty:</span>
              <span class="ml-2 text-gray-600">{{ card.loyalty }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pricing Information -->
    <div v-if="card.prices && card.prices.length > 0" class="bg-white rounded-lg shadow-md p-6">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">Pricing Information</h2>
        <button
          @click="updatePrices"
          :disabled="updatingPrices"
          class="btn-primary"
        >
          <svg v-if="updatingPrices" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ updatingPrices ? 'Updating...' : 'Update Prices' }}
        </button>
      </div>

      <!-- Price Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Type
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Condition
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Foil
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Price (EUR)
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Updated
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="price in card.prices" :key="`${price.price_type}-${price.condition}-${price.foil}`">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 capitalize">
                {{ price.price_type }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ price.condition }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ price.foil ? 'Yes' : 'No' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-mtg-blue">
                €{{ price.price }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(price.price_updated_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- No Pricing Information -->
    <div v-else class="bg-white rounded-lg shadow-md p-6">
      <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No pricing information available</h3>
        <p class="text-gray-600 mb-4">This card may not have pricing data from Cardmarket</p>
        <button
          v-if="card.cardmarket_id"
          @click="updatePrices"
          :disabled="updatingPrices"
          class="btn-primary"
        >
          {{ updatingPrices ? 'Updating...' : 'Try to Update Prices' }}
        </button>
      </div>
    </div>
  </div>

  <!-- Loading State -->
  <div v-else-if="loading" class="text-center py-12">
    <div class="inline-flex items-center">
      <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-mtg-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <span class="text-lg text-gray-600">Loading card details...</span>
    </div>
  </div>

  <!-- Error State -->
  <div v-else class="text-center py-12">
    <div class="text-gray-500">
      <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
      </svg>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Card not found</h3>
      <p class="text-gray-600">The requested card could not be found</p>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

export default {
  name: 'CardDetail',
  props: {
    id: {
      type: String,
      required: true
    }
  },
  setup(props) {
    const route = useRoute()
    const card = ref(null)
    const loading = ref(true)
    const updatingPrices = ref(false)

    // Load card details
    const loadCard = async () => {
      try {
        const response = await fetch(`/api/cards/${props.id}`)
        if (response.ok) {
          const data = await response.json()
          card.value = data.data
        }
      } catch (error) {
        console.error('Failed to load card:', error)
      } finally {
        loading.value = false
      }
    }

    // Update prices
    const updatePrices = async () => {
      if (!card.value || !card.value.cardmarket_id) return

      updatingPrices.value = true
      
      try {
        const response = await fetch(`/api/cards/${props.id}/update-prices`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          }
        })

        if (response.ok) {
          const data = await response.json()
          card.value = data.data
        }
      } catch (error) {
        console.error('Failed to update prices:', error)
      } finally {
        updatingPrices.value = false
      }
    }

    // Handle image error
    const handleImageError = (event) => {
      event.target.src = '/images/card-placeholder.jpg'
    }

    // Get color class
    const getColorClass = (color) => {
      const colorClasses = {
        'W': 'bg-mtg-white text-gray-800',
        'U': 'bg-mtg-blue text-white',
        'B': 'bg-mtg-black text-white',
        'R': 'bg-mtg-red text-white',
        'G': 'bg-mtg-green text-white',
        'C': 'bg-mtg-colorless text-gray-800'
      }
      return colorClasses[color] || 'bg-gray-200 text-gray-800'
    }

    // Format date
    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString()
    }

    // Initialize
    onMounted(() => {
      loadCard()
    })

    return {
      card,
      loading,
      updatingPrices,
      updatePrices,
      handleImageError,
      getColorClass,
      formatDate
    }
  }
}
</script>