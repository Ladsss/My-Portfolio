/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_API_URL?: string
  readonly VITE_FACEBOOK_URL?: string
  readonly VITE_GITHUB_URL?: string
  readonly VITE_INSTAGRAM_URL?: string
  readonly VITE_LINKEDIN_URL?: string
  readonly VITE_TIKTOK_URL?: string
  readonly VITE_CV_URL?: string
  readonly VITE_CONTACT_EMAIL?: string
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}

declare module '*.vue' {
  import type { DefineComponent } from 'vue'

  const component: DefineComponent
  export default component
}
