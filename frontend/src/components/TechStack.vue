<script setup lang="ts">
const skillGroups = [
  {
    title: 'Frontend Languages & Frameworks',
    number: '01',
    description: 'Tools I use to build beautiful, responsive, and interactive user interfaces.',
    skills: [
      { name: 'Vue 3', icon: 'mdi-vuejs' },
      { name: 'JavaScript', icon: 'mdi-language-javascript' },
      { name: 'TypeScript', icon: 'mdi-language-typescript' },
      { name: 'Composition API', icon: 'mdi-code-braces' },
      { name: 'Vite', icon: 'mdi-lightning-bolt' },
      { name: 'Vuetify', icon: 'mdi-vuetify' },
      { name: 'SCSS', icon: 'mdi-sass' },
      { name: 'Vue Router', icon: 'mdi-routes' },
      { name: 'Pinia', icon: 'mdi-store' },
      { name: 'Axios', icon: 'mdi-api' },
    ],
  },
  {
    title: 'Backend & Database',
    number: '02',
    description: 'Backend tools for secure APIs, reliable data, and scalable application logic.',
    skills: [
      { name: 'PHP', icon: 'mdi-language-php' },
      { name: 'Laravel', icon: 'mdi-laravel' },
      { name: 'REST APIs', icon: 'mdi-api' },
      { name: 'MySQL', icon: 'mdi-database' },
      { name: 'Redis', icon: 'mdi-database-outline' },
      { name: 'Authentication & RBAC', icon: 'mdi-shield-lock-outline' },
      { name: 'Queue workers', icon: 'mdi-layers-outline' },
    ],
  },
]
</script>

<template>
  <section id="skills" class="skills-section section-pad">
    <div class="section-wrap">
      <div class="section-heading">
        <p class="eyebrow">02 — MY TECH STACK</p>
        <span class="heading-rule"></span>
        <span class="section-index">02 / 06</span>
      </div>

      <div class="skills-rows">
        <article v-for="group in skillGroups" :key="group.number" class="skill-row">
          <div class="skill-row-copy">
            <span class="skill-row-number">{{ group.number }}</span>
            <h2>{{ group.title }}</h2>
            <p>{{ group.description }}</p>
          </div>
          <div class="skill-items">
            <div v-for="skill in group.skills" :key="skill.name" class="skill-item">
              <v-icon :icon="skill.icon" size="27" aria-hidden="true" />
              <span>{{ skill.name }}</span>
            </div>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>

<style lang="scss">
@use './TechStack.scss';
</style>
