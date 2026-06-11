/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./app/**/*.{vue,js,ts,jsx,tsx}",
    "./components/**/*.{vue,js,ts,jsx,tsx}",
    "./layouts/**/*.vue",
    "./pages/**/*.vue",
    "./plugins/**/*.{js,ts}",
    "./app.vue",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        'bg-deep': 'rgb(var(--bg-deep) / <alpha-value>)',
        'bg-base': 'rgb(var(--bg-base) / <alpha-value>)',
        'bg-elevated': 'rgb(var(--bg-elevated) / <alpha-value>)',
        'surface': 'rgb(var(--surface) / var(--surface-opacity))',
        'surface-hover': 'rgb(var(--surface) / var(--surface-hover-opacity))',
        'foreground': 'rgb(var(--foreground) / <alpha-value>)',
        'foreground-muted': 'rgb(var(--foreground-muted) / <alpha-value>)',
        'foreground-subtle': 'rgb(var(--foreground-subtle) / var(--foreground-subtle-opacity))',
        'accent': {
          DEFAULT: '#5E6AD2',
          bright: '#6872D9',
          glow: 'rgba(94,106,210,0.3)',
        },
        'border-default': 'rgb(var(--border-default) / var(--border-default-opacity))',
        'border-hover': 'rgb(var(--border-hover) / var(--border-hover-opacity))',
        'border-accent': 'rgba(94,106,210,0.30)',
      },
      fontFamily: {
        sans: ['Inter', 'Geist Sans', 'system-ui', 'sans-serif'],
        mono: ['JetBrains Mono', 'monospace'],
      },
      borderRadius: {
        '2xl': '16px',
        'xl': '12px',
      },
      boxShadow: {
        'card': '0 0 0 1px var(--border-default), 0 2px 20px rgba(0,0,0,0.4), 0 0 40px rgba(0,0,0,0.2)',
        'card-hover': '0 0 0 1px var(--border-hover), 0 8px_40px rgba(0,0,0,0.5), 0 0 80px rgba(94,106,210,0.1)',
        'accent-glow': '0 0 0 1px rgba(94,106,210,0.5), 0 4px 12px rgba(94,106,210,0.3), inset 0 1px 0 0 rgba(255,255,255,0.2)',
        'inner-highlight': 'inset 0 1px 0 0 rgba(255,255,255,0.1)',
      },
      animation: {
        'float': 'float 10s ease-in-out infinite',
        'shimmer': 'shimmer 2s linear infinite',
        'glow-pulse': 'glow-pulse 4s ease-in-out infinite',
      },
      keyframes: {
        float: {
          '0%, 100%': { transform: 'translateY(0) rotate(0deg)' },
          '50%': { transform: 'translateY(-20px) rotate(1deg)' },
        },
        shimmer: {
          '0%': { backgroundPosition: '200% center' },
          '100%': { backgroundPosition: '-200% center' },
        },
        'glow-pulse': {
          '0%, 100%': { opacity: 0.3 },
          '50%': { opacity: 0.6 },
        }
      },
      backgroundImage: {
        'noise': "url('https://pixabay.com/images/download/alexey_hulsov-star-1951963_1280.jpg')",
      }
    },
  },
  plugins: [],
}

