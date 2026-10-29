// Policy jump navigation.
function initPolicyNavigation() {
  const nav = document.querySelector('.siff-policy-nav');
  const main = nav?.closest('main');
  if (!main) return;

  let select = nav.querySelector('.siff-policy-select');
  if (!select) {
    const links = nav.querySelectorAll('a[href^="#"]');
    if (!links.length) return;
    select = document.createElement('select');
    select.className = 'siff-policy-select';
    select.setAttribute('aria-label', 'Jump to a policy section');
    select.add(new Option('Choose a section', ''));
    links.forEach((link) => select.add(new Option(link.textContent.trim(), link.hash)));
    nav.replaceChildren(select);
  }

  function updateOffsets() {
    const header = document.querySelector('.wp-site-blocks > header');
    const adminBar = document.querySelector('#wpadminbar');
    let top = 0;
    if (adminBar && getComputedStyle(adminBar).position === 'fixed') {
      top = adminBar.getBoundingClientRect().height;
    }
    if (header) {
      const styles = getComputedStyle(header);
      if (styles.position === 'sticky' || styles.position === 'fixed') {
        top = header.getBoundingClientRect().height + (parseFloat(styles.top) || 0);
      }
    }
    main.style.setProperty('--siff-policy-top', top + 'px');
    main.style.setProperty('--siff-policy-nav-height', nav.getBoundingClientRect().height + 'px');
  }

  select.addEventListener('change', () => {
    const hash = select.value;
    const heading = document.getElementById(hash.substring(1));
    if (heading) {
      updateOffsets();
      if (window.location.hash !== hash) window.history.pushState(null, '', hash);
      heading.scrollIntoView({ block: 'start' });
    }
    select.value = '';
  });

  window.addEventListener('load', updateOffsets);
  window.addEventListener('resize', updateOffsets);
  updateOffsets();
}

initPolicyNavigation();
