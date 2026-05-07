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
      title: 'XoSo AI',
      titleTemplate: '%s · XoSo AI',
      htmlAttrs: {
        lang: 'vi',
      },
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/logo-medium.ico' },
      ],
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'theme-color', content: '#ef4444' },
        { name: 'application-name', content: 'XoSo AI' },
        { property: 'og:site_name', content: 'XoSo AI' },
        { property: 'og:type', content: 'website' },
        { name: 'twitter:card', content: 'summary_large_image' },
      ],
    },
  },
  runtimeConfig: {
    public: {
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL || 'http://localhost:8989/api',
      siteUrl: process.env.NUXT_PUBLIC_SITE_URL || 'http://localhost:3000',
      googleClientId: process.env.NUXT_PUBLIC_GOOGLE_CLIENT_ID || '',
    },
  },
})
