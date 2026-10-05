<?php
/**
 * Title: Platinum Vendors
 * Slug: siff-theme/vendors-platinum
 * Categories: featured
 * Description: Platinum vendors in a single-column directory section.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tier = get_term_by( 'slug', 'platinum', 'siff-vendor-tier' );
if ( ! $tier || is_wp_error( $tier ) ) {
	return;
}

$query = array(
	'query' => array(
		'postType' => 'siff-vendor',
		'perPage'  => 100,
		'offset'   => 0,
		'order'    => 'asc',
		'orderBy'  => 'title',
		'inherit'  => false,
		'taxQuery' => array(
			'siff-vendor-tier' => array( (int) $tier->term_id ),
		),
	),
);
?>
<!-- wp:group {"tagName":"section","className":"vendors-platinum","layout":{"type":"constrained"}} -->
<section class="wp-block-group vendors-platinum">
	<!-- wp:heading -->
	<h2 class="wp-block-heading"><?php esc_html_e( 'Platinum vendors', 'siff-theme' ); ?></h2>
	<!-- /wp:heading -->
	<!-- wp:query <?php echo wp_json_encode( $query ); ?> -->
	<div class="wp-block-query">
		<!-- wp:post-template {"layout":{"type":"grid","columnCount":1}} -->
		<!-- wp:siff/vendor-card /-->
		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->
</section>
<!-- /wp:group -->
