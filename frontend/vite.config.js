import vue from '@vitejs/plugin-vue'
import VueDevTools from 'vite-plugin-vue-devtools'
import { defineConfig } from 'vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [VueDevTools(), vue()],
  server: {
    proxy: {
      '/api': process.env.LARAVEL_API_URL || 'http://127.0.0.1:8000',
    },
  },
})
