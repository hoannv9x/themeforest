// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  modules: ['@nuxtjs/tailwindcss', '@pinia/nuxt'],
  experimental: {
    payloadExtraction: false
  },
  app: {
    head: {
      title: 'XoSo AI', // default fallback title
      htmlAttrs: {
        lang: 'vi',
      },
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/logo-medium.ico' },
      ],
    },
  },
  runtimeConfig: {
    public: {
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL || 'http://localhost:8989/api',
    },
  },
})