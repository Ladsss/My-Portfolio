<script setup lang="ts">
import { onMounted, onUnmounted, ref } from "vue";

interface Particle {
  x: number;
  y: number;
  vx: number;
  vy: number;
  radius: number;
  alpha: number;
}

interface NavigatorCapabilities extends Navigator {
  deviceMemory?: number;
  connection?: {
    saveData?: boolean;
  };
}

const canvasElement = ref<HTMLCanvasElement | null>(null);
const isEnabled = ref(false);

const particles: Particle[] = [];
const pointer = { x: 0, y: 0, active: false };
let canvasContext: CanvasRenderingContext2D | null = null;
let animationFrame = 0;
let lastFrameTime = 0;
let lastPointerTime = 0;
let pulseStartedAt = 0;
let width = 0;
let height = 0;
let pixelRatio = 1;
let frameInterval = 0;
let reducedMotionQuery: MediaQueryList | null = null;
let isPageVisible = true;

const capabilities = navigator as NavigatorCapabilities;
const isLowPerformanceDevice =
  (capabilities.deviceMemory !== undefined && capabilities.deviceMemory <= 4) ||
  (navigator.hardwareConcurrency > 0 && navigator.hardwareConcurrency <= 4) ||
  capabilities.connection?.saveData === true;
const shouldDisableEffects =
  (capabilities.deviceMemory !== undefined && capabilities.deviceMemory <= 2) ||
  (navigator.hardwareConcurrency > 0 && navigator.hardwareConcurrency <= 2) ||
  capabilities.connection?.saveData === true;

function particleCountForViewport() {
  const area = width * height;
  const isMobile = width < 600;
  const isTablet = width < 900;
  const count = Math.floor(
    area / (isMobile ? 24000 : isTablet ? 20000 : 13500),
  );
  const maxCount = isLowPerformanceDevice
    ? 24
    : isMobile
      ? 36
      : isTablet
        ? 65
        : 100;

  return Math.max(12, Math.min(count, maxCount));
}

function createParticle(): Particle {
  const angle = Math.random() * Math.PI * 2;
  const speed = 0.08 + Math.random() * (isLowPerformanceDevice ? 0.12 : 0.2);

  return {
    x: Math.random() * width,
    y: Math.random() * height,
    vx: Math.cos(angle) * speed,
    vy: Math.sin(angle) * speed,
    radius: 0.7 + Math.random() * 1.2,
    alpha: 0.28 + Math.random() * 0.42,
  };
}

function resizeCanvas() {
  const canvas = canvasElement.value;
  if (!canvas || !canvasContext) return;

  width = window.innerWidth;
  height = window.innerHeight;
  pixelRatio = Math.min(window.devicePixelRatio || 1, 1.5);
  canvas.width = Math.round(width * pixelRatio);
  canvas.height = Math.round(height * pixelRatio);
  canvasContext.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);

  const count = particleCountForViewport();
  while (particles.length < count) particles.push(createParticle());
  particles.length = Math.min(particles.length, count);
}

function updatePointer(event: PointerEvent) {
  pointer.x = event.clientX;
  pointer.y = event.clientY;
  pointer.active = true;
  lastPointerTime = performance.now();
}

function startClickPulse(event: PointerEvent) {
  pointer.x = event.clientX;
  pointer.y = event.clientY;
  pointer.active = true;
  lastPointerTime = performance.now();
  pulseStartedAt = lastPointerTime;
}

function drawFrame(timestamp: number) {
  animationFrame = 0;
  if (!canvasContext || !isEnabled.value || !isPageVisible) return;
  if (frameInterval && timestamp - lastFrameTime < frameInterval) {
    animationFrame = requestAnimationFrame(drawFrame);
    return;
  }

  const elapsed = Math.min((timestamp - (lastFrameTime || timestamp)) / 16.67, 2);
  lastFrameTime = timestamp;
  canvasContext.clearRect(0, 0, width, height);

  if (timestamp - lastPointerTime > 1800) pointer.active = false;

  const pulseElapsed = timestamp - pulseStartedAt;
  const isPulseActive =
    pulseStartedAt > 0 && pulseElapsed >= 0 && pulseElapsed < 900;
  const pulseProgress = isPulseActive
    ? Math.max(0, Math.min(pulseElapsed / 900, 1))
    : 1;
  const pulseRadius = pulseProgress * 300;
  const interactionRadius = width < 600 ? 90 : 130;

  for (const particle of particles) {
    if (pointer.active) {
      const dx = particle.x - pointer.x;
      const dy = particle.y - pointer.y;
      const distance = Math.max(Math.hypot(dx, dy), 1);
      if (distance < interactionRadius) {
        const force = (1 - distance / interactionRadius) * 0.018 * elapsed;
        particle.vx += (dx / distance) * force;
        particle.vy += (dy / distance) * force;
      }

      if (isPulseActive) {
        const pulseDistance = Math.abs(distance - pulseRadius);
        if (pulseDistance < 34) {
          const force =
            (1 - pulseDistance / 34) * 0.12 * (1 - pulseProgress) * elapsed;
          particle.vx += (dx / distance) * force;
          particle.vy += (dy / distance) * force;
        }
      }
    }

    particle.vx *= 0.995;
    particle.vy *= 0.995;
    particle.x += particle.vx * elapsed;
    particle.y += particle.vy * elapsed;

    if (particle.x < -4) particle.x = width + 4;
    else if (particle.x > width + 4) particle.x = -4;
    if (particle.y < -4) particle.y = height + 4;
    else if (particle.y > height + 4) particle.y = -4;
  }

  const connectionDistance = width < 600 ? 82 : 112;
  const connectionDistanceSquared = connectionDistance * connectionDistance;
  canvasContext.beginPath();
  for (let i = 0; i < particles.length; i += 1) {
    const particle = particles[i];
    for (let j = i + 1; j < particles.length; j += 1) {
      const nearby = particles[j];
      const dx = particle.x - nearby.x;
      const dy = particle.y - nearby.y;
      if (dx * dx + dy * dy < connectionDistanceSquared) {
        canvasContext.moveTo(particle.x, particle.y);
        canvasContext.lineTo(nearby.x, nearby.y);
      }
    }
  }
  canvasContext.strokeStyle = "rgba(167, 139, 250, 0.12)";
  canvasContext.lineWidth = 0.7;
  canvasContext.stroke();

  for (const particle of particles) {
    canvasContext.beginPath();
    canvasContext.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
    canvasContext.fillStyle = `rgba(196, 181, 253, ${particle.alpha})`;
    canvasContext.fill();
  }

  if (isPulseActive) {
    canvasContext.beginPath();
    canvasContext.arc(pointer.x, pointer.y, pulseRadius, 0, Math.PI * 2);
    canvasContext.strokeStyle = `rgba(167, 139, 250, ${(1 - pulseProgress) * 0.18})`;
    canvasContext.lineWidth = 1;
    canvasContext.stroke();
  }

  animationFrame = requestAnimationFrame(drawFrame);
}

function updateMotionPreference() {
  if (!reducedMotionQuery) return;
  const shouldEnable = !reducedMotionQuery.matches && !shouldDisableEffects;
  isEnabled.value = shouldEnable;

  if (!shouldEnable) {
    cancelAnimationFrame(animationFrame);
    animationFrame = 0;
    canvasContext?.clearRect(0, 0, width, height);
    return;
  }

  resizeCanvas();
  if (isPageVisible && !animationFrame) {
    lastFrameTime = 0;
    animationFrame = requestAnimationFrame(drawFrame);
  }
}

function handleVisibilityChange() {
  isPageVisible = document.visibilityState === "visible";
  if (!isPageVisible) {
    cancelAnimationFrame(animationFrame);
    animationFrame = 0;
    return;
  }

  if (isEnabled.value && !animationFrame) {
    lastFrameTime = 0;
    animationFrame = requestAnimationFrame(drawFrame);
  }
}

onMounted(() => {
  const canvas = canvasElement.value;
  canvasContext = canvas?.getContext("2d", { alpha: true }) ?? null;
  if (!canvasContext) return;

  reducedMotionQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
  frameInterval = isLowPerformanceDevice ? 1000 / 30 : 0;

  window.addEventListener("resize", resizeCanvas, { passive: true });
  window.addEventListener("pointermove", updatePointer, { passive: true });
  window.addEventListener("pointerdown", startClickPulse, { passive: true });
  document.addEventListener("visibilitychange", handleVisibilityChange);
  reducedMotionQuery.addEventListener("change", updateMotionPreference);
  updateMotionPreference();
});

onUnmounted(() => {
  cancelAnimationFrame(animationFrame);
  window.removeEventListener("resize", resizeCanvas);
  window.removeEventListener("pointermove", updatePointer);
  window.removeEventListener("pointerdown", startClickPulse);
  document.removeEventListener("visibilitychange", handleVisibilityChange);
  reducedMotionQuery?.removeEventListener("change", updateMotionPreference);
});
</script>

<template>
  <canvas
    v-show="isEnabled"
    ref="canvasElement"
    class="particle-background"
    aria-hidden="true"
  />
</template>
