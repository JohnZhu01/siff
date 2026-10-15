<?php
/**
 * Title: Festival Schedule Days
 * Slug: siff-theme/schedule-days
 * Categories: query
 * Description: Three native event Query Loops ordered by scheduled start time.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

foreach ( siff_schedule_days() as $slug => $day ) :
	$query = array(
		'namespace' => 'siff/schedule-' . $slug,
		'query'     => array(
			// No pagination: events after the first 100 for this day are not shown.
			'perPage'         => 100,
			'pages'           => 0,
			'offset'          => 0,
			'postType'        => 'siff-event',
			'order'           => 'asc',
			'orderBy'         => 'title',
			'inherit'         => false,
			'siffSchedule'    => 'festival-2027',
			'siffScheduleDay' => $day['date'],
		),
		'className' => 'siff-schedule-query',
	);
	$section = array( 'tagName' => 'section', 'anchor' => $slug, 'className' => 'siff-schedule-day' );
?>
<!-- wp:group <?php echo wp_json_encode( $section ); ?> -->
<section id="<?php echo esc_attr( $slug ); ?>" class="wp-block-group siff-schedule-day">
<!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo esc_html( $day['label'] ); ?></h2>
<!-- /wp:heading -->
<!-- wp:query <?php echo wp_json_encode( $query ); ?> -->
<div class="wp-block-query siff-schedule-query">
<!-- wp:post-template -->
<!-- wp:siff/schedule-event /-->
<!-- /wp:post-template -->
<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'No events are scheduled for this day yet.', 'siff-theme' ); ?></p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results -->
</div>
<!-- /wp:query -->
</section>
<!-- /wp:group -->
<?php endforeach; ?>
