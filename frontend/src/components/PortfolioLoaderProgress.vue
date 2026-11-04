<script setup lang="ts">
defineProps<{
  progress: number;
}>();
</script>

<template>
  <div class="portfolio-loader__progress">
    <div class="portfolio-loader__progress-heading">
      <span>Loading portfolio</span>
      <output class="portfolio-loader__percentage">{{ progress }}%</output>
    </div>
    <div
      class="portfolio-loader__progress-track"
      role="progressbar"
      aria-label="Portfolio loading progress"
      aria-valuemin="0"
      aria-valuemax="100"
      :aria-valuenow="progress"
    >
      <span
        class="portfolio-loader__progress-fill"
        :style="{ width: `${progress}%` }"
      ></span>
    </div>
  </div>
</template>

<style scoped lang="scss">
.portfolio-loader__progress {
  display: grid;
  width: 100%;
  gap: 9px;
}

.portfolio-loader__progress-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  color: var(--muted);
  font: 500 9px/1.4 var(--mono);
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.portfolio-loader__percentage {
  color: var(--highlight);
  font: 600 12px/1 var(--mono);
  font-variant-numeric: tabular-nums;
}

.portfolio-loader__progress-track {
  height: 3px;
  overflow: hidden;
  border-radius: 999px;
  background: color-mix(in srgb, var(--highlight) 16%, transparent);
}

.portfolio-loader__progress-fill {
  display: block;
  height: 100%;
  border-radius: inherit;
  background: linear-gradient(90deg, var(--primary-dark), var(--highlight));
  box-shadow: 0 0 12px color-mix(in srgb, var(--primary) 62%, transparent);
  transition: width 160ms linear;
}

@media (prefers-reduced-motion: reduce) {
  .portfolio-loader__progress-fill {
    transition: none;
  }
}
</style>
