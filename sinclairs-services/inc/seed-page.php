<?php
/**
 * Find (or create) the Our Services page, and report whether it actually
 * calls the shortcode.
 *
 * Deliberately conservative about existing pages. The live Our Services
 * page is Elementor-built: its shortcode lives inside `_elementor_data`,
 * not in `post_content`, and Elementor replaces `post_content` wholesale
 * when it renders. Writing to `post_content` there would be invisible at
 * best and destructive at worst, so this NEVER edits a page it did not
 * create. When the shortcode is missing it says so in an admin notice and
 * explains what to change, rather than guessing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const SSVC_PAGE_SLUG = 'our-services';

/**
 * The Services page, or null.
 *
 * Looked up by slug first, then by title, so a site that renamed the page
 * still resolves.
 */
function ssvc_find_services_page() {
	// Backed by a global rather than a static so ssvc_reset_page_cache()
	// can actually clear it after the page is created mid-request.
	if ( array_key_exists( 'ssvc_page_cache', $GLOBALS ) ) {
		return $GLOBALS['ssvc_page_cache'];
	}

	$page = get_page_by_path( SSVC_PAGE_SLUG );

	if ( ! $page ) {
		$found = get_posts( array(
			'post_type'        => 'page',
			'post_status'      => array( 'publish', 'draft', 'private' ),
			'posts_per_page'   => 1,
			'title'            => 'Our Services',
			'no_found_rows'    => true,
			'suppress_filters' => false,
		) );

		$page = $found ? $found[0] : null;
	}

	$GLOBALS['ssvc_page_cache'] = $page;

	return $page;
}

/**
 * URL of the Services page — the real permalink where one exists, so the
 * seeded menu anchors stay correct even if the slug differs.
 */
function ssvc_services_page_url() {
	$page = ssvc_find_services_page();
	$url  = $page ? trailingslashit( get_permalink( $page ) ) : home_url( '/' . SSVC_PAGE_SLUG . '/' );

	return apply_filters( 'sinclairs_services_page_url', $url );
}

/**
 * Where (if anywhere) the shortcode is on the page.
 *
 * @return string 'content' | 'elementor' | 'missing' | 'no-page'
 */
function ssvc_page_shortcode_status() {
	$page = ssvc_find_services_page();

	if ( ! $page ) {
		return 'no-page';
	}

	if ( has_shortcode( (string) $page->post_content, 'sinclairs_services' ) ) {
		return 'content';
	}

	// Elementor stores the widget tree as JSON in post meta; the shortcode
	// sits in a widget's settings rather than in post_content.
	$elementor = get_post_meta( $page->ID, '_elementor_data', true );

	if ( is_string( $elementor ) && false !== strpos( $elementor, 'sinclairs_services' ) ) {
		return 'elementor';
	}

	return 'missing';
}

function ssvc_page_is_elementor_built( $page_id ) {
	return 'builder' === get_post_meta( $page_id, '_elementor_edit_mode', true );
}

/**
 * Create the page only when there isn't one. Returns the page ID, or 0 if
 * creation failed.
 */
function ssvc_seed_page() {
	$page = ssvc_find_services_page();

	if ( $page ) {
		return (int) $page->ID;
	}

	$id = wp_insert_post( array(
		'post_title'   => 'Our Services',
		'post_name'    => SSVC_PAGE_SLUG,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => '[sinclairs_services]',
	) );

	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}

	// Reset the lookup cache so the menu seeder, which runs next, links to
	// the page that was just created rather than the fallback path.
	ssvc_reset_page_cache();

	return (int) $id;
}

/**
 * Clear the per-request lookup cache after creating the page, so callers
 * later in the same request see it.
 */
function ssvc_reset_page_cache() {
	unset( $GLOBALS['ssvc_page_cache'] );
}

/**
 * Tell the admin what, if anything, still needs doing.
 */
function ssvc_page_admin_notice() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}

	$status = ssvc_page_shortcode_status();

	if ( 'content' === $status || 'elementor' === $status ) {
		return;
	}

	$page = ssvc_find_services_page();

	echo '<div class="notice notice-warning"><p><strong>';
	esc_html_e( 'Sinclairs Services', 'sinclairs-services' );
	echo '</strong><br>';

	if ( 'no-page' === $status ) {
		esc_html_e( 'No Our Services page was found, so the shortcode has nowhere to render. Create a page and add [sinclairs_services] to it.', 'sinclairs-services' );
	} elseif ( $page && ssvc_page_is_elementor_built( $page->ID ) ) {
		esc_html_e( 'The Our Services page is built with Elementor and does not yet call [sinclairs_services]. Edit the page in Elementor and set its Shortcode widget to [sinclairs_services] — this plugin will not edit an Elementor layout automatically.', 'sinclairs-services' );
		echo ' <a href="' . esc_url( get_edit_post_link( $page->ID ) ) . '">' . esc_html__( 'Edit the page', 'sinclairs-services' ) . '</a>';
	} else {
		esc_html_e( 'The Our Services page does not yet call [sinclairs_services]. Add the shortcode to the page content.', 'sinclairs-services' );
		if ( $page ) {
			echo ' <a href="' . esc_url( get_edit_post_link( $page->ID ) ) . '">' . esc_html__( 'Edit the page', 'sinclairs-services' ) . '</a>';
		}
	}

	echo '</p></div>';
}
add_action( 'admin_notices', 'ssvc_page_admin_notice' );
