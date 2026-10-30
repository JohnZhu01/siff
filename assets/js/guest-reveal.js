// Reveal initial guest cards once.
function initGuestReveal() {
  const cards = document.querySelectorAll(".siff-guests .wp-block-post-template > li:nth-child(-n + 3)");
  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

  if (!cards.length || !window.IntersectionObserver || reducedMotion.matches) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      observer.unobserve(entry.target);

      if (reducedMotion.matches) return;

      const card = entry.target;
      card.classList.add("siff-guest-reveal");

      card.addEventListener("animationend", () => {
        card.classList.remove("siff-guest-reveal");
      }, { once: true });
    });
  }, { threshold: 0.1 });

  cards.forEach((card) => observer.observe(card));
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initGuestReveal);
} else {
  initGuestReveal();
}
