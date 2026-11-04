<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import aimsPreview from '../assets/Aims.png'
import ermsPreview from '../assets/ERMS.png'

type Project = {
  title: string
  category: string
  type: string
  description: string
  client?: string
  year?: string
  problem: string
  role: string
  features: string[]
  impact?: string[]
  tags: string[]
  repository: string | null
  demo: string | null
  accent: string
  preview: string
}

const selectedProject = ref<Project | null>(null)
const projects = ref<Project[]>([])
const isLoading = ref(true)
const loadError = ref('')
const emit = defineEmits<{
  ready: []
}>()
const apiBaseUrl = (import.meta.env.VITE_API_URL || '').replace(/\/+$/, '')
const isProjectDialogOpen = computed({
  get: () => selectedProject.value !== null,
  set: (isOpen: boolean) => {
    if (!isOpen) selectedProject.value = null
  },
})
onMounted(async () => {
  try {
    const response = await axios.get<{ data: Project[] }>(`${apiBaseUrl}/api/projects`)
    projects.value = response.data.data
  } catch {
    loadError.value = 'Projects could not be loaded right now. Please try again later.'
  } finally {
    isLoading.value = false
    emit('ready')
  }
})
const visibleProjects = computed(() => projects.value)
</script>

<template>
  <section id="work" class="work-section section-wrap section-pad">
    <div class="section-heading">
      <p class="eyebrow">03 — SELECTED PROJECTS</p>
      <span class="heading-rule"></span>
      <span class="section-index">03 / 06</span>
    </div>

    <div class="work-header">
      <div>
        <h2>Things I’ve <span class="serif-word">made.</span></h2>
        <p>Full-stack platforms, resilient APIs, and quality-focused delivery.</p>
      </div>
      <a class="text-link" href="#contact">Have something in mind? <v-icon icon="mdi-arrow-up-right" size="17" /></a>
    </div>

    <p v-if="isLoading" class="project-status" role="status">Loading projects…</p>
    <p v-else-if="loadError" class="project-status error" role="alert">{{ loadError }}</p>
    <div class="project-grid">
      <button
        v-for="(project, index) in visibleProjects"
        :key="project.title"
        class="project-card"
        type="button"
        @click="selectedProject = project"
      >
        <div class="project-preview" :class="[`preview-${project.preview}`, `accent-${project.accent}`]">
          <div v-if="project.preview === 'aims'" class="project-screenshot-preview">
            <img :src="aimsPreview" alt="" class="project-preview-image aims-preview-image" />
            <span class="project-image-badge">
              <v-icon icon="mdi-lock-outline" size="13" />
              Private project
            </span>
          </div>
          <div v-else-if="project.preview === 'erms'" class="project-screenshot-preview">
            <img :src="ermsPreview" alt="" class="project-preview-image erms-preview-image" />
            <span class="project-image-badge">
              <v-icon icon="mdi-bank" size="13" />
              Government project
            </span>
          </div>
          <div v-else class="preview-browser">
            <div class="browser-bar"><i></i><i></i><i></i><span>your-project.com</span></div>
            <div v-if="project.preview === 'overview'" class="preview-overview">
              <aside><span>◈</span><i></i><i></i><i></i></aside>
              <div class="preview-content">
                <span class="preview-kicker">PROJECT PREVIEW</span>
                <strong>Build something<br />that matters.</strong>
                <div class="preview-lines"><i></i><i></i></div>
                <span class="preview-action">Explore <b>↗</b></span>
              </div>
            </div>
            <div v-else-if="project.preview === 'dashboard'" class="preview-dashboard">
              <aside><span>◈</span><i></i><i></i><i></i></aside>
              <div class="dash-content">
                <span class="dash-title">Overview <small>Today ⌄</small></span>
                <div class="stat-row"><i><small>Total records</small><b>—</b></i><i><small>Active items</small><b>—</b></i></div>
                <div class="chart"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
              </div>
            </div>
            <div v-else class="preview-analytics">
              <span class="dash-title">Activity <small>Project preview</small></span>
              <div class="analytics-big">Your metrics<small>Replace with project data</small></div>
              <div class="analytics-chart">
                <svg viewBox="0 0 400 120" preserveAspectRatio="none">
                  <path d="M0 96 C40 82 40 101 82 70 S142 83 182 54 S246 74 286 40 S340 54 400 8" />
                  <path class="chart-fill" d="M0 96 C40 82 40 101 82 70 S142 83 182 54 S246 74 286 40 S340 54 400 8 V120 H0 Z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <div class="project-info">
          <div class="project-card-meta">
            <span class="project-type">{{ project.type }}</span>
            <span class="project-count">{{ String(index + 1).padStart(2, '0') }} / {{ String(visibleProjects.length).padStart(2, '0') }}</span>
          </div>
          <h3>{{ project.title }}</h3>
          <p class="project-summary">{{ project.description }}</p>
          <div class="project-tags">
            <span v-for="tag in project.tags" :key="tag">{{ tag }}</span>
          </div>
          <div class="project-card-action">
            <span>Explore case study</span>
            <v-icon icon="mdi-arrow-up-right" size="17" />
          </div>
        </div>
      </button>
    </div>
    <p v-if="!isLoading && !loadError && visibleProjects.length === 0" class="project-status">
      No projects match this category yet.
    </p>
  </section>

  <v-dialog v-model="isProjectDialogOpen" max-width="1240" scrollable>
    <v-card v-if="selectedProject" class="project-dialog">
      <div class="project-detail-layout">
        <aside
          class="project-detail-visual"
          :class="{
            'project-detail-visual-aims': selectedProject.preview === 'aims',
            'project-detail-visual-erms': selectedProject.preview === 'erms',
          }"
        >
          <v-btn
            class="project-detail-back"
            icon="mdi-arrow-left"
            variant="text"
            aria-label="Close project details"
            @click="selectedProject = null"
          />
          <template v-if="selectedProject.preview === 'aims'">
            <div class="detail-code-panel detail-code-panel-top" aria-hidden="true">
              <span><i></i> project.ts</span>
              <code>const project = {<br />&nbsp;&nbsp;type: 'digital-solution',<br />&nbsp;&nbsp;framework: 'Laravel 11',<br />&nbsp;&nbsp;status: 'private'<br />}</code>
            </div>
            <div class="detail-code-panel detail-code-panel-bottom" aria-hidden="true">
              <span><i></i> deploy.config.js</span>
              <code>export default defineConfig({<br />&nbsp;&nbsp;secure: true,<br />&nbsp;&nbsp;reliable: true,<br />&nbsp;&nbsp;accountable: true<br />})</code>
            </div>
            <div class="detail-image-frame">
              <img :src="aimsPreview" alt="AIMS sign-in screen for the Assets Inventory Management System" />
              <span class="project-image-badge">
                <v-icon icon="mdi-lock-outline" size="13" />
                Private project
              </span>
            </div>
            <span class="detail-visual-caption">ASSETS INVENTORY MANAGEMENT SYSTEM</span>
          </template>
          <template v-else-if="selectedProject.preview === 'erms'">
            <div class="detail-code-panel detail-code-panel-top" aria-hidden="true">
              <span><i></i> erms.ts</span>
              <code>const platform = {<br />&nbsp;&nbsp;frontend: 'Vue 3',<br />&nbsp;&nbsp;backend: 'Laravel API',<br />&nbsp;&nbsp;workflow: 'RCP review'<br />}</code>
            </div>
            <div class="detail-code-panel detail-code-panel-bottom" aria-hidden="true">
              <span><i></i> access.config.js</span>
              <code>export default defineConfig({<br />&nbsp;&nbsp;roleBased: true,<br />&nbsp;&nbsp;validated: true,<br />&nbsp;&nbsp;resilient: true<br />})</code>
            </div>
            <div class="detail-image-frame">
              <img :src="ermsPreview" alt="ERMS sign-in screen for the Energy Resiliency Management System" />
              <span class="project-image-badge">
                <v-icon icon="mdi-bank" size="13" />
                Government project
              </span>
            </div>
            <span class="detail-visual-caption">ENERGY RESILIENCY MANAGEMENT SYSTEM</span>
          </template>
          <div v-else class="project-detail-placeholder">
            <span class="detail-placeholder-icon"><v-icon icon="mdi-code-tags" size="42" /></span>
            <small>{{ selectedProject.category }} PROJECT</small>
            <strong>{{ selectedProject.title }}</strong>
            <span class="detail-placeholder-rule"></span>
            <span class="detail-placeholder-tags">{{ selectedProject.tags.slice(0, 3).join(' · ') }}</span>
          </div>
        </aside>

        <section class="project-detail-content">
          <div class="project-detail-topline">
            <span class="project-type">{{ selectedProject.type }}</span>
            <v-btn
              class="project-detail-close"
              icon="mdi-close"
              variant="text"
              aria-label="Close project details"
              @click="selectedProject = null"
            />
          </div>
          <p class="project-detail-kicker">PROJECT CASE STUDY</p>
          <h2 id="project-dialog-title">{{ selectedProject.title }}</h2>
          <p class="project-detail-description">{{ selectedProject.description }}</p>

          <div v-if="selectedProject.client || selectedProject.year" class="project-detail-meta">
            <div v-if="selectedProject.client">
              <small>CLIENT</small>
              <strong>{{ selectedProject.client }}</strong>
            </div>
            <div v-if="selectedProject.year">
              <small>PROJECT YEAR</small>
              <strong>{{ selectedProject.year }}</strong>
            </div>
          </div>

          <div class="project-detail-section">
            <p class="project-detail-label">TECHNOLOGY USED</p>
            <div class="project-tags project-detail-tags">
              <span v-for="tag in selectedProject.tags" :key="tag">{{ tag }}</span>
            </div>
          </div>

          <div class="dialog-links">
            <a v-if="selectedProject.repository" :href="selectedProject.repository" target="_blank" rel="noopener noreferrer">
              View source code <v-icon icon="mdi-arrow-top-right" size="15" />
            </a>
            <a v-if="selectedProject.demo" :href="selectedProject.demo" target="_blank" rel="noopener noreferrer">
              View live project <v-icon icon="mdi-arrow-top-right" size="15" />
            </a>
          </div>
        </section>
      </div>
    </v-card>
  </v-dialog>
</template>

<style lang="scss">
@use './Projects.scss';
</style>
