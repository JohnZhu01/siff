<?php
/**
 * Title: Gold Vendors
 * Slug: siff-theme/vendors-gold
 * Categories: featured
 * Description: Gold vendors in a directory section.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tier = get_term_by( 'slug', 'gold', 'siff-vendor-tier' );
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
<!-- wp:group {"tagName":"section","className":"vendors-gold","layout":{"type":"constrained"}} -->
<section class="wp-block-group vendors-gold">
	<!-- wp:heading -->
	<h2 class="wp-block-heading"><?php esc_html_e( 'Gold vendors', 'siff-theme' ); ?></h2>
	<!-- /wp:heading -->
	<!-- wp:query <?php echo wp_json_encode( $query ); ?> -->
	<div class="wp-block-query">
		<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
		<!-- wp:siff/vendor-card /-->
		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->
</section>
<!-- /wp:group -->
