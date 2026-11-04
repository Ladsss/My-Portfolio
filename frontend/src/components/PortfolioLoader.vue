<script setup lang="ts">
import PortfolioLoaderOrbit from "./PortfolioLoaderOrbit.vue";
import PortfolioLoaderProgress from "./PortfolioLoaderProgress.vue";

defineProps<{
  progress: number;
}>();
</script>

<template>
  <div
    class="portfolio-loader"
    role="status"
    aria-live="polite"
    :aria-label="`Initializing portfolio experience, ${progress}%`"
  >
    <div class="portfolio-loader__ambient" aria-hidden="true">
      <span class="portfolio-loader__particle portfolio-loader__particle--one"></span>
      <span class="portfolio-loader__particle portfolio-loader__particle--two"></span>
      <span class="portfolio-loader__particle portfolio-loader__particle--three"></span>
      <span class="portfolio-loader__particle portfolio-loader__particle--four"></span>
    </div>

    <div class="portfolio-loader__content">
      <p class="portfolio-loader__message">Initializing experience</p>
      <PortfolioLoaderOrbit />
      <PortfolioLoaderProgress :progress="progress" />
      <span class="portfolio-loader__signature">REYMART EPAN · DEVELOPER PORTFOLIO</span>
    </div>
  </div>
</template>

<style scoped lang="scss">
.portfolio-loader {
  position: fixed;
  z-index: 9999;
  inset: 0;
  display: grid;
  place-items: center;
  overflow: hidden;
  background:
    radial-gradient(
      ellipse at 50% 42%,
      color-mix(in srgb, var(--primary) 17%, transparent),
      transparent 42%
    ),
    var(--bg);
  color: var(--text);
  isolation: isolate;
  pointer-events: all;
}

.portfolio-loader__ambient {
  position: absolute;
  inset: 0;
  overflow: hidden;
  background:
    radial-gradient(
      circle at 18% 22%,
      color-mix(in srgb, var(--highlight) 8%, transparent),
      transparent 27%
    ),
    radial-gradient(
      circle at 83% 76%,
      color-mix(in srgb, var(--primary-dark) 12%, transparent),
      transparent 32%
    );
}

.portfolio-loader__ambient::before,
.portfolio-loader__ambient::after {
  position: absolute;
  top: 50%;
  left: 50%;
  width: min(72vw, 520px);
  aspect-ratio: 1;
  border: 1px solid color-mix(in srgb, var(--highlight) 8%, transparent);
  border-radius: 50%;
  content: "";
  transform: translate(-50%, -50%);
}

.portfolio-loader__ambient::after {
  width: min(54vw, 390px);
  border-color: color-mix(in srgb, var(--highlight) 6%, transparent);
}

.portfolio-loader__particle {
  position: absolute;
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background: var(--highlight);
  box-shadow: 0 0 14px 3px color-mix(in srgb, var(--primary) 55%, transparent);
  animation: loader-particle-drift 5.6s ease-in-out infinite alternate;
}

.portfolio-loader__particle--one {
  top: 24%;
  left: 28%;
}

.portfolio-loader__particle--two {
  top: 31%;
  right: 25%;
  width: 3px;
  height: 3px;
  animation-delay: -1.2s;
}

.portfolio-loader__particle--three {
  right: 31%;
  bottom: 23%;
  animation-delay: -2.4s;
}

.portfolio-loader__particle--four {
  bottom: 32%;
  left: 24%;
  width: 3px;
  height: 3px;
  animation-delay: -3.1s;
}

.portfolio-loader__content {
  position: relative;
  display: grid;
  width: min(360px, calc(100vw - 40px));
  justify-items: center;
  gap: 20px;
  padding: 28px;
}

.portfolio-loader__message {
  margin: 0;
  color: var(--text);
  font: 500 12px/1.5 var(--font-body);
  letter-spacing: 0.035em;
}

.portfolio-loader__signature {
  color: var(--muted);
  font: 500 8px/1.4 var(--mono);
  letter-spacing: 0.13em;
  text-align: center;
}

@keyframes loader-particle-drift {
  from {
    opacity: 0.3;
    transform: translate3d(0, 7px, 0);
  }

  to {
    opacity: 0.9;
    transform: translate3d(0, -9px, 0);
  }
}

@media (max-width: 480px) {
  .portfolio-loader__content {
    gap: 16px;
    padding: 20px;
  }
}

@media (max-width: 480px) {
  .portfolio-loader__content {
    gap: 16px;
    padding: 20px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .portfolio-loader__particle {
    animation: none;
  }
}
</style>
