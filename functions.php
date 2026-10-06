<?php

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
