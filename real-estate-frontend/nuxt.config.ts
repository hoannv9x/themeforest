// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: false },
  app: {
    head: {
      link: [
        {
          rel: 'stylesheet',
          href: 'https://cdn.jsdelivr.net/npm/pannellum/build/pannellum.css'
        }
      ],
      script: [
        {
          src: 'https://cdn.jsdelivr.net/npm/pannellum/build/pannellum.js',
          defer: true
        }
      ]
    }
  },
  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
  ],
  experimental: {
    payloadExtraction: false,
  },
  tailwindcss: {
    cssPath: '~/assets/css/main.css', // Path to your Tailwind CSS file
    configPath: 'tailwind.config.js',
    exposeConfig: false,
    injectPosition: 'first',
    viewer: true,
  },
  runtimeConfig: {
    public: {
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL || 'http://localhost:8989/api', // Default to Laravel's default API URL
    },
  },
  vite: {
    server: {
      hmr: {
        overlay: false,
      },
    },
    optimizeDeps: {
      include: [
        '@vue/devtools-core',
        '@vue/devtools-kit',
        'axios',
      ]
    }
  }
})
