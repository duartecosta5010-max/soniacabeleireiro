(() => {
  const THRESHOLD_PX = 140; 
  const STORAGE_KEY = "bottomPanelDismissed";

  const $panel = document.getElementById("bottomPanel");
  const $overlay = document.getElementById("bottomPanelOverlay");
  if (!$panel || !$overlay) return;

  const $closeBtn = $panel.querySelector("[data-bottom-panel-close]");

  let isOpen = false;

  function docHeight() {
    return Math.max(
      document.documentElement.scrollHeight,
      document.body.scrollHeight
    );
  }

  function isPageScrollable() {
    return docHeight() > window.innerHeight + 200;
  }

  function nearBottom() {
    const scrollBottom = window.scrollY + window.innerHeight;
    const height = docHeight();
    return scrollBottom >= height - THRESHOLD_PX;
  }

  function openPanel() {
    if (isOpen) return;
    isOpen = true;

    document.body.classList.add("bottom-panel-open");
    $panel.setAttribute("aria-hidden", "false");
    $overlay.setAttribute("aria-hidden", "false");
  }

  function closePanel(setDismissed = true) {
    if (!isOpen) return;
    isOpen = false;

    document.body.classList.remove("bottom-panel-open");
    $panel.setAttribute("aria-hidden", "true");
    $overlay.setAttribute("aria-hidden", "true");

    if (setDismissed) {
      sessionStorage.setItem(STORAGE_KEY, "1");
    }
  }

  function tick() {
    if (sessionStorage.getItem(STORAGE_KEY) === "1") return;
    if (!isPageScrollable()) return;

    if (nearBottom()) openPanel();
  }

  window.addEventListener("scroll", tick, { passive: true });
  window.addEventListener("resize", tick);

  if ($closeBtn) $closeBtn.addEventListener("click", () => closePanel(true));
  $overlay.addEventListener("click", () => closePanel(true));

  window.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closePanel(true);
  });

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => setTimeout(tick, 150));
  } else {
    setTimeout(tick, 150);
  }
})();