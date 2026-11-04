import { createApp } from 'vue'
import { createVuetify } from 'vuetify'
import {
  VApp,
  VBtn,
  VCard,
  VDialog,
  VIcon,
  VProgressCircular,
  VSnackbar,
} from 'vuetify/components'
import { createRouter, createWebHistory, RouterView } from 'vue-router'
import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import './style.scss'
import App from './App.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [{ path: '/', component: App }],
  scrollBehavior(to) {
    if (to.hash) {
      const behavior = window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ? 'auto'
        : 'smooth'

      return { el: to.hash, behavior, top: 84 }
    }

    return { top: 0 }
  },
})

document.addEventListener('click', (event) => {
  const anchor = event.target instanceof Element
    ? event.target.closest<HTMLAnchorElement>('a[href^="#"]')
    : null

  if (!anchor) return

  const hash = anchor.getAttribute('href')
  if (!hash || hash === '#') return

  event.preventDefault()
  void router.push({ path: '/', hash })
})

const vuetify = createVuetify({
  components: {
    VApp,
    VBtn,
    VCard,
    VDialog,
    VIcon,
    VProgressCircular,
    VSnackbar,
  },
  icons: { defaultSet: 'mdi' },
})

createApp(RouterView).use(router).use(vuetify).mount('#app')
