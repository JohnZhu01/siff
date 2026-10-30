<?php
// Remove admin menu links for non-Administrator accounts
function fwd_remove_admin_links() {
	if ( !current_user_can( 'manage_options' ) ) {
        remove_menu_page( 'edit-comments.php' );  // Remove Comments link
        remove_menu_page( 'themes.php' ); 
        remove_menu_page( 'tools.php' ); 
        remove_menu_page( 'admin.php' ); 
        remove_menu_page('woocommerce-marketing');
        remove_menu_page( 'admin.php?page=wc-settings&tab=checkout&from=PAYMENTS_MENU_ITEM' ); 
        remove_menu_page('wpseo_page_academy');
        remove_menu_page('post-new.php?post_type=page');
        
        

	}
}
add_action( 'admin_menu', 'fwd_remove_admin_links' );


function remove_submenus(){

    // If the current user is not an admin
    if ( !current_user_can('manage_options') ) {
        remove_submenu_page('wp-admin', 'users');
        remove_submenu_page('woocommerce', 'wc-settings');
        remove_submenu_page('woocommerce', 'wc-admin');
        remove_submenu_page('woocommerce', 'wc-status');
        remove_submenu_page('woocommerce', 'wc-admin&path=/extensions');
        remove_submenu_page('woocommerce', 'wc-settings&tab=checkout&section=stripe_api');
        remove_submenu_page('edit.php?post_type=page', 'post-new.php?post_type=page');
        remove_submenu_page('users.php', 'users.php');
        

    }
}

add_action( 'admin_init', 'remove_submenus' );