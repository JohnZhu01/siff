<?php
// Event modal.
function siff_render_event_modal() {
    ?>
    <dialog id="siff-event-modal" aria-labelledby="siff-modal-title">
        <button type="button" id="siff-modal-close" aria-label="Close event details">
            &times;
        </button>

        <div class="siff-modal-layout">
            <div class="siff-modal-image">
                <img id="siff-modal-image" alt="" hidden>
            </div>

            <div class="siff-modal-info">
                <h2 id="siff-modal-title">Event details</h2>

                <div id="siff-modal-meta"></div>

                <p id="siff-modal-person" hidden></p>
            </div>

            <div class="siff-modal-description">
                <h3 id="siff-modal-description-title">Description</h3>

                <div id="siff-modal-content"></div>
            </div>
        </div>
    </dialog>
    <?php
}

add_action('wp_footer', 'siff_render_event_modal');

function siff_enqueue_event_modal_assets() {
    wp_enqueue_style(
        'siff-event-modal',
        get_theme_file_uri('/assets/css/event-modal.css'),
        array(),
        filemtime(get_theme_file_path('/assets/css/event-modal.css'))
    );

    wp_enqueue_script(
        'siff-event-modal',
        get_theme_file_uri('/assets/js/event-modal.js'),
        array(),
        filemtime(get_theme_file_path('/assets/js/event-modal.js')),
        true
    );
}

add_action('wp_enqueue_scripts', 'siff_enqueue_event_modal_assets');

function siff_localize_event_modal() {
    wp_localize_script('siff-event-modal', 'siffModal', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
    ));
}

add_action('wp_enqueue_scripts', 'siff_localize_event_modal', 20);

// Event details for the modal.
add_action('wp_ajax_siff_event_details', 'siff_get_event_details');
add_action('wp_ajax_nopriv_siff_event_details', 'siff_get_event_details');

function siff_get_event_details() {
    $event_id = absint($_GET['event_id'] ?? 0);
    $event = get_post($event_id);

    // Only expose published event posts.
    if (
        !$event ||
        $event->post_type !== 'siff-event' ||
        $event->post_status !== 'publish'
    ) {
        wp_send_json_error('Event not found.', 404);
    }

    if (!function_exists('get_field')) {
        wp_send_json_error('Event fields are unavailable.', 503);
    }

    $event_types = wp_get_post_terms(
        $event_id,
        'siff-event-types',
        array('fields' => 'slugs')
    );

    if (is_wp_error($event_types)) {
        $event_types = array();
    }

    wp_send_json_success(array(
        'types' => $event_types,
        'instructor' => get_field('instructor', $event_id),
        'instructor_profession' => get_field('instructor_profession', $event_id),
        'speaker' => get_field('speaker', $event_id),
        'speaker_profession' => get_field('speaker_profession', $event_id),
        'image' => get_the_post_thumbnail_url($event_id, 'large') ?: '',
        'title' => html_entity_decode(
            get_the_title($event_id),
            ENT_QUOTES,
            get_bloginfo('charset')
        ),
        'description' => get_field('synopsis', $event_id)
            ?: get_field('short_description', $event_id),
        'date' => get_field('screening_date_and_time', $event_id)
            ?: get_field('date_and_time', $event_id),
        'filmmaker' => get_field('filmmaker', $event_id),
        'director' => get_field('director', $event_id),
        'venue' => get_field('venue', $event_id),
        'duration' => get_field('runtime', $event_id)
            ?: get_field('duration', $event_id),
    ));
}

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

// Replace the marked Post Date block with the event's ACF date and time.
add_filter('render_block_core/post-date', function ($html, $attributes, $block) {
    $classes = preg_split('/\s+/', trim($attributes['attrs']['className'] ?? ''));
    $event_id = absint($block->context['postId'] ?? 0);

    if (!in_array('siff-event-date', $classes, true)
        || get_post_type($event_id) !== 'siff-event'
        || !function_exists('get_field')) {
        return $html;
    }

    $date = get_field('screening_date_and_time', $event_id)
        ?: get_field('date_and_time', $event_id);

    // Never fall back to the publication date for an event card.
    if (!$date) {
        return '';
    }

    return '<div class="' . esc_attr('wp-block-post-date ' . implode(' ', $classes))
        . '">' . esc_html($date) . '</div>';
}, 10, 3);

// Event type labels inside cards.
function siff_event_type_labels($html, $parsed_block) {
    if (($parsed_block['attrs']['term'] ?? '') !== 'siff-event-types') {
        return $html;
    }

    return preg_replace('/<\/?a\b[^>]*>/i', '', $html);
}

add_filter('render_block_core/post-terms', 'siff_event_type_labels', 10, 2);

// Connect event titles to the modal.
add_filter('render_block_core/post-title', function ($html, $parsed_block, $block) {
    $classes = preg_split('/\s+/', trim($parsed_block['attrs']['className'] ?? ''));
    $event_id = absint($block->context['postId'] ?? 0);

    if (!in_array('siff-event-trigger', $classes, true)
        || get_post_type($event_id) !== 'siff-event') {
        return $html;
    }

    $processor = new WP_HTML_Tag_Processor($html);
    if ($processor->next_tag('A')) {
        $processor->set_attribute('data-event-id', $event_id);
        $processor->set_attribute('aria-haspopup', 'dialog');
        $processor->set_attribute('aria-controls', 'siff-event-modal');
    }

    return $processor->get_updated_html();
}, 10, 3);


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
