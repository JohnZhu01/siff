// Show or hide guests already loaded with the page.
function initGuestControls() {
  document.querySelectorAll(".siff-guests").forEach((section) => {
    const grid = section.querySelector(".wp-block-post-template");
    if (!grid) return;

    const cards = Array.from(grid.children);
    const firstCount = 3;
    if (cards.length <= firstCount) return;

    // Replace the old pagination with a simple button.
    section.querySelectorAll(".wp-block-query-pagination, .wp-load-more").forEach((item) => item.remove());
    section.querySelector(".wp-load-more__button")?.closest(".wp-block-buttons")?.remove();

    const controls = document.createElement("div");
    controls.className = "wp-block-buttons siff-guest-controls";
    const button = document.createElement("button");
    button.type = "button";
    button.className = "wp-block-button__link wp-element-button siff-guest-toggle";
    controls.append(button);
    grid.after(controls);
    let expanded = false;

    function updateGuests() {
      cards.forEach((card, index) => {
        card.hidden = !expanded && index >= firstCount;
      });
      button.textContent = expanded ? "See fewer guests" : "See more guests";
      button.setAttribute("aria-expanded", String(expanded));
    }

    // Start downloading all guest images before the button is pressed.
    grid.querySelectorAll("img").forEach((image) => {
      image.loading = "eager";
    });

    button.addEventListener("click", () => {
      expanded = !expanded;
      updateGuests();

      if (expanded) {
        // Start at the first new guest, below the sticky header.
        const header = document.querySelector(".wp-site-blocks > header");
        const headerBottom = header ? Math.max(0, header.getBoundingClientRect().bottom) : 0;
        const firstNewGuest = cards[firstCount];
        const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

        firstNewGuest.setAttribute("tabindex", "-1");
        firstNewGuest.focus({ preventScroll: true });
        window.scrollTo({
          top: Math.max(0, window.scrollY + firstNewGuest.getBoundingClientRect().top - headerBottom - 24),
          behavior: reducedMotion.matches ? "instant" : "smooth",
        });
      } else {
        button.scrollIntoView({ block: "nearest" });
      }
    });
    updateGuests();
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initGuestControls);
} else {
  initGuestControls();
}
