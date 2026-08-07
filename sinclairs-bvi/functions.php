<?php
/**
 * Sinclairs (BVI) theme bootstrap.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SBVI_VERSION', '1.1.0' );
define( 'SBVI_DIR', get_template_directory() );
define( 'SBVI_URI', get_template_directory_uri() );

/**
 * Theme setup: supports, menus, image sizes.
 */
function sbvi_setup() {
	load_theme_textdomain( 'sinclairs-bvi', SBVI_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 116,
		'width'       => 333,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	// Pages get an excerpt field — used as the short "dek" line under page H1s.
	add_post_type_support( 'page', 'excerpt' );

	register_nav_menus( array(
		'footer_services' => __( 'Footer — Services (auto-filled, override optional)', 'sinclairs-bvi' ),
	) );

	add_image_size( 'sbvi-hero', 2200, 1400, true );
	add_image_size( 'sbvi-banner', 1800, 480, true );
	add_image_size( 'sbvi-portrait', 900, 1125, true );
	add_image_size( 'sbvi-panel', 1000, 750, true );
	add_image_size( 'sbvi-card', 800, 600, true );
}
add_action( 'after_setup_theme', 'sbvi_setup' );

/**
 * Includes.
 */
require SBVI_DIR . '/inc/helpers.php';
require SBVI_DIR . '/inc/repeater-field.php';
require SBVI_DIR . '/inc/cpt-service.php';
require SBVI_DIR . '/inc/cpt-article.php';
require SBVI_DIR . '/inc/cpt-testimonial.php';
require SBVI_DIR . '/inc/page-meta-boxes.php';
require SBVI_DIR . '/inc/customizer.php';
require SBVI_DIR . '/inc/contact-form.php';
require SBVI_DIR . '/inc/admin-assets.php';
require SBVI_DIR . '/inc/seed-content.php';

/**
 * File's last-modified time as its cache-busting version, so an edit to
 * style.css or main.js is never masked by a browser serving a stale
 * cached copy from a previous ?ver= — which is exactly what happened
 * during development (theme-file edits with no version bump, so the
 * <link>/<script> URL never changed and browsers kept the old file).
 */
function sbvi_asset_version( $relative_path ) {
	$file = SBVI_DIR . '/' . ltrim( $relative_path, '/' );
	return file_exists( $file ) ? (string) filemtime( $file ) : SBVI_VERSION;
}

/**
 * Front-end asset enqueue.
 */
function sbvi_assets() {
	wp_enqueue_style( 'sbvi-fonts', 'https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,wght@0,400;0,600;1,400;1,600&display=swap', array(), null );
	wp_enqueue_style( 'sbvi-style', SBVI_URI . '/assets/css/style.css', array(), sbvi_asset_version( 'assets/css/style.css' ) );
	wp_enqueue_script( 'sbvi-main', SBVI_URI . '/assets/js/main.js', array(), sbvi_asset_version( 'assets/js/main.js' ), true );

	if ( is_singular( 'service' ) ) {
		wp_enqueue_script( 'sbvi-main' );
	}
}
add_action( 'wp_enqueue_scripts', 'sbvi_assets' );

/**
 * Sensible content-width default (mostly relevant to oEmbeds).
 */
$GLOBALS['content_width'] = 1440;

/**
 * Trim the admin bar / login CSS noise is intentionally left to defaults —
 * nothing custom needed there.
 */

/**
 * Widen the search-engine-friendly excerpt length isn't needed; excerpts
 * here are short "dek" lines authors write deliberately, so we do not run
 * them through wp_trim_excerpt's automatic word-count truncation logic.
 */
