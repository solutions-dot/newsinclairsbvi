<?php
/**
 * Plugin Name:       Sinclairs (BVI) — Hero Slider
 * Plugin URI:        https://sinclairsbvi.com/
 * Description:       A lightweight replacement for Slider Revolution. Upload 1920&times;1080 images, set a focal point per slide so the crop never cuts off the subject, position heading and button independently, pick a transition, and show the slide navigation in a band <em>below</em> the images rather than on top of them. Output with the <code>[sinclairs_slider]</code> shortcode or the Sinclairs Slider block.
 * Version:           1.2.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Sinclairs (BVI)
 * License:           GPL-2.0-or-later
 * Text Domain:       sinclairs-slider
 *
 * Theme-agnostic by design: it never registers a font of its own and
 * inherits whatever the active theme has already loaded, so the slider
 * matches the rest of the site out of the box. A single optional
 * override in Slider → Settings exists for the case where the hero is
 * meant to differ from body copy.
 *
 * Output
 * ------
 *   [sinclairs_slider]              All published slides, in menu order.
 *   [sinclairs_slider ids="4,9"]    Just those slides, in that order.
 *   sinclairs_slider();             Template tag, same arguments as an array.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SBVIS_VERSION', '1.2.0' );
define( 'SBVIS_DIR', plugin_dir_path( __FILE__ ) );
define( 'SBVIS_URI', plugin_dir_url( __FILE__ ) );

require SBVIS_DIR . 'inc/post-type.php';
require SBVIS_DIR . 'inc/settings.php';
require SBVIS_DIR . 'inc/meta-boxes.php';
require SBVIS_DIR . 'inc/render.php';

/**
 * Cache-bust from the file's mtime so a CSS/JS change is picked up the
 * moment it is deployed, without having to remember to bump the version.
 */
function sbvis_asset_version( $relative_path ) {
	$file = SBVIS_DIR . $relative_path;

	return file_exists( $file ) ? (string) filemtime( $file ) : SBVIS_VERSION;
}

/**
 * 1920x1080 is the size the client is told to upload at, so register a
 * hard crop at exactly that and a couple of smaller steps for phones.
 * The focal point stored on each slide drives object-position, so these
 * crops are centre-cropped only as a last-resort fallback.
 */
add_action( 'after_setup_theme', 'sbvis_register_image_sizes' );
function sbvis_register_image_sizes() {
	add_image_size( 'sbvis-slide', 1920, 1080, true );
	add_image_size( 'sbvis-slide-md', 1280, 720, true );
	add_image_size( 'sbvis-slide-sm', 768, 432, true );
	add_image_size( 'sbvis-thumb', 160, 160, true );
}

add_action( 'plugins_loaded', 'sbvis_load_textdomain' );
function sbvis_load_textdomain() {
	load_plugin_textdomain( 'sinclairs-slider', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

register_activation_hook( __FILE__, 'sbvis_activate' );
function sbvis_activate() {
	sbvis_register_post_type();
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
