const modal = document.querySelector("#siff-event-modal");
const closeButton = document.querySelector("#siff-modal-close");
const title = document.querySelector("#siff-modal-title");
const content = document.querySelector("#siff-modal-content");
const meta = document.querySelector("#siff-modal-meta");
const descriptionTitle = document.querySelector("#siff-modal-description-title");
const image = document.querySelector("#siff-modal-image");
if (modal && closeButton && title && content && image && meta) {
  let activeRequest;
  const eventCache = new Map();

  document.addEventListener("click", async (event) => {
    // Ignore clicks inside the modal, including its close button.
    if (modal.contains(event.target)) return;

    const trigger = event.target.closest("[data-event-id]");
    if (!trigger) return;

    event.preventDefault();
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
      url.searchParams.set("event_id", trigger.dataset.eventId);

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
        descriptionTitle.textContent = types.includes("film") ? "Synopsis" : "Description";
      }
      if (details.image) {
        image.src = details.image;
        image.alt = "";
        image.hidden = false;
      }
      content.replaceChildren();

      // Insert field values as text rather than interpreting them as HTML.
      function addDetail(container, label, value) {
        if (!value) return;
        const paragraph = document.createElement("p");
        if (label === "Instructor" || label === "Speaker" || label === "Filmmaker" || (label === "Director" && !details.filmmaker)) {
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
  modal.addEventListener("close", () => activeRequest?.abort());
}
