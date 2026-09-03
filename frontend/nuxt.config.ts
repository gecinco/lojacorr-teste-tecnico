export default defineNuxtConfig({
  ssr: false,
  devtools: { enabled: true },

  modules: [
    '@pinia/nuxt',
  ],

  css: ['~/assets/css/main.css'],

  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api/v1',
    },
  },

  app: {
    head: {
      title: 'Portal de Contratação de Seguros',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'Plataforma de contratação e gestão de apólices de seguros para corretores.' },
        { name: 'theme-color', content: '#7b1e2a' },
      ],
      link: [
        { rel: 'icon', type: 'image/svg+xml', href: '/favicon.svg' },
        { rel: 'alternate icon', type: 'image/x-icon', href: '/favicon.ico' },
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap' },
      ],
    },
  },

  typescript: {
    strict: true,
    typeCheck: false,
  },

  compatibilityDate: '2024-01-01',
})