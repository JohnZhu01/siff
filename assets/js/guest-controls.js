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
    button.className = "wp-block-button__link wp-element-button siff-guests-less";
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
      if (!expanded) button.scrollIntoView({ block: "nearest" });
    });
    updateGuests();
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initGuestControls);
} else {
  initGuestControls();
}
