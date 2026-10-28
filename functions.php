<?php

require_once get_theme_file_path('/inc/event-modal.php');
require_once get_theme_file_path('/inc/schedule.php');
require_once get_theme_file_path('/inc/events-page-update.php');

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
    if (!in_array('siff-guest-role', $classes, true)
        || get_post_type($guest_id) !== 'guest'
        || trim(wp_strip_all_tags($html)) !== '') {
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

// Register the vendor card block.
function siff_register_vendor_card()
{
    register_block_type(
        get_theme_file_path('blocks/vendor-card'),
        array(
            'render_callback' => 'siff_render_vendor_card',
        )
    );
}

add_action('init', 'siff_register_vendor_card');

function siff_render_vendor_card($attributes, $content, $block)
{
    // Get the vendor ID from the Query Loop.
    $vendor_id = absint($block->context['postId'] ?? 0);

    if (! $vendor_id || 'siff-vendor' !== get_post_type($vendor_id) || 'publish' !== get_post_status($vendor_id)) {
        return '';
    }

    ob_start();
    include get_theme_file_path('blocks/vendor-card/render.php');
    return ob_get_clean();
}

// Load the theme stylesheet in front-end and block editor content.
function siff_enqueue_theme_styles()
{
    wp_enqueue_style(
        'siff-theme',
        get_stylesheet_uri(),
        array(),
        filemtime(get_stylesheet_directory() . '/style.css')
    );
}

add_action('enqueue_block_assets', 'siff_enqueue_theme_styles');

// Load the policy navigation on the policy page.
function siff_policy_page_class($classes)
{
    if (is_page('policy') || is_privacy_policy()) {
        $classes[] = 'siff-policy-page';
    }
    return $classes;
}

add_filter('body_class', 'siff_policy_page_class');

// Load the policy dropdown script.
function siff_enqueue_policy_navigation()
{
    if (!is_page(array('policy', 'privacy-policy')) && !is_privacy_policy()) {
        return;
    }

    wp_enqueue_script(
        'siff-policy-navigation',
        get_theme_file_uri('assets/js/policy-navigation.js'),
        array(),
        filemtime(get_theme_file_path('/assets/js/policy-navigation.js')),
        true
    );
}

add_action('wp_enqueue_scripts', 'siff_enqueue_policy_navigation');

// Load type filtering on the Vendors page.
function siff_enqueue_vendor_directory_assets()
{
    if (! is_page('vendors')) {
        return;
    }

    wp_enqueue_script(
        'siff-vendor-directory',
        get_theme_file_uri('assets/js/vendor-directory.js'),
        array(),
        filemtime(get_theme_file_path('assets/js/vendor-directory.js')),
        true
    );
}

add_action('wp_enqueue_scripts', 'siff_enqueue_vendor_directory_assets');

function siff_enqueue_vendor_directory_styles()
{
    $post = get_post();
    if (! is_page('vendors') && ! (is_admin() && $post && 'page' === $post->post_type && 'vendors' === $post->post_name)) {
        return;
    }

    wp_enqueue_style(
        'siff-vendor-directory',
        get_theme_file_uri('assets/css/vendor-directory.css'),
        array(),
        filemtime(get_theme_file_path('assets/css/vendor-directory.css'))
    );
}

add_action('enqueue_block_assets', 'siff_enqueue_vendor_directory_styles');

function siff_register_event_date_block()
{
    wp_register_script(
        'siff-event-date-editor',
        get_theme_file_uri('/blocks/event-date/editor.js'),
        array('wp-blocks', 'wp-element', 'wp-block-editor'),
        filemtime(get_theme_file_path('/blocks/event-date/editor.js')),
        true
    );

    register_block_type(
        get_theme_file_path('/blocks/event-date')
    );
}

add_action('init', 'siff_register_event_date_block');

// Event type labels inside cards.
function siff_event_type_labels($html, $parsed_block)
{
    if (($parsed_block['attrs']['term'] ?? '') !== 'siff-event-types') {
        return $html;
    }

    return preg_replace('/<\/?a\b[^>]*>/i', '', $html);
}

add_filter('render_block_core/post-terms', 'siff_event_type_labels', 10, 2);

// Load map assets on pages with a map.
function siff_enqueue_leaflet_assets()
{
    $post = get_queried_object();
    if (!is_singular() || !($post instanceof WP_Post)) {
        return;
    }

    $content = $post->post_content;
    if (!has_block('siff/event-locator', $post)
        && strpos($content, 'wp-custom-map') === false
        && strpos($content, 'siff-event-locator') === false) {
        return;
    }

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
        get_theme_file_uri('/assets/js/custom-map-init.js'),
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
add_action('wp_enqueue_scripts', 'siff_enqueue_leaflet_assets');

// Register the Event Map Locator Block.
function siff_register_event_locator()
{
    register_block_type(
        'siff/event-locator',
        [
            'title' => 'Event Locator Map',
            'category' => 'theme',
            'icon' => 'location-alt',
            'description' => 'Display a location of the event in the map.',
            'render_callback' => function () {
                return sprintf(
                    '<div id="siff-event-locator"></div>'
                );
            },
            'supports' => [
                'autoRegister' => true,
            ]
        ]
    );
}

add_action('init', 'siff_register_event_locator');


// Keep the Events patterns available after file-based deployments.
function siff_register_events_grid_pattern()
{
    $registry = WP_Block_Patterns_Registry::get_instance();
    $patterns = array(
        'special-guests' => __('Special Guests', 'siff-theme'),
        'events-grid' => __('Events Grid', 'siff-theme'),
        'events-page' => __('Events Page', 'siff-theme'),
    );

    foreach ($patterns as $name => $title) {
        $slug = 'siff-theme/' . $name;
        if ($registry->is_registered($slug)) {
            continue;
        }

        $file = get_theme_file_path('/patterns/' . $name . '.php');
        if (!is_readable($file)) {
            continue;
        }

        ob_start();
        include $file;
        $content = ob_get_clean();

        register_block_pattern($slug, array(
            'title' => $title,
            'categories' => array('featured'),
            'inserter' => true,
            'content' => $content,
        ));
    }
}

add_action('init', 'siff_register_events_grid_pattern', 20);

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
function custom_box_office_add_to_cart_text($text, $product)
{
    if ($product && 'yes' === get_post_meta($product->get_id(), '_ticket', true) && $product->is_purchasable() && $product->is_in_stock()) {
        return __('Buy Ticket', 'siff-theme');
    }
    return $text;
}

// Registering custom ACF block

function siff_register_festival_highlights_block()
{
    if (function_exists('acf_register_block_type')) {
        acf_register_block_type(array(
            'name'            => 'festival-highlights',
            'title'           => 'Festival Highlights Slider',
            'description'     => 'A featured slider for SIFF festival highlights.',
            'render_template' => 'blocks/festival-highlights/render.php',
            'category'        => 'design',
            'icon'            => 'format-gallery',
            'keywords'        => array(
                'festival',
                'highlights',
                'slider'
            ),
            'mode'            => 'preview',
            'supports'        => array(
                'align' => array('wide', 'full'),
                'anchor' => true
            ),
            'enqueue_assets'  => 'siff_enqueue_festival_highlights_assets'
        ));
    }
}
add_action('acf/init', 'siff_register_festival_highlights_block');


function siff_enqueue_festival_highlights_assets()
{
    $theme_uri = get_template_directory_uri();

    wp_enqueue_style(
        'siff-swiper',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
        array(),
        '11'
    );

    wp_enqueue_style(
        'siff-festival-highlights',
        $theme_uri . '/assets/css/festival-highlights.css',
        array('siff-swiper'),
        filemtime(get_theme_file_path('/assets/css/festival-highlights.css'))
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
        $theme_uri . '/assets/js/festival-highlights.js',
        array('siff-swiper'),
        '1.0',
        true
    );
}

