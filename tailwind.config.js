/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        'mtg-white': '#FFFBD5',
        'mtg-blue': '#0E68AB',
        'mtg-black': '#150B00',
        'mtg-red': '#D3202A',
        'mtg-green': '#00733E',
      },
      fontFamily: {
        'sans': ['Inter', 'ui-sans-serif', 'system-ui'],
      },
    },
  },
  plugins: [],
}