// Policy dropdown.
function setupPolicyNavigation() {
  const nav = document.querySelector(".siff-policy-nav");
  const header = document.querySelector(".wp-site-blocks > header");

  if (!nav) {
    return;
  }

  document.body.classList.add("siff-policy-page");

  const links = nav.querySelectorAll("a");
  nav.setAttribute("role", "navigation");
  nav.setAttribute("aria-label", "Policy sections");

  // Turn the links into dropdown options.
  const dropdown = document.createElement("select");
  dropdown.className = "siff-policy-select";
  dropdown.setAttribute("aria-label", "Jump to a policy section");

  const placeholder = document.createElement("option");
  placeholder.value = "";
  placeholder.textContent = "Jump to a section";
  placeholder.disabled = true;
  placeholder.selected = true;
  dropdown.append(placeholder);

  links.forEach(function (link) {
    const option = document.createElement("option");
    option.value = link.hash;
    option.textContent = link.textContent.trim();
    dropdown.append(option);
  });

  dropdown.addEventListener("change", function () {
    const hash = dropdown.value;
    const heading = document.getElementById(hash.substring(1));
    if (heading) {
      window.location.hash = hash;
      // Allow repeat jumps to the same section.
      heading.scrollIntoView();
    }
    dropdown.value = "";
  });
  nav.append(dropdown);
  nav.classList.add("has-section-dropdown");

  // Keep the dropdown below the header.
  function updateOffsets() {
    let headerHeight = 0;
    if (header) {
      headerHeight = header.offsetHeight;
    }
    document.documentElement.style.setProperty(
      "--siff-header-height", headerHeight + "px"
    );
    document.documentElement.style.setProperty(
      "--siff-policy-nav-height", nav.offsetHeight + "px"
    );
  }

  updateOffsets();
  window.addEventListener("load", updateOffsets);
  window.addEventListener("resize", updateOffsets);
}

setupPolicyNavigation();
