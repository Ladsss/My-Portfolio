<template>
  <div class="portfolio-loader__orbit" aria-hidden="true">
    <span class="portfolio-loader__orbit-line portfolio-loader__orbit-line--outer"></span>
    <span class="portfolio-loader__orbit-line portfolio-loader__orbit-line--inner"></span>
    <span class="portfolio-loader__orbit-node portfolio-loader__orbit-node--vue">Vue</span>
    <span class="portfolio-loader__orbit-node portfolio-loader__orbit-node--laravel">Laravel</span>
    <span class="portfolio-loader__orbit-node portfolio-loader__orbit-node--js">JS</span>
    <span class="portfolio-loader__orbit-node portfolio-loader__orbit-node--css">CSS</span>
    <span class="portfolio-loader__code-mark">&lt;/&gt;</span>
  </div>
</template>

<style scoped lang="scss">
.portfolio-loader__orbit {
  position: relative;
  display: grid;
  width: min(240px, 62vw);
  aspect-ratio: 1;
  place-items: center;
  filter: drop-shadow(0 0 28px color-mix(in srgb, var(--primary) 24%, transparent));
  animation: loader-orbit-breathe 3s ease-in-out infinite;
}

.portfolio-loader__orbit-line {
  position: absolute;
  border: 1px solid color-mix(in srgb, var(--highlight) 24%, transparent);
  border-radius: 50%;
}

.portfolio-loader__orbit-line--outer {
  inset: 5%;
  transform: rotate(-28deg) scaleY(0.7);
}

.portfolio-loader__orbit-line--inner {
  inset: 17%;
  border-color: color-mix(in srgb, var(--highlight) 12%, transparent);
}

.portfolio-loader__code-mark {
  display: grid;
  width: 82px;
  height: 82px;
  place-items: center;
  border: 1px solid color-mix(in srgb, var(--highlight) 40%, transparent);
  border-radius: 24px;
  background:
    linear-gradient(145deg, rgba(255, 255, 255, 0.16), transparent 58%),
    linear-gradient(135deg, var(--primary), var(--primary-dark));
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.2),
    0 12px 32px -12px var(--glow);
  color: var(--white);
  font: 600 25px/1 var(--mono);
  letter-spacing: -0.12em;
  text-indent: -0.12em;
}

.portfolio-loader__orbit-node {
  position: absolute;
  display: grid;
  min-width: 39px;
  height: 25px;
  place-items: center;
  padding: 0 7px;
  border: 1px solid color-mix(in srgb, var(--highlight) 28%, transparent);
  border-radius: 999px;
  background: color-mix(in srgb, var(--card) 88%, transparent);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.18);
  color: var(--highlight);
  font: 500 9px/1 var(--mono);
  animation: loader-node-float 3.2s ease-in-out infinite alternate;
}

.portfolio-loader__orbit-node--vue {
  top: 8%;
  left: 12%;
}

.portfolio-loader__orbit-node--laravel {
  top: 30%;
  right: 0;
  animation-delay: -1s;
}

.portfolio-loader__orbit-node--js {
  right: 13%;
  bottom: 7%;
  animation-delay: -2s;
}

.portfolio-loader__orbit-node--css {
  bottom: 27%;
  left: 0;
  animation-delay: -1.6s;
}

@keyframes loader-orbit-breathe {
  0%,
  100% {
    transform: scale(0.98);
  }

  50% {
    transform: scale(1.025);
  }
}

@keyframes loader-node-float {
  from {
    translate: 0 3px;
  }

  to {
    translate: 0 -3px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .portfolio-loader__orbit,
  .portfolio-loader__orbit-node {
    animation: none;
  }
}
</style>
