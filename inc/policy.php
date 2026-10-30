<?php

function siff_is_policy_page()
{
    return is_page(array('policy', 'privacy-policy')) || is_privacy_policy();
}

// Policy page styling.
function siff_policy_page_class($classes)
{
    if (siff_is_policy_page()) {
        $classes[] = 'siff-policy-page';
    }
    return $classes;
}
add_filter('body_class', 'siff_policy_page_class');
