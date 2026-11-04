<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from "vue";

interface Position {
  x: number;
  y: number;
}

interface SpringState {
  current: number;
  target: number;
  velocity: number;
}

interface SpringConfig {
  stiffness: number;
  damping: number;
  mass: number;
}

const DESKTOP_POINTER_QUERY = "(any-hover: hover) and (any-pointer: fine)";

const isEnabled = ref(false);
const isVisible = ref(false);
const cursorElement = ref<HTMLDivElement | null>(null);

// Spring configs matching the MagicUI specifications
const posConfig: SpringConfig = { stiffness: 400, damping: 45, mass: 1 };
const rotConfig: SpringConfig = { stiffness: 300, damping: 60, mass: 1 };
const scaleConfig: SpringConfig = { stiffness: 500, damping: 35, mass: 1 };

// Live spring states
const springX: SpringState = { current: 0, target: 0, velocity: 0 };
const springY: SpringState = { current: 0, target: 0, velocity: 0 };
const springRotation: SpringState = { current: 0, target: 0, velocity: 0 };
const springScale: SpringState = { current: 1, target: 1, velocity: 0 };

const lastMousePos = { x: 0, y: 0 };
const mouseVelocity = { x: 0, y: 0 };
let lastUpdateTime = performance.now();
let lastFrameTime = performance.now();
let previousAngle = 0;
let accumulatedRotation = 0;
let scaleTimeout: ReturnType<typeof setTimeout> | null = null;
let rafId = 0;
let moveRafId = 0;

// Damped harmonic oscillator simulation (identical to Framer Motion's useSpring)
function stepSpring(state: SpringState, config: SpringConfig, dt: number) {
  const force = -config.stiffness * (state.current - state.target) - config.damping * state.velocity;
  const accel = force / config.mass;
  state.velocity += accel * dt;
  state.current += state.velocity * dt;
}

function updateVelocity(currentPos: Position) {
  const currentTime = performance.now();
  const deltaTime = currentTime - lastUpdateTime;

  if (deltaTime > 0) {
    mouseVelocity.x = (currentPos.x - lastMousePos.x) / deltaTime;
    mouseVelocity.y = (currentPos.y - lastMousePos.y) / deltaTime;
  }

  lastUpdateTime = currentTime;
  lastMousePos.x = currentPos.x;
  lastMousePos.y = currentPos.y;
}

function handlePointer(e: PointerEvent) {
  if (e.pointerType === "touch") return;

  if (!isVisible.value) {
    isVisible.value = true;
    springX.current = e.clientX;
    springY.current = e.clientY;
  }

  const currentPos = { x: e.clientX, y: e.clientY };
  updateVelocity(currentPos);

  springX.target = currentPos.x;
  springY.target = currentPos.y;

  const speed = Math.hypot(mouseVelocity.x, mouseVelocity.y);

  if (speed > 0.1) {
    const currentAngle = Math.atan2(mouseVelocity.y, mouseVelocity.x) * (180 / Math.PI) + 90;

    let angleDiff = currentAngle - previousAngle;
    if (angleDiff > 180) angleDiff -= 360;
    if (angleDiff < -180) angleDiff += 360;

    accumulatedRotation += angleDiff;
    springRotation.target = accumulatedRotation;
    previousAngle = currentAngle;

    springScale.target = 0.95;

    if (scaleTimeout !== null) {
      clearTimeout(scaleTimeout);
    }

    scaleTimeout = setTimeout(() => {
      springScale.target = 1;
    }, 150);
  }
}

function onPointerMove(e: PointerEvent) {
  if (e.pointerType === "touch") return;
  if (moveRafId) return;

  moveRafId = requestAnimationFrame(() => {
    handlePointer(e);
    moveRafId = 0;
  });
}

function renderLoop(now: number) {
  const delta = Math.min((now - lastFrameTime) / 1000, 0.032);
  lastFrameTime = now;

  // Substep for physics stability
  const substeps = 4;
  const subDt = delta / substeps;

  for (let i = 0; i < substeps; i++) {
    stepSpring(springX, posConfig, subDt);
    stepSpring(springY, posConfig, subDt);
    stepSpring(springRotation, rotConfig, subDt);
    stepSpring(springScale, scaleConfig, subDt);
  }

  if (cursorElement.value) {
    cursorElement.value.style.transform = `translate3d(${springX.current}px, ${springY.current}px, 0) translate(-50%, -50%) rotate(${springRotation.current}deg) scale(${springScale.current})`;
  }

  rafId = requestAnimationFrame(renderLoop);
}

onMounted(() => {
  const mediaQuery = window.matchMedia(DESKTOP_POINTER_QUERY);

  const updateEnabled = () => {
    isEnabled.value = mediaQuery.matches;
    if (!isEnabled.value) {
      isVisible.value = false;
      document.body.style.cursor = "auto";
    } else {
      document.body.style.cursor = "none";
    }
  };

  updateEnabled();
  mediaQuery.addEventListener("change", updateEnabled);

  if (isEnabled.value) {
    document.body.style.cursor = "none";
    window.addEventListener("pointermove", onPointerMove, { passive: true });
    lastFrameTime = performance.now();
    rafId = requestAnimationFrame(renderLoop);
  }

  onBeforeUnmount(() => {
    mediaQuery.removeEventListener("change", updateEnabled);
    window.removeEventListener("pointermove", onPointerMove);
    document.body.style.cursor = "auto";

    if (rafId) cancelAnimationFrame(rafId);
    if (moveRafId) cancelAnimationFrame(moveRafId);
    if (scaleTimeout !== null) clearTimeout(scaleTimeout);
  });
});
</script>

<template>
  <div
    v-if="isEnabled"
    ref="cursorElement"
    class="magicui-smooth-cursor"
    :style="{ opacity: isVisible ? 1 : 0 }"
    aria-hidden="true"
  >
    <slot>
      <!-- Exact MagicUI Default SVG with Drop Shadow Filter -->
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="50"
        height="54"
        viewBox="0 0 50 54"
        fill="none"
        class="cursor-svg"
      >
        <g filter="url(#filter0_d_91_7928)">
          <path
            d="M42.6817 41.1495L27.5103 6.79925C26.7269 5.02557 24.2082 5.02558 23.3927 6.79925L7.59814 41.1495C6.75833 42.9759 8.52712 44.8902 10.4125 44.1954L24.3757 39.0496C24.8829 38.8627 25.4385 38.8627 25.9422 39.0496L39.8121 44.1954C41.6849 44.8902 43.4884 42.9759 42.6817 41.1495Z"
            fill="black"
          />
          <path
            d="M43.7146 40.6933L28.5431 6.34306C27.3556 3.65428 23.5772 3.69516 22.3668 6.32755L6.57226 40.6778C5.3134 43.4156 7.97238 46.298 10.803 45.2549L24.7662 40.109C25.0221 40.0147 25.2999 40.0156 25.5494 40.1082L39.4193 45.254C42.2261 46.2953 44.9254 43.4347 43.7146 40.6933Z"
            stroke="white"
            stroke-width="2.25825"
          />
        </g>
        <defs>
          <filter
            id="filter0_d_91_7928"
            x="0.602397"
            y="0.952444"
            width="49.0584"
            height="52.428"
            filterUnits="userSpaceOnUse"
            color-interpolation-filters="sRGB"
          >
            <feFlood flood-opacity="0" result="BackgroundImageFix" />
            <feColorMatrix
              in="SourceAlpha"
              type="matrix"
              values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
              result="hardAlpha"
            />
            <feOffset dy="2.25825" />
            <feGaussianBlur stdDeviation="2.25825" />
            <feComposite in2="hardAlpha" operator="out" />
            <feColorMatrix
              type="matrix"
              values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.08 0"
            />
            <feBlend
              mode="normal"
              in2="BackgroundImageFix"
              result="effect1_dropShadow_91_7928"
            />
            <feBlend
              mode="normal"
              in="SourceGraphic"
              in2="effect1_dropShadow_91_7928"
              result="shape"
            />
          </filter>
        </defs>
      </svg>
    </slot>
  </div>
</template>

<style scoped>
.magicui-smooth-cursor {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 3000;
  pointer-events: none;
  will-change: transform, opacity;
  transition: opacity 0.15s ease-out;
  transform-origin: center center;
}

.cursor-svg {
  transform: scale(0.5);
  transform-origin: center center;
  display: block;
}
</style>