<?php
/** Schedule row markup included by siff_render_schedule_event(). */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$name  = html_entity_decode( get_the_title( $event_id ), ENT_QUOTES, get_bloginfo( 'charset' ) );
$start = siff_schedule_event_start( $event_id );
$types = wp_get_post_terms( $event_id, 'siff-event-types', array( 'fields' => 'names' ) );
$venue = function_exists( 'get_field' ) ? get_field( 'venue', $event_id ) : '';
$venue = is_string( $venue ) ? trim( $venue ) : '';
?>
<article <?php echo get_block_wrapper_attributes( array( 'class' => 'siff-schedule-row' ) ); ?>>
	<div class="siff-schedule-row__time">
		<?php if ( $start ) : ?>
			<time datetime="<?php echo esc_attr( $start->format( DATE_ATOM ) ); ?>">
				<span class="siff-schedule-row__date"><?php echo esc_html( wp_date( 'M j, Y', $start->getTimestamp(), wp_timezone() ) ); ?></span>
				<strong><?php echo esc_html( wp_date( get_option( 'time_format' ), $start->getTimestamp(), wp_timezone() ) ); ?></strong>
			</time>
		<?php else : ?>
			<?php esc_html_e( 'Time to be confirmed', 'siff-theme' ); ?>
		<?php endif; ?>
	</div>
	<div class="siff-schedule-row__details">
		<h3><?php echo esc_html( $name ); ?></h3>
		<?php if ( ! is_wp_error( $types ) && $types ) : ?>
			<p class="siff-schedule-row__type"><?php echo esc_html( implode( ' · ', $types ) ); ?></p>
		<?php endif; ?>
	</div>
	<p class="siff-schedule-row__venue"><?php echo esc_html( '' !== $venue ? $venue : __( 'Venue to be confirmed', 'siff-theme' ) ); ?></p>
	<button type="button" class="siff-schedule-row__view wp-element-button" data-event-id="<?php echo esc_attr( $event_id ); ?>" aria-haspopup="dialog" aria-controls="siff-event-modal" aria-label="<?php
		/* translators: %s: event title. */
		echo esc_attr( sprintf( __( 'View Event: %s', 'siff-theme' ), $name ) );
	?>"><?php esc_html_e( 'View Event', 'siff-theme' ); ?></button>
</article>
