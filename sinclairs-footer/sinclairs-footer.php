<?php
/**
 * Plugin Name:       Sinclairs (BVI) — Footer
 * Plugin URI:        https://sinclairsbvi.com/
 * Description:       The site footer as one shortcode, <code>[sinclairs_footer]</code>: navigation, opening hours and contact details in three balanced columns, each with an icon. Phone numbers dial, the email opens a mail client and the address opens a map. Does not include the copyright bar.
 * Version:           1.4.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Sinclairs (BVI)
 * License:           GPL-2.0-or-later
 * Text Domain:       sinclairs-footer
 *
 * The shortcode paints no background of its own — it takes the colour of
 * whatever section it is dropped into, which is the existing teal panel
 * — and registers no font, so it matches the theme. Colours are the
 * site's palette, the same values the Our Services plugin uses.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SBVIF_VERSION', '1.4.0' );
define( 'SBVIF_DIR', plugin_dir_path( __FILE__ ) );
define( 'SBVIF_URI', plugin_dir_url( __FILE__ ) );

require SBVIF_DIR . 'inc/icons.php';
require SBVIF_DIR . 'inc/content.php';
require SBVIF_DIR . 'inc/shortcode.php';

/**
 * Cache-bust from the file's mtime, so a CSS change is picked up as soon
 * as it is deployed without anyone remembering to bump a number.
 */
function sbvif_asset_version( $relative_path ) {
	$file = SBVIF_DIR . $relative_path;

	return file_exists( $file ) ? (string) filemtime( $file ) : SBVIF_VERSION;
}

add_action( 'wp_enqueue_scripts', 'sbvif_register_assets' );
function sbvif_register_assets() {
	wp_register_style( 'sbvif', SBVIF_URI . 'assets/footer.css', array(), sbvif_asset_version( 'assets/footer.css' ) );
	wp_register_script( 'sbvif', SBVIF_URI . 'assets/footer.js', array(), sbvif_asset_version( 'assets/footer.js' ), true );
}

/**
 * A footer renders after wp_head() has long gone out, so enqueueing the
 * stylesheet at that point would leave it to the footer's own print and
 * the panel would paint unstyled first. Print it inline at the shortcode
 * instead, exactly where it is needed.
 */
function sbvif_enqueue_assets() {
	sbvif_register_assets();

	wp_enqueue_script( 'sbvif' );

	if ( wp_style_is( 'sbvif', 'done' ) ) {
		return;
	}

	if ( did_action( 'wp_head' ) && ! doing_action( 'wp_head' ) ) {
		wp_print_styles( 'sbvif' );
		return;
	}

	wp_enqueue_style( 'sbvif' );
}
