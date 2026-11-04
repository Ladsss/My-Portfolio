<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from "vue";

interface CardData {
  title?: string;
  subtitle?: string;
  portrait?: string;
  footerText?: string;
}

const props = withDefaults(
  defineProps<{
    title?: string;
    subtitle?: string;
    footerText?: string;
    portrait?: string;
    dataUrl?: string;
  }>(),
  {
    title: "Find your perfect perfume",
    subtitle: "Fauget",
    footerText: "reallygreatsite.com",
    portrait: "Your text portrait goes here.",
    dataUrl: undefined,
  },
);

const emit = defineEmits<{
  flip: [flipped: boolean];
  "play-toggle": [playing: boolean];
  "like-toggle": [liked: boolean];
}>();

const isFlipped = ref(false);
const isFlipping = ref(false);
const isPlaying = ref(false);
const liked = ref(false);
const loading = ref(false);
const loadError = ref("");
const currentTime = ref(10 * 60 + 23);
const duration = 59 * 60 + 12;
const tiltX = ref(0);
const tiltY = ref(0);
const apiData = ref<CardData>({});

let playbackInterval: ReturnType<typeof setInterval> | undefined;
let flipTimeout: ReturnType<typeof setTimeout> | undefined;
let requestController: AbortController | undefined;

const displayTitle = computed(() => apiData.value.title || props.title);
const displaySubtitle = computed(() => apiData.value.subtitle || props.subtitle);
const displayFooterText = computed(() => apiData.value.footerText || props.footerText);
const displayPortrait = computed(() => apiData.value.portrait || props.portrait);
const progress = computed(() => (currentTime.value / duration) * 100);
const formattedCurrentTime = computed(() => formatTime(currentTime.value));
const formattedDuration = computed(() => formatTime(duration));
const cardStyle = computed(() => ({
  "--card-flip": isFlipped.value ? "180deg" : "0deg",
  "--card-tilt-x": `${tiltX.value}deg`,
  "--card-tilt-y": `${tiltY.value}deg`,
}));

function formatTime(seconds: number): string {
  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = seconds % 60;
  return `${minutes}:${String(remainingSeconds).padStart(2, "0")}`;
}

function toggleFlip() {
  if (isFlipping.value) return;

  isFlipping.value = true;
  isFlipped.value = !isFlipped.value;
  emit("flip", isFlipped.value);
  if (flipTimeout) clearTimeout(flipTimeout);
  flipTimeout = setTimeout(() => {
    isFlipping.value = false;
    if (isFlipped.value && isPlaying.value) {
      isPlaying.value = false;
      emit("play-toggle", false);
    }
  }, 800);
}

function onCardKeydown(event: KeyboardEvent) {
  if (event.target !== event.currentTarget || (event.key !== "Enter" && event.key !== " ")) {
    return;
  }
  event.preventDefault();
  toggleFlip();
}

function onMouseMove(event: MouseEvent) {
  if (isFlipping.value) return;
  const bounds = (event.currentTarget as HTMLElement).getBoundingClientRect();
  const x = (event.clientX - bounds.left) / bounds.width - 0.5;
  const y = (event.clientY - bounds.top) / bounds.height - 0.5;
  tiltX.value = -y * 5;
  tiltY.value = x * 5;
}

function resetTilt() {
  tiltX.value = 0;
  tiltY.value = 0;
}

function toggleLike() {
  liked.value = !liked.value;
  emit("like-toggle", liked.value);
}

function togglePlayback() {
  isPlaying.value = !isPlaying.value;
  emit("play-toggle", isPlaying.value);
}

function seekTo(event: Event) {
  currentTime.value = Number((event.target as HTMLInputElement).value);
}

function skip(seconds: number) {
  currentTime.value = Math.min(duration, Math.max(0, currentTime.value + seconds));
}

function isCardData(value: unknown): value is CardData {
  if (!value || typeof value !== "object") return false;
  return ["title", "subtitle", "portrait", "footerText"].every((key) => {
    const field = (value as Record<string, unknown>)[key];
    return field === undefined || typeof field === "string";
  });
}

async function loadCardData() {
  if (!props.dataUrl) return;

  requestController = new AbortController();
  loading.value = true;
  loadError.value = "";

  try {
    const response = await fetch(props.dataUrl, { signal: requestController.signal });
    if (!response.ok) throw new Error(`Card data request failed (${response.status}).`);

    const payload: unknown = await response.json();
    const data =
      payload && typeof payload === "object" && "data" in payload
        ? (payload as { data: unknown }).data
        : payload;
    if (!isCardData(data)) throw new Error("Card data response has an invalid shape.");
    apiData.value = data;
  } catch (error) {
    if (requestController.signal.aborted) return;
    loadError.value = "Card details couldn’t be loaded. Showing defaults.";
    console.error("Could not load flip-card player data.", error);
  } finally {
    if (!requestController.signal.aborted) loading.value = false;
  }
}

onMounted(() => {
  void loadCardData();
  playbackInterval = setInterval(() => {
    if (!isPlaying.value) return;
    if (currentTime.value >= duration) {
      currentTime.value = duration;
      isPlaying.value = false;
      emit("play-toggle", false);
      return;
    }
    currentTime.value += 1;
  }, 1000);
});

onBeforeUnmount(() => {
  if (playbackInterval) clearInterval(playbackInterval);
  if (flipTimeout) clearTimeout(flipTimeout);
  requestController?.abort();
});
</script>

<template>
  <div
    class="flip-card-player"
    :class="{ 'is-flipped': isFlipped, 'is-flipping': isFlipping }"
    :style="cardStyle"
    role="group"
    tabindex="0"
    :aria-label="`${displayTitle} music card. Press Enter or Space to flip.`"
    :aria-expanded="isFlipped"
    @click="toggleFlip"
    @keydown="onCardKeydown"
    @mousemove="onMouseMove"
    @mouseleave="resetTilt"
  >
    <div class="flip-card-player__inner">
      <section
        class="flip-card-player__face flip-card-player__front"
        aria-label="Music card"
        :aria-hidden="isFlipped"
        :inert="isFlipped"
      >
        <div class="flip-card-player__art">
          <slot name="portrait" :portrait="displayPortrait">
            <pre class="flip-card-player__portrait">{{ displayPortrait }}</pre>
          </slot>
        </div>

        <div class="flip-card-player__track">
          <div class="flip-card-player__track-copy">
            <h2>{{ displayTitle }}</h2>
            <p>{{ displaySubtitle }}</p>
          </div>
          <button
            class="flip-card-player__icon-button flip-card-player__like"
            type="button"
            :aria-label="liked ? 'Unlike track' : 'Like track'"
            :aria-pressed="liked"
            @click.stop="toggleLike"
          >
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path
                d="M12 21s-8.5-5.3-8.5-11.2A4.8 4.8 0 0 1 12 6.7a4.8 4.8 0 0 1 8.5 3.1C20.5 15.7 12 21 12 21Z"
                :fill="liked ? 'currentColor' : 'none'"
              />
            </svg>
          </button>
        </div>

        <div class="flip-card-player__timeline" @click.stop>
          <input
            class="flip-card-player__seek"
            type="range"
            min="0"
            :max="duration"
            :value="currentTime"
            :style="{ '--seek-progress': `${progress}%` }"
            :aria-label="`Track progress: ${formattedCurrentTime} of ${formattedDuration}`"
            @input="seekTo"
          />
          <div class="flip-card-player__time-labels">
            <span>{{ formattedCurrentTime }}</span>
            <span>{{ formattedDuration }}</span>
          </div>
        </div>

        <div class="flip-card-player__controls">
          <button
            class="flip-card-player__icon-button"
            type="button"
            aria-label="Rewind 10 seconds"
            @click.stop="skip(-10)"
          >
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M11 5v14L2 12l9-7Zm11 0v14l-9-7 9-7Z" fill="currentColor" />
            </svg>
          </button>
          <button
            class="flip-card-player__play"
            type="button"
            :aria-label="isPlaying ? 'Pause' : 'Play'"
            :aria-pressed="isPlaying"
            @click.stop="togglePlayback"
          >
            <svg v-if="!isPlaying" viewBox="0 0 24 24" aria-hidden="true">
              <path d="m8 5 12 7-12 7V5Z" fill="currentColor" />
            </svg>
            <svg v-else viewBox="0 0 24 24" aria-hidden="true">
              <path d="M7 5h4v14H7zM14 5h4v14h-4z" fill="currentColor" />
            </svg>
          </button>
          <button
            class="flip-card-player__icon-button"
            type="button"
            aria-label="Forward 10 seconds"
            @click.stop="skip(10)"
          >
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M13 5v14l9-7-9-7ZM2 5v14l9-7-9-7Z" fill="currentColor" />
            </svg>
          </button>
        </div>

        <p v-if="loading" class="flip-card-player__status" role="status">Loading card details…</p>
        <p v-else-if="loadError" class="flip-card-player__status" role="status">{{ loadError }}</p>
        <footer class="flip-card-player__footer">{{ displayFooterText }}</footer>
      </section>

      <section
        class="flip-card-player__face flip-card-player__back"
        aria-label="Card details"
        :aria-hidden="!isFlipped"
        :inert="!isFlipped"
      >
        <slot name="back">
          <p>
            A little more about this card goes here. Add a bio, story, or any other
            details in the back slot.
          </p>
          <span class="flip-card-player__back-hint">Click to return</span>
        </slot>
      </section>
    </div>
  </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap");

.flip-card-player {
  width: min(100%, 360px);
  aspect-ratio: 4 / 5;
  margin-inline: auto;
  perspective: 1200px;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
  outline: none;
}

.flip-card-player:focus-visible {
  outline: 2px solid #f3ead9;
  outline-offset: 6px;
  border-radius: 22px;
}

.flip-card-player__inner {
  position: relative;
  width: 100%;
  height: 100%;
  transform-style: preserve-3d;
  transform:
    rotateX(var(--card-tilt-x, 0deg))
    rotateY(calc(var(--card-flip, 0deg) + var(--card-tilt-y, 0deg)));
  transition: transform 800ms ease;
}

.flip-card-player__face {
  position: absolute;
  inset: 0;
  overflow: hidden;
  border: 1px solid rgba(243, 234, 217, 0.22);
  border-radius: 22px;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  box-shadow: 0 24px 60px rgba(4, 12, 8, 0.36);
  color: #f3ead9;
  font-family: "Space Mono", monospace;
}

.flip-card-player__front {
  display: flex;
  flex-direction: column;
  padding: 8% 9% 6%;
  background:
    radial-gradient(ellipse at 18% 18%, rgba(196, 184, 136, 0.42), transparent 36%),
    radial-gradient(ellipse at 84% 24%, rgba(111, 134, 94, 0.52), transparent 42%),
    linear-gradient(145deg, #263d30 0%, #52634a 46%, #1f3d2f 100%);
  isolation: isolate;
}

.flip-card-player__front::before {
  position: absolute;
  z-index: -1;
  inset: 0;
  background:
    repeating-radial-gradient(
      circle at 10% 12%,
      rgba(243, 234, 217, 0.09) 0,
      rgba(243, 234, 217, 0.09) 0.5px,
      transparent 0.8px,
      transparent 3px
    ),
    repeating-linear-gradient(
      118deg,
      transparent 0,
      rgba(12, 26, 19, 0.12) 1px,
      transparent 3px,
      transparent 8px
    );
  content: "";
  opacity: 0.22;
  pointer-events: none;
}

.flip-card-player__front::after {
  position: absolute;
  z-index: -1;
  inset: 0;
  background: linear-gradient(180deg, rgba(14, 24, 18, 0.1), transparent 40%, rgba(12, 22, 16, 0.28));
  content: "";
  pointer-events: none;
}

.flip-card-player__art {
  display: grid;
  width: 82%;
  aspect-ratio: 1;
  flex: 0 0 auto;
  place-items: center;
  align-self: center;
  overflow: hidden;
  border: 1px solid rgba(48, 68, 51, 0.18);
  border-radius: 20px;
  background: #f3ead9;
  box-shadow: 0 14px 32px rgba(9, 17, 12, 0.32);
  color: #1f3d2f;
}

.flip-card-player__portrait {
  width: 100%;
  max-height: 100%;
  margin: 0;
  overflow: hidden;
  color: #1f3d2f;
  font: 400 clamp(4px, 1.25vw, 7px)/1.15 "Space Mono", monospace;
  text-align: center;
  white-space: pre;
}

.flip-card-player__track {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 12px;
  margin-top: 6%;
}

.flip-card-player__track-copy {
  min-width: 0;
  flex: 1;
}

.flip-card-player__track-copy h2,
.flip-card-player__track-copy p {
  overflow: hidden;
  margin: 0;
  font: 400 clamp(10px, 3.2vw, 15px)/1.5 "Space Mono", monospace;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.flip-card-player__track-copy p {
  opacity: 0.86;
}

.flip-card-player__icon-button {
  display: grid;
  width: 32px;
  height: 32px;
  flex: 0 0 auto;
  place-items: center;
  padding: 0;
  border: 0;
  background: transparent;
  color: #f3ead9;
  cursor: pointer;
}

.flip-card-player__icon-button svg {
  width: 19px;
  height: 19px;
  stroke: currentColor;
  stroke-width: 1.2;
}

.flip-card-player__like[aria-pressed="true"] {
  color: #f1b3aa;
}

.flip-card-player__timeline {
  margin-top: 4%;
}

.flip-card-player__seek {
  display: block;
  width: 100%;
  height: 14px;
  margin: 0;
  appearance: none;
  background: transparent;
  cursor: pointer;
}

.flip-card-player__seek::-webkit-slider-runnable-track {
  height: 1.5px;
  background: linear-gradient(
    to right,
    #f3ead9 0,
    #f3ead9 var(--seek-progress),
    rgba(243, 234, 217, 0.48) var(--seek-progress),
    rgba(243, 234, 217, 0.48) 100%
  );
}

.flip-card-player__seek::-moz-range-track {
  height: 1.5px;
  background: rgba(243, 234, 217, 0.48);
}

.flip-card-player__seek::-moz-range-progress {
  height: 1.5px;
  background: #f3ead9;
}

.flip-card-player__seek::-webkit-slider-thumb {
  width: 8px;
  height: 8px;
  margin-top: -3.25px;
  appearance: none;
  border: 1px solid #f3ead9;
  border-radius: 50%;
  background: #f3ead9;
}

.flip-card-player__seek::-moz-range-thumb {
  width: 7px;
  height: 7px;
  border: 1px solid #f3ead9;
  border-radius: 50%;
  background: #f3ead9;
}

.flip-card-player__seek:focus-visible {
  outline: 1px solid #f3ead9;
  outline-offset: 2px;
}

.flip-card-player__time-labels {
  display: flex;
  justify-content: space-between;
  margin-top: 2px;
  font: 400 10px/1.4 "Space Mono", monospace;
}

.flip-card-player__controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 18px;
  margin-top: 3%;
}

.flip-card-player__play {
  display: grid;
  width: 42px;
  height: 42px;
  place-items: center;
  padding: 0;
  border: 0;
  border-radius: 50%;
  background: #f3ead9;
  color: #1f2d24;
  cursor: pointer;
}

.flip-card-player__play svg {
  width: 20px;
  height: 20px;
}

.flip-card-player__footer {
  margin-top: auto;
  padding-top: 3%;
  font: 400 9px/1.4 "Space Mono", monospace;
  text-align: center;
}

.flip-card-player__status {
  margin: 5px 0 0;
  color: #f3ead9;
  font: 400 8px/1.4 "Space Mono", monospace;
  text-align: center;
}

.flip-card-player__back {
  display: grid;
  place-items: center;
  padding: 12%;
  background:
    radial-gradient(ellipse at 80% 10%, rgba(117, 132, 86, 0.32), transparent 42%),
    linear-gradient(145deg, #1f3d2f, #17271f);
  transform: rotateY(180deg);
  text-align: center;
}

.flip-card-player__back :deep(p) {
  margin: 0;
  color: #f3ead9;
  font: 400 12px/1.8 "Space Mono", monospace;
}

.flip-card-player__back-hint {
  position: absolute;
  bottom: 8%;
  color: rgba(243, 234, 217, 0.72);
  font: 400 8px/1.4 "Space Mono", monospace;
}

.flip-card-player button:focus-visible {
  outline: 2px solid #f3ead9;
  outline-offset: 3px;
}

@media (max-width: 380px) {
  .flip-card-player__front {
    padding-inline: 8%;
  }

  .flip-card-player__art {
    width: 78%;
    border-radius: 16px;
  }

  .flip-card-player__controls {
    gap: 12px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .flip-card-player__inner {
    transition-duration: 0.01ms;
  }
}
</style>
