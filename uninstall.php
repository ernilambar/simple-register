<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Simple_Register
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
if ( is_multisite() ) {
	global $wpdb;
	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );

	delete_option( 'sr_plugin_options' );

	if ( $blogs ) {
		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog['blog_id'] );
			delete_option( 'sr_plugin_options' );
			restore_current_blog();
		}
	}
} else {
	delete_option( 'sr_plugin_options' );
}
