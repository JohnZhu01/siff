const policyLinks = document.querySelectorAll(".siff-policy-nav a");

policyLinks.forEach(function (link) {
    link.addEventListener("click", function () {
        policyLinks.forEach(function (item) {
            item.classList.remove("is-active");
        });

        link.classList.add("is-active");
    });
});
