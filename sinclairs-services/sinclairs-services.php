<?php
/**
 * Plugin Name:       Sinclairs (BVI) — Our Services
 * Plugin URI:        https://sinclairsbvi.com/our-services/
 * Description:       Renders the whole "Our Services" page — the In a Nutshell index, a jump-to dropdown and every practice area with its Information &amp; FAQs — from one shortcode. Also adds the "Our Services" nav dropdown that anchor-links to each section.
 * Version:           2.3.6
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Sinclairs (BVI)
 * License:           GPL-2.0-or-later
 * Text Domain:       sinclairs-services
 *
 * This REPLACES the sinclairs-services plugin currently live on the site.
 * It deliberately keeps that plugin's folder name, asset paths
 * (assets/frontend.css, assets/frontend.js), script/style handles
 * (sc-frontend-css, sc-frontend-js) and `sc-` class prefix, so it drops
 * straight in over the existing install and the client's icons carry over.
 *
 * Shortcodes
 * ----------
 *   [sinclairs_services]            Whole page: intro + nutshell index + dropdown + all sections.
 *   [sinclairs_services_nutshell]   Just the In a Nutshell index (for use elsewhere on the site).
 *   [sinclairs_services_menu]       Just the jump-to dropdown box.
 *
 * The copy lives in inc/content.php, transcribed from the client's Word
 * documents, and runs through the `sinclairs_services_content` filter
 * before rendering — see README.md.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SSVC_VERSION', '2.3.6' );
define( 'SSVC_DIR', plugin_dir_path( __FILE__ ) );
define( 'SSVC_URI', plugin_dir_url( __FILE__ ) );

require SSVC_DIR . 'inc/content.php';
require SSVC_DIR . 'inc/icons.php';
require SSVC_DIR . 'inc/seed-page.php';
require SSVC_DIR . 'inc/shortcode.php';
require SSVC_DIR . 'inc/nav.php';
require SSVC_DIR . 'inc/seed-menu.php';

/**
 * Activation seeds, in this order:
 *
 *   1. the Our Services page — only when one doesn't already exist. An
 *      existing page is never rewritten; see inc/seed-page.php for why
 *      (it is Elementor-built on this site).
 *   2. real submenu items under "Our Services", one per section anchor,
 *      so the dropdown is editable in Appearance → Menus rather than
 *      injected invisibly at render time.
 *
 * Page first, so the menu links point at the real permalink. Both steps
 * are idempotent.
 */
register_activation_hook( __FILE__, 'ssvc_activate' );

/**
 * Handles and paths match the plugin already on the site so an upgrade
 * doesn't leave a stale stylesheet cached under a different name.
 */
function ssvc_register_assets() {
	$css = SSVC_DIR . 'assets/frontend.css';
	$js  = SSVC_DIR . 'assets/frontend.js';

	wp_register_style(
		'sc-frontend-css',
		SSVC_URI . 'assets/frontend.css',
		array(),
		file_exists( $css ) ? (string) filemtime( $css ) : SSVC_VERSION
	);

	wp_register_script(
		'sc-frontend-js',
		SSVC_URI . 'assets/frontend.js',
		array(),
		file_exists( $js ) ? (string) filemtime( $js ) : SSVC_VERSION,
		true
	);
}
add_action( 'init', 'ssvc_register_assets' );

/**
 * Called by each shortcode as it renders, so pages without the shortcode
 * don't pay for the CSS/JS.
 */
function ssvc_enqueue_assets() {
	wp_enqueue_style( 'sc-frontend-css' );
	wp_enqueue_script( 'sc-frontend-js' );
}

/**
 * The "Our Services" submenu appears in the site header on every page, so
 * its styling has to load everywhere — not only on the Services page.
 *
 * This used to be gated on ssvc_nav_dropdown_enabled(), which is false
 * once real menu items have been seeded. That left the seeded submenu
 * completely unstyled on every page that doesn't run the shortcode. The
 * stylesheet is small, and the rules are namespaced, so it now always
 * loads.
 */
function ssvc_enqueue_nav_assets() {
	wp_enqueue_style( 'sc-frontend-css' );
	wp_enqueue_script( 'sc-frontend-js' );
}
add_action( 'wp_enqueue_scripts', 'ssvc_enqueue_nav_assets', 20 );
