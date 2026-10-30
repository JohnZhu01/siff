/*
Contents
  Measurements
  Loading spinner
  Detail rows
  Event modal
  Event filters and search
  Guest expand and collapse
  Setup
*/

// Measurements.
function toRem(value) {
  const rootSize = parseFloat(
    getComputedStyle(document.documentElement).fontSize,
  );
  return value / rootSize + "rem";
}

// Height of the sticky header.
function getHeaderOffset() {
  const header = document.querySelector(
    ".wp-site-blocks > header.wp-block-template-part",
  );

  if (header) {
    const styles = getComputedStyle(header);
    if (styles.position === "sticky" || styles.position === "fixed") {
      return (
        header.getBoundingClientRect().height + (parseFloat(styles.top) || 0)
      );
    }
  }

  const adminBar = document.querySelector("#wpadminbar");
  return adminBar && getComputedStyle(adminBar).position === "fixed"
    ? adminBar.getBoundingClientRect().height
    : 0;
}

// Loading spinner.
function showEventLoader(container) {
  container.innerHTML = `
    <div role="status">
      <div class="siff-modal-loader" aria-hidden="true"></div>
      <span class="screen-reader-text">Loading event details…</span>
    </div>
  `;
}

// Detail rows.
function addEventDetail(container, label, value) {
  if (!value) return;

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
  const overviewTab = document.querySelector("#siff-tab-overview");
  const detailsTab = document.querySelector("#siff-tab-details");
  const credits = document.querySelector("#siff-modal-credits");

  const tabs = [overviewTab, detailsTab];
  const panels = [
    document.querySelector("#siff-panel-overview"),
    document.querySelector("#siff-panel-details"),
  ];
  const image = document.querySelector("#siff-modal-image");
  const imageArea = image?.closest(".siff-modal-image");

  if (
    !modal ||
    !modalBody ||
    !closeButton ||
    !title ||
    !content ||
    !meta ||
    !credits ||
    !detailsSection ||
    !descriptionTitle ||
    !imageArea
  )
    return;

  if (moveFocus) {
    tabs[index].focus({ preventScroll: true });
  }
}

tabs.forEach((tab, index) => {
  tab.addEventListener("click", () => selectTab(index));

  tab.addEventListener("keydown", (event) => {
    let next;

    switch (event.key) {
      case "ArrowRight":
        next = (index + 1) % tabs.length;
        break;
      case "ArrowLeft":
        next = (index - 1 + tabs.length) % tabs.length;
        break;
      case "Home":
        next = 0;
        break;
      case "End":
        next = tabs.length - 1;
        break;
      default:
        return;
    }

    event.preventDefault();
    selectTab(next, true);
  });
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
    overviewTab.textContent = "Synopsis";
  } else {
    overviewTab.textContent = "Overview";
  }
  imageArea.hidden = !details.image;
  if (details.image) {
    image.src = details.image;
    image.alt = "";
    image.hidden = false;
  }

  content.replaceChildren();
  addEventDetail(content, "", details.description);
  if (details.display_date) {
    addEventDetail(meta, "Date", details.display_date);
    addEventDetail(meta, "Time", details.display_time);
  } else {
    addEventDetail(meta, "Date and time", details.date);
  }
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
  // Leave links and controls inside the dialog alone.
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
  if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;

  event.preventDefault();
  activeTrigger = trigger;
  requestNumber++;
  const currentRequest = requestNumber;

  const eventId = trigger.dataset.eventId;
  const loadingStarted = performance.now();
  const minimumLoadingTime = 500;
  title.textContent = "Event details";
  overviewTab.textContent = "Overview";
  selectTab(0);

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
    const remaining = minimumLoadingTime - (performance.now() - loadingStarted);
    if (remaining > 0) {
      await new Promise((resolve) => setTimeout(resolve, remaining));
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

// Match the 180ms exit animation in animations.css.
const closeDuration = 180;
const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
let closeTimer;
let pointerStartedOutside = false;

function closeModal() {
  if (!modal.open || modal.classList.contains("is-closing")) return;

  if (reducedMotion.matches) {
    modal.close();
    return;
  }

  modal.classList.add("is-closing");
  closeTimer = window.setTimeout(() => modal.close(), closeDuration);
}

closeButton.addEventListener("click", closeModal);
modal.addEventListener("cancel", (event) => {
  event.preventDefault();
  closeModal();
});

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
  pointerStartedOutside =
    event.button === 0 && event.target === modal && isOutsideModal(event);
});

modal.addEventListener("click", (event) => {
  if (
    pointerStartedOutside &&
    event.target === modal &&
    isOutsideModal(event)
  ) {
    closeModal();
  }
  pointerStartedOutside = false;
});

modal.addEventListener("close", () => {
  window.clearTimeout(closeTimer);
  pointerStartedOutside = false;
  requestNumber++;
  modal.classList.remove("is-loading", "is-closing");
  activeTrigger?.focus();
});

// Event filters and search.
function initEventFilters() {
  const filterGroups = document.querySelectorAll(".siff-event-filters");
  if (!filterGroups.length) return;

  function updateFilterOffset() {
    document.documentElement.style.setProperty(
      "--siff-event-filters-top",
      toRem(getHeaderOffset()),
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
        title: (
          element.querySelector(".wp-block-post-title")?.textContent || ""
        ).toLowerCase(),
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

    // Results count and card wrapper.
    const status = document.createElement("p");
    status.setAttribute("role", "status");
    status.className = "siff-event-results";
    const resultsSection = document.createElement("div");
    resultsSection.className = "siff-event-results-section";
    grid.before(resultsSection);
    resultsSection.append(status, grid);

    // Show six matching events at a time.
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

    // Match cards by category and search text.
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
          type === "all" ||
          element.classList.contains(`siff-event-types-${type}`);

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

    // Bring the first results below the sticky controls.
    function changeEventType(type) {
      applyFilter(type, true);

      requestAnimationFrame(() => {
        const filterStyles = getComputedStyle(filters);
        const offset =
          filterStyles.position === "sticky"
            ? (parseFloat(filterStyles.top) || 0) +
              filters.getBoundingClientRect().height
            : getHeaderOffset();

        window.scrollTo({
          top: Math.max(
            0,
            window.scrollY +
              resultsSection.getBoundingClientRect().top -
              offset -
              12,
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

// Setup.
initEventModal();
initEventFilters();
