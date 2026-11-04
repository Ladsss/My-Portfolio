<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from "vue";
import trackArtwork from "../assets/mjoy.png";
import trackAudio from "../assets/Mariah Deborah - Na Para Bang (Official Lyric Visualizer).mp3";

const audioRef = ref<HTMLAudioElement | null>(null);
const isPlaying = ref(false);
const isFavorite = ref(false);
const currentTime = ref(0);
const duration = ref(0);
const playbackError = ref("");
const shareStatus = ref("");

const formattedCurrentTime = computed(() => formatTime(currentTime.value));
const formattedDuration = computed(() => formatTime(duration.value));
const seekStyle = computed(() => ({
  "--seek-progress": `${duration.value ? (currentTime.value / duration.value) * 100 : 0}%`,
}));

function formatTime(seconds: number): string {
  if (!Number.isFinite(seconds) || seconds < 0) return "0:00";
  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = Math.floor(seconds % 60);
  return `${minutes}:${String(remainingSeconds).padStart(2, "0")}`;
}

async function togglePlayback() {
  const audio = audioRef.value;
  if (!audio) return;

  playbackError.value = "";
  if (audio.paused) {
    try {
      await audio.play();
      isPlaying.value = true;
    } catch (error) {
      playbackError.value = "Playback couldn’t start. Please try again.";
      console.error("Could not start portfolio card audio.", error);
    }
    return;
  }

  audio.pause();
  isPlaying.value = false;
}

function seek(event: Event) {
  const audio = audioRef.value;
  if (!audio || !Number.isFinite(audio.duration)) return;
  const target = Number((event.target as HTMLInputElement).value);
  audio.currentTime = target;
  currentTime.value = target;
}

function skip(seconds: number) {
  const audio = audioRef.value;
  if (!audio || !Number.isFinite(audio.duration)) return;
  audio.currentTime = Math.min(audio.duration, Math.max(0, audio.currentTime + seconds));
  currentTime.value = audio.currentTime;
}

async function copyShareLink(text: string, url: string) {
  if (!navigator.clipboard?.writeText) {
    shareStatus.value = "Sharing and clipboard access are unavailable in this browser.";
    return;
  }

  try {
    await navigator.clipboard.writeText(`${text} ${url}`);
    shareStatus.value = "Portfolio link copied.";
  } catch (error) {
    shareStatus.value = "Could not copy the portfolio link.";
    console.error("Could not copy portfolio share link.", error);
  }
}

async function shareTrack() {
  const shareData = {
    title: "Na Para Bang — Mariah Deborah",
    text: "Listen to Na Para Bang on my portfolio.",
    url: window.location.href,
  };
  shareStatus.value = "";

  if (!navigator.share) {
    await copyShareLink(shareData.text, shareData.url);
    return;
  }

  try {
    await navigator.share(shareData);
    shareStatus.value = "Shared successfully.";
  } catch (error) {
    if (error instanceof DOMException && error.name === "AbortError") return;
    console.error("Could not share portfolio track.", error);
    await copyShareLink(shareData.text, shareData.url);
  }
}

function updateDuration() {
  duration.value = audioRef.value?.duration ?? 0;
}

function updateCurrentTime(event: Event) {
  if (event.currentTarget instanceof HTMLAudioElement) {
    currentTime.value = event.currentTarget.currentTime;
  }
}

function onAudioEnded() {
  isPlaying.value = false;
  currentTime.value = 0;
}

function onAudioError() {
  isPlaying.value = false;
  playbackError.value = "This track couldn’t be loaded.";
}

onBeforeUnmount(() => {
  audioRef.value?.pause();
});
</script>

<template>
  <section
    class="flip-card-music-player"
    aria-label="Na Parang Bang music player"
    @pointerdown.stop
    @pointerup.stop
    @pointercancel.stop
    @click.stop
    @keydown.stop
    @dragstart.stop
  >
    <audio
      ref="audioRef"
      :src="trackAudio"
      preload="metadata"
      @loadedmetadata="updateDuration"
      @durationchange="updateDuration"
      @timeupdate="updateCurrentTime"
      @play="isPlaying = true"
      @pause="isPlaying = false"
      @ended="onAudioEnded"
      @error="onAudioError"
    ></audio>

    <div class="flip-card-music-player__track">
      <div class="flip-card-music-player__details">
        <strong>Na Para Bang</strong>
      </div>
    </div>

    <div class="flip-card-music-player__times">
      <span aria-hidden="true">{{ formattedCurrentTime }}</span>
      <input
        class="flip-card-music-player__seek"
        type="range"
        min="0"
        :max="duration || 0"
        :value="currentTime"
        :disabled="!duration"
        :style="seekStyle"
        :aria-label="`Seek track, ${formattedCurrentTime} of ${formattedDuration}`"
        @input="seek"
      />
      <span aria-hidden="true">{{ formattedDuration }}</span>
    </div>

    <div class="flip-card-music-player__controls" role="group" aria-label="Playback controls">
      <button
        class="flip-card-music-player__share"
        type="button"
        aria-label="Share portfolio track"
        @click="shareTrack"
      >
        <v-icon icon="mdi-share-variant" size="19" aria-hidden="true" />
      </button>
      <button
        type="button"
        aria-label="Previous, back 10 seconds"
        :disabled="!duration"
        @click="skip(-10)"
      >
        <v-icon icon="mdi-skip-previous" size="22" aria-hidden="true" />
      </button>
      <button
        class="flip-card-music-player__play"
        type="button"
        :aria-label="isPlaying ? 'Pause track' : 'Play track'"
        @click="togglePlayback"
      >
        <v-icon
          :icon="isPlaying ? 'mdi-pause' : 'mdi-play'"
          size="22"
          aria-hidden="true"
        />
      </button>
      <button
        type="button"
        aria-label="Next, forward 10 seconds"
        :disabled="!duration"
        @click="skip(10)"
      >
        <v-icon icon="mdi-skip-next" size="22" aria-hidden="true" />
      </button>
      <button
        class="flip-card-music-player__favorite"
        type="button"
        :aria-label="isFavorite ? 'Remove from favorites' : 'Add to favorites'"
        :aria-pressed="isFavorite"
        @click="isFavorite = !isFavorite"
      >
        <v-icon
          :icon="isFavorite ? 'mdi-heart' : 'mdi-heart-outline'"
          size="18"
          aria-hidden="true"
        />
      </button>
    </div>

    <p v-if="playbackError" class="flip-card-music-player__error" role="status">
      {{ playbackError }}
    </p>
    <p v-if="shareStatus" class="flip-card-music-player__status" role="status">
      {{ shareStatus }}
    </p>
  </section>
</template>

<style scoped lang="scss">
.flip-card-music-player {
  position: absolute;
  z-index: 3;
  right: 16px;
  bottom: 16px;
  left: 16px;
  display: grid;
  gap: 8px;
  padding: 13px 14px 12px;
  border: 0;
  border-radius: 0;
  background: transparent;
  box-shadow: none;
  color: #fff;
  backdrop-filter: none;
  -webkit-backdrop-filter: none;
}

.flip-card-music-player audio {
  display: none;
}

.flip-card-music-player__track {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 9px;
}

.flip-card-music-player__artwork {
  width: 44px;
  height: 44px;
  flex: 0 0 44px;
  border: 1px solid rgba(196, 181, 253, 0.3);
  border-radius: 10px;
  object-fit: cover;
  box-shadow: 0 4px 14px rgba(4, 5, 16, 0.35);
}

.flip-card-music-player__details {
  display: grid;
  min-width: 0;
  gap: 2px;

  strong,
  span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  strong {
    color: #fff;
    font: 700 12px/1.3 var(--font-head);
  }

  span {
    color: rgba(221, 214, 254, 0.82);
    font: 500 9px/1.4 var(--font-body);
    letter-spacing: 0.04em;
  }
}

.flip-card-music-player__favorite,
.flip-card-music-player__controls button,
.flip-card-music-player__share {
  display: grid;
  flex: 0 0 auto;
  place-items: center;
  padding: 0;
  border: 0;
  background: transparent;
  color: rgba(221, 214, 254, 0.88);
  cursor: pointer;
  transition:
    color 160ms ease,
    transform 160ms ease,
    background-color 160ms ease;

  &:hover:not(:disabled) {
    color: #fff;
    transform: scale(1.08);
  }

  &:disabled {
    opacity: 0.45;
    cursor: not-allowed;
  }

  &:focus-visible {
    outline: 2px solid #c4b5fd;
    outline-offset: 3px;
  }
}

.flip-card-music-player__favorite {
  width: 30px;
  height: 30px;

  &[aria-pressed="true"] {
    color: #f0abfc;
  }
}

.flip-card-music-player__seek {
  width: 0;
  min-width: 0;
  height: 5px;
  flex: 1 1 auto;
  margin: 0;
  appearance: none;
  border-radius: 0;
  background:
    linear-gradient(
      90deg,
      #8b5cf6 0,
      #8b5cf6 var(--seek-progress),
      rgba(255, 255, 255, 0.25) var(--seek-progress),
      rgba(255, 255, 255, 0.25) 100%
    );
  background-repeat: no-repeat;
  background-position: center;
  background-size: 100% 2px;
  cursor: pointer;

  &::-webkit-slider-thumb {
    width: 10px;
    height: 10px;
    appearance: none;
    border: 2px solid #fff;
    border-radius: 50%;
    background: #8b5cf6;
    box-shadow: 0 0 10px rgba(139, 92, 246, 0.72);
  }

  &::-webkit-slider-runnable-track {
    height: 9.5px;
    background: transparent;
  }

  &::-moz-range-thumb {
    width: 8px;
    height: 8px;
    border: 2px solid #fff;
    border-radius: 50%;
    background: #8b5cf6;
    box-shadow: 0 0 10px rgba(139, 92, 246, 0.72);
  }

  &::-moz-range-track {
    height: 5px;
    background: transparent;
  }

  &:focus-visible {
    outline: 2px solid #c4b5fd;
    outline-offset: 4px;
  }

  &:disabled {
    cursor: not-allowed;
    opacity: 0.55;
  }
}

.flip-card-music-player__times {
  display: flex;
  align-items: center;
  gap: 10px;
  color: rgba(245, 243, 255, 0.78);
  font: 400 9px/1.2 var(--mono);
  font-variant-numeric: tabular-nums;

  span {
    flex: 0 0 auto;
  }
}

.flip-card-music-player__controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: clamp(8px, 3vw, 15px);

  button {
    width: 30px;
    height: 30px;
  }

  .flip-card-music-player__share {
    width: 30px;
    height: 30px;
    cursor: default;
  }

  .flip-card-music-player__play {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: 1px solid rgba(221, 214, 254, 0.8);
    background: #ddd6fe;
    color: #171322;
    box-shadow:
      0 0 0 5px rgba(139, 92, 246, 0.12),
      0 0 24px rgba(139, 92, 246, 0.32);
    transition:
      transform 180ms ease,
      box-shadow 180ms ease,
      background-color 180ms ease;

    &:hover:not(:disabled) {
      transform: scale(1.08);
      background: #fff;
      box-shadow:
        0 0 0 6px rgba(139, 92, 246, 0.16),
        0 0 30px rgba(139, 92, 246, 0.46);
    }
  }
}

.flip-card-music-player__error {
  margin: 0;
  color: #fecaca;
  font: 500 8px/1.4 var(--font-body);
  text-align: center;
}

.flip-card-music-player__status {
  margin: 0;
  color: rgba(245, 243, 255, 0.82);
  font: 500 8px/1.4 var(--font-body);
  text-align: center;
}

@media (max-width: 380px) {
  .flip-card-music-player {
    right: 11px;
    bottom: 11px;
    left: 11px;
    gap: 7px;
    padding: 10px 11px 9px;
  }

  .flip-card-music-player__artwork {
    width: 38px;
    height: 38px;
    flex-basis: 38px;
  }

  .flip-card-music-player__controls {
    gap: 7px;

    button {
      width: 26px;
      height: 26px;
    }

    .flip-card-music-player__share {
      width: 26px;
      height: 26px;
    }

    .flip-card-music-player__play {
      width: 34px;
      height: 34px;
    }
  }
}

@media (prefers-reduced-motion: reduce) {
  .flip-card-music-player__controls .flip-card-music-player__play {
    transition: none;
  }
}
</style>
