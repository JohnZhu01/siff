# Events page setup

Deploy the theme branch containing `patterns/events-grid.php`, `functions.php`,
`assets/js/event-modal.js`, and `assets/css/event-modal.css` together.

## Insert the grid

1. Activate SIFF Theme on the live site.
2. Edit the Events page and keep its introduction and other page sections.
3. Open the block inserter, select Patterns, and search for **Events Grid**.
4. Insert the pattern where the event gallery belongs. Replace an older gallery
   rather than adding a second one.
5. Update the page and check the published page outside the editor.

The pattern displays up to 50 published `siff-event` posts. It uses the live
site's event IDs and featured images; no local URLs or database IDs are stored
in the pattern. Its card classes connect to the theme's modal and filtering code.
Changing a pattern file does not update copies already inserted into pages.

## Event data

The live site needs ACF active, the `siff-event` post type, and the
`siff-event-types` taxonomy with `film`, `workshop`, and `panel` slugs.
Each event needs its featured image, event type, and applicable ACF fields.
The theme pattern does not migrate posts, media, or ACF definitions.

Dates use `screening_date_and_time`, falling back to `date_and_time`.
The modal also reads `synopsis` or `short_description`, `runtime` or `duration`,
`venue`, `filmmaker`, `director`, `instructor`, `instructor_profession`,
`speaker`, and `speaker_profession`.

## Verify

- All, Films, Workshops, and Panels show the matching event cards.
- Clicking a title, image, date, or event type opens the correct modal.
- Tab and Enter open a title; Escape closes the modal and restores focus.
- The cards show screening dates rather than publication dates.
- `/wp-json/siff/v1/events/ID` returns a published event's details. Use a live
  event ID; missing events should return 404.

The REST URL is generated using `rest_url()`. On a page with the `events` slug,
details for up to 50 events are preloaded. Other pages use the REST fallback.
If the deployed changes are not visible, clear the site's page cache and refresh
the browser.
