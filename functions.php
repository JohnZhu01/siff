<?php
// Open Modal//
add_action('wp_footer', function () {
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
});
// Close Modal//
add_action('wp_enqueue_scripts', function () {
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
});

// Make WordPress's AJAX URL available to the modal script.
add_action('wp_enqueue_scripts', function () {
    wp_localize_script('siff-event-modal', 'siffModal', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
    ));
}, 20);

// Allow visitors and logged-in users to request event details.
add_action('wp_ajax_siff_event_details', 'siff_get_event_details');
add_action('wp_ajax_nopriv_siff_event_details', 'siff_get_event_details');

function siff_get_event_details()
{
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
