<template>
  <div class="flex items-center space-x-1">
    <span
      v-for="(symbol, index) in parsedCost"
      :key="index"
      class="mana-symbol"
      :class="getSymbolClass(symbol)"
    >
      {{ getSymbolText(symbol) }}
    </span>
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'ManaCost',
  props: {
    cost: {
      type: String,
      required: true
    }
  },
  setup(props) {
    const parsedCost = computed(() => {
      if (!props.cost) return [];
      
      // Parse mana cost string like "{3}{R}{R}" into ["3", "R", "R"]
      const matches = props.cost.match(/{([^}]+)}/g);
      if (!matches) return [];
      
      return matches.map(match => match.replace(/[{}]/g, ''));
    });

    const getSymbolClass = (symbol) => {
      // Check if it's a color symbol
      if (['W', 'U', 'B', 'R', 'G'].includes(symbol)) {
        switch (symbol) {
          case 'W':
            return 'mana-white';
          case 'U':
            return 'mana-blue';
          case 'B':
            return 'mana-black';
          case 'R':
            return 'mana-red';
          case 'G':
            return 'mana-green';
        }
      }
      
      // Check if it's a hybrid symbol like "W/U"
      if (symbol.includes('/')) {
        const colors = symbol.split('/');
        if (colors.length === 2) {
          // For hybrid, use a gradient or the first color
          return getSymbolClass(colors[0]);
        }
      }
      
      // Check if it's a Phyrexian symbol like "W/P"
      if (symbol.includes('/P')) {
        const color = symbol.replace('/P', '');
        return getSymbolClass(color);
      }
      
      // Generic mana or other symbols
      return 'mana-colorless';
    };

    const getSymbolText = (symbol) => {
      // For single-digit numbers, just return the number
      if (/^\d+$/.test(symbol)) {
        return symbol;
      }
      
      // For color symbols, return the letter
      if (['W', 'U', 'B', 'R', 'G'].includes(symbol)) {
        return symbol;
      }
      
      // For hybrid symbols, show both letters
      if (symbol.includes('/')) {
        return symbol.replace('/', '');
      }
      
      // For other symbols (X, T, etc.), return as is
      return symbol;
    };

    return {
      parsedCost,
      getSymbolClass,
      getSymbolText
    };
  }
};
</script>