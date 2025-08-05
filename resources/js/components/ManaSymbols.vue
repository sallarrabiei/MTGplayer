<template>
  <div class="flex items-center space-x-1">
    <span
      v-for="(symbol, index) in parsedSymbols"
      :key="index"
      :class="[
        'inline-flex items-center justify-center rounded-full text-xs font-bold',
        symbolClass(symbol),
        'w-5 h-5'
      ]"
    >
      {{ symbolText(symbol) }}
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  manaCost: {
    type: String,
    required: true,
  },
});

const parsedSymbols = computed(() => {
  if (!props.manaCost) return [];
  
  // Match mana symbols like {W}, {U}, {B}, {R}, {G}, {C}, {1}, {2}, etc.
  const regex = /\{([^}]+)\}/g;
  const symbols = [];
  let match;
  
  while ((match = regex.exec(props.manaCost)) !== null) {
    symbols.push(match[1]);
  }
  
  return symbols;
});

const symbolClass = (symbol) => {
  const classes = {
    'W': 'bg-yellow-100 text-yellow-900 border border-yellow-300',
    'U': 'bg-blue-500 text-white',
    'B': 'bg-gray-900 text-white',
    'R': 'bg-red-500 text-white',
    'G': 'bg-green-600 text-white',
    'C': 'bg-gray-300 text-gray-700',
  };
  
  // Check if it's a color
  if (classes[symbol]) {
    return classes[symbol];
  }
  
  // Check if it's a hybrid mana (e.g., W/U)
  if (symbol.includes('/')) {
    return 'bg-gradient-to-br from-gray-200 to-gray-400 text-gray-800';
  }
  
  // Generic mana (numbers)
  return 'bg-gray-200 text-gray-800 border border-gray-300';
};

const symbolText = (symbol) => {
  // For hybrid mana, show both symbols
  if (symbol.includes('/')) {
    return symbol;
  }
  
  // For phyrexian mana
  if (symbol.includes('P')) {
    return symbol;
  }
  
  // For everything else (colors and numbers)
  return symbol;
};
</script>