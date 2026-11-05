(() => {
  const clamp = (n, min, max) => Math.max(min, Math.min(max, n));

  function normalizeSlider(ba) {
    let beforeLayer = ba.querySelector(".ba__before") || ba.querySelector(".ba-before");
    let afterLayer  = ba.querySelector(".ba__after")  || ba.querySelector(".ba-after");

    if (!beforeLayer || !afterLayer) {
      const imgs = Array.from(ba.querySelectorAll("img"));
      if (imgs.length >= 2) {
        const imgBefore = imgs[0];
        const imgAfter  = imgs[1];

        afterLayer = document.createElement("div");
        afterLayer.className = "ba__layer ba__after";
        afterLayer.appendChild(imgAfter);

        beforeLayer = document.createElement("div");
        beforeLayer.className = "ba__layer ba__before";
        beforeLayer.appendChild(imgBefore);

        ba.innerHTML = "";
        ba.appendChild(afterLayer);
        ba.appendChild(beforeLayer);
      }
    }

    if (!beforeLayer || !afterLayer) return null;

    let line = ba.querySelector(".ba__line") || ba.querySelector(".ba-divider");
    if (!line) {
      line = document.createElement("div");
      line.className = "ba__line";
      ba.appendChild(line);
    }

    let knob = ba.querySelector(".ba__knob") || ba.querySelector(".ba-handle");
    if (!knob) {
      knob = document.createElement("div");
      knob.className = "ba__knob";
      knob.innerHTML = "<span>↔</span>";
      ba.appendChild(knob);
    } else if (!knob.textContent.trim() && knob.children.length === 0) {
      knob.innerHTML = "<span>↔</span>";
    }

    let range = ba.querySelector(".ba__range") || ba.querySelector(".ba-range");
    if (!range) {
      range = document.createElement("input");
      range.type = "range";
      range.className = "ba__range";
      range.min = "0";
      range.max = "100";
      range.step = "0.1";
      range.value = "50";
      range.setAttribute("aria-label", "Antes / Depois");
      ba.appendChild(range);
    } else {
      if (!range.min) range.min = "0";
      if (!range.max) range.max = "100";
      if (!range.step) range.step = "0.1";
      if (!range.value) range.value = "50";
    }

    return { beforeLayer, afterLayer, line, knob, range };
  }

  function setPos(ba, parts, posPercent) {
    const pos = clamp(posPercent, 0, 100);

    ba.style.setProperty("--pos", `${pos}%`);

    parts.range.value = String(pos);
  }

  function posFromPointer(ba, e) {
    const rect = ba.getBoundingClientRect();
    const x = e.clientX - rect.left;
    return clamp((x / rect.width) * 100, 0, 100);
  }

  function initSlider(ba) {
    const parts = normalizeSlider(ba);
    if (!parts) return;

    const initial = parseFloat(parts.range.value || "50");
    setPos(ba, parts, isNaN(initial) ? 50 : initial);

    parts.range.addEventListener("input", () => {
      const v = parseFloat(parts.range.value);
      setPos(ba, parts, isNaN(v) ? 50 : v);
    });

    let dragging = false;
    let raf = 0;
    let lastPos = null;

    const schedule = () => {
      if (raf) return;
      raf = requestAnimationFrame(() => {
        raf = 0;
        if (lastPos !== null) setPos(ba, parts, lastPos);
      });
    };

    const onDown = (e) => {
      dragging = true;
      try { ba.setPointerCapture(e.pointerId); } catch (_) {}
      lastPos = posFromPointer(ba, e);
      schedule();
    };

    const onMove = (e) => {
      if (!dragging) return;
      lastPos = posFromPointer(ba, e);
      schedule();
    };

    const onUp = () => {
      dragging = false;
      lastPos = null;
    };

    ba.addEventListener("pointerdown", onDown);
    ba.addEventListener("pointermove", onMove);
    ba.addEventListener("pointerup", onUp);
    ba.addEventListener("pointercancel", onUp);
    ba.addEventListener("pointerleave", onUp);

    // Setas do teclado
    if (!ba.hasAttribute("tabindex")) ba.setAttribute("tabindex", "0");
    ba.addEventListener("keydown", (e) => {
      if (e.key !== "ArrowLeft" && e.key !== "ArrowRight") return;
      e.preventDefault();
      const current = parseFloat(parts.range.value || "50");
      const step = e.shiftKey ? 10 : 2;
      const next = current + (e.key === "ArrowRight" ? step : -step);
      setPos(ba, parts, next);
    });
  }

  function boot() {
    document.querySelectorAll(".ba").forEach(initSlider);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();