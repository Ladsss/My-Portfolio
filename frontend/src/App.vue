<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref } from "vue";
import axios from "axios";
import { useCardSpotlight } from "./composables/useCardSpotlight";

import About from "./components/About.vue";
import Experience from "./components/Experience.vue";
import FlipCard from "./components/FlipCard.vue";
import Projects from "./components/Projects.vue";
import ParticleBackground from "./components/ParticleBackground.vue";
import TechStack from "./components/TechStack.vue";
import cardFront from "./assets/card-front.jpeg";
import mjoyPortrait from "./assets/mjoy.png";
import SmoothCursor from "./components/SmoothCursor.vue";
import TextPortrait from "./components/TextPortrait.vue";

const isDark = ref(true);
const mobileMenuOpen = ref(false);
const sectionIds = ["home", "about", "skills", "work", "experience", "contact"] as const;
type SectionId = (typeof sectionIds)[number];
const currentSection = ref<SectionId>("home");
const sectionLabels: Record<SectionId, string> = {
  home: "Home",
  about: "About",
  skills: "Stack",
  work: "Projects",
  experience: "Experience",
  contact: "Contact",
};
const currentSectionLabel = computed(
  () => sectionLabels[currentSection.value],
);
const currentYear = new Date().getFullYear();


const portraitTextureText = `
'Pag ba ako'ng nauunang
Maglahad ng palad at
Lumapit nang marahan
Magtataka ka ba
'Di man ako halata
Nagtitimping mag-isa
Dinadaan na lang sa tula
Tanong sa tadhana
Oo walang malisya
Para sayo
Sa akin meron
Kaya pasensya na

Kung pwede lang namang maibalik
Sa dati ang lahat pero hindi
'Yung walang ilangan, pwede kang batukan
Na para bang, para bang kaibigan
Baka nga wala namang mali
Kung mag-iba mana ng aking tingin
Nagpapakiramdaman
Sana'y mapanindigan
Ah ah ah
Na para bang para bang

Ba't ba ako'y alipin ng
Kung ano man ang sasabihin nila
Nagkakaintindihan
Naman tayong dalawa
'Di mawari kung pa'no nga ba 'to
Magpapatuloy ba o hihinto
Eh baka naman kasi lahat ay
Biglang magbago

Oo, walang malisya
Para sayo
Sa akin, meron na ah

Kung pwede lang namang maibalik
Sa dati ang lahat pero hindi
'Yung walang ilangan, pwede kang batukan
Na para bang, para bang kaibigan
Baka nga wala namang mali
Kung mag-iba mana ng aking tingin
Nagpapakiramdaman
Sana'y mapanindigan
Ah ah ah
Na para bang para bang
Ah ah-ah-ah ah
Na para bang para bang

Sabi sa sarili ko wala lang 'to
Pero ba't sayo na'ng aking sabado
Ang sabi sa sarili ko wala lang 'to
Wala lang 'to wala lang 'to
Sabi sa sarili ko wala lang 'to
Pero ba't sayo na'ng aking sabado
Ang sabi sa sarili ko wala lang 'to
Wala lang 'to wala lang, 'to

Walang malisya
Para sayo
Sa akin, meron
Kaya pasensya na

Kung pwede lang namang maibalik
Sa dati ang lahat pero hindi
'Yung walang ilangan, pwede kang batukan
Na para bang, para bang kaibigan
Baka nga wala namang mali
Kung mag-iba mana ng aking tingin
Nagpapakiramdaman
Sana'y mapanindigan

Sabi ko sa sarili ko wala lang 'to
Pero ba't sayo na'ng aking sabado
Sabi ko sa sarili ko wala lang 'to
Wala lang 'to wala lang 'to
Sabi ko sa sarili ko wala lang 'to
Pero ba't sayo na'ng sabado
Ang sabi sa sarili ko wala lang 'to
Na para bang para bang
`;


let sectionObserver: IntersectionObserver | undefined;

const apiBaseUrl = (import.meta.env.VITE_API_URL || "").replace(/\/+$/, "");
const githubUrl = import.meta.env.VITE_GITHUB_URL;
const facebookUrl = import.meta.env.VITE_FACEBOOK_URL;
const instagramUrl = import.meta.env.VITE_INSTAGRAM_URL;
const tiktokUrl = import.meta.env.VITE_TIKTOK_URL;
const linkedInUrl = import.meta.env.VITE_LINKEDIN_URL;
const cvUrl = import.meta.env.VITE_CV_URL;
const contactEmail = import.meta.env.VITE_CONTACT_EMAIL;
const heroSocialLinks = [
  { label: "Facebook", icon: "mdi-facebook", href: facebookUrl },
  { label: "Instagram", icon: "mdi-instagram", href: instagramUrl },
  { label: "TikTok", icon: "mdi-music-note-eighth", href: tiktokUrl },
  { label: "LinkedIn", icon: "mdi-linkedin", href: linkedInUrl },
  { label: "GitHub", icon: "mdi-github", href: githubUrl },
].filter((social) => social.href);

type GameStatus = "ready" | "playing" | "paused" | "gameover" | "victory";
type EnemyPattern = "drift" | "sine" | "zigzag";

interface GameEnemy {
  id: number;
  x: number;
  y: number;
  vx: number;
  vy: number;
  radius: number;
  age: number;
  phase: number;
  pattern: EnemyPattern;
  color: string;
}

interface GameParticle {
  x: number;
  y: number;
  vx: number;
  vy: number;
  radius: number;
  life: number;
  maxLife: number;
  color: string;
}

interface GameLaser {
  x1: number;
  y1: number;
  x2: number;
  y2: number;
  life: number;
  color: string;
}

const gameModeActive = ref(false);
const gameCanvas = ref<HTMLCanvasElement | null>(null);
const gamePanelTitle = ref<HTMLElement | null>(null);
const gameModeLaunchButton = ref<HTMLButtonElement | null>(null);
const gameStatus = ref<GameStatus>("ready");
const gameKills = ref(0);
const gameLives = ref(5);
const gameScore = ref(0);
const gameBossHp = ref(10);
const gameBossActive = ref(false);
const gameElapsed = ref(0);
const gameCombo = ref(0);
const gameScorePulse = ref(false);
const gameSoundAvailable = ref(false);
const gameSoundEnabled = ref(false);
const gameSoundError = ref(false);
const gameTargetPosition = ref({ x: 0, y: 0, visible: false });
const gamePointerStyle = computed(() => ({
  left: `${gamePointer.x}px`,
  top: `${gamePointer.y}px`,
}));
const gameTargetStyle = computed(() => ({
  left: `${gameTargetPosition.value.x}px`,
  top: `${gameTargetPosition.value.y}px`,
}));
const gameStatusLabel = computed(() => {
  if (gameStatus.value === "playing") return "In progress";
  if (gameStatus.value === "paused") return "Paused";
  if (gameStatus.value === "gameover") return "Game over";
  if (gameStatus.value === "victory") return "Victory";
  return "Ready";
});
const formattedGameTime = computed(() => {
  const totalSeconds = Math.floor(gameElapsed.value);
  return `${String(Math.floor(totalSeconds / 60)).padStart(2, "0")}:${String(totalSeconds % 60).padStart(2, "0")}`;
});

const gamePointer = reactive({ x: 0, y: 0 });
const gameEnemies: GameEnemy[] = [];
const gameParticles: GameParticle[] = [];
const gameLasers: GameLaser[] = [];
const gameCanvasSize = { width: 0, height: 0 };
let gameFrameId = 0;
let gameLastFrame = 0;
let gameSpawnClock = 0;
let gameNextEnemyId = 0;
let gameLastHitTime = 0;
let gameLastHullDamage = 0;
let gameScorePulseUntil = 0;
let touchStart: { pointerId: number; x: number; y: number } | undefined;
let gameStars: Array<{ x: number; y: number; radius: number; alpha: number }> = [];
let gameContext: CanvasRenderingContext2D | null = null;
let gameAudioContext: AudioContext | undefined;

const GAME_MAX_HULL = 5;
const GAME_HULL_DAMAGE_COOLDOWN = 900;
const enemyColors = ["#c4b5fd", "#67e8f9", "#f0abfc", "#a7f3d0"];
const gameEnemySpeedMultiplier = computed(() => {
  if (gameKills.value >= 40) return 1.7;
  if (gameKills.value >= 30) return 1.45;
  if (gameKills.value >= 15) return 1.2;
  return 1;
});
const gameBossPosition = () => ({
  x: gameCanvasSize.width / 2 + Math.sin(gameElapsed.value * 0.85) * gameCanvasSize.width * 0.26,
  y: Math.min(120, gameCanvasSize.height * 0.22),
});

function resetGameState() {
  gameStatus.value = "ready";
  gameKills.value = 0;
  gameLives.value = GAME_MAX_HULL;
  gameScore.value = 0;
  gameBossHp.value = 10;
  gameBossActive.value = false;
  gameElapsed.value = 0;
  gameCombo.value = 0;
  gameScorePulse.value = false;
  gamePointer.x = (gameCanvasSize.width || window.innerWidth) / 2;
  gamePointer.y = (gameCanvasSize.height || window.innerHeight) * 0.7;
  gameTargetPosition.value = { ...gamePointer, visible: false };
  gameEnemies.length = 0;
  gameParticles.length = 0;
  gameLasers.length = 0;
  gameSpawnClock = 0;
  gameNextEnemyId = 0;
  gameLastHitTime = 0;
  gameLastHullDamage = 0;
}

function resizeGameCanvas() {
  const canvas = gameCanvas.value;
  if (!canvas) return;

  const bounds = canvas.getBoundingClientRect();
  const nextWidth = Math.max(1, bounds.width);
  const nextHeight = Math.max(1, bounds.height);
  const scaleX = gameCanvasSize.width ? nextWidth / gameCanvasSize.width : 1;
  const scaleY = gameCanvasSize.height ? nextHeight / gameCanvasSize.height : 1;
  gameCanvasSize.width = nextWidth;
  gameCanvasSize.height = nextHeight;
  gamePointer.x *= scaleX;
  gamePointer.y *= scaleY;

  for (const enemy of gameEnemies) {
    enemy.x *= scaleX;
    enemy.y *= scaleY;
  }
  for (const particle of gameParticles) {
    particle.x *= scaleX;
    particle.y *= scaleY;
  }
  for (const laser of gameLasers) {
    laser.x1 *= scaleX;
    laser.y1 *= scaleY;
    laser.x2 *= scaleX;
    laser.y2 *= scaleY;
  }

  const pixelRatio = Math.min(window.devicePixelRatio || 1, 2);
  canvas.width = Math.round(nextWidth * pixelRatio);
  canvas.height = Math.round(nextHeight * pixelRatio);
  gameContext = canvas.getContext("2d");
  gameContext?.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
  gameStars = Array.from({ length: Math.floor((nextWidth * nextHeight) / 8500) }, () => ({
    x: Math.random() * nextWidth,
    y: Math.random() * nextHeight,
    radius: Math.random() * 1.4 + 0.25,
    alpha: Math.random() * 0.55 + 0.15,
  }));
}

function openGameMode() {
  if (gameModeActive.value) return;

  gameModeActive.value = true;
  resetGameState();
  gameStatus.value = "playing";
  window.addEventListener("keydown", onGameKeyDown);
  window.addEventListener("resize", resizeGameCanvas);
  window.addEventListener("pointermove", updateGamePointer, { passive: true });
  window.addEventListener("pointerdown", onGamePointerDown);
  window.addEventListener("pointerup", onGamePointerUp);
  window.addEventListener("pointercancel", onGamePointerCancel);
  void nextTick(() => {
    resizeGameCanvas();
    gameLastFrame = performance.now();
    gameFrameId = requestAnimationFrame(renderGameFrame);
    gamePanelTitle.value?.focus({ preventScroll: true });
  });
}

function exitGameMode() {
  if (!gameModeActive.value) return;

  gameModeActive.value = false;
  window.removeEventListener("keydown", onGameKeyDown);
  window.removeEventListener("resize", resizeGameCanvas);
  window.removeEventListener("pointermove", updateGamePointer);
  window.removeEventListener("pointerdown", onGamePointerDown);
  window.removeEventListener("pointerup", onGamePointerUp);
  window.removeEventListener("pointercancel", onGamePointerCancel);
  if (gameFrameId) cancelAnimationFrame(gameFrameId);
  gameFrameId = 0;
  touchStart = undefined;
  gameModeLaunchButton.value?.focus({ preventScroll: true });
}

function startNewGame() {
  resetGameState();
  gameStatus.value = "playing";
  gameLastFrame = performance.now();
}

function toggleGamePause() {
  if (gameStatus.value === "playing") {
    gameStatus.value = "paused";
  } else if (gameStatus.value === "paused") {
    gameStatus.value = "playing";
    gameLastFrame = performance.now();
  }
}

function onGameKeyDown(event: KeyboardEvent) {
  if (event.key === "Escape" || event.key.toLowerCase() === "p") {
    if (gameStatus.value === "playing" || gameStatus.value === "paused") {
      event.preventDefault();
      toggleGamePause();
    }
    return;
  }

  if (event.code !== "Space" || gameStatus.value !== "playing") return;
  const target = event.target;
  if (
    target instanceof HTMLElement &&
    target.closest("button, a, input, textarea, select")
  ) return;

  event.preventDefault();
  fireGameWeapon();
}

function updateGamePointer(event: PointerEvent) {
  gamePointer.x = Math.min(window.innerWidth, Math.max(0, event.clientX));
  gamePointer.y = Math.min(window.innerHeight, Math.max(0, event.clientY));
}

function isGameControlTarget(target: EventTarget | null) {
  return target instanceof Element &&
    Boolean(target.closest("a, button, input, textarea, select, [role='button'], [contenteditable='true']"));
}

function onGamePointerDown(event: PointerEvent) {
  updateGamePointer(event);
  if (isGameControlTarget(event.target)) return;
  if (event.pointerType === "touch") {
    touchStart = { pointerId: event.pointerId, x: event.clientX, y: event.clientY };
  } else if (event.button === 0) {
    fireGameWeapon();
  }
}

function onGamePointerUp(event: PointerEvent) {
  if (event.pointerType !== "touch" || touchStart?.pointerId !== event.pointerId) return;

  const start = touchStart;
  touchStart = undefined;
  if (
    !isGameControlTarget(event.target) &&
    Math.hypot(event.clientX - start.x, event.clientY - start.y) < 10
  ) {
    updateGamePointer(event);
    fireGameWeapon();
  }
}

function onGamePointerCancel(event: PointerEvent) {
  if (touchStart?.pointerId === event.pointerId) touchStart = undefined;
}

async function toggleGameSound() {
  if (!gameSoundAvailable.value) return;
  if (gameSoundEnabled.value) {
    gameSoundEnabled.value = false;
    return;
  }

  try {
    gameAudioContext ??= new AudioContext();
    await gameAudioContext.resume();
    gameSoundEnabled.value = true;
    gameSoundError.value = false;
  } catch {
    gameSoundEnabled.value = false;
    gameSoundError.value = true;
  }
}

function playGameTone(kind: "laser" | "impact" | "explosion") {
  const context = gameAudioContext;
  if (!gameSoundEnabled.value || !context || context.state !== "running") return;

  const oscillator = context.createOscillator();
  const gain = context.createGain();
  const startFrequency = kind === "laser" ? 640 : kind === "impact" ? 240 : 130;
  const endFrequency = kind === "laser" ? 180 : kind === "impact" ? 90 : 38;
  const duration = kind === "laser" ? 0.11 : kind === "impact" ? 0.16 : 0.48;
  const startTime = context.currentTime;

  oscillator.type = kind === "explosion" ? "sawtooth" : "triangle";
  oscillator.frequency.setValueAtTime(startFrequency, startTime);
  oscillator.frequency.exponentialRampToValueAtTime(endFrequency, startTime + duration);
  gain.gain.setValueAtTime(kind === "laser" ? 0.045 : 0.075, startTime);
  gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration);
  oscillator.connect(gain);
  gain.connect(context.destination);
  oscillator.start(startTime);
  oscillator.stop(startTime + duration);
}

function createExplosion(x: number, y: number, color: string, count = 18) {
  for (let index = 0; index < count; index += 1) {
    const angle = Math.random() * Math.PI * 2;
    const speed = 35 + Math.random() * 190;
    const lifetime = 0.35 + Math.random() * 0.65;
    gameParticles.push({
      x,
      y,
      vx: Math.cos(angle) * speed,
      vy: Math.sin(angle) * speed,
      radius: 1.5 + Math.random() * 3.4,
      life: lifetime,
      maxLife: lifetime,
      color,
    });
  }
}

function addGameScore(points: number) {
  gameScore.value += points;
  gameScorePulse.value = true;
  gameScorePulseUntil = performance.now() + 220;
}

function fireGameWeapon() {
  if (gameStatus.value !== "playing" || !gameCanvasSize.width) return;

  const playerX = gameCanvasSize.width / 2;
  const playerY = gameCanvasSize.height - 34;
  const now = performance.now();
  let laserColor = "#c4b5fd";
  gameLasers.push({
    x1: playerX,
    y1: playerY,
    x2: gamePointer.x,
    y2: gamePointer.y,
    life: 0.16,
    color: laserColor,
  });
  playGameTone("laser");

  if (gameBossActive.value) {
    const boss = gameBossPosition();
    if (Math.hypot(gamePointer.x - boss.x, gamePointer.y - boss.y) <= 56) {
      laserColor = "#f0abfc";
      gameLasers[gameLasers.length - 1].color = laserColor;
      gameBossHp.value -= 1;
      playGameTone(gameBossHp.value === 0 ? "explosion" : "impact");
      createExplosion(boss.x + (Math.random() - 0.5) * 55, boss.y + (Math.random() - 0.5) * 30, laserColor, 12);
      addGameScore(50);
      if (gameBossHp.value <= 0) {
        gameBossHp.value = 0;
        gameKills.value += 1;
        addGameScore(1000);
        createExplosion(boss.x, boss.y, "#ffffff", 90);
        gameStatus.value = "victory";
      }
    }
    return;
  }

  const hitIndex = gameEnemies.findIndex(
    (enemy) => Math.hypot(gamePointer.x - enemy.x, gamePointer.y - enemy.y) <= enemy.radius + 13,
  );
  if (hitIndex < 0) return;

  const [enemy] = gameEnemies.splice(hitIndex, 1);
  playGameTone("explosion");
  createExplosion(enemy.x, enemy.y, enemy.color);
  gameKills.value += 1;
  gameCombo.value = now - gameLastHitTime < 1800 ? gameCombo.value + 1 : 1;
  gameLastHitTime = now;
  addGameScore(100 + Math.min(gameCombo.value - 1, 8) * 25);

  if (gameKills.value >= 50) {
    gameBossActive.value = true;
    gameBossHp.value = 10;
    gameEnemies.length = 0;
    createExplosion(gameCanvasSize.width / 2, gameCanvasSize.height * 0.22, "#f0abfc", 45);
  }
}

function spawnGameEnemy() {
  const width = gameCanvasSize.width;
  const radius = 13 + Math.random() * 7;
  const patterns: EnemyPattern[] = ["drift", "sine", "zigzag"];
  gameEnemies.push({
    id: gameNextEnemyId++,
    x: radius + Math.random() * Math.max(1, width - radius * 2),
    y: Math.random() * Math.max(1, gameCanvasSize.height * 0.62),
    vx: (Math.random() - 0.5) * 36,
    vy: 32 + Math.random() * 24,
    radius,
    age: 0,
    phase: Math.random() * Math.PI * 2,
    pattern: patterns[Math.floor(Math.random() * patterns.length)],
    color: enemyColors[Math.floor(Math.random() * enemyColors.length)],
  });
}

function drawGameShip(
  context: CanvasRenderingContext2D,
  x: number,
  y: number,
  radius: number,
  color: string,
  angle: number,
  boss = false,
) {
  context.save();
  context.translate(x, y);
  context.rotate(angle);
  context.shadowColor = color;
  context.shadowBlur = boss ? 24 : 14;
  context.fillStyle = boss ? "rgba(36, 16, 62, 0.96)" : "rgba(17, 24, 39, 0.94)";
  context.strokeStyle = color;
  context.lineWidth = boss ? 2.5 : 1.7;
  context.beginPath();
  if (boss) {
    context.moveTo(radius * 1.2, 0);
    context.lineTo(radius * 0.25, -radius * 0.52);
    context.lineTo(-radius * 0.58, -radius * 0.92);
    context.lineTo(-radius * 0.43, -radius * 0.26);
    context.lineTo(-radius, 0);
    context.lineTo(-radius * 0.43, radius * 0.26);
    context.lineTo(-radius * 0.58, radius * 0.92);
    context.lineTo(radius * 0.25, radius * 0.52);
  } else {
    context.moveTo(radius, 0);
    context.lineTo(-radius * 0.56, -radius * 0.68);
    context.lineTo(-radius * 0.28, 0);
    context.lineTo(-radius * 0.56, radius * 0.68);
  }
  context.closePath();
  context.fill();
  context.stroke();
  context.shadowBlur = 0;
  context.fillStyle = color;
  context.beginPath();
  context.arc(radius * 0.05, 0, boss ? 4 : 2.6, 0, Math.PI * 2);
  context.fill();
  context.restore();
}

function renderGameFrame(now: number) {
  if (!gameModeActive.value) return;
  const context = gameContext;
  const width = gameCanvasSize.width;
  const height = gameCanvasSize.height;
  if (!context || !width || !height) {
    gameFrameId = requestAnimationFrame(renderGameFrame);
    return;
  }

  const delta = Math.min(Math.max((now - gameLastFrame) / 1000, 0), 0.04);
  gameLastFrame = now;
  const shouldAdvance = gameStatus.value !== "paused" && gameStatus.value !== "ready";
  if (shouldAdvance) {
    if (gameStatus.value === "playing") gameElapsed.value += delta;
    if (gameScorePulse.value && now >= gameScorePulseUntil) gameScorePulse.value = false;

    if (gameStatus.value === "playing" && !gameBossActive.value) {
      gameSpawnClock += delta;
      const spawnInterval = Math.max(0.7, 1.05 - gameKills.value * 0.005);
      if (gameSpawnClock >= spawnInterval && gameEnemies.length < 10) {
        gameSpawnClock = 0;
        spawnGameEnemy();
      }
    }

    for (let index = gameEnemies.length - 1; index >= 0; index -= 1) {
      const enemy = gameEnemies[index];
      enemy.age += delta;
      const speed = gameEnemySpeedMultiplier.value;
      if (enemy.pattern === "sine") {
        enemy.x += (enemy.vx + Math.sin(enemy.age * 3.2 + enemy.phase) * 28) * speed * delta;
      } else if (enemy.pattern === "zigzag") {
        enemy.x += (enemy.vx + Math.sign(Math.sin(enemy.age * 3.2 + enemy.phase)) * 44) * speed * delta;
      } else {
        enemy.x += enemy.vx * speed * delta;
      }
      enemy.y += enemy.vy * speed * delta;
      if (enemy.y > height + enemy.radius * 2) {
        gameEnemies.splice(index, 1);
        if (
          gameStatus.value === "playing" &&
          !gameBossActive.value &&
          now - gameLastHullDamage >= GAME_HULL_DAMAGE_COOLDOWN
        ) {
          gameLastHullDamage = now;
          gameLives.value -= 1;
          createExplosion(width / 2, height - 31, "#fca5a5", 12);
          if (gameLives.value <= 0) gameStatus.value = "gameover";
        }
      } else if (enemy.x < -enemy.radius * 2 || enemy.x > width + enemy.radius * 2) {
        gameEnemies.splice(index, 1);
      }
    }

    for (let index = gameParticles.length - 1; index >= 0; index -= 1) {
      const particle = gameParticles[index];
      particle.x += particle.vx * delta;
      particle.y += particle.vy * delta;
      particle.vx *= 0.985;
      particle.vy *= 0.985;
      particle.life -= delta;
      if (particle.life <= 0) gameParticles.splice(index, 1);
    }
    for (let index = gameLasers.length - 1; index >= 0; index -= 1) {
      gameLasers[index].life -= delta;
      if (gameLasers[index].life <= 0) gameLasers.splice(index, 1);
    }
  }

  context.clearRect(0, 0, width, height);
  for (const star of gameStars) {
    context.globalAlpha = star.alpha * 0.12 * (0.72 + Math.sin(now / 650 + star.x) * 0.28);
    context.fillStyle = "#ddd6fe";
    context.beginPath();
    context.arc(star.x, star.y, star.radius, 0, Math.PI * 2);
    context.fill();
  }
  context.globalAlpha = 1;

  for (const laser of gameLasers) {
    context.globalAlpha = Math.min(1, laser.life / 0.16);
    context.strokeStyle = laser.color;
    context.shadowColor = laser.color;
    context.shadowBlur = 14;
    context.lineWidth = 2.5;
    context.beginPath();
    context.moveTo(laser.x1, laser.y1);
    context.lineTo(laser.x2, laser.y2);
    context.stroke();
  }
  context.globalAlpha = 1;
  context.shadowBlur = 0;

  for (const enemy of gameEnemies) {
    drawGameShip(context, enemy.x, enemy.y, enemy.radius, enemy.color, Math.atan2(enemy.vy, enemy.vx));
  }

  if (gameBossActive.value && gameStatus.value !== "victory") {
    const boss = gameBossPosition();
    drawGameShip(context, boss.x, boss.y, 37, "#f0abfc", Math.PI, true);
  }

  drawGameShip(
    context,
    width / 2,
    height - 31,
    18,
    "#c4b5fd",
    Math.atan2(gamePointer.y - (height - 31), gamePointer.x - width / 2),
  );

  for (const particle of gameParticles) {
    context.globalAlpha = Math.max(0, particle.life / particle.maxLife);
    context.fillStyle = particle.color;
    context.shadowColor = particle.color;
    context.shadowBlur = 12;
    context.beginPath();
    context.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
    context.fill();
  }
  context.globalAlpha = 1;
  context.shadowBlur = 0;

  let target: { x: number; y: number } | undefined;
  if (gameStatus.value === "playing" && gameBossActive.value) {
    const boss = gameBossPosition();
    if (Math.hypot(gamePointer.x - boss.x, gamePointer.y - boss.y) <= 68) target = boss;
  } else if (gameStatus.value === "playing") {
    target = gameEnemies.find(
      (enemy) => Math.hypot(gamePointer.x - enemy.x, gamePointer.y - enemy.y) <= enemy.radius + 16,
    );
  }
  gameTargetPosition.value = target
    ? { x: target.x, y: target.y, visible: true }
    : { x: gamePointer.x, y: gamePointer.y, visible: false };

  gameFrameId = requestAnimationFrame(renderGameFrame);
}

useCardSpotlight();

onMounted(() => {
  gameSoundAvailable.value = "AudioContext" in window;
  if (!("IntersectionObserver" in window)) return;

  const sections = sectionIds.flatMap((id) => {
    const element = document.getElementById(id);
    return element ? [{ id, element }] : [];
  });

  sectionObserver = new IntersectionObserver(
    () => {
      const activationLine = window.innerHeight * 0.28;
      const current = sections.find(({ element }) => {
        const bounds = element.getBoundingClientRect();
        return bounds.top <= activationLine && bounds.bottom > activationLine;
      });

      if (current) currentSection.value = current.id;
    },
    {
      rootMargin: "-18% 0px -70% 0px",
      threshold: 0,
    },
  );

  sections.forEach(({ element }) => sectionObserver?.observe(element));
});

onBeforeUnmount(() => {
  sectionObserver?.disconnect();
  if (gameModeActive.value) {
    exitGameMode();
  }
  if (gameAudioContext && gameAudioContext.state !== "closed") {
    void gameAudioContext.close();
  }
});

const form = ref({
  name: "",
  email: "",
  subject: "",
  message: "",
  website: "",
});

const formState = ref<"idle" | "sending" | "success" | "error">("idle");
const fieldErrors = ref<Record<string, string>>({});
const contactSnackbar = ref({
  visible: false,
  message: "",
  type: "success" as "success" | "error",
});

function clearFieldError(field: string) {
  delete fieldErrors.value[field];
}

function showContactSnackbar(
  message: string,
  type: "success" | "error",
) {
  contactSnackbar.value = { visible: true, message, type };
}

async function copyContactEmail() {
  if (!contactEmail) return;

  try {
    await navigator.clipboard.writeText(contactEmail);
    showContactSnackbar("Email address copied to clipboard.", "success");
  } catch {
    showContactSnackbar(
      "Couldn’t copy the email address. You can select and copy it instead.",
      "error",
    );
  }
}

async function submitContact() {
  if (formState.value === "sending") return;

  formState.value = "sending";
  contactSnackbar.value.visible = false;
  fieldErrors.value = {};

  try {
    const response = await axios.post(`${apiBaseUrl}/api/contact`, form.value);

    formState.value = "success";
    showContactSnackbar(
      response.data.message || "Your message has been sent. Thanks for reaching out!",
      "success",
    );

    form.value = {
      name: "",
      email: "",
      subject: "",
      message: "",
      website: "",
    };
  } catch (error) {
    const response = axios.isAxiosError(error) ? error.response : undefined;
    const errors = response?.data?.errors;

    if (response?.status === 422 && errors && typeof errors === "object") {
      fieldErrors.value = Object.fromEntries(
        Object.entries(errors).flatMap(([field, messages]) =>
          typeof field === "string" &&
          Array.isArray(messages) &&
          typeof messages[0] === "string"
            ? [[field, messages[0]]]
            : [],
        ),
      );
      showContactSnackbar(
        "Please review the highlighted fields and try again.",
        "error",
      );
    } else {
      showContactSnackbar(
        "Your message couldn’t be sent right now. Please try again or email me directly.",
        "error",
      );
    }

    formState.value = "error";
  }
}
</script>

<template>
  <v-app
    :theme="isDark ? 'dark' : 'light'"
    class="portfolio-app"
    :class="{ 'game-mode-open': gameModeActive }"
  >
    <!-- Smooth Custom Cursor -->
    <SmoothCursor />
    <div class="site-shell" :class="{ 'theme-dark': isDark }">
      <ParticleBackground />
      <div
        v-if="gameModeActive"
        class="space-game"
        aria-label="Spaceship game effects"
      >
        <canvas ref="gameCanvas" class="space-game__canvas" aria-hidden="true"></canvas>
        <div
          class="space-game__reticle"
          :class="{ 'is-inactive': gameStatus !== 'playing' }"
          :style="gamePointerStyle"
          aria-hidden="true"
        >
          <span></span>
        </div>
        <div
          v-if="gameTargetPosition.visible && gameStatus === 'playing'"
          class="space-game__lock"
          :style="gameTargetStyle"
          aria-hidden="true"
        >
          <span></span>
        </div>
        <div
          v-if="gameStatus === 'victory'"
          class="space-game__victory-toast"
          role="status"
          aria-live="polite"
        >
          <span class="space-game__victory-icon" aria-hidden="true">
            <v-icon icon="mdi-trophy-outline" size="21" />
          </span>
          <span class="space-game__victory-copy">
            <strong>Congratulations, Pilot!</strong>
            <span>You defeated the Void Warden. The portfolio is safe!</span>
          </span>
          <span class="space-game__victory-score">
            {{ gameScore.toLocaleString() }} pts
          </span>
        </div>

        <aside class="space-game__panel" aria-label="Game controls and score">
          <div class="space-game__panel-heading">
            <span class="space-game__mark" aria-hidden="true">
              <v-icon icon="mdi-rocket-launch-outline" size="17" />
            </span>
            <span
              ref="gamePanelTitle"
              class="space-game__title"
              tabindex="-1"
              aria-label="Game active. Click or tap anywhere to fire, or press Space."
            >BUG BLASTER</span>
            <span class="space-game__status-dot" :class="`is-${gameStatus}`" aria-hidden="true"></span>
            <span class="space-game__status-text" aria-live="polite">{{ gameStatusLabel }}</span>
          </div>

          <div class="space-game__hud" aria-live="polite">
            <div class="space-game__stat">
              <span class="space-game__stat-label">KILLS</span>
              <strong>{{ String(gameKills).padStart(2, "0") }}<small>/50</small></strong>
            </div>
            <div
              class="space-game__stat"
              :class="{ 'is-pulsing': gameScorePulse }"
            >
              <span class="space-game__stat-label">SCORE</span>
              <strong>{{ gameScore.toLocaleString() }}</strong>
            </div>
            <div class="space-game__stat">
              <span class="space-game__stat-label">HULL</span>
              <strong>{{ gameLives }}<small>/{{ GAME_MAX_HULL }}</small></strong>
            </div>
          </div>

          <div
            v-if="gameBossActive"
            class="space-game__boss-health"
            aria-label="Boss health"
            :aria-valuenow="gameBossHp"
            aria-valuemin="0"
            aria-valuemax="10"
            role="progressbar"
          >
            <div class="space-game__boss-label">
              <span>VOID WARDEN</span>
              <strong>{{ gameBossHp }} <small>/ 10 HP</small></strong>
            </div>
            <div class="space-game__boss-track">
              <span :style="{ width: `${gameBossHp * 10}%` }"></span>
            </div>
          </div>

          <p
            v-if="gameStatus === 'victory' || gameStatus === 'gameover'"
            class="space-game__result"
            role="status"
          >
            {{ gameStatus === "victory" ? "Sector secured!" : "Hull depleted." }}
            Score {{ gameScore.toLocaleString() }} · {{ gameKills }} kills
          </p>
          <p v-if="gameSoundError" class="space-game__sound-error" role="status">
            Audio couldn’t start. Try enabling sound again.
          </p>

          <div class="space-game__actions" aria-label="Game actions">
            <button
              class="space-game__icon-button"
              type="button"
              :aria-label="gameSoundEnabled ? 'Mute game sounds' : 'Enable game sounds'"
              :aria-pressed="gameSoundEnabled"
              :disabled="!gameSoundAvailable"
              @click="toggleGameSound"
            >
              <v-icon
                :icon="gameSoundEnabled ? 'mdi-volume-high' : 'mdi-volume-off'"
                size="17"
              />
            </button>
            <button
              class="space-game__icon-button"
              type="button"
              :aria-label="gameStatus === 'playing' ? 'Pause game' : 'Resume game'"
              :disabled="gameStatus === 'victory' || gameStatus === 'gameover'"
              @click="toggleGamePause"
            >
              <v-icon
                :icon="gameStatus === 'playing' ? 'mdi-pause' : 'mdi-play'"
                size="17"
              />
            </button>
            <button
              class="space-game__icon-button"
              type="button"
              aria-label="Restart game"
              @click="startNewGame"
            >
              <v-icon icon="mdi-restart" size="17" />
            </button>
            <button
              class="space-game__exit"
              type="button"
              @click="exitGameMode"
            >
              <v-icon icon="mdi-close" size="15" />
              Exit
            </button>
          </div>
          <p class="space-game__instructions">
            Click or tap anywhere to fire · Space to shoot · Esc to pause
            <span v-if="gameCombo > 1" class="space-game__combo">COMBO ×{{ gameCombo }}</span>
          </p>
        </aside>
      </div>
      <header
        class="topbar"
        :inert="gameModeActive"
        :aria-hidden="gameModeActive ? 'true' : undefined"
      >
        <a
          class="brand"
          href="#home"
          aria-label="Go to home"
          :class="{ 'is-active': currentSection === 'home' }"
          :aria-current="currentSection === 'home' ? 'page' : undefined"
        >
          <span class="brand-mark">RE</span>
          <span>
            REYMART EPAN
            <span class="brand-year"> / {{ currentYear }}</span>
          </span>
        </a>

        <nav
          class="desktop-nav"
          :class="[
            { 'mobile-open': mobileMenuOpen },
            `active-${currentSection}`,
          ]"
          aria-label="Main navigation"
          @click="mobileMenuOpen = false"
        >
          <a
            href="#about"
            :class="{ 'is-active': currentSection === 'about' }"
            :aria-current="currentSection === 'about' ? 'location' : undefined"
          >About</a>
          <a
            href="#skills"
            :class="{ 'is-active': currentSection === 'skills' }"
            :aria-current="currentSection === 'skills' ? 'location' : undefined"
          >Stack</a>
          <a
            href="#work"
            :class="{ 'is-active': currentSection === 'work' }"
            :aria-current="currentSection === 'work' ? 'location' : undefined"
          >Projects</a>
          <a
            href="#experience"
            :class="{ 'is-active': currentSection === 'experience' }"
            :aria-current="
              currentSection === 'experience' ? 'location' : undefined
            "
          >Experience</a>
          <span class="nav-indicator" aria-hidden="true"></span>
        </nav>

        <div class="nav-actions">
          <button
            class="mobile-menu-toggle"
            type="button"
            :aria-label="
              mobileMenuOpen ? 'Close navigation menu' : 'Open navigation menu'
            "
            :aria-expanded="mobileMenuOpen"
            @click="mobileMenuOpen = !mobileMenuOpen"
          >
            <span class="mobile-current-section">{{ currentSectionLabel }}</span>
            <v-icon
              :icon="mobileMenuOpen ? 'mdi-close' : 'mdi-menu'"
              size="20"
            />
          </button>

          <button
            class="theme-toggle"
            type="button"
            :aria-label="
              isDark ? 'Switch to light theme' : 'Switch to dark theme'
            "
            @click="isDark = !isDark"
          >
            <v-icon
              :icon="isDark ? 'mdi-weather-sunny' : 'mdi-weather-night'"
              size="19"
            />
          </button>

          <a
            class="nav-contact"
            href="#contact"
            :class="{ 'is-active': currentSection === 'contact' }"
            :aria-current="currentSection === 'contact' ? 'location' : undefined"
          >
            Let’s talk
            <v-icon icon="mdi-arrow-top-right" size="16" />
          </a>
        </div>
      </header>

      <main
        :inert="gameModeActive"
        :aria-hidden="gameModeActive ? 'true' : undefined"
      >
        <!-- Hero Section -->
        <section id="home" class="hero section-wrap">
          <div class="hero-copy">
            <h1 class="hero-headline">
              <span class="hero-title-line">Hi, I’m</span>
              <span class="hero-title-line"
                ><span class="accent-word">Lads.</span></span
              >
            </h1>

            <p class="eyebrow hero-eyebrow">
              <span class="live-dot"></span>
              <span class="hero-eyebrow-text">FULL-STACK DEVELOPER</span>
            </p>

            <p class="hero-intro">
              A full-stack web developer specializing in building modern,
              responsive, and user-friendly web applications. Skilled in Vue.js
              for frontend development and Laravel for backend development, with
              a focus on creating clean, scalable, and reliable digital
              solutions that turn ideas into meaningful user experiences.
            </p>

            <div class="hero-actions">
              <a class="button button-primary" href="#work">
                View projects
                <v-icon icon="mdi-arrow-down-right" size="18" />
              </a>
              <a class="button button-quiet" href="#about">
                About me
                <v-icon icon="mdi-arrow-right" size="17" />
              </a>
            </div>

            <div class="hero-foot">
              <nav class="hero-social-links" aria-label="Social media">
                <a
                  v-for="social in heroSocialLinks"
                  :key="social.label"
                  :href="social.href"
                  :aria-label="social.label"
                  :data-tooltip="social.label"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <v-icon :icon="social.icon" size="17" />
                </a>
              </nav>
            </div>
          </div>
          <a class="scroll-cue" href="#about">
            <span class="scroll-line"></span>
            SCROLL TO EXPLORE
          </a>

          <div
            class="hero-visual"
            aria-label="Illustration of the development and quality assurance workflow"
          >
            <div class="visual-orbit orbit-one" aria-hidden="true"></div>
            <div class="visual-orbit orbit-two" aria-hidden="true"></div>
            <div
              class="orbit-tech-track orbit-tech-track-one"
              role="group"
              aria-label="Frontend technology orbit"
            >
              <span
                class="orbit-tech orbit-tech-vue"
                role="img"
                aria-label="Vue.js"
                title="Vue.js"
                tabindex="0"
              >
                <span class="orbit-tech-badge">
                  <span class="orbit-tech-name">Vue.js</span>
                </span>
              </span>
              <span
                class="orbit-tech orbit-tech-html"
                role="img"
                aria-label="HTML5"
                title="HTML5"
                tabindex="0"
              >
                <span class="orbit-tech-badge">
                  <span class="orbit-tech-name">HTML5</span>
                </span>
              </span>
              <span
                class="orbit-tech orbit-tech-javascript"
                role="img"
                aria-label="JavaScript"
                title="JavaScript"
                tabindex="0"
              >
                <span class="orbit-tech-badge">
                  <span class="orbit-tech-name">JavaScript</span>
                </span>
              </span>
            </div>
            <div
              class="orbit-tech-track orbit-tech-track-two"
              role="group"
              aria-label="Backend and styling technology orbit"
            >
              <span
                class="orbit-tech orbit-tech-laravel"
                role="img"
                aria-label="Laravel"
                title="Laravel"
                tabindex="0"
              >
                <span class="orbit-tech-badge">
                  <span class="orbit-tech-name">Laravel</span>
                </span>
              </span>
              <span
                class="orbit-tech orbit-tech-css"
                role="img"
                aria-label="CSS3"
                title="CSS3"
                tabindex="0"
              >
                <span class="orbit-tech-badge">
                  <span class="orbit-tech-name">CSS3</span>
                </span>
              </span>
            </div>

            <div class="hero-flip-card">
              <FlipCard
                class="portfolio-flip-card"
                aria-label="Portfolio profile card. Activate to flip."
                :width="300"
                :height="400"
                :radius="22"
                background="#111827"
                color="#f9fafb"
                :tilt-max="9"
                :glare-opacity="0.16"
                :hover-scale="1.025"
              >
                <template #front>
                  <img
                    class="portfolio-card-image"
                    :src="cardFront"
                    alt="Portrait of Reymart Epan"
                    draggable="false"
                  />
                </template>
                <template #back>
                  <div class="portfolio-card-back">
                    <TextPortrait
                      class="portfolio-text-portrait"
                      :src="mjoyPortrait"
                      alt="A text-based portrait of Mjoy, shaped by the image's light and shadow"
                      :text="portraitTextureText"
                      highlight-text="Ang sabi sa sarili ko wala lang 'to"
                    />
                    <blockquote class="portfolio-card-lyric">
                      “Pero ba't sa'yo na'ng aking Sabado?”
                    </blockquote>
                  </div>
                </template>
              </FlipCard>
            </div>
            <button
              class="game-mode-launch hero-highlight"
              type="button"
              ref="gameModeLaunchButton"
              :aria-pressed="gameModeActive"
              @pointerdown.stop
              @click.stop="gameModeActive ? exitGameMode() : openGameMode()"
            >
              <span class="hero-highlight-icon" aria-hidden="true">
                <v-icon icon="mdi-gamepad-variant-outline" size="15" />
              </span>
              <span>{{ gameModeActive ? "GAME MODE ON" : "GAME MODE" }}</span>
              <v-icon class="game-mode-launch__arrow" icon="mdi-arrow-up-right" size="15" />
            </button>

            <div class="hero-highlights" aria-label="Professional highlights">
              <div class="hero-highlight">
                <span class="hero-highlight-icon" aria-hidden="true">
                  <v-icon icon="mdi-briefcase-clock-outline" size="15" />
                </span>
                <span>1+ Year of Experience</span>
              </div>
              <div class="hero-highlight">
                <span class="hero-highlight-icon" aria-hidden="true">
                  <v-icon icon="mdi-code-tags" size="15" />
                </span>
                <span>Full-Stack Developer</span>
              </div>
              <a
                v-if="cvUrl"
                class="hero-highlight hero-cv-link"
                :href="cvUrl"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Download CV"
              >
                <span class="hero-highlight-icon" aria-hidden="true">
                  <v-icon icon="mdi-download" size="15" />
                </span>
                <span>Download CV</span>
              </a>
            </div>
          </div>
        </section>

        <!-- Portfolio Sections -->
        <About />
        <TechStack />
        <Projects />
        <Experience />

        <!-- Contact Section -->
        <section id="contact" class="contact-section section-wrap section-pad">
          <div class="section-heading">
            <p class="eyebrow">05 — YOUR NEXT MOVE</p>
            <span class="heading-rule"></span>
            <span class="section-index">05 / 06</span>
          </div>

          <div class="contact-grid">
            <div>
              <h2>
                Have a good<br />
                one in <span class="serif-word">mind?</span>
              </h2>

              <p class="contact-intro">
                Have a project, API, or quality engineering challenge in mind?
                Send a message and I’ll get back to you.
              </p>

              <div class="contact-details-grid">
                <div class="contact-info-card availability-card">
                  <span class="contact-card-label">Live status</span>
                  <span class="availability-value">
                    <span class="availability-dot" aria-hidden="true"></span>
                    Available for opportunities
                  </span>
                </div>

                <div v-if="contactEmail" class="contact-info-card email-card">
                  <span class="contact-card-label">Direct email</span>
                  <div class="email-card-actions">
                    <a class="email-card-address" :href="`mailto:${contactEmail}`">
                      {{ contactEmail }}
                    </a>
                    <button
                      class="copy-email-button"
                      type="button"
                      aria-label="Copy email address"
                      @click="copyContactEmail"
                    >
                      <v-icon icon="mdi-content-copy" size="13" />
                      Copy email
                    </button>
                  </div>
                </div>

                <nav class="social-links" aria-label="Professional profiles">
                  <a
                    v-if="githubUrl"
                    :href="githubUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    GitHub
                    <v-icon icon="mdi-arrow-top-right" size="14" />
                  </a>

                  <a
                    v-if="linkedInUrl"
                    :href="linkedInUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    LinkedIn
                    <v-icon icon="mdi-arrow-top-right" size="14" />
                  </a>
                </nav>
              </div>
            </div>

            <form
              class="contact-form"
              aria-labelledby="contact-form-title"
              :aria-busy="formState === 'sending'"
              @submit.prevent="submitContact"
            >
              <div class="contact-form-heading">
                <div>
                  <p class="contact-form-kicker">LET’S START A CONVERSATION</p>
                  <h3 id="contact-form-title">Send a message</h3>
                </div>
                <span class="contact-form-required">All fields required</span>
              </div>

              <input
                v-model="form.website"
                class="honeypot"
                type="text"
                name="website"
                tabindex="-1"
                autocomplete="off"
                aria-hidden="true"
              />

              <div class="field-row">
                <div class="form-field">
                  <label for="contact-name">Your name</label>
                  <input
                    id="contact-name"
                    v-model="form.name"
                    required
                    maxlength="120"
                    autocomplete="name"
                    placeholder="Your name"
                    :aria-invalid="Boolean(fieldErrors.name)"
                    :aria-describedby="
                      fieldErrors.name ? 'contact-name-error' : undefined
                    "
                    @input="clearFieldError('name')"
                  />
                  <span
                    v-if="fieldErrors.name"
                    id="contact-name-error"
                    class="field-error"
                  >{{ fieldErrors.name }}</span>
                </div>

                <div class="form-field">
                  <label for="contact-email">Email address</label>
                  <input
                    id="contact-email"
                    v-model="form.email"
                    type="email"
                    required
                    maxlength="255"
                    autocomplete="email"
                    placeholder="you@example.com"
                    :aria-invalid="Boolean(fieldErrors.email)"
                    :aria-describedby="
                      fieldErrors.email ? 'contact-email-error' : undefined
                    "
                    @input="clearFieldError('email')"
                  />
                  <span
                    v-if="fieldErrors.email"
                    id="contact-email-error"
                    class="field-error"
                  >{{ fieldErrors.email }}</span>
                </div>
              </div>

              <div class="form-field">
                <label for="contact-subject">Subject</label>
                <input
                  id="contact-subject"
                  v-model="form.subject"
                  required
                  maxlength="160"
                  placeholder="Project or inquiry"
                  :aria-invalid="Boolean(fieldErrors.subject)"
                  :aria-describedby="
                    fieldErrors.subject ? 'contact-subject-error' : undefined
                  "
                  @input="clearFieldError('subject')"
                />
                <span
                  v-if="fieldErrors.subject"
                  id="contact-subject-error"
                  class="field-error"
                >{{ fieldErrors.subject }}</span>
              </div>

              <div class="form-field">
                <label for="contact-message">Your message</label>
                <textarea
                  id="contact-message"
                  v-model="form.message"
                  required
                  maxlength="5000"
                  rows="4"
                  placeholder="Tell me about your project, timeline, or QA needs..."
                  :aria-invalid="Boolean(fieldErrors.message)"
                  :aria-describedby="
                    fieldErrors.message ? 'contact-message-error' : undefined
                  "
                  @input="clearFieldError('message')"
                ></textarea>
                <span
                  v-if="fieldErrors.message"
                  id="contact-message-error"
                  class="field-error"
                >{{ fieldErrors.message }}</span>
              </div>

              <div class="form-submit-row">
                <button
                  class="button button-primary"
                  type="submit"
                  :disabled="formState === 'sending'"
                  :aria-label="
                    formState === 'sending'
                      ? 'Sending your message'
                      : 'Send a message'
                  "
                >
                  <v-progress-circular
                    v-if="formState === 'sending'"
                    indeterminate
                    size="16"
                    width="2"
                    aria-hidden="true"
                  />
                  <span v-if="formState === 'sending'">Sending…</span>
                  <template v-else>
                    Send a message
                    <v-icon icon="mdi-arrow-up-right" size="17" />
                  </template>
                </button>
              </div>
            </form>
          </div>
        </section>
      </main>

      <v-snackbar
        v-model="contactSnackbar.visible"
        :class="`contact-snackbar contact-snackbar--${contactSnackbar.type}`"
        location="top right"
        :offset="16"
        :timeout="5000"
        :role="contactSnackbar.type === 'error' ? 'alert' : 'status'"
        :aria-live="contactSnackbar.type === 'error' ? 'assertive' : 'polite'"
        aria-atomic="true"
      >
        <span>{{ contactSnackbar.message }}</span>
        <template #actions>
          <v-btn
            icon="mdi-close"
            variant="text"
            density="comfortable"
            aria-label="Dismiss notification"
            @click="contactSnackbar.visible = false"
          />
        </template>
      </v-snackbar>

      <!-- Footer -->
      <footer
        class="footer section-wrap"
        :inert="gameModeActive"
        :aria-hidden="gameModeActive ? 'true' : undefined"
      >
        <a class="brand" href="#home">
          <span class="brand-mark">RE</span>
          <span>
            REYMART EPAN
            <span class="brand-year"> / {{ currentYear }}</span>
          </span>
        </a>

        <span>DESIGNED & BUILT WITH CARE</span>
        <a href="#home">BACK TO TOP ↑</a>
      </footer>
    </div>
  </v-app>
</template>

<style lang="scss">
@use "./App.scss";

.game-mode-launch {
  position: absolute;
  z-index: 5;
  top: clamp(18px, 4vh, 36px);
  right: clamp(18px, 4vw, 48px);
  cursor: pointer;
  transition:
    transform 180ms ease,
    border-color 180ms ease,
    color 180ms ease;

  &:hover {
    transform: translateY(-2px);
    border-color: var(--highlight);
    color: var(--highlight);
  }

  &:active {
    transform: translateY(0) scale(0.985);
  }

  &:focus-visible {
    outline: 2px solid var(--highlight);
    outline-offset: 2px;
  }
}

.game-mode-launch__arrow {
  flex: 0 0 auto;
  color: var(--muted);
  transition: transform 180ms ease;

  .game-mode-launch:hover & {
    transform: translate(2px, -2px);
  }
}

.portfolio-app.game-mode-open .magicui-smooth-cursor {
  opacity: 0 !important;
}

.portfolio-app.game-mode-open button {
  cursor: pointer !important;
}

.site-shell > :not(.particle-background):not(.topbar).space-game {
  position: fixed;
  z-index: 5000;
  inset: 0;
}

.space-game {
  display: flex;
  flex-direction: column;
  gap: clamp(14px, 2.5vh, 26px);
  overflow: auto;
  padding: clamp(16px, 3.2vw, 42px);
  background:
    radial-gradient(ellipse at 50% -18%, rgba(109, 40, 217, 0.28), transparent 58%),
    rgba(7, 11, 24, 0.98);
  color: #f9fafb;
  animation: space-game-enter 360ms cubic-bezier(0.22, 1, 0.36, 1) both;

  &__header {
    display: grid;
    width: min(1120px, 100%);
    margin-inline: auto;
    grid-template-columns: minmax(150px, 1fr) auto minmax(190px, 1fr);
    align-items: center;
    gap: clamp(12px, 2vw, 28px);
  }

  &__identity {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 12px;

    h2 {
      overflow: hidden;
      color: #fff;
      font-size: clamp(1.05rem, 2vw, 1.35rem);
      letter-spacing: -0.035em;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  }

  &__mark {
    display: grid;
    width: 42px;
    height: 42px;
    flex: 0 0 auto;
    place-items: center;
    border: 1px solid rgba(196, 181, 253, 0.3);
    border-radius: 13px;
    background: rgba(139, 92, 246, 0.15);
    color: #c4b5fd;
    box-shadow: inset 0 0 20px rgba(139, 92, 246, 0.12);
  }

  &__eyebrow {
    margin: 0 0 4px;
    color: #a78bfa;
    font: 700 0.59rem/1.2 var(--mono);
    letter-spacing: 0.14em;
  }

  &__hud {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: clamp(14px, 2.6vw, 34px);
    padding: 9px 16px;
    border: 1px solid rgba(196, 181, 253, 0.13);
    border-radius: 15px;
    background: rgba(17, 24, 39, 0.72);
    box-shadow: inset 0 1px rgba(255, 255, 255, 0.035);
  }

  &__stat {
    display: grid;
    gap: 4px;
    min-width: 46px;

    strong {
      display: flex;
      align-items: baseline;
      gap: 3px;
      color: #f9fafb;
      font: 700 0.96rem/1 var(--mono);
      white-space: nowrap;
      transition: color 140ms ease, transform 140ms ease;

      small {
        color: #6b7280;
        font-size: 0.65rem;
        font-weight: 500;
      }
    }

    &.is-pulsing strong {
      color: #ddd6fe;
      transform: scale(1.08);
    }

    &--status strong {
      gap: 7px;
      font-family: var(--font-body);
      font-size: 0.78rem;
    }
  }

  &__stat-label {
    color: #7f8798;
    font: 700 0.55rem/1 var(--mono);
    letter-spacing: 0.13em;
  }

  &__status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #a78bfa;
    box-shadow: 0 0 10px rgba(167, 139, 250, 0.7);

    &.is-playing {
      background: #34d399;
      box-shadow: 0 0 10px rgba(52, 211, 153, 0.75);
      animation: game-status-pulse 1.5s ease-in-out infinite;
    }

    &.is-paused {
      background: #fbbf24;
      box-shadow: 0 0 10px rgba(251, 191, 36, 0.55);
    }

    &.is-victory {
      background: #67e8f9;
      box-shadow: 0 0 10px rgba(103, 232, 249, 0.65);
    }
  }

  &__actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
  }

  &__icon-button,
  &__exit {
    display: inline-flex;
    min-width: 40px;
    height: 40px;
    align-items: center;
    justify-content: center;
    gap: 7px;
    border: 1px solid rgba(196, 181, 253, 0.18);
    border-radius: 11px;
    background: rgba(17, 24, 39, 0.78);
    color: #e5e7eb;
    transition: background 160ms ease, border-color 160ms ease, transform 160ms ease;

    &:hover {
      transform: translateY(-1px);
      border-color: rgba(196, 181, 253, 0.52);
      background: rgba(139, 92, 246, 0.18);
    }

    &:focus-visible {
      outline: 2px solid #c4b5fd;
      outline-offset: 2px;
    }
  }

  &__exit {
    padding-inline: 12px;
    color: #d1d5db;
    font-size: 0.76rem;
    font-weight: 650;
  }

  &__arena-wrap {
    display: flex;
    width: min(1120px, 100%);
    min-height: 0;
    flex: 1;
    flex-direction: column;
    gap: 11px;
    margin: 0 auto;
  }

  &__arena {
    position: relative;
    min-height: 280px;
    flex: 1;
    overflow: hidden;
    border: 1px solid rgba(196, 181, 253, 0.23);
    border-radius: clamp(15px, 2vw, 24px);
    background: #070b18;
    box-shadow:
      0 24px 80px rgba(0, 0, 0, 0.42),
      inset 0 0 70px rgba(109, 40, 217, 0.07);
  }

  &__canvas {
    display: block;
    width: 100%;
    height: 100%;
    cursor: none;
    touch-action: none;
    outline: none;
  }

  &__reticle,
  &__lock {
    position: absolute;
    z-index: 2;
    width: 28px;
    height: 28px;
    pointer-events: none;
    transform: translate(-50%, -50%);
  }

  &__reticle {
    display: grid;
    place-items: center;
    border: 1px solid rgba(221, 214, 254, 0.7);
    border-radius: 50%;
    box-shadow: 0 0 14px rgba(196, 181, 253, 0.28);
    transition: border-color 90ms ease, box-shadow 90ms ease;

    &::before,
    &::after,
    span::before,
    span::after {
      position: absolute;
      content: "";
      background: #ddd6fe;
      box-shadow: 0 0 6px rgba(196, 181, 253, 0.7);
    }

    &::before,
    &::after {
      width: 1px;
      height: 6px;
    }

    &::before {
      top: -4px;
    }

    &::after {
      bottom: -4px;
    }

    span::before,
    span::after {
      width: 6px;
      height: 1px;
    }

    span::before {
      left: -4px;
    }

    span::after {
      right: -4px;
    }
  }

  &__lock {
    width: 42px;
    height: 42px;
    border: 1px solid rgba(52, 211, 153, 0.88);
    border-radius: 50%;
    box-shadow: 0 0 18px rgba(52, 211, 153, 0.28), inset 0 0 12px rgba(52, 211, 153, 0.14);
    animation: game-target-lock 1.1s ease-in-out infinite;

    &::before,
    &::after,
    span {
      position: absolute;
      display: block;
      content: "";
      background: #6ee7b7;
      box-shadow: 0 0 8px rgba(52, 211, 153, 0.85);
    }

    &::before,
    &::after {
      left: 50%;
      width: 7px;
      height: 1px;
      transform: translateX(-50%);
    }

    &::before {
      top: -2px;
    }

    &::after {
      bottom: -2px;
    }

    span {
      top: 50%;
      left: 50%;
      width: 4px;
      height: 4px;
      border-radius: 50%;
      transform: translate(-50%, -50%);
    }
  }

  &__boss-health {
    position: absolute;
    z-index: 3;
    top: 22px;
    left: 50%;
    width: min(330px, calc(100% - 34px));
    padding: 11px 14px 12px;
    border: 1px solid rgba(240, 171, 252, 0.28);
    border-radius: 12px;
    background: rgba(17, 10, 29, 0.84);
    box-shadow: 0 12px 36px rgba(0, 0, 0, 0.26);
    transform: translateX(-50%);
  }

  &__boss-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 8px;
    color: #f0abfc;
    font: 700 0.66rem/1 var(--mono);
    letter-spacing: 0.08em;

    span {
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    strong {
      white-space: nowrap;
    }

    small {
      color: #b6a2c2;
      font-size: 0.59rem;
      font-weight: 500;
    }
  }

  &__boss-track {
    height: 6px;
    overflow: hidden;
    border-radius: 999px;
    background: rgba(240, 171, 252, 0.13);

    span {
      display: block;
      height: 100%;
      border-radius: inherit;
      background: linear-gradient(90deg, #a855f7, #f0abfc);
      box-shadow: 0 0 14px rgba(240, 171, 252, 0.65);
      transition: width 180ms ease;
    }
  }

  &__state {
    position: absolute;
    z-index: 4;
    inset: 0;
    display: grid;
    place-items: center;
    padding: 22px;
    background: rgba(5, 8, 18, 0.57);
    backdrop-filter: blur(4px);
    animation: game-state-enter 240ms ease both;

    &--victory {
      background: rgba(10, 6, 22, 0.7);
    }
  }

  &__state-card {
    display: flex;
    width: min(410px, 100%);
    flex-direction: column;
    align-items: center;
    padding: clamp(22px, 4vw, 38px);
    border: 1px solid rgba(196, 181, 253, 0.22);
    border-radius: 21px;
    background: linear-gradient(145deg, rgba(24, 21, 43, 0.97), rgba(14, 18, 33, 0.97));
    box-shadow: 0 26px 90px rgba(0, 0, 0, 0.45), inset 0 1px rgba(255, 255, 255, 0.07);
    text-align: center;

    h3 {
      margin: 1px 0 8px;
      color: #fff;
      font-size: clamp(1.45rem, 4vw, 2rem);
      letter-spacing: -0.045em;
    }
  }

  &__state-icon {
    display: grid;
    width: 58px;
    height: 58px;
    margin-bottom: 18px;
    place-items: center;
    border: 1px solid rgba(196, 181, 253, 0.25);
    border-radius: 18px;
    background: rgba(139, 92, 246, 0.16);
    color: #c4b5fd;
    box-shadow: 0 0 34px rgba(139, 92, 246, 0.14);

    .space-game__state--victory & {
      border-color: rgba(103, 232, 249, 0.34);
      background: rgba(34, 211, 238, 0.12);
      color: #67e8f9;
    }
  }

  &__state-copy {
    max-width: 320px;
    margin: 0;
    color: #aeb5c3;
    font-size: 0.88rem;
    line-height: 1.65;

    strong {
      color: #f9fafb;
      font-family: var(--mono);
    }
  }

  &__result-divider {
    padding-inline: 5px;
    color: #7c3aed;
  }

  &__primary-button {
    display: inline-flex;
    min-height: 45px;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 23px;
    padding: 0 19px;
    border: 1px solid rgba(221, 214, 254, 0.55);
    border-radius: 12px;
    background: linear-gradient(110deg, #6d28d9, #7c3aed);
    box-shadow: 0 8px 26px rgba(109, 40, 217, 0.34), inset 0 1px rgba(255, 255, 255, 0.15);
    color: #fff;
    font-size: 0.86rem;
    font-weight: 700;
    transition: transform 160ms ease, box-shadow 160ms ease;

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 32px rgba(109, 40, 217, 0.48);
    }

    &:active {
      transform: translateY(0) scale(0.98);
    }

    &:focus-visible {
      outline: 2px solid #ddd6fe;
      outline-offset: 3px;
    }
  }

  &__instructions {
    display: flex;
    min-height: 23px;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin: 0;
    color: #777f91;
    font: 600 0.6rem/1.3 var(--mono);
    letter-spacing: 0.08em;
  }

  &__instructions kbd {
    padding: 4px 6px;
    border: 1px solid rgba(196, 181, 253, 0.16);
    border-radius: 5px;
    background: rgba(17, 24, 39, 0.8);
    color: #c4b5fd;
    font: inherit;
    letter-spacing: 0.04em;
  }

  &__combo {
    color: #67e8f9;
    text-shadow: 0 0 12px rgba(103, 232, 249, 0.45);
  }

  &__mobile-hint {
    display: none;
  }
}

@keyframes space-game-enter {
  from {
    opacity: 0;
    transform: scale(0.985);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes game-state-enter {
  from {
    opacity: 0;
    transform: translateY(8px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes game-status-pulse {
  50% {
    opacity: 0.45;
  }
}

@keyframes game-target-lock {
  50% {
    box-shadow: 0 0 22px rgba(52, 211, 153, 0.42), inset 0 0 14px rgba(52, 211, 153, 0.2);
    transform: translate(-50%, -50%) scale(1.1);
  }
}

@media (max-width: 760px) {
  .space-game {
    gap: 12px;
    padding: 14px;

    &__header {
      grid-template-columns: 1fr auto;
      gap: 12px;
    }

    &__identity {
      grid-column: 1;
      grid-row: 1;
    }

    &__hud {
      grid-column: 1 / -1;
      grid-row: 2;
      justify-content: space-around;
      gap: 10px;
      padding: 10px 12px;
    }

    &__actions {
      grid-column: 2;
      grid-row: 1;
    }

    &__exit {
      min-width: 40px;
      width: 40px;
      padding: 0;
      overflow: hidden;
      font-size: 0;
    }

    &__arena {
      min-height: 250px;
    }

    &__instructions {
      justify-content: center;
      flex-wrap: wrap;
      gap: 9px 16px;
      font-size: 0.55rem;
    }
  }
}

@media (max-width: 480px) {
  .game-mode-launch {
    top: 14px;
    right: 12px;
    min-height: 34px;
    gap: 6px;
    padding-right: 9px;
    font-size: 10px;
  }

  .game-mode-launch .hero-highlight-icon {
    flex-basis: 22px;
    width: 22px;
    height: 22px;
  }

  .space-game {
    gap: 10px;
    padding: 10px;

    &__identity {
      gap: 8px;
    }

    &__mark {
      width: 36px;
      height: 36px;
      border-radius: 11px;
    }

    &__eyebrow {
      font-size: 0.51rem;
    }

    &__hud {
      gap: 7px;
      padding: 9px 7px;
    }

    &__stat {
      min-width: auto;

      strong {
        font-size: 0.8rem;
      }

      &--status strong {
        font-size: 0.68rem;
      }
    }

    &__stat-label {
      font-size: 0.49rem;
    }

    &__icon-button,
    &__exit {
      min-width: 35px;
      height: 36px;
      border-radius: 9px;
    }

    &__arena {
      min-height: 230px;
      border-radius: 14px;
    }

    &__instructions {
      .space-game__mobile-hint {
        display: inline;
        color: #c4b5fd;
      }

      > span:first-child,
      > span:last-child {
        display: none;
      }
    }
  }
}

@media (prefers-reduced-motion: reduce) {
  .game-mode-launch,
  .space-game *,
  .space-game::before,
  .space-game::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    scroll-behavior: auto !important;
    transition-duration: 0.01ms !important;
  }
}

.site-shell > :not(.particle-background):not(.topbar).space-game {
  position: fixed;
  z-index: 5000;
  inset: 0;
  pointer-events: none;
}

.space-game {
  display: block;
  overflow: visible;
  padding: 0;
  background: transparent;
  animation: none;
  pointer-events: none;

  &__canvas {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
  }

  &__panel {
    position: fixed;
    z-index: 1;
    top: calc(72px + 12px + env(safe-area-inset-top));
    left: clamp(12px, 2vw, 24px);
    display: grid;
    width: min(278px, calc(100vw - 24px));
    gap: 12px;
    padding: 13px;
    border: 1px solid rgba(196, 181, 253, 0.26);
    border-radius: 16px;
    background: color-mix(in srgb, var(--card) 88%, transparent);
    box-shadow:
      0 14px 44px rgba(0, 0, 0, 0.26),
      inset 0 1px rgba(255, 255, 255, 0.07);
    color: var(--text);
    backdrop-filter: blur(18px);
    pointer-events: auto;
  }

  &__victory-toast {
    position: fixed;
    z-index: 2;
    top: clamp(94px, 15vh, 150px);
    left: 50%;
    display: flex;
    width: min(510px, calc(100vw - 32px));
    align-items: center;
    gap: 13px;
    padding: 14px 17px;
    border: 1px solid rgba(103, 232, 249, 0.4);
    border-radius: 16px;
    background: color-mix(in srgb, var(--card) 90%, rgba(34, 211, 238, 0.16));
    box-shadow:
      0 16px 54px rgba(0, 0, 0, 0.3),
      0 0 30px rgba(103, 232, 249, 0.13),
      inset 0 1px rgba(255, 255, 255, 0.09);
    color: var(--text);
    backdrop-filter: blur(18px);
    pointer-events: none;
    transform: translateX(-50%);
    animation: game-victory-toast-enter 420ms cubic-bezier(0.22, 1, 0.36, 1) both;
  }

  &__victory-icon {
    display: grid;
    width: 40px;
    height: 40px;
    flex: 0 0 auto;
    place-items: center;
    border: 1px solid rgba(103, 232, 249, 0.3);
    border-radius: 12px;
    background: rgba(34, 211, 238, 0.12);
    color: #67e8f9;
    box-shadow: 0 0 22px rgba(103, 232, 249, 0.12);
  }

  &__victory-copy {
    display: grid;
    min-width: 0;
    gap: 4px;

    strong {
      color: #f9fafb;
      font: 700 0.92rem/1.2 var(--font-head);
    }

    > span {
      color: var(--muted);
      font-size: 0.72rem;
      line-height: 1.4;
    }
  }

  &__victory-score {
    margin-left: auto;
    color: #67e8f9;
    font: 700 0.68rem/1 var(--mono);
    white-space: nowrap;
  }

  &__panel-heading {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 8px;
  }

  &__mark {
    width: 30px;
    height: 30px;
    border-radius: 9px;
  }

  &__title {
    overflow: hidden;
    color: var(--text);
    font: 700 0.68rem/1 var(--mono);
    letter-spacing: 0.1em;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  &__status-dot {
    flex: 0 0 auto;
    margin-left: auto;
  }

  &__status-text {
    color: var(--muted);
    font-size: 0.65rem;
    white-space: nowrap;
  }

  &__hud {
    justify-content: space-between;
    gap: 12px;
    padding: 9px 10px;
    border-color: var(--line);
    border-radius: 11px;
    background: color-mix(in srgb, var(--bg) 62%, transparent);
  }

  &__stat {
    min-width: 0;

    strong {
      font-size: 0.84rem;
    }

    &--status {
      display: none;
    }
  }

  &__stat-label {
    font-size: 0.49rem;
  }

  &__boss-health {
    position: static;
    width: 100%;
    padding: 8px 10px;
    border-color: rgba(240, 171, 252, 0.24);
    background: rgba(30, 15, 42, 0.72);
    box-shadow: none;
    transform: none;
  }

  &__boss-label {
    margin-bottom: 6px;
    font-size: 0.57rem;
  }

  &__boss-track {
    height: 5px;
  }

  &__actions {
    justify-content: flex-start;
    gap: 6px;
  }

  &__icon-button,
  &__exit {
    min-width: 34px;
    height: 32px;
    border-color: var(--line);
    border-radius: 9px;
    background: color-mix(in srgb, var(--bg) 70%, transparent);
    color: var(--text);
  }

  &__exit {
    min-width: 64px;
    margin-left: auto;
    padding-inline: 9px;
    font-size: 0.7rem;
  }

  &__icon-button:disabled {
    cursor: not-allowed !important;
    opacity: 0.42;
  }

  &__instructions,
  &__result,
  &__sound-error {
    margin: -4px 0 0;
    color: var(--muted);
    font: 500 0.58rem/1.5 var(--mono);
    letter-spacing: 0.015em;
  }

  &__result {
    margin: 0;
    color: #c4b5fd;
  }

  &__sound-error {
    margin: 0;
    color: var(--danger);
  }

  &__combo {
    float: right;
    margin-left: 6px;
    color: #67e8f9;
  }

  &__reticle,
  &__lock {
    position: absolute;
    z-index: 0;
    pointer-events: none;
  }

  &__reticle.is-inactive {
    opacity: 0.42;
  }
}

@media (max-width: 600px) {
  .space-game__victory-toast {
    top: calc(128px + env(safe-area-inset-top));
    gap: 9px;
    padding: 11px;
    border-radius: 13px;
  }

  .space-game__victory-icon {
    width: 34px;
    height: 34px;
  }

  .space-game__victory-copy strong {
    font-size: 0.8rem;
  }

  .space-game__victory-copy > span {
    font-size: 0.65rem;
  }

  .space-game__victory-score {
    font-size: 0.6rem;
  }

  .space-game__panel {
    top: calc(72px + 9px + env(safe-area-inset-top));
    right: auto;
    left: 9px;
    width: min(252px, calc(100vw - 18px));
    gap: 9px;
    padding: 10px;
    border-radius: 14px;
  }

  .space-game__instructions {
    max-width: 100%;
    font-size: 0.53rem;
  }
}

@keyframes game-victory-toast-enter {
  from {
    opacity: 0;
    transform: translate(-50%, -12px) scale(0.97);
  }

  to {
    opacity: 1;
    transform: translate(-50%, 0) scale(1);
  }
}
</style>