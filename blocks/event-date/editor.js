wp.blocks.registerBlockType("siff/event-date", {
  edit: function () {
    const props = wp.blockEditor.useBlockProps({
      className: "siff-event-date",
    });

    return wp.element.createElement(
      "p",
      props,
      "Event screening date — preview on the site",
    );
  },

  save: function () {
    return null;
  },
});
