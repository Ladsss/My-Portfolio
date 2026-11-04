<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from "vue";

type Axis = "x" | "y";

interface PointerGesture {
  pointerId: number;
  startX: number;
  startY: number;
  startRotation: number;
  lastRotation: number;
  lastTime: number;
  velocity: number;
  moved: boolean;
}

const props = withDefaults(
  defineProps<{
    axis?: Axis;
    flipOnClick?: boolean;
    draggable?: boolean;
    tilt?: boolean;
    tiltMax?: number;
    glare?: boolean;
    glareOpacity?: number;
    hoverScale?: number;
    perspective?: number;
    width?: number;
    height?: number;
    radius?: number;
    background?: string;
    color?: string;
    shadow?: boolean;
    shadowColor?: string;
    shadowOpacity?: number;
    disabled?: boolean;
    ariaLabel?: string;
  }>(),
  {
    axis: "y",
    flipOnClick: true,
    draggable: true,
    tilt: true,
    tiltMax: 12,
    glare: true,
    glareOpacity: 0.22,
    hoverScale: 1.03,
    perspective: 1100,
    width: 300,
    height: 400,
    radius: 22,
    background: "#27272a",
    color: "#f5f5f5",
    shadow: true,
    shadowColor: "#000000",
    shadowOpacity: 0.45,
    disabled: false,
    ariaLabel: "Flip card",
  },
);

const emit = defineEmits<{
  flipChange: [flipped: boolean];
}>();

const rotation = ref(0);
const tiltX = ref(0);
const tiltY = ref(0);
const glareX = ref(50);
const glareY = ref(50);
const hover = ref(false);
const dragging = ref(false);
const reducedMotion = ref(false);
let gesture: PointerGesture | null = null;
let motionQuery: MediaQueryList | undefined;

const flipped = computed(() => Math.abs(Math.round(rotation.value / 180)) % 2 === 1);
const cardStyle = computed(() => ({
  "--flip-width": `${props.width}px`,
  "--flip-height": `${props.height}px`,
  "--flip-radius": `${props.radius}px`,
  "--flip-background": props.background,
  "--flip-color": props.color,
  "--flip-shadow-color": props.shadowColor,
  "--flip-shadow-opacity": props.shadowOpacity,
  "--flip-glare-opacity": props.glareOpacity,
  "--flip-perspective": `${props.perspective}px`,
  "--flip-rotation": `${rotation.value}deg`,
  "--flip-tilt-x": `${tiltX.value}deg`,
  "--flip-tilt-y": `${tiltY.value}deg`,
  "--flip-glare-x": `${glareX.value}%`,
  "--flip-glare-y": `${glareY.value}%`,
  "--flip-scale": hover.value && !props.disabled ? props.hoverScale : 1,
}));

function settle(nextRotation: number) {
  rotation.value = nextRotation;
  emit("flipChange", Math.abs(Math.round(nextRotation / 180)) % 2 === 1);
}

function flip() {
  settle(flipped.value ? 0 : 180);
}

function resetTilt() {
  tiltX.value = 0;
  tiltY.value = 0;
}

function onPointerDown(event: PointerEvent) {
  if (props.disabled || event.button !== 0) return;

  const element = event.currentTarget as HTMLElement;
  element.setPointerCapture(event.pointerId);
  gesture = {
    pointerId: event.pointerId,
    startX: event.clientX,
    startY: event.clientY,
    startRotation: rotation.value,
    lastRotation: rotation.value,
    lastTime: performance.now(),
    velocity: 0,
    moved: false,
  };
}

function onPointerMove(event: PointerEvent) {
  if (gesture?.pointerId === event.pointerId) {
    const distance = props.axis === "y"
      ? event.clientX - gesture.startX
      : gesture.startY - event.clientY;
    if (!gesture.moved && Math.abs(distance) < 4) return;

    if (!props.draggable || reducedMotion.value) {
      gesture.moved = true;
      return;
    }

    gesture.moved = true;
    dragging.value = true;
    resetTilt();
    const element = event.currentTarget as HTMLElement;
    const span = props.axis === "y" ? element.clientWidth : element.clientHeight;
    const nextRotation = gesture.startRotation + (distance / Math.max(span, 1)) * 180;
    const now = performance.now();
    const elapsed = now - gesture.lastTime;
    if (elapsed > 0) gesture.velocity = ((nextRotation - gesture.lastRotation) / elapsed) * 1000;
    gesture.lastRotation = nextRotation;
    gesture.lastTime = now;
    rotation.value = nextRotation;
    return;
  }

  if (!props.tilt || props.disabled || reducedMotion.value || event.pointerType === "touch") return;
  const rect = (event.currentTarget as HTMLElement).getBoundingClientRect();
  const x = Math.min(1, Math.max(0, (event.clientX - rect.left) / rect.width));
  const y = Math.min(1, Math.max(0, (event.clientY - rect.top) / rect.height));
  tiltX.value = (0.5 - y) * 2 * props.tiltMax;
  tiltY.value = (x - 0.5) * 2 * props.tiltMax;
  glareX.value = x * 100;
  glareY.value = y * 100;
}

function onPointerUp(event: PointerEvent, cancelled = false) {
  const activeGesture = gesture;
  if (!activeGesture || activeGesture.pointerId !== event.pointerId) return;

  gesture = null;
  dragging.value = false;
  const element = event.currentTarget as HTMLElement;
  if (element.hasPointerCapture(event.pointerId)) element.releasePointerCapture(event.pointerId);

  if (!activeGesture.moved) {
    if (!cancelled && props.flipOnClick) flip();
    return;
  }

  const current = rotation.value;
  const projected = cancelled
    ? activeGesture.startRotation
    : current + Math.min(0.35, Math.max(-0.35, activeGesture.velocity * 0.00016)) * 180;
  settle(Math.round(projected / 180) * 180);
}

function onPointerLeave() {
  hover.value = false;
  resetTilt();
}

function onKeyDown(event: KeyboardEvent) {
  if (props.disabled || (event.key !== "Enter" && event.key !== " ")) return;
  event.preventDefault();
  if (!event.repeat) flip();
}

onMounted(() => {
  motionQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
  reducedMotion.value = motionQuery.matches;
  motionQuery.addEventListener("change", onMotionPreferenceChange);
});

function onMotionPreferenceChange(event: MediaQueryListEvent) {
  reducedMotion.value = event.matches;
  if (event.matches) resetTilt();
}

onBeforeUnmount(() => {
  motionQuery?.removeEventListener("change", onMotionPreferenceChange);
});
</script>

<template>
  <div
    class="flip-card"
    :class="{
      'is-dragging': dragging,
      'is-disabled': disabled,
      'has-shadow': shadow,
      'has-glare': glare,
    }"
    :style="cardStyle"
    role="button"
    :tabindex="disabled ? -1 : 0"
    :aria-pressed="flipped"
    :aria-label="ariaLabel"
    :aria-disabled="disabled || undefined"
    @pointerdown="onPointerDown"
    @pointermove="onPointerMove"
    @pointerup="onPointerUp($event)"
    @pointercancel="onPointerUp($event, true)"
    @lostpointercapture="onPointerUp($event, true)"
    @pointerenter="hover = true"
    @pointerleave="onPointerLeave"
    @keydown="onKeyDown"
    @dragstart.prevent
  >
    <span v-if="shadow" class="flip-card__shadow" aria-hidden="true"></span>
    <div class="flip-card__tilt">
      <div class="flip-card__rotor" :class="`axis-${axis}`">
        <div class="flip-card__face flip-card__face--front" :aria-hidden="flipped">
          <slot name="front"></slot>
          <span v-if="glare" class="flip-card__glare" aria-hidden="true"></span>
        </div>
        <div class="flip-card__face flip-card__face--back" :aria-hidden="!flipped">
          <slot name="back"></slot>
          <span v-if="glare" class="flip-card__glare" aria-hidden="true"></span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.flip-card {
  position: relative;
  display: block;
  width: min(var(--flip-width), 100%);
  height: min(var(--flip-height), 100%);
  border-radius: var(--flip-radius);
  color: var(--flip-color);
  outline: none;
  cursor: none;
  user-select: none;
  -webkit-user-select: none;
  -webkit-tap-highlight-color: transparent;
  touch-action: pan-y;
  transform:
    perspective(var(--flip-perspective))
    scale(var(--flip-scale))
    rotateX(var(--flip-tilt-x))
    rotateY(var(--flip-tilt-y));
  transition: transform 360ms cubic-bezier(0.2, 0.8, 0.2, 1);

  &.is-dragging {
    cursor: none;
    transition: none;
  }

  &:focus-visible {
    outline: 2px solid #c4b5fd;
    outline-offset: 6px;
  }

  &.is-disabled {
    cursor: default;
    opacity: 0.65;
  }

  @media (hover: hover) and (pointer: fine) {
    &:not(.is-disabled) {
      cursor: none;
    }
  }
}

.flip-card__shadow {
  position: absolute;
  inset: 12% 9% -5%;
  border-radius: var(--flip-radius);
  background: color-mix(
    in srgb,
    var(--flip-shadow-color) calc(var(--flip-shadow-opacity) * 100%),
    transparent
  );
  filter: blur(22px);
  pointer-events: none;
}

.flip-card__tilt,
.flip-card__rotor {
  position: absolute;
  inset: 0;
  transform-style: preserve-3d;
}

.flip-card__rotor {
  transform: rotateY(var(--flip-rotation));
  transition: transform 620ms cubic-bezier(0.22, 1, 0.36, 1);

  &.axis-x {
    transform: rotateX(var(--flip-rotation));
  }

  .is-dragging & {
    transition: none;
  }
}

.flip-card__face {
  position: absolute;
  inset: 0;
  overflow: hidden;
  border: 1px solid rgba(196, 181, 253, 0.34);
  border-radius: var(--flip-radius);
  background: var(--flip-background);
  color: var(--flip-color);
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  box-shadow:
    inset 0 1px rgba(255, 255, 255, 0.16),
    0 24px 60px -30px rgba(0, 0, 0, 0.8);
}

.flip-card__face--back {
  transform: rotateY(180deg);
}

.axis-x .flip-card__face--back {
  transform: rotateX(180deg);
}

.flip-card__glare {
  position: absolute;
  inset: 0;
  background: radial-gradient(
    circle at var(--flip-glare-x) var(--flip-glare-y),
    rgba(255, 255, 255, var(--flip-glare-opacity)) 0%,
    rgba(255, 255, 255, 0.08) 28%,
    transparent 64%
  );
  opacity: 0;
  pointer-events: none;
  transition: opacity 180ms ease;

  .has-glare:hover & {
    opacity: 1;
  }
}

@media (prefers-reduced-motion: reduce) {
  .flip-card,
  .flip-card__rotor {
    transition-duration: 0.01ms;
  }
}
</style>
