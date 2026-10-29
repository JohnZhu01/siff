<?php

require_once get_theme_file_path('/inc/event-modal.php');
require_once get_theme_file_path('/inc/schedule.php');
require_once get_theme_file_path('/inc/policy.php');
require_once get_theme_file_path('/inc/events-page-update.php');


function siff_enqueues()
{
    // Load the theme stylesheet in front-end and block editor content.
    wp_enqueue_style(
        'siff-theme',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get('Version'),
        'all'
    );
    // Animation styles.
    wp_enqueue_style(
        'siff-animations',
        get_theme_file_uri('/assets/css/animations.css'),
        array('siff-theme'),
        filemtime(get_theme_file_path('/assets/css/animations.css'))
    );

    // Guest reveal.
    wp_enqueue_script(
        'siff-guest-reveal',
        get_theme_file_uri('/assets/js/guest-reveal.js'),
        array(),
        filemtime(get_theme_file_path('/assets/js/guest-reveal.js')),
        true
    );

    // Guest controls.
    wp_enqueue_script(
        'siff-guest-controls',
        get_theme_file_uri('/assets/js/guest-controls.js'),
        array(),
        filemtime(get_theme_file_path('/assets/js/guest-controls.js')),
        true
    );

    // Load the scroll-to-top button.
    wp_enqueue_script(
        'scroll-to-top',
        get_theme_file_uri('assets/js/scroll-to-top.js'),
        array(),
        filemtime(get_theme_file_path('assets/js/scroll-to-top.js')),
        true
    );

    if (is_page('vendors')) {
        // Load type filtering on the Vendors page.
        wp_enqueue_script(
            'siff-vendor-directory',
            get_theme_file_uri('assets/js/vendor-directory.js'),
            array(),
            filemtime(get_theme_file_path('assets/js/vendor-directory.js')),
            true
        );
        $post = get_post();
        if (is_page('vendors') ||  (is_admin() && $post && 'page' === $post->post_type && 'vendors' === $post->post_name)) {
            wp_enqueue_style(
                'siff-vendor-directory',
                get_theme_file_uri('assets/css/vendor-directory.css'),
                array(),
                filemtime(get_theme_file_path('assets/css/vendor-directory.css'))
            );
        }
    }

    // Load map assets on pages with a map.
    $post = get_queried_object();
    if (is_singular() || ($post instanceof WP_Post)) {
        $content = $post->post_content;
        if (
            has_block('siff/event-locator', $post)
            || strpos($content, 'wp-custom-map') === true
            || strpos($content, 'siff-event-locator') === true
        ) {
            wp_enqueue_style(
                'leaflet',
                'https://cdn.jsdelivr.net/npm/leaflet@2.0.0-alpha.1/dist/leaflet.css',
                array(),
                '2.0.0-alpha.1'
            );

            wp_register_script_module(
                'leaflet',
                'https://cdn.jsdelivr.net/npm/leaflet@2.0.0-alpha.1/dist/leaflet.js',
                array(),
                '2.0.0-alpha.1'
            );

            wp_enqueue_script_module(
                'siff-location-map',
                get_theme_file_uri('assets/js/custom-map-init.js'),
                array('leaflet'),
                filemtime(get_theme_file_path('assets/js/custom-map-init.js'))
            );
            wp_enqueue_style(
                'custom-map',
                get_theme_file_uri('assets/css/custom-map.css'),
                array(),
                filemtime(get_theme_file_path('assets/css/custom-map.css'))
            );
        }
    }

    wp_enqueue_style(
        'siff-swiper',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
        array(),
        '11'
    );

    wp_enqueue_style(
        'siff-festival-highlights',
        get_theme_file_uri('assets/css/festival-highlights.css'),
        array('siff-swiper'),
        filemtime(get_theme_file_path('assets/css/festival-highlights.css'))
    );

    wp_enqueue_script(
        'siff-swiper',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
        array(),
        '11',
        true
    );

    wp_enqueue_script(
        'siff-festival-highlights',
        get_theme_file_uri('assets/js/festival-highlights.js'),
        array('siff-swiper'),
        '1.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'siff_enqueues');


// Load every guest, including queries saved in the page editor.
function siff_load_all_guests($block)
{
    $classes = explode(' ', $block['attrs']['className'] ?? '');
    if ($block['blockName'] === 'core/query'
        && in_array('siff-guests', $classes, true)
        && ($block['attrs']['query']['postType'] ?? '') === 'guest') {
        $block['attrs']['query']['perPage'] = max(1, (int) wp_count_posts('guest')->publish);
        $block['attrs']['query']['pages'] = 0;
        $block['attrs']['query']['offset'] = 0;
    }
    return $block;
}
add_filter('render_block_data', 'siff_load_all_guests');

// Filters 

// Allow the error page to use the numeric slug 404.
function siff_allow_404_slug($override, $slug, $post_id, $status, $post_type)
{
    if ($post_type === 'page' && $slug === '404') {
        $page = get_page_by_path('404');
        if (!$page || $page->ID === $post_id) {
            return '404';
        }
    }
    return $override;
}
add_filter('pre_wp_unique_post_slug', 'siff_allow_404_slug', 10, 5);

// Show the editable 404 page through the default page template.
function siff_404_page_template($template)
{
    $page = get_page_by_path('404');
    if (!$page || $page->post_status !== 'publish') {
        return $template;
    }
    global $post;
    $post = $page;
    setup_postdata($post);
    return get_page_template();
}
add_filter('404_template', 'siff_404_page_template');

// Style the error page and missing URLs the same way.
function siff_error_page_class($classes)
{
    if (is_404() || is_page('404')) {
        $classes[] = 'siff-error-page';
    }
    return $classes;
}
add_filter('body_class', 'siff_error_page_class');

// Enable ACF field bindings in the editor.
add_filter('acf/settings/enable_datastore', '__return_true');

// Allow guest roles to appear in bound paragraph blocks.
function siff_enable_guest_role_binding($field)
{
    $field['allow_in_bindings'] = 1;
    return $field;
}

add_filter('acf/load_field/name=role', 'siff_enable_guest_role_binding');

// Show guest roles when ACF bindings are unavailable.
function siff_guest_role_fallback($html, $parsed_block, $block)
{
    $classes = explode(' ', $parsed_block['attrs']['className'] ?? '');
    $guest_id = absint($block->context['postId'] ?? get_the_ID());
    if (
        !in_array('siff-guest-role', $classes, true)
        || get_post_type($guest_id) !== 'guest'
        || trim(wp_strip_all_tags($html)) !== ''
    ) {
        return $html;
    }

    $role = get_post_meta($guest_id, 'role', true);
    if (!is_string($role) || trim($role) === '') {
        return $html;
    }

    return preg_replace_callback('/(<p\b[^>]*>).*?(<\/p>)/s', function ($matches) use ($role) {
        return $matches[1] . esc_html($role) . $matches[2];
    }, $html, 1);
}
add_filter('render_block_core/paragraph', 'siff_guest_role_fallback', 10, 3);


// Event type labels inside cards.
function siff_event_type_labels($html, $parsed_block)
{
    if (($parsed_block['attrs']['term'] ?? '') !== 'siff-event-types') {
        return $html;
    }

    return preg_replace('/<\/?a\b[^>]*>/i', '', $html);
}

add_filter('render_block_core/post-terms', 'siff_event_type_labels', 10, 2);


// Overwrite the add to cart button in the shop page
add_filter('woocommerce_product_add_to_cart_url', 'custom_box_office_add_to_cart_url', 10, 2);
function custom_box_office_add_to_cart_url($url, $product)
{
    if ($product && 'yes' === get_post_meta($product->get_id(), '_ticket', true) && $product->is_purchasable() && $product->is_in_stock()) {
        $current_url = remove_query_arg(array('add-to-cart', 'quantity'));
        return add_query_arg('add-to-cart', $product->get_id(), $current_url);
    }
    return $url;
}

add_filter('woocommerce_product_add_to_cart_text', 'custom_box_office_add_to_cart_text', 10, 2);


function yoast_to_bottom()
{
    return 'low';
}
add_filter('wpseo_metabox_prio', 'yoast_to_bottom');


function custom_box_office_add_to_cart_text($text, $product)
{
    if ($product && 'yes' === get_post_meta($product->get_id(), '_ticket', true) && $product->is_purchasable() && $product->is_in_stock()) {
        return __('Buy Ticket', 'siff-theme');
    }
    return $text;
}


// Load custom blocks
require get_theme_file_path() . '/blocks/siff-blocks.php';
