document.querySelectorAll('.siff-vendor-directory').forEach((directory) => {
    const sections = Array.from(directory.querySelectorAll(
        '.vendors-platinum, .vendors-gold, .vendors-silver, .vendors-bronze'
    ));
    const vendors = sections.flatMap((section) => Array.from(section.querySelectorAll('.wp-block-post')))
        .filter((item) => item.querySelector('.siff-vendor-card'));

    if (!vendors.length) return;

    const controls = document.createElement('div');
    controls.className = 'siff-vendor-filters';
    controls.innerHTML = `
        <div class="siff-vendor-filters__types" role="group" aria-label="Vendor type">
            <button type="button" data-type="all" aria-pressed="true">All</button>
            <button type="button" data-type="food" aria-pressed="false">Food</button>
            <button type="button" data-type="merchandise" aria-pressed="false">Merchandise</button>
        </div>
        <p class="siff-vendor-filters__status" role="status" aria-live="polite" aria-atomic="true"></p>
    `;
    directory.prepend(controls);

    const buttons = controls.querySelectorAll('[data-type]');
    const status = controls.querySelector('[role="status"]');
    let selectedType = 'all';

    function filterVendors() {
        let count = 0;

        vendors.forEach((item) => {
            const visible = selectedType === 'all' || item.classList.contains(`siff-vendor-type-${selectedType}`);
            item.hidden = !visible;
            if (visible) count++;
        });

        sections.forEach((section) => {
            section.hidden = !section.querySelector('.wp-block-post:not([hidden])');
        });

        status.textContent = count === 0
            ? 'No vendors match this type. Select All to view all vendors.'
            : `${count} ${count === 1 ? 'vendor' : 'vendors'} shown`;
    }

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            selectedType = button.dataset.type;
            buttons.forEach((item) => item.setAttribute('aria-pressed', String(item === button)));
            filterVendors();
        });
    });

    filterVendors();
});
