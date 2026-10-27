# siff-theme

## Policy page

The `/policy/` page uses `templates/page-policy.html` and the theme pattern
`patterns/policy-page.php`. The pattern contains the heading, jump links, and
policy text, so deploying the theme transfers those changes without copying
the local page content. Edit policy wording in the pattern file.

The live site still needs a published page with the slug `policy`. A template
customized in the WordPress Site Editor can take priority over the theme file;
reset that policy template's customizations if the file changes do not appear.

The saved WordPress page content is retained but is not displayed by this template.
FWD44 – Team 3 – Capstone Project
