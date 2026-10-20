/* Core sends these custom query properties to the existing event REST collection endpoint. */
wp.domReady(function () {
	Object.entries(siffScheduleDays).forEach(function ([slug, day]) {
		const namespace = "siff/schedule-" + slug;
		wp.blocks.registerBlockVariation("core/query", {
			name: namespace,
			title: "SIFF Schedule: " + day.label,
			description: wp.i18n.__("Events ordered by their ACF scheduled start.", "siff-theme"),
			icon: "calendar-alt",
			isActive: ["namespace"],
			scope: ["inserter"],
			allowedControls: [],
			attributes: {
				namespace: namespace,
				className: "siff-schedule-query",
				query: {
					// Match the pattern's 100-event daily limit; no pagination.
					perPage: 100,
					pages: 0,
					offset: 0,
					postType: "siff-event",
					order: "asc",
					orderBy: "title",
					inherit: false,
					siffSchedule: "festival-2027",
					siffScheduleDay: day.date,
				},
			},
			innerBlocks: [
				["core/post-template", {}, [["siff/schedule-event"]]],
				["core/query-no-results", {}, [["core/paragraph", {
					content: wp.i18n.__("No events are scheduled for this day yet.", "siff-theme"),
				}]]],
			],
		});
	});
});
