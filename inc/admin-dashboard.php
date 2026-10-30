<?php
/**
 * Dashboard customizations, without a plugin.
 *
 * 1. Remove the default and plugin widgets the client doesn't need.
 * 2. Add a "How-to guides" widget with links to the client tutorial
 *    and the screens she uses most.
 *
 * Demo tip: comment out each add_action() line before presenting, then
 * uncomment them one at a time and refresh the Dashboard to show the change.
 *
 * @package siff
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove Dashboard widgets.
 *
 * Widget IDs: open the Dashboard, right-click a widget → Inspect,
 * and copy the id of its outer <div class="postbox">.
 */
function siff_remove_dashboard_widgets() {
    remove_meta_box( 'wc_admin_dashboard_setup', 'dashboard', 'normal' ); 
    remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' ); 
    remove_meta_box( 'dashboard_primary', 'dashboard', 'side' ); 
    remove_meta_box( 'wpseo-wincher-dashboard-overview', 'dashboard', 'normal' ); 
    remove_meta_box( 'wpseo-dashboard-overview', 'dashboard', 'normal' ); 
    remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' ); 
    remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' ); 
    remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' ); 
    remove_meta_box( 'wp_mail_smtp_reports_widget_lite', 'dashboard', 'normal' ); 
    remove_meta_box( 'wpcom_daily_writing_prompt', 'dashboard', 'side' ); 
    remove_meta_box( 'jetpack_newsletter_dashboard_widget', 'dashboard', 'side' ); 

}
// Priority 20 runs after plugins have added their widgets, so they can be removed.
add_action( 'wp_dashboard_setup', 'siff_remove_dashboard_widgets', 20 );

/**
 * Add the "How-to guides" widget.
 */
function siff_add_dashboard_widgets() {
	wp_add_dashboard_widget(
		'siff_how_to',                                        // Widget ID.
		__( 'The Seaside Independent Film Festival : How-to guides', 'siff' ),   // Title.
		'siff_render_how_to_widget'                           // Callback that prints the content.
	);
}
add_action( 'wp_dashboard_setup', 'siff_add_dashboard_widgets' );

/**
 * Print the "How-to guides" widget.
 */
function siff_render_how_to_widget() {
	// Replace with the real tutorial link once it's published.
	$tutorial_url = 'add our link here';

	?>
	<p><?php esc_html_e( 'Step-by-step guides for running the site.', 'siff' ); ?></p>

	<p>
		<a class="button button-primary" href="<?php echo esc_url( $tutorial_url ); ?>" target="_blank" rel="noopener">
			<?php esc_html_e( 'Open the client tutorial', 'siff' ); ?>
		</a>
	</p>
	<?php
}