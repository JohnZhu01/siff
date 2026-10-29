// Guest loading and collapse controls.
function initGuestControls() {
  document.querySelectorAll(".siff-guests").forEach((section) => {
    const grid = section.querySelector(".wp-block-post-template");
    const more = section.querySelector(".wp-load-more__button");
    const controls = more?.closest(".wp-block-buttons");
    if (!grid || !controls || !more.dataset.queryUrl) return;

    const firstPage = Number(more.dataset.queryNextPage);
    const lastPage = Number(more.dataset.queryMaxPage);
    const initialCount = grid.children.length;
    const region = section.dataset.qllmQueryRegion || "";
    const pages = new Map();
    let nextPage = firstPage;
    let loading = false;

    const less = document.createElement("button");
    less.type = "button";
    less.className = "wp-block-button__link wp-element-button siff-guests-less";
    less.textContent = "See fewer guests";
    less.hidden = true;
    controls.append(less);

    const message = document.createElement("p");
    message.setAttribute("role", "status");
    controls.after(message);

    function pageUrl(pageNumber) {
      const url = new URL(window.location.href);
      url.searchParams.set(more.dataset.queryUrl, pageNumber);
      return url.href;
    }

    // Reuse preloaded pages, including requests still loading.
    function getPage(pageNumber) {
      if (!pages.has(pageNumber)) {
        const request = fetch(pageUrl(pageNumber))
          .then((response) => {
            if (!response.ok) throw new Error("Guest request failed.");
            return response.text();
          })
          .then((html) => {
            const page = new DOMParser().parseFromString(html, "text/html");
            const cards = page.querySelector(
              `.wp-block-query[data-qllm-query-region="${region}"] .wp-block-post-template`,
            );
            if (!cards) throw new Error("Guest cards not found.");
            return cards.innerHTML;
          })
          .catch((error) => {
            pages.delete(pageNumber);
            throw error;
          });
        pages.set(pageNumber, request);
      }
      return pages.get(pageNumber);
    }

    function preloadNextPage() {
      if (nextPage <= lastPage) {
        // Retry on click if preloading fails.
        getPage(nextPage).catch(() => {});
      }
    }

    function updateButtons() {
      more.closest(".wp-block-button").hidden = nextPage > lastPage;
      more.dataset.queryNextPage = nextPage;
      more.href = pageUrl(nextPage);
      more.setAttribute("aria-expanded", String(grid.children.length > initialCount));
      less.hidden = grid.children.length <= initialCount;
    }

    async function loadGuests(event) {
      if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
      event.preventDefault();
      event.stopPropagation();
      if (loading) return;

      loading = true;
      less.disabled = true;
      more.classList.add("loading");
      grid.setAttribute("aria-busy", "true");
      message.textContent = "";

      try {
        const cards = await getPage(nextPage);
        grid.insertAdjacentHTML("beforeend", cards);
        nextPage++;
        updateButtons();
        if (nextPage > lastPage) less.focus({ preventScroll: true });
        preloadNextPage();
      } catch {
        message.textContent = "Guests could not load. Please try again.";
      } finally {
        loading = false;
        less.disabled = false;
        more.classList.remove("loading");
        grid.setAttribute("aria-busy", "false");
      }
    }

    more.addEventListener("click", loadGuests);
    less.addEventListener("click", () => {
      // Keep the original cards and remove the added ones.
      while (grid.children.length > initialCount) {
        grid.lastElementChild.remove();
      }
      nextPage = firstPage;
      message.textContent = "";
      updateButtons();
      more.focus({ preventScroll: true });
      more.scrollIntoView({ block: "nearest" });
    });

    updateButtons();
    preloadNextPage();
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initGuestControls);
} else {
  initGuestControls();
}
