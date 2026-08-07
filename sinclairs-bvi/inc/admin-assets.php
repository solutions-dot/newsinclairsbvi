<?php
/**
 * Admin-only assets: repeater row cloning + the media-library image picker.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sbvi_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, array( 'service', 'page', 'testimonial' ), true ) ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style( 'sbvi-admin', SBVI_URI . '/assets/css/admin.css', array(), sbvi_asset_version( 'assets/css/admin.css' ) );
	wp_enqueue_script( 'sbvi-admin-repeater', SBVI_URI . '/assets/js/admin-repeater.js', array(), sbvi_asset_version( 'assets/js/admin-repeater.js' ), true );
}
add_action( 'admin_enqueue_scripts', 'sbvi_admin_assets' );
