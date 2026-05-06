// tailwind.config.js
export default {
  content: [
    "./components/**/*.{vue,js,ts}",
    "./layouts/**/*.vue",
    "./pages/**/*.vue",
    "./app.vue",
    "./plugins/**/*.{js,ts}",
  ],
  theme: {
    extend: {
      backgroundImage: {
        'logo': 'url("/logo.png")',
        'logo-medium': 'url("/logo-medium.png")',
        'logo-small': 'url("/logo-small.png")',
      },
    },
  },
  plugins: [],
}