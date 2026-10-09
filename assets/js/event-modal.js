function initEventModal() {
  const modal = document.querySelector("#siff-event-modal");
  const closeButton = document.querySelector("#siff-modal-close");
  const title = document.querySelector("#siff-modal-title");
  const content = document.querySelector("#siff-modal-content");
  const meta = document.querySelector("#siff-modal-meta");
  const descriptionTitle = document.querySelector("#siff-modal-description-title");
  const image = document.querySelector("#siff-modal-image");

  if (!modal || !closeButton || !title || !content || !image || !meta) return;
  let activeRequest;
  let activeTrigger;
  const eventCache = new Map(
    Object.entries(siffModal.events || {})
  );

  document.addEventListener("click", async (event) => {
    // Leave links and controls inside the dialog alone.
    if (modal.contains(event.target)) return;

    if (!(event.target instanceof Element)) return;
    let trigger = event.target.closest("[data-event-id]");
    if (!trigger) {
      const card = event.target.closest(".siff-event-card");
      if (!card) return;
      if (event.target.closest("a, button, input, select, textarea, label")) return;
      trigger = card.querySelector(".siff-event-trigger a[data-event-id]");
    }
    if (!trigger) return;
    if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;

    event.preventDefault();
    activeTrigger = trigger;
    activeRequest?.abort();
    const request = new AbortController();
    activeRequest = request;

    const eventId = trigger.dataset.eventId;
    title.textContent = "Event details";
    if (descriptionTitle) descriptionTitle.textContent = "Description";
    content.textContent = "Loading…";
    meta.replaceChildren();
    image.hidden = true;
    image.removeAttribute("src");
    if (!modal.open) modal.showModal();

    try {
      const url = new URL(siffModal.ajaxUrl);
      url.searchParams.set("action", "siff_event_details");
      url.searchParams.set("event_id", eventId);

      let details = eventCache.get(eventId);
      if (!details) {
        const response = await fetch(url, { signal: request.signal });
        const result = await response.json();

        if (!response.ok || !result.success) {
          throw new Error("Unable to load this event.");
        }
        if (request.signal.aborted) return;

        details = result.data;
        eventCache.set(eventId, details);
      }
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
    let count = 0;

    cards.forEach((card) => {
      const matches =
        type === "all" || card.classList.contains(`siff-event-types-${type}`);

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
      : "No events in this category.";
  }

  controls.forEach(({ type, button }) => {
    button.addEventListener("click", () => applyFilter(type));
  });

  applyFilter("all");
});
