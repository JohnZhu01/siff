// Measurements.
function toRem(value) {
  const rootSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
  return value / rootSize + "rem";
}

// Loading spinner.
function showEventLoader(container) {
  container.innerHTML = `
    <div role="status">
      <div class="siff-modal-loader" aria-hidden="true"></div>
      <span class="screen-reader-text">Loading event details...</span>
    </div>
  `;
}

// Detail rows.
function addEventDetail(container, label, value) {
  if (!value) return;

  // Separate the date and time.
  if (label === "Date and time") {
    const parts = String(value).match(
      /^(\d{1,2}\/\d{1,2}\/\d{4}|\d{4}-\d{2}-\d{2})[,\sT]*(\d{1,2}:\d{2}(?::\d{2})?\s*(?:am|pm)?)$/i,
    );

    if (parts) {
      let year;
      let month;
      let day;

      if (parts[1].includes("/")) {
        const dateParts = parts[1].split("/");
        day = Number(dateParts[0]);
        month = Number(dateParts[1]);
        year = Number(dateParts[2]);
      } else {
        const dateParts = parts[1].split("-");
        year = Number(dateParts[0]);
        month = Number(dateParts[1]);
        day = Number(dateParts[2]);
      }
      const date = new Date(Date.UTC(year, month - 1, day));
      const displayDate = date.toLocaleDateString("en-GB", {
        day: "numeric",
        month: "short",
        year: "numeric",
        timeZone: "UTC",
      });

      addEventDetail(container, "Date", displayDate);
      addEventDetail(container, "Time", parts[2].toUpperCase());
      return;
    }
  }

  const paragraph = document.createElement("p");

  if (label) {
    const labelText = document.createElement("strong");
    labelText.textContent = label + ":";
    const detailText = document.createElement("span");
    detailText.textContent = value;
    paragraph.append(labelText, " ", detailText);
  } else {
    paragraph.textContent = value;
  }
  container.append(paragraph);
}

// Event modal.
function initEventModal() {
  const modal = document.querySelector("#siff-event-modal");
  const modalBody = document.querySelector(
    "#siff-event-modal .siff-modal-scroll",
  );
  const closeButton = document.querySelector("#siff-modal-close");
  const title = document.querySelector("#siff-modal-title");
  const content = document.querySelector("#siff-modal-content");
  const meta = document.querySelector("#siff-modal-meta");
  const descriptionTitle = document.querySelector("#siff-modal-description-title");
  const credits = document.querySelector("#siff-modal-credits");
  const detailsSection = document.querySelector(".siff-modal-info");
  const mobileModal = window.matchMedia("(max-width: 47.9375rem)");

  const image = document.querySelector("#siff-modal-image");
  const imageArea = image?.closest(".siff-modal-image");

  if (!modal || !modalBody || !closeButton || !title || !content || !meta
      || !credits || !detailsSection || !descriptionTitle || !imageArea) return;

  function resetDetails() {
    detailsSection.open = !mobileModal.matches;
  }

  // Collapse details on mobile.
  resetDetails();
  mobileModal.addEventListener("change", resetDetails);
  detailsSection.querySelector("summary").addEventListener("click", (event) => {
    if (!mobileModal.matches) event.preventDefault();
  });

  let requestNumber = 0;
  let activeTrigger;
  const eventCache = siffModal.events || {};

  image.addEventListener("error", () => {
    imageArea.hidden = true;
  });

  function renderDetails(details) {
    const types = details.types || [];
    modal.classList.remove("is-loading");
    title.textContent = details.title;
    if (types.includes("film")) {
      descriptionTitle.textContent = "Synopsis";
    } else {
      descriptionTitle.textContent = "Overview";
    }
    imageArea.hidden = !details.image;
    if (details.image) {
      image.src = details.image;
      image.alt = "";
      image.hidden = false;
    }

    content.replaceChildren();
    addEventDetail(content, "", details.description);
    addEventDetail(meta, "Date and time", details.date);
    addEventDetail(meta, "Venue", details.venue);
    addEventDetail(credits, "Duration", details.duration);

    if (types.includes("film")) {
      addEventDetail(credits, "Filmmaker", details.filmmaker);
      addEventDetail(credits, "Director", details.director);
    } else if (types.includes("workshop")) {
      addEventDetail(credits, "Instructor", details.instructor);
      addEventDetail(credits, "Profession", details.instructor_profession);
    } else if (types.includes("panel")) {
      addEventDetail(credits, "Speaker", details.speaker);
      addEventDetail(credits, "Profession", details.speaker_profession);
    }
  }

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
    requestNumber++;
    const currentRequest = requestNumber;

    const eventId = trigger.dataset.eventId;
    title.textContent = "Event details";
    resetDetails();
    descriptionTitle.textContent = "Overview";
    modal.classList.add("is-loading");
    showEventLoader(content);
    meta.replaceChildren();
    credits.replaceChildren();
    image.hidden = true;
    imageArea.hidden = true;
    image.removeAttribute("src");
    if (!modal.open) modal.showModal();
    modalBody.scrollTop = 0;

    try {
      let details = eventCache[eventId];

      if (!details) {
        const url = new URL(encodeURIComponent(eventId), siffModal.restUrl);

        const response = await fetch(url);

        const result = await response.json();

        if (!response.ok) {
          throw new Error(result.message || "Unable to load this event.");
        }

        if (currentRequest !== requestNumber) return;

        details = result;
        eventCache[eventId] = details;
      }
      if (currentRequest !== requestNumber || !modal.open) return;

      renderDetails(details);
    } catch (error) {
      if (currentRequest === requestNumber && modal.open) {
        modal.classList.remove("is-loading");
        content.textContent = "Unable to load event details. Please try again.";
      }
    }
  });

  closeButton.addEventListener("click", () => modal.close());
  let pointerStartedOutside = false;

  function isOutsideModal(event) {
    const bounds = modal.getBoundingClientRect();
    return (
      event.clientX < bounds.left ||
      event.clientX > bounds.right ||
      event.clientY < bounds.top ||
      event.clientY > bounds.bottom
    );
  }

  modal.addEventListener("pointerdown", (event) => {
    pointerStartedOutside = event.button === 0 && event.target === modal && isOutsideModal(event);
  });

  modal.addEventListener("click", (event) => {
    if (pointerStartedOutside && event.target === modal && isOutsideModal(event)) {
      modal.close();
    }
    pointerStartedOutside = false;
  });

  modal.addEventListener("close", () => {
    pointerStartedOutside = false;
    requestNumber++;
    modal.classList.remove("is-loading");
    activeTrigger?.focus();
  });
}

// Event filters and search.
function initEventFilters() {
  const filterGroups = document.querySelectorAll(".siff-event-filters");
  if (!filterGroups.length) return;

  const header = document.querySelector(
    ".wp-site-blocks > header.wp-block-template-part",
  );

  // Only allow space for headers that stay on screen.
  function getHeaderOffset() {
    if (header) {
      const styles = getComputedStyle(header);
      if (styles.position === "sticky" || styles.position === "fixed") {
        return header.getBoundingClientRect().height + (parseFloat(styles.top) || 0);
      }
    }

    const adminBar = document.querySelector("#wpadminbar");
    return adminBar && getComputedStyle(adminBar).position === "fixed"
      ? adminBar.getBoundingClientRect().height
      : 0;
  }

  function updateFilterOffset() {
    document.documentElement.style.setProperty(
      "--siff-event-filters-top", toRem(getHeaderOffset()),
    );
  }

  window.addEventListener("load", updateFilterOffset);
  window.addEventListener("resize", updateFilterOffset);
  updateFilterOffset();

  filterGroups.forEach((filters) => {
    const query = filters.closest(".wp-block-query");
    const grid = query?.querySelector(".wp-block-post-template");
    if (!grid) return;
    const searchForm = query.querySelector(".siff-event-search");
    const searchInput = searchForm?.querySelector('input[type="search"]');
    let activeType = "all";

    const cards = [...grid.children]
      .filter((card) => card.classList.contains("wp-block-post"))
      .map((element) => ({
        element,
        title: (element.querySelector(".wp-block-post-title")?.textContent || "").toLowerCase(),
        link: element.querySelector(".wp-block-post-title a"),
      }));

  const types = ["all", "film", "workshop", "panel"];
  const controls = [];

  types.forEach((type) => {
    const link = filters.querySelector(
      `.siff-filter-${type} .wp-block-button__link`,
    );
    if (!link) return;

    // Use buttons for keyboard access.
    const button = document.createElement("button");
    button.type = "button";
    button.className = link.className;
    button.textContent = link.textContent;
    link.replaceWith(button);

      controls.push({ type, button });
    });

    // Mobile filter.
    const mobileFilter = document.createElement("label");
    mobileFilter.className = "siff-mobile-filter";
    mobileFilter.textContent = "Event type";

    const select = document.createElement("select");

    controls.forEach(({ type, button }) => {
      select.add(new Option(button.textContent, type));
    });

    mobileFilter.append(select);
    filters.prepend(mobileFilter);

    // Results area.
    const status = document.createElement("p");
    status.setAttribute("role", "status");
    status.className = "siff-event-results";
    const resultsSection = document.createElement("div");
    resultsSection.className = "siff-event-results-section";
    grid.before(resultsSection);
    resultsSection.append(status, grid);

    // Show six events at a time.
    const batchSize = 6;
    let visibleCards = [];
    const loadMore = document.createElement("button");
    loadMore.type = "button";
    loadMore.className = "siff-event-load-more";
    loadMore.textContent = "Load more events";
    resultsSection.append(loadMore);

    // Card animations.
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    let cardAnimations = [];

    function clearCardAnimations() {
      cardAnimations.forEach((animation) => animation.cancel());
      cardAnimations = [];
    }

    reducedMotion.addEventListener("change", clearCardAnimations);

    // Filter the cards.
    function applyFilter(type, animate = false, limit = batchSize) {
      clearCardAnimations();
      activeType = type;
      select.value = type;
      const searchText = (searchInput?.value || "").trim().toLowerCase();
      let count = 0;
      let animationIndex = 0;
      visibleCards = [];

      cards.forEach((card) => {
        const { element, title } = card;
        const matchesType =
          type === "all" || element.classList.contains(`siff-event-types-${type}`);

      const matches = matchesType && title.includes(searchText);

        if (matches) count++;
        const wasHidden = element.hidden;
        element.hidden = !matches || count > limit;
        if (!element.hidden) {
          visibleCards.push(card);
          if (
            animate &&
            !reducedMotion.matches &&
            (limit === batchSize || wasHidden)
          ) {
            const animation = element.animate(
              [
                { opacity: 0, transform: "translateY(0.5rem)" },
                { opacity: 1, transform: "translateY(0)" },
              ],
              {
                duration: 300,
                delay: Math.min(animationIndex++ * 30, 150),
                easing: "ease-out",
                fill: "backwards",
              },
            );
            cardAnimations.push(animation);
            animation.onfinish = () => {
              const index = cardAnimations.indexOf(animation);
              if (index !== -1) cardAnimations.splice(index, 1);
            };
          }
        }
      });

    controls.forEach((control) => {
      control.button.setAttribute(
        "aria-pressed",
        String(control.type === type),
      );
    });

      const shown = visibleCards.length;
      loadMore.hidden = shown >= count;
      status.textContent = count
        ? `Showing ${shown} of ${count} event${count === 1 ? "" : "s"}`
        : "No matching events.";
    }

    // Scroll to the results.
    function changeEventType(type) {
      applyFilter(type, true);

      requestAnimationFrame(() => {
        const filterStyles = getComputedStyle(filters);
        const offset = filterStyles.position === "sticky"
          ? (parseFloat(filterStyles.top) || 0) + filters.getBoundingClientRect().height
          : getHeaderOffset();

        window.scrollTo({
          top: Math.max(
            0,
            window.scrollY + resultsSection.getBoundingClientRect().top - offset - 12,
          ),
          behavior: reducedMotion.matches ? "instant" : "smooth",
        });
      });
    }

    // Filter and search controls.
    loadMore.addEventListener("click", () => {
      const previousCount = visibleCards.length;
      applyFilter(activeType, true, previousCount + batchSize);
      visibleCards[previousCount]?.link?.focus({ preventScroll: true });
    });

    select.addEventListener("change", () => {
      changeEventType(select.value);
    });

    controls.forEach(({ type, button }) => {
      button.addEventListener("click", () => changeEventType(type));
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

// Guest controls.
function initGuestControls() {
document.querySelectorAll(".siff-guests").forEach((query) => {
  const grid = query.querySelector(".wp-block-post-template");
  const more = query.querySelector(".wp-load-more__button");
  const pagination = more?.closest(".wp-block-buttons");

  if (!grid || !pagination) return;

    more.setAttribute("aria-expanded", "false");
    const originalPagination = pagination.cloneNode(true);
    const originalCards = grid.innerHTML;
    const initialCount = grid.children.length;
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    let heightAnimation = null;
    let loadingGuests = false;
    let loadingHeight = 0;

    // Keep the grid steady while loading.
    function freezeGuestGrid() {
      const height = grid.getBoundingClientRect().height;
      heightAnimation?.cancel();
      heightAnimation = null;
      grid.style.height = toRem(height);
      grid.style.overflow = "hidden";
      return height;
    }

    // Animate the grid height.
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

      grid.style.height = toRem(toHeight);
      heightAnimation = grid.animate(
        [{ height: toRem(fromHeight) }, { height: toRem(toHeight) }],
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

  document.addEventListener("qllmLoadStart", () => {
    if (!query.querySelector(".wp-load-more__button.loading")) return;
    if (loadingGuests) return;
    loadingGuests = true;
    loadingHeight = freezeGuestGrid();
    grid.setAttribute("aria-busy", "true");
    less.disabled = true;
  });

  document.addEventListener("qllmLoadEnd", () => {
    if (!loadingGuests || query.querySelector(".wp-load-more__button.loading")) return;
    loadingGuests = false;
    grid.setAttribute("aria-busy", "false");
    animateGuestGrid(loadingHeight);
    less.disabled = false;
    lessPagination.hidden = grid.children.length <= initialCount;
    query.querySelector(".wp-load-more__button")?.setAttribute(
      "aria-expanded", String(grid.children.length > initialCount),
    );
  });

  less.addEventListener("click", () => {
    const fromHeight = freezeGuestGrid();
    grid.innerHTML = originalCards;

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

      const header = document.querySelector(".wp-site-blocks > header");
      const headerStyles = header ? getComputedStyle(header) : null;
      const headerHeight = headerStyles && ["sticky", "fixed"].includes(headerStyles.position)
        ? header.getBoundingClientRect().height + (parseFloat(headerStyles.top) || 0)
        : 0;
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

// Setup.
initEventModal();
initEventFilters();
initGuestControls();
