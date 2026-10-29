<?php

function siff_is_policy_page()
{
    return is_page(array('policy', 'privacy-policy')) || is_privacy_policy();
}

// Policy styles and navigation.
function siff_policy_page_class($classes)
{
    if (siff_is_policy_page()) {
        $classes[] = 'siff-policy-page';
    }
    return $classes;
}
add_filter('body_class', 'siff_policy_page_class');

function siff_enqueue_policy_navigation()
{
    if (!siff_is_policy_page()) {
        return;
    }
    wp_enqueue_script(
        'siff-policy-navigation',
        get_theme_file_uri('/assets/js/policy-navigation.js'),
        array(),
        filemtime(get_theme_file_path('/assets/js/policy-navigation.js')),
        true
    );
}
add_action('wp_enqueue_scripts', 'siff_enqueue_policy_navigation');
