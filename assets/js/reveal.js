document.addEventListener("DOMContentLoaded", () => {
  const items = document.querySelectorAll(".reveal");
  if (!items.length) return;

  items.forEach((el, idx) => {
    const delay = Math.min(idx * 70, 280); // máximo 280ms
    el.dataset.revealDelay = String(delay);
  });

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        const el = entry.target;

        if (entry.isIntersecting) {
          el.style.transitionDelay = `${el.dataset.revealDelay || 0}ms`;
          el.classList.add("is-visible");
        } else {
          el.style.transitionDelay = "0ms";
          el.classList.remove("is-visible");
        }
      });
    },
    {
      threshold: 0.15,              
      rootMargin: "0px 0px -5% 0px" 
    }
  );

  items.forEach((el) => observer.observe(el));
});