<?php

require_once get_theme_file_path('/inc/event-modal.php');
require_once get_theme_file_path('/inc/schedule.php');

// Enable ACF field bindings in the editor.
add_filter('acf/settings/enable_datastore', '__return_true');

// Register the vendor card block.
function siff_register_vendor_card() {
	register_block_type(
		get_theme_file_path( 'blocks/vendor-card' ),
		array(
			'render_callback' => 'siff_render_vendor_card',
		)
	);
}

add_action( 'init', 'siff_register_vendor_card' );

function siff_render_vendor_card( $attributes, $content, $block ) {
	// Get the vendor ID from the Query Loop.
	$vendor_id = absint( $block->context['postId'] ?? 0 );

	if ( ! $vendor_id || 'siff-vendor' !== get_post_type( $vendor_id ) || 'publish' !== get_post_status( $vendor_id ) ) {
		return '';
	}

	ob_start();
	include get_theme_file_path( 'blocks/vendor-card/render.php' );
	return ob_get_clean();
}

// Load featured vendor styles in the editor and front end.
function siff_register_featured_vendors_style() {
	wp_enqueue_block_style(
		'core/group',
		array(
			'handle' => 'siff-featured-vendors',
			'src'    => get_theme_file_uri( 'assets/css/featured-vendors.css' ),
			'path'   => get_theme_file_path( 'assets/css/featured-vendors.css' ),
			'ver'    => filemtime( get_theme_file_path( 'assets/css/featured-vendors.css' ) ),
		)
	);
}

add_action( 'init', 'siff_register_featured_vendors_style' );

// Load type filtering on the Vendors page.
function siff_enqueue_vendor_directory_assets() {
	if ( ! is_page( 'vendors' ) ) {
		return;
	}

	wp_enqueue_script(
		'siff-vendor-directory',
		get_theme_file_uri( 'assets/js/vendor-directory.js' ),
		array(),
		filemtime( get_theme_file_path( 'assets/js/vendor-directory.js' ) ),
		true
	);

	wp_enqueue_style(
		'siff-vendor-directory',
		get_theme_file_uri( 'assets/css/vendor-directory.css' ),
		array(),
		filemtime( get_theme_file_path( 'assets/css/vendor-directory.css' ) )
	);
}

add_action( 'wp_enqueue_scripts', 'siff_enqueue_vendor_directory_assets' );

function siff_register_event_date_block() {
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
function siff_event_type_labels($html, $parsed_block) {
    if (($parsed_block['attrs']['term'] ?? '') !== 'siff-event-types') {
        return $html;
    }

    return preg_replace('/<\/?a\b[^>]*>/i', '', $html);
}

add_filter('render_block_core/post-terms', 'siff_event_type_labels', 10, 2);

//load leaflet css and js
function siff_enqueue_leaflet_assets(){
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
        filemtime( get_theme_file_path( 'assets/js/custom-map-init.js' ) )
    );
    wp_enqueue_style(
		'custom-map',
		get_theme_file_uri( 'assets/css/custom-map.css' ),
		array(),
		filemtime( get_theme_file_path( 'assets/css/custom-map.css' ) )
	);


}
add_action( 'wp_enqueue_scripts', 'siff_enqueue_leaflet_assets' );

// Keep the Events patterns available after file-based deployments.
function siff_register_events_grid_pattern() {
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
add_filter( 'woocommerce_product_add_to_cart_url', 'custom_box_office_add_to_cart_url', 10, 2 );
function custom_box_office_add_to_cart_url( $url, $product ) {
    if ( $product && 'yes' === get_post_meta( $product->get_id(), '_ticket', true ) && $product->is_purchasable() && $product->is_in_stock() ) {
        $current_url = remove_query_arg( array( 'add-to-cart', 'quantity' ) );
        return add_query_arg( 'add-to-cart', $product->get_id(), $current_url );
    }
    return $url;
}

add_filter( 'woocommerce_product_add_to_cart_text', 'custom_box_office_add_to_cart_text', 10, 2 );
function custom_box_office_add_to_cart_text( $text, $product ) {
    if ( $product && 'yes' === get_post_meta( $product->get_id(), '_ticket', true ) && $product->is_purchasable() && $product->is_in_stock() ) {
        return __( 'Buy Ticket', 'siff-theme' );
    }
    return $text;
}
