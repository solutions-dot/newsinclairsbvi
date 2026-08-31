<?php
/**
 * Plugin Name:       Sinclairs (BVI) — Testimonials
 * Plugin URI:        https://sinclairsbvi.com/
 * Description:       A testimonials carousel: the client's name and title above, their words below in quotes. One shortcode, <code>[sinclairs_testimonials]</code>. The box takes the height of whichever testimonial is showing rather than sitting at the height of the longest, and the arrows and dots sit under the card so they never overlap the text.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Sinclairs (BVI)
 * License:           GPL-2.0-or-later
 * Text Domain:       sinclairs-testimonials
 *
 * Theme-agnostic: no font is registered, so the carousel inherits
 * whatever typeface the active theme loads. Colours come from the
 * site's existing palette — the same values the Our Services plugin
 * uses — so the two read as one piece of work.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SBVIT_VERSION', '1.0.0' );
define( 'SBVIT_DIR', plugin_dir_path( __FILE__ ) );
define( 'SBVIT_URI', plugin_dir_url( __FILE__ ) );

require SBVIT_DIR . 'inc/content.php';
require SBVIT_DIR . 'inc/shortcode.php';

/**
 * Cache-bust from the file's mtime, so a CSS or JS change is picked up
 * as soon as it is deployed without anyone remembering to bump a number.
 */
function sbvit_asset_version( $relative_path ) {
	$file = SBVIT_DIR . $relative_path;

	return file_exists( $file ) ? (string) filemtime( $file ) : SBVIT_VERSION;
}

add_action( 'wp_enqueue_scripts', 'sbvit_register_assets' );
function sbvit_register_assets() {
	wp_register_style( 'sbvit', SBVIT_URI . 'assets/testimonials.css', array(), sbvit_asset_version( 'assets/testimonials.css' ) );
	wp_register_script( 'sbvit', SBVIT_URI . 'assets/testimonials.js', array(), sbvit_asset_version( 'assets/testimonials.js' ), true );
}

/**
 * Enqueue at render time, and print the stylesheet inline when wp_head
 * has already gone out — the template-tag case, where enqueueing would
 * otherwise leave the card to paint unstyled first.
 */
function sbvit_enqueue_assets() {
	sbvit_register_assets();

	wp_enqueue_script( 'sbvit' );

	if ( wp_style_is( 'sbvit', 'done' ) ) {
		return;
	}

	if ( did_action( 'wp_head' ) && ! doing_action( 'wp_head' ) ) {
		wp_print_styles( 'sbvit' );
		return;
	}

	wp_enqueue_style( 'sbvit' );
}
