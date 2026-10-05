<?php
/**
 * Title: Featured Vendors
 * Slug: siff-theme/featured-vendors
 * Categories: featured
 * Description: Dynamic Platinum and Gold vendor logos, separate from the footer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- wp:group {"tagName":"section","align":"full","className":"siff-featured-vendors"} -->
<section class="wp-block-group alignfull siff-featured-vendors">
<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Our featured vendors', 'siff-theme' ); ?></h2>
<!-- /wp:heading -->
<?php
$featured_tiers = array(
	'platinum' => __( 'Platinum vendors', 'siff-theme' ),
	'gold'     => __( 'Gold vendors', 'siff-theme' ),
);
foreach ( $featured_tiers as $tier_slug => $label ) :
	$tier = get_term_by( 'slug', $tier_slug, 'siff-vendor-tier' );
	if ( ! $tier || is_wp_error( $tier ) ) {
		continue;
	}
	$query = array(
		'queryId'   => 'platinum' === $tier_slug ? 3101 : 3102,
		'query'     => array(
			'perPage'  => 100,
			'pages'    => 0,
			'offset'   => 0,
			'postType' => 'siff-vendor',
			'order'    => 'asc',
			'orderBy'  => 'title',
			'inherit'  => false,
			'taxQuery' => array(
				'siff-vendor-tier' => array( (int) $tier->term_id ),
			),
		),
		'className' => 'siff-featured-vendors__' . $tier_slug,
	);
?>
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html( $label ); ?></h3>
<!-- /wp:heading -->
<!-- wp:query <?php echo wp_json_encode( $query ); ?> -->
<div class="wp-block-query siff-featured-vendors__<?php echo esc_attr( $tier_slug ); ?>">
<!-- wp:post-template {"className":"siff-featured-vendors__grid","layout":{"type":"grid","columnCount":3}} -->
<!-- wp:post-featured-image {"isLink":false,"sizeSlug":"full","scale":"contain"} /-->
<!-- /wp:post-template -->
</div>
<!-- /wp:query -->
<?php endforeach; ?>
</section>
<!-- /wp:group -->
