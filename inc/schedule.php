<?php
/**
 * Schedule extensions: native Query Loops, ACF start times and a dynamic row.
 *
 * The editor uses the existing CPT REST collection; visitors use WP_Query.
 * Both paths opt into the same narrowly scoped SQL through a private query flag.
 *
 * Programme limit: each saved Query Loop requests at most 100 events per day.
 * There is no pagination, so events 101 onward are not displayed. This is enough
 * for the current 2/9/5 programme. Before exceeding it, add native pagination to
 * the pattern, editor variations AND existing page content, then test both views.
 *
 * Patterns use WordPress's native /patterns discovery. For local theme work,
 * WP_DEVELOPMENT_MODE = 'theme' in the local wp-config.php bypasses the pattern
 * cache; WP_ENVIRONMENT_TYPE = 'local' alone does not. Keep that setting out of
 * LIVE and the theme repository. This theme does not change either setting.
 * If a newly added pattern is missing while caching is enabled, clear
 * wp_get_theme()->delete_pattern_cache() once and reload. Do not clear that
 * cache or manually register this pattern on every request.
 * @see https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/extending-the-query-loop-block/
 * @see https://developer.wordpress.org/themes/patterns/registering-patterns/
 * @see https://make.wordpress.org/core/2023/07/14/configuring-development-mode-in-6-3/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Festival dates are configuration; event details always come from the posts. */
function siff_schedule_days() {
	return array(
		'friday'   => array( 'date' => '2027-06-11', 'label' => __( 'Friday · June 11', 'siff-theme' ) ),
		'saturday' => array( 'date' => '2027-06-12', 'label' => __( 'Saturday · June 12', 'siff-theme' ) ),
		'sunday'   => array( 'date' => '2027-06-13', 'label' => __( 'Sunday · June 13', 'siff-theme' ) ),
	);
}

/** Only the three assignment dates are accepted, including in REST requests. */
function siff_schedule_valid_day( $day ) {
	return is_string( $day ) && in_array( $day, array_column( siff_schedule_days(), 'date' ), true );
}

/** Register the row using the same PHP-only convention as the vendor card. */
function siff_register_schedule() {
	wp_register_style(
		'siff-schedule',
		get_theme_file_uri( 'assets/css/schedule.css' ),
		array(),
		filemtime( get_theme_file_path( 'assets/css/schedule.css' ) )
	);
	register_block_type(
		get_theme_file_path( 'blocks/schedule-event' ),
		array( 'render_callback' => 'siff_render_schedule_event' )
	);
}
add_action( 'init', 'siff_register_schedule' );

/**
 * A stable page class also scopes the shared modal, which lives in the footer.
 * Match the page slug so the Schedule can move to a site with different IDs.
 *
 * @param string[] $classes Existing body classes.
 * @return string[] Body classes with the Schedule marker when appropriate.
 */
function siff_schedule_body_classes( $classes ) {
	if ( is_page( 'schedule' ) ) {
		$classes[] = 'siff-schedule-page';
	}
	return $classes;
}
add_filter( 'body_class', 'siff_schedule_body_classes' );

/** Include the surrounding day/focus styles on Schedule and in its editor. */
function siff_enqueue_schedule_styles() {
	$post = get_post();
	if ( is_page( 'schedule' ) || ( is_admin() && $post && 'page' === $post->post_type && 'schedule' === $post->post_name ) ) {
		wp_enqueue_style( 'siff-schedule' );
	}
}
add_action( 'enqueue_block_assets', 'siff_enqueue_schedule_styles' );

/** Variations label the three loops and hide unrelated query controls. */
function siff_enqueue_schedule_editor() {
	wp_enqueue_script(
		'siff-schedule-editor',
		get_theme_file_uri( 'assets/js/schedule-editor.js' ),
		array( 'wp-blocks', 'wp-dom-ready', 'wp-i18n' ),
		filemtime( get_theme_file_path( 'assets/js/schedule-editor.js' ) ),
		true
	);
	wp_add_inline_script( 'siff-schedule-editor', 'const siffScheduleDays = ' . wp_json_encode( siff_schedule_days() ) . ';', 'before' );
}
add_action( 'enqueue_block_editor_assets', 'siff_enqueue_schedule_editor' );

/** Mark only our explicit Schedule loops, including their No Results queries. */
function siff_schedule_query_vars( $args, $block ) {
	$query = $block->context['query'] ?? array();
	if ( 'festival-2027' !== ( $query['siffSchedule'] ?? '' )
		|| 'siff-event' !== ( $query['postType'] ?? '' )
		|| ! empty( $query['inherit'] ) ) {
		return $args;
	}
	return siff_schedule_query_args( $args, $query['siffScheduleDay'] ?? '' );
}
add_filter( 'query_loop_block_query_vars', 'siff_schedule_query_vars', 10, 2 );

/** Share validated query settings between front-end loops and editor previews. */
function siff_schedule_query_args( $args, $day ) {
	if ( ! siff_schedule_valid_day( $day ) ) {
		$args['post__in'] = array( 0 );
		return $args;
	}
	$args['post_type']           = 'siff-event';
	$args['post_status']         = 'publish';
	$args['ignore_sticky_posts'] = true;
	$args['siff_schedule_day']   = $day;
	return $args;
}

/** Validate our two extra parameters on the existing event collection endpoint. */
function siff_schedule_rest_params( $params ) {
	$params['siffSchedule'] = array(
		'description' => 'Opt into the SIFF festival Schedule query.',
		'type'        => 'string',
		'enum'        => array( 'festival-2027' ),
	);
	$params['siffScheduleDay'] = array(
		'description' => 'Festival date in YYYY-MM-DD format.',
		'type'        => 'string',
		'enum'        => array_column( siff_schedule_days(), 'date' ),
	);
	return $params;
}
add_filter( 'rest_siff-event_collection_params', 'siff_schedule_rest_params' );

/** Core passes custom Query Loop parameters to REST for the editor preview. */
function siff_schedule_rest_query( $args, $request ) {
	if ( 'festival-2027' !== $request->get_param( 'siffSchedule' ) ) {
		return $args;
	}
	return siff_schedule_query_args( $args, $request->get_param( 'siffScheduleDay' ) );
}
add_filter( 'rest_siff-event_query', 'siff_schedule_rest_query', 10, 2 );

/**
 * ACF stores both date-time pickers as Y-m-d H:i:s (local wall-clock time).
 * One effective start combines films and other events in chronological order.
 * Sorting two separate meta clauses would incorrectly group by field instead.
 * No SQL changes are made without our private flag AND the event post type.
 */
function siff_schedule_query_clauses( $clauses, $query ) {
	$day = $query->get( 'siff_schedule_day' );
	if ( 'siff-event' !== $query->get( 'post_type' ) || ! siff_schedule_valid_day( $day ) ) {
		return $clauses;
	}
	global $wpdb;
	$next_day = ( new DateTimeImmutable( $day, wp_timezone() ) )->modify( '+1 day' )->format( 'Y-m-d' );
	$clauses['join'] .= $wpdb->prepare(
		" LEFT JOIN {$wpdb->postmeta} AS siff_schedule_film ON ({$wpdb->posts}.ID = siff_schedule_film.post_id AND siff_schedule_film.meta_key = %s)
		LEFT JOIN {$wpdb->postmeta} AS siff_schedule_other ON ({$wpdb->posts}.ID = siff_schedule_other.post_id AND siff_schedule_other.meta_key = %s)",
		'screening_date_and_time',
		'date_and_time'
	);
	$start = "CAST(COALESCE(NULLIF(siff_schedule_film.meta_value, ''), NULLIF(siff_schedule_other.meta_value, '')) AS DATETIME)";
	$clauses['where'] .= $wpdb->prepare( " AND $start >= %s AND $start < %s", $day . ' 00:00:00', $next_day . ' 00:00:00' );
	$clauses['orderby'] = "$start ASC, {$wpdb->posts}.post_title ASC, {$wpdb->posts}.ID ASC";
	return $clauses;
}
add_filter( 'posts_clauses', 'siff_schedule_query_clauses', 10, 2 );

/** Read raw ACF values, with the same field precedence as the shared modal. */
function siff_schedule_event_start( $event_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}
	$raw = get_field( 'screening_date_and_time', $event_id, false )
		?: get_field( 'date_and_time', $event_id, false );
	if ( ! is_string( $raw ) || '' === $raw ) {
		return null;
	}
	$start = DateTimeImmutable::createFromFormat( '!Y-m-d H:i:s', $raw, wp_timezone() );
	return $start && $start->format( 'Y-m-d H:i:s' ) === $raw ? $start : null;
}

/** The Post Template supplies postId, so no IDs or event details are hard-coded. */
function siff_render_schedule_event( $attributes, $content, $block ) {
	$event_id = absint( $block->context['postId'] ?? 0 );
	if ( ! $event_id || 'siff-event' !== get_post_type( $event_id ) || 'publish' !== get_post_status( $event_id ) ) {
		return '';
	}
	ob_start();
	include get_theme_file_path( 'blocks/schedule-event/render.php' );
	return ob_get_clean();
}
