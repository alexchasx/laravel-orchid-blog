export default defineNuxtConfig({
  compatibilityDate: '2025-07-01',

  /** Публичный сайт блога рендерится на сервере (SEO). */
  ssr: true,

  modules: ['vuetify-nuxt-module'],

  css: ['@mdi/font/css/materialdesignicons.min.css', '~/assets/css/main.css'],

  app: {
    head: {
      htmlAttrs: { lang: 'ru' },
      titleTemplate: '%s | Блог о разработке',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        {
          name: 'description',
          content:
            'IT-блог о веб-разработке: PHP, Laravel, JavaScript, Nginx и смежные технологии.',
        },
      ],
    },
  },

  runtimeConfig: {
    public: {
      /** Базовый URL Laravel (API + медиа). В prod задаётся через NUXT_PUBLIC_API_BASE. */
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8080',
    },
  },

  nitro: {
    devProxy: {
      '/api': { target: 'http://localhost:8080/api', changeOrigin: true },
      '/storage': { target: 'http://localhost:8080/storage', changeOrigin: true },
    },
  },

  typescript: {
    strict: true,
  },

  vuetify: {
    moduleOptions: {},
    vuetifyOptions: {
      theme: {
        defaultTheme: 'light',
        themes: {
          light: {
            dark: false,
            colors: {
              primary: '#0B57D0',
              secondary: '#5F6368',
              accent: '#3D5AFE',
              background: '#F7F8FA',
              surface: '#FFFFFF',
            },
          },
          dark: {
            dark: true,
            colors: {
              primary: '#8AB4F8',
              secondary: '#9AA0A6',
              accent: '#AECBFA',
              background: '#101418',
              surface: '#1B2027',
            },
          },
        },
      },
      defaults: {
        VBtn: { variant: 'text', rounded: 'pill' },
        VCard: { rounded: 'xl', elevation: 0 },
        VTextField: { variant: 'outlined', density: 'comfortable' },
        VTextarea: { variant: 'outlined', density: 'comfortable' },
      },
    },
  },
})
