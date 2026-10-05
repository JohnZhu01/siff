<?php
/**
 * Vendor card markup included by siff_render_vendor_card().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$name    = get_the_title( $vendor_id );
$logo_id = get_post_thumbnail_id( $vendor_id );
$tiers   = wp_get_post_terms( $vendor_id, 'siff-vendor-tier', array( 'fields' => 'names' ) );
$types   = wp_get_post_terms( $vendor_id, 'siff-vendor-type', array( 'fields' => 'names' ) );
$labels  = array_merge(
	is_wp_error( $tiers ) ? array() : $tiers,
	is_wp_error( $types ) ? array() : $types
);

$description  = '';
$location     = '';
$availability = '';
$website      = '';

if ( function_exists( 'get_field' ) ) {
	$description  = get_field( 'vendor_description', $vendor_id );
	$location     = get_field( 'vendor_location', $vendor_id );
	$availability = get_field( 'vendor_availability', $vendor_id );
	$website      = get_field( 'vendor_website', $vendor_id );
}
?>
<article <?php echo get_block_wrapper_attributes( array( 'class' => 'siff-vendor-card' ) ); ?> aria-label="<?php echo esc_attr( $name ); ?>">
	<?php if ( $logo_id ) : ?>
		<div class="siff-vendor-card__logo">
			<?php // WordPress escapes image attributes and uses the attachment's existing alt text. ?>
			<?php echo wp_get_attachment_image( $logo_id, 'full' ); ?>
		</div>
	<?php endif; ?>
	<div class="siff-vendor-card__details">
		<h3><?php echo esc_html( $name ); ?></h3>
		<?php if ( $labels ) : ?>
			<p class="siff-vendor-card__meta"><?php echo esc_html( implode( ' · ', $labels ) ); ?></p>
		<?php endif; ?>
		<?php if ( $description ) : ?>
			<p><?php echo nl2br( esc_html( $description ) ); ?></p>
		<?php endif; ?>
		<?php if ( $location ) : ?>
			<p><?php echo esc_html( $location ); ?></p>
		<?php endif; ?>
		<?php if ( $availability ) : ?>
			<p><?php echo nl2br( esc_html( $availability ) ); ?></p>
		<?php endif; ?>
		<?php if ( $website && esc_url( $website ) ) : ?>
			<a href="<?php echo esc_url( $website ); ?>"><?php
				/* translators: %s: vendor business name. */
				echo esc_html( sprintf( __( 'Visit %s website', 'siff-theme' ), $name ) );
			?></a>
		<?php endif; ?>
	</div>
</article>
