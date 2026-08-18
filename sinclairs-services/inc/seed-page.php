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
 * The detail page is a child of the index page, so it reads as
 * /our-services/practice-areas/.
 */
const SSVC_DETAIL_SLUG = 'practice-areas';

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
 * The detail page (the ten sections), or null.
 */
function ssvc_find_detail_page() {
	if ( array_key_exists( 'ssvc_detail_cache', $GLOBALS ) ) {
		return $GLOBALS['ssvc_detail_cache'];
	}

	$page = get_page_by_path( SSVC_PAGE_SLUG . '/' . SSVC_DETAIL_SLUG );

	if ( ! $page ) {
		// Fall back to a top-level slug, in case the page was moved out
		// from under the index page.
		$page = get_page_by_path( SSVC_DETAIL_SLUG );
	}

	$GLOBALS['ssvc_detail_cache'] = $page;

	return $page;
}

/**
 * Clear the per-request lookup cache — used after creating the detail
 * page, and before re-checking it right after its status just changed.
 */
function ssvc_reset_detail_page_cache() {
	unset( $GLOBALS['ssvc_detail_cache'] );
}

/**
 * Is the detail page there AND publicly viewable?
 *
 * Callers must check this before linking to it — every public-facing
 * decision (index/list fallback, nav menu target) goes through here.
 * ssvc_detail_page_url() falls back to a guessed path when the page is
 * absent, which is fine for building a URL but must never be used to
 * emit live links — doing so produces a page full of 404s.
 *
 * "Exists" alone isn't enough: get_page_by_path() has no status filter,
 * so it happily returns a draft or private page. Without the publish
 * check here, a client who creates the practice-areas page as a draft
 * (to build it out before going live) would have public visitors sent
 * to a page they can't see — the exact same class of bug as the missing
 * page, just one step later. Deliberately NOT used by the "have we
 * already run setup" checks (ssvc_maybe_seed_pages(), ssvc_seed_detail_page())
 * — those use ssvc_find_detail_page() directly and must keep matching a
 * draft/private page too, or they would recreate it as a duplicate, or
 * re-flush rewrite rules on every admin page load while the draft sits
 * unpublished.
 */
function ssvc_detail_page_exists() {
	$page = ssvc_find_detail_page();

	return (bool) ( $page && 'publish' === $page->post_status );
}

/**
 * URL of the detail page — the target every index row, jump-box option
 * and nav dropdown item points at.
 */
function ssvc_detail_page_url() {
	$page = ssvc_find_detail_page();
	$url  = $page
		? trailingslashit( get_permalink( $page ) )
		: home_url( '/' . SSVC_PAGE_SLUG . '/' . SSVC_DETAIL_SLUG . '/' );

	return apply_filters( 'sinclairs_services_detail_page_url', $url );
}

/**
 * Create the detail page under the index page. Only ever creates; an
 * existing page is left alone, exactly as with the index page.
 */
function ssvc_seed_detail_page() {
	$page = ssvc_find_detail_page();

	if ( $page ) {
		return (int) $page->ID;
	}

	$parent = ssvc_find_services_page();

	$id = wp_insert_post( array(
		'post_title'   => 'Practice Areas',
		'post_name'    => SSVC_DETAIL_SLUG,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_parent'  => $parent ? (int) $parent->ID : 0,
		'post_content' => '[sinclairs_services_detail]',
	) );

	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}

	ssvc_reset_detail_page_cache();

	return (int) $id;
}

/**
 * Create the detail page automatically if it is missing.
 *
 * Activation is the obvious place for this, but activation only runs when
 * someone deactivates and reactivates — updating the plugin's files does
 * not trigger it. That left sites stuck with no detail page and the index
 * falling back to the single-page arrangement. Running on admin_init as
 * well means the split comes up on its own the next time wp-admin is
 * loaded, with no manual step.
 *
 * Cheap: after the first successful run ssvc_find_detail_page() short-
 * circuits on a single get_page_by_path() (status-agnostic — see the
 * comment on the check itself for why). A failure is recorded so a site
 * that genuinely can't create the page doesn't retry on every request.
 */
function ssvc_maybe_seed_pages() {
	if ( wp_doing_ajax() || ( function_exists( 'wp_installing' ) && wp_installing() ) ) {
		return;
	}

	if ( get_option( 'ssvc_autocreate_failed' ) ) {
		return;
	}

	// Nothing to attach the child page to yet.
	if ( ! ssvc_find_services_page() ) {
		return;
	}

	// Status-agnostic on purpose: this is "have we already done the
	// one-time creation," not "is it safe to link the public to it." A
	// draft or private page at this path must still count as already
	// created, or every admin page load would call ssvc_seed_detail_page()
	// (harmless — it finds the existing page) followed by
	// flush_rewrite_rules() (not harmless — expensive, and not meant to
	// run on every request) for as long as the page stays unpublished.
	if ( ssvc_find_detail_page() ) {
		return;
	}

	$id = ssvc_seed_detail_page();

	if ( ! $id ) {
		update_option( 'ssvc_autocreate_failed', 1 );
		return;
	}

	// Point the menu at the page that now exists, and flush rewrites so
	// /our-services/practice-areas/ resolves without a manual
	// Settings → Permalinks → Save.
	ssvc_seed_menu_items();
	flush_rewrite_rules( false );
}
add_action( 'admin_init', 'ssvc_maybe_seed_pages' );

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

	// The detail page not being publicly viewable is the more urgent
	// problem: the index will still render, but its rows have nowhere
	// public to go, so the plugin falls back to showing the sections
	// inline until this is resolved. Two distinct causes get two distinct
	// messages, because the fix for each is different — creating a page
	// that already exists as a draft would be a silent no-op, and telling
	// the admin to "publish it" when nothing exists yet would send them
	// looking for a page that isn't there.
	if ( ! ssvc_detail_page_exists() ) {
		$existing = ssvc_find_detail_page();

		echo '<div class="notice notice-warning"><p><strong>';
		esc_html_e( 'Sinclairs Services', 'sinclairs-services' );
		echo '</strong><br>';

		if ( $existing ) {
			esc_html_e( 'The practice-areas page exists but is not published, so the index is showing all ten sections inline instead of linking to them.', 'sinclairs-services' );
			echo ' <a class="button button-primary" href="' . esc_url( get_edit_post_link( $existing->ID ) ) . '">';
			esc_html_e( 'Edit the page', 'sinclairs-services' );
			echo '</a></p></div>';
		} else {
			$url = wp_nonce_url(
				admin_url( 'admin-post.php?action=ssvc_seed_menu' ),
				'ssvc_seed_menu'
			);
			esc_html_e( 'The practice-areas page does not exist, so the index is showing all ten sections inline instead of linking to them.', 'sinclairs-services' );
			echo ' <a class="button button-primary" href="' . esc_url( $url ) . '">';
			esc_html_e( 'Create it now', 'sinclairs-services' );
			echo '</a></p></div>';
		}
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
