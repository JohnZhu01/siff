// Jump to the selected policy section.
const policyDropdown = document.querySelector(".siff-policy-select");

if (policyDropdown) {
  policyDropdown.addEventListener("change", function () {
    const hash = policyDropdown.value;
    const heading = document.getElementById(hash.substring(1));

    if (heading) {
      window.location.hash = hash;
      heading.scrollIntoView();
    }

    // Reset same section
    policyDropdown.value = "";
  });
}
