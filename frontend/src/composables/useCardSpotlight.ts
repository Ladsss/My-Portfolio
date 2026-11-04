import { onMounted, onUnmounted } from "vue";

const spotlightCardSelector =
  ".project-card, .skill-card, .principles > div, .timeline-item";

export function useCardSpotlight() {
  let siteShell: HTMLElement | null = null;

  function updateSpotlight(event: PointerEvent) {
    if (event.pointerType === "touch") return;

    siteShell?.style.setProperty("--global-spotlight-x", `${event.clientX}px`);
    siteShell?.style.setProperty("--global-spotlight-y", `${event.clientY}px`);
    siteShell?.style.setProperty("--global-spotlight-opacity", "1");

    const target = event.target;
    if (!(target instanceof Element)) return;

    const card = target.closest<HTMLElement>(spotlightCardSelector);
    if (!card) return;

    const bounds = card.getBoundingClientRect();
    card.style.setProperty(
      "--spotlight-x",
      `${((event.clientX - bounds.left) / bounds.width) * 100}%`,
    );
    card.style.setProperty(
      "--spotlight-y",
      `${((event.clientY - bounds.top) / bounds.height) * 100}%`,
    );
  }

  function hideSpotlight() {
    siteShell?.style.setProperty("--global-spotlight-opacity", "0");
  }

  onMounted(() => {
    if (window.matchMedia("(pointer: coarse)").matches) return;
    siteShell = document.querySelector<HTMLElement>(".site-shell");
    if (!siteShell) return;

    document.addEventListener("pointermove", updateSpotlight, {
      passive: true,
    });
    window.addEventListener("pointerleave", hideSpotlight);
  });

  onUnmounted(() => {
    document.removeEventListener("pointermove", updateSpotlight);
    window.removeEventListener("pointerleave", hideSpotlight);
    siteShell?.style.removeProperty("--global-spotlight-x");
    siteShell?.style.removeProperty("--global-spotlight-y");
    siteShell?.style.removeProperty("--global-spotlight-opacity");
    siteShell = null;
  });
}
