/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        // MTG-themed colors
        'mtg-white': '#f8f6d8',
        'mtg-blue': '#c1d7e9',
        'mtg-black': '#a69f9d',
        'mtg-red': '#e49977',
        'mtg-green': '#a3c095',
        'mtg-gold': '#d4af37',
        'mtg-colorless': '#c4c4c4',
        'mtg-multicolor': '#a3a3a3',
      },
      fontFamily: {
        'mtg': ['Beleren', 'serif'],
      },
      screens: {
        'sm': '640px',
        'md': '768px',
        'lg': '1024px',
        'xl': '1280px',
        '2xl': '1536px',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}

