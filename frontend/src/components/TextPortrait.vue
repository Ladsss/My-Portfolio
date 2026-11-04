<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";

const props = defineProps<{
  src: string;
  alt: string;
  text: string;
  highlightText?: string;
}>();

const canvasRef = ref<HTMLCanvasElement | null>(null);
const hasError = ref(false);
let observer: ResizeObserver | undefined;
let portrait: HTMLImageElement | undefined;
let lastSize = "";

// Normalized once, not on every render
const characters = computed(() =>
  props.text.toUpperCase().replace(/\s+/g, " ").trim().split(""),
);
const highlightedCharacterIndices = computed(() => {
  const phrase = props.highlightText?.toUpperCase().replace(/\s+/g, " ").trim();
  const indices = new Set<number>();
  if (!phrase) return indices;

  const source = characters.value.join("");
  let searchFrom = 0;
  while (searchFrom < source.length) {
    const start = source.indexOf(phrase, searchFrom);
    if (start < 0) break;
    for (let index = start; index < start + phrase.length; index += 1) {
      indices.add(index);
    }
    searchFrom = start + 1;
  }
  return indices;
});

function renderPortrait() {
  const canvas = canvasRef.value;
  if (!canvas || !portrait?.complete || !portrait.naturalWidth) return;

  const bounds = canvas.getBoundingClientRect();
  if (!bounds.width || !bounds.height) return;

  const pixelRatio = Math.min(window.devicePixelRatio || 1, 2);
  const width = Math.round(bounds.width);
  const height = Math.round(bounds.height);
  const sizeKey = `${width}x${height}@${pixelRatio}`;
  if (sizeKey === lastSize) return;
  lastSize = sizeKey;

  if (!characters.value.length || !characters.value.some((c) => c !== " ")) {
    hasError.value = true;
    return;
  }

  canvas.width = Math.round(width * pixelRatio);
  canvas.height = Math.round(height * pixelRatio);

  const context = canvas.getContext("2d");
  if (!context) {
    hasError.value = true;
    return;
  }

  context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
  context.fillStyle = "#171322";
  context.fillRect(0, 0, width, height);

  // Crop the image to cover the canvas, centered
  const aspect = width / height;
  const imageAspect = portrait.naturalWidth / portrait.naturalHeight;
  const cropScale = 1.18;
  const sourceWidth =
    (imageAspect > aspect ? portrait.naturalHeight * aspect : portrait.naturalWidth) /
    cropScale;
  const sourceHeight =
    (imageAspect > aspect ? portrait.naturalHeight : portrait.naturalWidth / aspect) /
    cropScale;
  const sourceX = (portrait.naturalWidth - sourceWidth) / 2;
  const sourceY = (portrait.naturalHeight - sourceHeight) / 2;

  // Small canvas used only to read pixel brightness
  const sampleWidth = Math.max(1, width);
  const sampleHeight = Math.max(1, height);
  const sampleCanvas = document.createElement("canvas");
  sampleCanvas.width = sampleWidth;
  sampleCanvas.height = sampleHeight;

  const sampleContext = sampleCanvas.getContext("2d", { willReadFrequently: true });
  if (!sampleContext) {
    hasError.value = true;
    return;
  }

  sampleContext.drawImage(
    portrait,
    sourceX,
    sourceY,
    sourceWidth,
    sourceHeight,
    0,
    0,
    sampleWidth,
    sampleHeight,
  );
  const pixels = sampleContext.getImageData(0, 0, sampleWidth, sampleHeight).data;

  // Faint background photo under the text
  context.save();
  context.globalAlpha = 0.12;
  context.filter = "grayscale(100%) contrast(1.3) brightness(0.9)";
  context.drawImage(
    portrait,
    sourceX,
    sourceY,
    sourceWidth,
    sourceHeight,
    0,
    0,
    width,
    height,
  );
  context.restore();

  // Text layer
  context.save();
  const baseFontSize = Math.max(4.5, Math.min(6, width / 52));
  const cellWidth = baseFontSize * 0.68;
  const lineHeight = baseFontSize * 1.3;
  context.textBaseline = "top";
  context.textAlign = "center";

  const total = characters.value.length;
  const highlightedIndices = highlightedCharacterIndices.value;
  const highlightedGlyphs: Array<{
    character: string;
    x: number;
    y: number;
    fontSize: number;
    family: string;
    style: string;
  }> = [];
  let characterIndex = 0;
  let row = 0;

  for (let y = 0; y < height; y += lineHeight, row += 1) {
    for (let x = 0; x < width; x += cellWidth) {
      const sourceIndex = characterIndex % total;
      const character = characters.value[sourceIndex];
      characterIndex += 1;
      if (character === " ") continue;

      const sampleX = Math.min(sampleWidth - 1, Math.floor(x + cellWidth / 2));
      const sampleY = Math.min(sampleHeight - 1, Math.floor(y + lineHeight / 2));
      const pixelIndex = (sampleY * sampleWidth + sampleX) * 4;
      const brightness =
        (pixels[pixelIndex] * 0.299 +
          pixels[pixelIndex + 1] * 0.587 +
          pixels[pixelIndex + 2] * 0.114) /
        255;

      const fontScale =
        brightness < 0.25 ? 0.82 : brightness < 0.46 ? 0.96 : brightness < 0.68 ? 1.12 : 1.32;
      const fontSize = baseFontSize * fontScale;
      const family = row % 7 === 0 ? "Georgia, serif" : "monospace";
      const style = row % 11 === 0 ? "italic " : "";
      const weight = brightness > 0.55 ? 700 : 500;

      if (highlightedIndices.has(sourceIndex)) {
        highlightedGlyphs.push({
          character,
          x: x + cellWidth / 2,
          y,
          fontSize,
          family,
          style,
        });
        continue;
      }

      context.font = `${style}${weight} ${fontSize}px ${family}`;
      context.fillStyle =
        brightness < 0.24
          ? "#9b78c2"
          : brightness < 0.48
            ? "#ce8fb5"
            : brightness < 0.72
              ? "#edaaa8"
              : "#fff0d8";
      context.globalAlpha =
        brightness < 0.25 ? 0.42 : brightness < 0.46 ? 0.62 : brightness < 0.68 ? 0.82 : 0.98;
      context.fillText(character, x + cellWidth / 2, y);
    }
  }

  // Photo overlay so the face reads clearly
  context.globalAlpha = 0.24;
  context.filter = "grayscale(100%) contrast(1.18)";
  context.drawImage(
    portrait,
    sourceX,
    sourceY,
    sourceWidth,
    sourceHeight,
    0,
    0,
    width,
    height,
  );
  context.restore();

  context.save();
  context.textBaseline = "top";
  context.textAlign = "center";
  context.fillStyle = "#fff0bd";
  context.globalAlpha = 1;
  context.shadowColor = "rgba(255, 218, 150, 0.8)";
  context.shadowBlur = 2;
  for (const glyph of highlightedGlyphs) {
    context.font = `${glyph.style}900 ${glyph.fontSize * 1.08}px ${glyph.family}`;
    context.fillText(glyph.character, glyph.x, glyph.y);
  }
  context.restore();

  // Vignette
  const vignette = context.createRadialGradient(
    width / 2,
    height / 2,
    Math.min(width, height) * 0.22,
    width / 2,
    height / 2,
    Math.max(width, height) * 0.72,
  );
  vignette.addColorStop(0, "rgba(23, 19, 34, 0)");
  vignette.addColorStop(1, "rgba(14, 11, 24, 0.5)");
  context.fillStyle = vignette;
  context.fillRect(0, 0, width, height);
}

function forceRender() {
  lastSize = "";
  renderPortrait();
}

function loadPortrait() {
  if (portrait) {
    portrait.onload = null;
    portrait.onerror = null;
  }
  hasError.value = false;
  portrait = new Image();
  portrait.onload = forceRender;
  portrait.onerror = () => {
    hasError.value = true;
  };
  portrait.src = props.src;
}

watch(() => [props.text, props.highlightText] as const, forceRender);
watch(() => props.src, loadPortrait);

onMounted(() => {
  loadPortrait();
  observer = new ResizeObserver(renderPortrait);
  if (canvasRef.value) observer.observe(canvasRef.value);
});

onBeforeUnmount(() => {
  observer?.disconnect();
  if (portrait) {
    portrait.onload = null;
    portrait.onerror = null;
  }
});
</script>

<template>
  <canvas
    v-if="!hasError"
    ref="canvasRef"
    class="text-portrait"
    role="img"
    :aria-label="alt"
  ></canvas>
  <img v-else class="text-portrait text-portrait-fallback" :src="src" :alt="alt" />
</template>

<style scoped lang="scss">
.text-portrait {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.text-portrait-fallback {
  object-fit: cover;
}
</style>