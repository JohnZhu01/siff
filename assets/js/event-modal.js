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
    if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey)
      return;

    event.preventDefault();
    activeTrigger = trigger;
    activeRequest?.abort();
    const request = new AbortController();
    activeRequest = request;

    const eventId = trigger.dataset.eventId;
    const loadingStarted = performance.now();
    const minimumLoadingTime = 500;
    title.textContent = "Event details";
    if (descriptionTitle) descriptionTitle.textContent = "Description";
    content.textContent = "Loading…";
    meta.replaceChildren();
    image.hidden = true;
    image.removeAttribute("src");
    if (!modal.open) modal.showModal();

    try {
      let details = eventCache.get(eventId);

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

      function addDetail(container, label, value) {
        if (!value) return;
        const paragraph = document.createElement("p");
        const startsCredit =
          label === "Instructor" ||
          label === "Speaker" ||
          label === "Filmmaker" ||
          (label === "Director" && !details.filmmaker);

        if (startsCredit) {
          paragraph.classList.add("siff-modal-credit-start");
        }
        paragraph.textContent = label ? `${label}: ${value}` : value;
        container.append(paragraph);
      }

      addDetail(content, "", details.description);
      addDetail(meta, "Date and time", details.date);
      addDetail(meta, "Venue", details.venue);
      addDetail(meta, "Duration", details.duration);
      if (types.includes("film")) {
        addDetail(meta, "Filmmaker", details.filmmaker);
        addDetail(meta, "Director", details.director);
      } else if (types.includes("workshop")) {
        addDetail(meta, "Instructor", details.instructor);
        addDetail(meta, "Profession", details.instructor_profession);
      } else if (types.includes("panel")) {
        addDetail(meta, "Speaker", details.speaker);
        addDetail(meta, "Profession", details.speaker_profession);
      }
    } catch (error) {
      if (!request.signal.aborted) {
        content.textContent = "Unable to load event details. Please try again.";
      }
    }
  });

  closeButton.addEventListener("click", () => modal.close());
  modal.addEventListener("close", () => {
    activeRequest?.abort();
    activeTrigger?.focus();
  });
}

initEventModal();

document.querySelectorAll(".siff-event-filters").forEach((filters) => {
  const query = filters.closest(".wp-block-query");
  const grid = query?.querySelector(".wp-block-post-template");
  if (!grid) return;
  const searchForm=query.querySelector(".siff-event-search");
const searchInput = searchForm?.querySelector('input[type="search"]');
  let activeType="all"

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

    // Use a real button for keyboard-accessible filtering.
    const button = document.createElement("button");
    button.type = "button";
    button.className = link.className;
    button.textContent = link.textContent;
    link.replaceWith(button);

    controls.push({ type, button });
  });

  const status = document.createElement("p");
  status.setAttribute("role", "status");
  grid.before(status);

  function applyFilter(type) {
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
      if (matches) count++;
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

  controls.forEach(({ type, button }) => {
    button.addEventListener("click", () => applyFilter(type));
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

document.querySelectorAll(".siff-guests").forEach((query) => {
  const grid = query.querySelector(".wp-block-post-template");
  const more = query.querySelector(".wp-load-more__button");
  const pagination = more?.closest(".wp-block-buttons");

  if (!grid || !pagination) return;

  const originalCards = grid.innerHTML;
  const originalPagination = pagination.cloneNode(true);
  const initialCount = grid.children.length;

  const less = document.createElement("button");
  less.type = "button";
  less.className = more.className;
  less.classList.remove("wp-load-more__button", "loading");
  less.classList.add("siff-guests-less");
  less.style.cssText = more.style.cssText;
  less.textContent = "See less";

  const lessPagination = originalPagination.cloneNode(true);
  lessPagination.classList.add("siff-guests-less-controls");
  lessPagination.querySelector(".wp-load-more__button").replaceWith(less);
  lessPagination.hidden = true;
  query.append(lessPagination);

  document.addEventListener("qllmLoadStart", () => {
    less.disabled = true;
  });

  document.addEventListener("qllmLoadEnd", () => {
    less.disabled = false;
    lessPagination.hidden = grid.children.length <= initialCount;
  });

  less.addEventListener("click", () => {
    grid.innerHTML = originalCards;

    query.querySelectorAll(".wp-load-more__button").forEach((button) => {
      button.closest(".wp-block-buttons")?.remove();
    });

    const restoredPagination = originalPagination.cloneNode(true);
    lessPagination.before(restoredPagination);
    lessPagination.hidden = true;
    restoredPagination.querySelector(".wp-load-more__button")?.focus();
  });
});
