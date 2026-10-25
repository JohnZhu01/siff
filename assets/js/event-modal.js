// Loading spinner.
function showEventLoader(container) {
  container.innerHTML = `
        <div role="status">
            <div class="siff-modal-loader" aria-hidden="true"></div>
            <span class="screen-reader-text">Loading event details…</span>
        </div>
    `;
}

// Build detail rows.
function addEventDetail(container, label, value, startsCredit = false) {
  if (!value) return;
  const paragraph = document.createElement("p");

  if (startsCredit) {
    paragraph.classList.add("siff-modal-credit-start");
  }
  if (label) {
    const labelText = document.createElement("strong");
    labelText.textContent = label + ":";
    const detailText = document.createElement("span");
    const dateParts =
      label === "Date and time"
        ? String(value).match(
            /^(\d{1,2}\/\d{1,2}\/\d{4}|\d{4}-\d{2}-\d{2})[,\sT]*(\d{1,2}:\d{2}(?::\d{2})?\s*(?:am|pm)?)$/i,
          )
        : null;

    if (dateParts) {
      detailText.append(
        dateParts[1],
        document.createElement("br"),
        dateParts[2],
      );
    } else {
      detailText.textContent = value;
    }
    paragraph.append(labelText, " ", detailText);
  } else {
    paragraph.textContent = value;
  }
  container.append(paragraph);
}

// Event modal.
function initEventModal() {
  const modal = document.querySelector("#siff-event-modal");
  const closeButton = document.querySelector("#siff-modal-close");
  const title = document.querySelector("#siff-modal-title");
  const content = document.querySelector("#siff-modal-content");
  const meta = document.querySelector("#siff-modal-meta");
  const descriptionTitle = document.querySelector(
    "#siff-modal-description-title",
  );
  const image = document.querySelector("#siff-modal-image");

  if (!modal || !closeButton || !title || !content || !image || !meta) return;
  let activeRequest;
  let activeTrigger;
  const eventCache = new Map(Object.entries(siffModal.events || {}));

  document.addEventListener("click", async (event) => {
    // Ignore clicks inside the modal.
    if (modal.contains(event.target)) return;

    if (!(event.target instanceof Element)) return;
    let trigger = event.target.closest("[data-event-id]");
    if (!trigger) {
      const card = event.target.closest(".siff-event-card");
      if (!card) return;
      if (event.target.closest("a, button, input, select, textarea, label"))
        return;
      trigger = card.querySelector(".siff-event-trigger a[data-event-id]");
    }
    if (!trigger) return;
    if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey)
      return;

    event.preventDefault();
    activeTrigger = trigger;
    activeRequest?.abort();
    const request = new AbortController();
    activeRequest = request;

    // Reset details before loading.
    const eventId = trigger.dataset.eventId;
    const loadingStarted = performance.now();
    const minimumLoadingTime = 500;
    title.textContent = "Event details";
    if (descriptionTitle) descriptionTitle.textContent = "Description";
    showEventLoader(content);
    meta.replaceChildren();
    image.hidden = true;
    image.removeAttribute("src");
    if (!modal.open) modal.showModal();

    try {
      let details = eventCache.get(eventId);

      // Fetch details not already cached.
      if (!details) {
        const url = new URL(encodeURIComponent(eventId), siffModal.restUrl);

        const response = await fetch(url, {
          signal: request.signal,
        });

        const result = await response.json();

        if (!response.ok) {
          throw new Error(result.message || "Unable to load this event.");
        }

        if (request.signal.aborted) return;

        details = result;
        eventCache.set(eventId, details);
      }
      const remaining =
        minimumLoadingTime - (performance.now() - loadingStarted);
      if (remaining > 0) {
        await new Promise((resolve) => setTimeout(resolve, remaining));
      }
      if (request.signal.aborted || !modal.open) return;

      title.textContent = details.title;
      const types = details.types || [];
      if (descriptionTitle) {
        descriptionTitle.textContent = types.includes("film")
          ? "Synopsis"
          : "Description";
      }
      if (details.image) {
        image.src = details.image;
        image.alt = "";
        image.hidden = false;
      }
      content.replaceChildren();

      addEventDetail(content, "", details.description);
      addEventDetail(meta, "Date and time", details.date);
      addEventDetail(meta, "Venue", details.venue);
      addEventDetail(meta, "Duration", details.duration);
      if (types.includes("film")) {
        addEventDetail(meta, "Filmmaker", details.filmmaker, true);
        addEventDetail(meta, "Director", details.director, !details.filmmaker);
      } else if (types.includes("workshop")) {
        addEventDetail(meta, "Instructor", details.instructor, true);
        addEventDetail(meta, "Profession", details.instructor_profession);
      } else if (types.includes("panel")) {
        addEventDetail(meta, "Speaker", details.speaker, true);
        addEventDetail(meta, "Profession", details.speaker_profession);
      }
    } catch (error) {
      if (!request.signal.aborted) {
        content.textContent = "Unable to load event details. Please try again.";
      }
    }
  });

  // Close button and backdrop clicks.
  closeButton.addEventListener("click", () => modal.close());
  modal.addEventListener("click", (event) => {
    const bounds = modal.getBoundingClientRect();

    const clickedOutside =
      event.clientX < bounds.left ||
      event.clientX > bounds.right ||
      event.clientY < bounds.top ||
      event.clientY > bounds.bottom;

    if (event.target === modal && clickedOutside) {
      modal.close();
    }
  });
  modal.addEventListener("close", () => {
    activeRequest?.abort();
    activeTrigger?.focus();
  });
}

// Event filters and search.
function initEventFilters() {
  document.querySelectorAll(".siff-event-filters").forEach((filters) => {
    const query = filters.closest(".wp-block-query");
    const grid = query?.querySelector(".wp-block-post-template");
    if (!grid) return;
    const searchForm = query.querySelector(".siff-event-search");
    const searchInput = searchForm?.querySelector('input[type="search"]');
    let activeType = "all";

    const cards = [...grid.children].filter((card) =>
      card.classList.contains("wp-block-post"),
    );

    const types = ["all", "film", "workshop", "panel"];
    const controls = [];

    types.forEach((type) => {
      const link = filters.querySelector(
        `.siff-filter-${type} .wp-block-button__link`,
      );
      if (!link) return;

      // Replace filter links with buttons.
      const button = document.createElement("button");
      button.type = "button";
      button.className = link.className;
      button.textContent = link.textContent;
      link.replaceWith(button);

      controls.push({ type, button });
    });

    // Results count and card wrapper.
    const status = document.createElement("p");
    status.setAttribute("role", "status");
    status.className = "siff-event-results";
    const resultsSection = document.createElement("div");
    resultsSection.className = "siff-event-results-section";
    grid.before(resultsSection);
    resultsSection.append(status, grid);

    // Card animations.
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const cardAnimations = new Map();

    function clearCardAnimations() {
      cardAnimations.forEach((animation) => animation.cancel());
      cardAnimations.clear();
    }

    reducedMotion.addEventListener("change", clearCardAnimations);

    // Match cards by category and search text.
    function applyFilter(type, animate = false) {
      clearCardAnimations();
      activeType = type;
      const searchText = (searchInput?.value || "").trim().toLowerCase();
      let count = 0;

      cards.forEach((card) => {
        const title = (
          card.querySelector(".wp-block-post-title")?.textContent || ""
        ).toLowerCase();

        const matchesType =
          type === "all" || card.classList.contains(`siff-event-types-${type}`);

        const matches = matchesType && title.includes(searchText);

        card.hidden = !matches;
        if (matches) {
          count++;
          if (animate && !reducedMotion.matches) {
            const animation = card.animate(
              [
                { opacity: 0, transform: "translateY(8px)" },
                { opacity: 1, transform: "translateY(0)" },
              ],
              {
                duration: 300,
                delay: Math.min((count - 1) * 30, 150),
                easing: "ease-out",
                fill: "backwards",
              },
            );
            cardAnimations.set(card, animation);
            animation.onfinish = () => cardAnimations.delete(card);
          }
        }
      });

      controls.forEach((control) => {
        control.button.setAttribute(
          "aria-pressed",
          String(control.type === type),
        );
      });

      status.textContent = count
        ? `${count} event${count === 1 ? "" : "s"} shown`
        : "No matching events.";
    }

    // Filter and search controls.
    controls.forEach(({ type, button }) => {
      button.addEventListener("click", () => applyFilter(type, true));
    });
    searchInput?.addEventListener("input", () => {
      applyFilter(activeType);
    });

    searchForm?.addEventListener("submit", (event) => {
      event.preventDefault();
      applyFilter(activeType);
    });
    applyFilter("all");
  });
}

// Guest expand and collapse.
function initGuestControls() {
  document.querySelectorAll(".siff-guests").forEach((query) => {
    const grid = query.querySelector(".wp-block-post-template");
    const more = query.querySelector(".wp-load-more__button");
    const pagination = more?.closest(".wp-block-buttons");

    if (!grid || !pagination) return;

    more.setAttribute("aria-expanded", "false");
    const originalPagination = pagination.cloneNode(true);
    const initialCount = grid.children.length;
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    let heightAnimation = null;
    let loadingGuests = false;
    let restoreFocusAfterLoad = false;

    // Hold the grid height while guests load.
    function freezeGuestGrid() {
      const height = grid.getBoundingClientRect().height;
      heightAnimation?.cancel();
      heightAnimation = null;
      grid.style.height = `${height}px`;
      grid.style.overflow = "hidden";
      return height;
    }

    // Animate the height change.
    function animateGuestGrid(fromHeight) {
      grid.style.height = "auto";
      const toHeight = grid.getBoundingClientRect().height;

      function clearHeight() {
        grid.style.removeProperty("height");
        grid.style.removeProperty("overflow");
        heightAnimation = null;
      }

      if (reducedMotion.matches || Math.abs(toHeight - fromHeight) < 1) {
        clearHeight();
        return toHeight;
      }

      grid.style.height = `${toHeight}px`;
      heightAnimation = grid.animate(
        [{ height: `${fromHeight}px` }, { height: `${toHeight}px` }],
        { duration: 400, easing: "cubic-bezier(0.22, 1, 0.36, 1)" },
      );
      heightAnimation.onfinish = clearHeight;
      return toHeight;
    }

    // Collapse button.
    const less = document.createElement("button");
    less.type = "button";
    less.className = more.className;
    less.classList.remove("wp-load-more__button", "loading");
    less.classList.add("siff-guests-less");
    less.textContent = "See less guests";
    less.setAttribute("aria-expanded", "true");

    const lessPagination = originalPagination.cloneNode(true);
    lessPagination.classList.add("siff-guests-less-controls");
    lessPagination.querySelector(".wp-load-more__button").replaceWith(less);
    lessPagination.hidden = true;
    query.append(lessPagination);

    // Guest loading hooks.
    document.addEventListener("qllmLoadStart", () => {
      const loadingButton = query.querySelector(".wp-load-more__button.loading");
      if (!loadingButton) return;
      loadingGuests = true;
      restoreFocusAfterLoad = loadingButton.contains(document.activeElement);
      freezeGuestGrid();
      less.disabled = true;
    });

    document.addEventListener("qllmLoadEnd", () => {
      if (!loadingGuests) return;
      loadingGuests = false;
      const fromHeight = grid.getBoundingClientRect().height;
      less.disabled = false;
      const expanded = grid.children.length > initialCount;

      query.querySelectorAll(".wp-load-more__button").forEach((button) => {
        const controls = button.closest(".wp-block-buttons");
        if (!controls) return;
        controls.hidden = expanded;
      });

      lessPagination.hidden = !expanded;
      animateGuestGrid(fromHeight);
      if (expanded && restoreFocusAfterLoad) less.focus({ preventScroll: true });
    });

    // Restore the initial guests and controls.
    less.addEventListener("click", () => {
      const fromHeight = freezeGuestGrid();
      // Keep the original portraits.
      [...grid.children].slice(initialCount).forEach((card) => card.remove());

      query.querySelectorAll(".wp-load-more__button").forEach((button) => {
        button.closest(".wp-block-buttons")?.remove();
      });

      const restoredPagination = originalPagination.cloneNode(true);
      lessPagination.before(restoredPagination);
      lessPagination.hidden = true;
      const collapsedHeight = animateGuestGrid(fromHeight);
      restoredPagination
        .querySelector(".wp-load-more__button")
        ?.focus({ preventScroll: true });

      const headerHeight =
        document.querySelector(".wp-site-blocks > header")?.getBoundingClientRect().height || 0;
      if (grid.getBoundingClientRect().top + collapsedHeight < headerHeight + 24) {
        window.scrollTo({
          top: Math.max(
            0,
            window.scrollY + query.getBoundingClientRect().top - headerHeight - 24,
          ),
          behavior: reducedMotion.matches ? "instant" : "smooth",
        });
      }
    });
  });
}

initEventModal();
initEventFilters();
initGuestControls();
