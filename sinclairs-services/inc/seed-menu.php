<?php
/**
 * Seed real WordPress menu items for the service anchors.
 *
 * inc/nav.php can inject a dropdown at render time without touching the
 * menu, but that panel is invisible in Appearance → Menus, so the client
 * can't reorder or rename anything. Seeding creates genuine child menu
 * items instead — custom links to /our-services/#anchor — which the theme
 * renders as its own native submenu and which the client can edit like
 * any other menu item.
 *
 * Seeding runs on activation and is idempotent: an item whose URL already
 * exists under the parent is skipped, so re-running never duplicates.
 * Once seeded, the injected dropdown switches itself off so the two can't
 * both appear.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const SSVC_SEEDED_OPTION = 'ssvc_menu_seeded';

/**
 * Every nav menu that contains an "Our Services" item, paired with that
 * item. A theme may register the same page in more than one menu (a
 * desktop and a mobile menu, say), so all of them are returned.
 *
 * @return array List of [ 'menu_id' => int, 'parent' => WP_Post ].
 */
function ssvc_find_services_menu_items() {
	$found = array();
	$menus = wp_get_nav_menus();

	if ( is_wp_error( $menus ) || ! $menus ) {
		return $found;
	}

	foreach ( $menus as $menu ) {
		$items = wp_get_nav_menu_items( $menu->term_id );

		if ( ! $items ) {
			continue;
		}

		foreach ( $items as $item ) {
			// Only top-level items can be the parent of the seeded set.
			if ( (int) $item->menu_item_parent !== 0 ) {
				continue;
			}

			if ( ssvc_is_services_menu_item( $item ) ) {
				$found[] = array(
					'menu_id' => (int) $menu->term_id,
					'parent'  => $item,
				);
				break;
			}
		}
	}

	return $found;
}

/**
 * The menu items already sitting under a given parent.
 */
function ssvc_existing_children( $menu_id, $parent_id ) {
	$children = array();
	$items    = wp_get_nav_menu_items( $menu_id );

	if ( ! $items ) {
		return $children;
	}

	foreach ( $items as $item ) {
		if ( (int) $item->menu_item_parent === (int) $parent_id ) {
			$children[] = $item;
		}
	}

	return $children;
}

/**
 * Create the child items. Safe to call repeatedly.
 *
 * @return int Number of menu items created plus repointed.
 */
function ssvc_seed_menu_items() {
	if ( ! function_exists( 'wp_update_nav_menu_item' ) ) {
		require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
	}

	$targets  = ssvc_find_services_menu_items();
	$sections = ssvc_section_index();
	// Dropdown items go to the detail page's anchors; the parent "Our
	// Services" item keeps pointing at the index page. If the detail page
	// isn't there, target the index instead — which is where the sections
	// render in that case — rather than seeding ten dead links.
	$base     = ssvc_detail_page_exists()
		? ssvc_detail_page_url()
		: ssvc_services_page_url();
	$created  = 0;
	$updated  = 0;

	foreach ( $targets as $target ) {
		$menu_id  = $target['menu_id'];
		$parent   = $target['parent'];
		$children = ssvc_existing_children( $menu_id, $parent->ID );
		$position = (int) $parent->menu_order;
		$have     = array();

		// Pass 1 — adopt and, where needed, repoint the items already
		// there. An item is "ours" if its fragment names a known section.
		// This is what migrates a menu seeded before the index/detail
		// split, whose children still point at the index page's anchors;
		// skipping them would leave the dropdown silently broken.
		foreach ( $children as $item ) {
			$fragment = wp_parse_url( (string) $item->url, PHP_URL_FRAGMENT );

			if ( ! $fragment || ! isset( $sections[ $fragment ] ) ) {
				continue;
			}

			$have[ $fragment ] = true;
			$wanted            = $base . '#' . $fragment;

			if ( untrailingslashit( (string) $item->url ) === untrailingslashit( $wanted ) ) {
				continue;
			}

			$result = wp_update_nav_menu_item( $menu_id, (int) $item->ID, array(
				'menu-item-title'     => $item->title,
				'menu-item-url'       => $wanted,
				'menu-item-status'    => 'publish',
				'menu-item-type'      => 'custom',
				'menu-item-parent-id' => $parent->ID,
				'menu-item-position'  => (int) $item->menu_order,
			) );

			if ( ! is_wp_error( $result ) && $result ) {
				$updated++;
			}
		}

		// Pass 2 — create whatever is still missing.
		foreach ( $sections as $id => $title ) {
			if ( isset( $have[ $id ] ) ) {
				continue;
			}

			$position++;

			$result = wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => $title,
				'menu-item-url'       => $base . '#' . $id,
				'menu-item-status'    => 'publish',
				'menu-item-type'      => 'custom',
				'menu-item-parent-id' => $parent->ID,
				'menu-item-position'  => $position,
			) );

			if ( ! is_wp_error( $result ) && $result ) {
				$created++;
			}
		}
	}

	if ( $created || $updated ) {
		update_option( SSVC_SEEDED_OPTION, 1 );
	}

	return $created + $updated;
}

/**
 * Activation hook target. Wrapped so a site with no menu yet doesn't
 * fatal — it simply seeds nothing, and the admin notice below offers to
 * seed once a menu exists.
 */
function ssvc_activate() {
	// Pages first: the menu anchors are built from the detail page's
	// permalink, and the detail page is created as a child of the index.
	ssvc_seed_page();
	ssvc_seed_detail_page();
	ssvc_seed_menu_items();
}

/**
 * Admin notice + one-click seeding, for the common case where the menu is
 * built (or the Services item added) after the plugin was activated.
 */
function ssvc_seed_admin_notice() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	if ( get_option( SSVC_SEEDED_OPTION ) ) {
		return;
	}

	if ( ! ssvc_find_services_menu_items() ) {
		return;
	}

	$url = wp_nonce_url(
		admin_url( 'admin-post.php?action=ssvc_seed_menu' ),
		'ssvc_seed_menu'
	);

	echo '<div class="notice notice-info"><p>';
	echo esc_html__( 'Sinclairs Services: the "Our Services" menu item can be given a submenu linking to each practice area.', 'sinclairs-services' );
	echo ' <a class="button button-primary" href="' . esc_url( $url ) . '">';
	echo esc_html__( 'Add the submenu items', 'sinclairs-services' );
	echo '</a></p></div>';
}
add_action( 'admin_notices', 'ssvc_seed_admin_notice' );

function ssvc_handle_seed_request() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to edit menus.', 'sinclairs-services' ) );
	}

	check_admin_referer( 'ssvc_seed_menu' );

	// Create the pages first, so the menu items can point at a real
	// detail page rather than falling back to the index.
	ssvc_seed_page();
	ssvc_seed_detail_page();

	$created = ssvc_seed_menu_items();

	// Record the choice even when nothing was created, so a site whose
	// items already exist stops being nagged by the notice.
	update_option( SSVC_SEEDED_OPTION, 1 );

	wp_safe_redirect( add_query_arg( 'ssvc_seeded', (int) $created, admin_url( 'nav-menus.php' ) ) );
	exit;
}
add_action( 'admin_post_ssvc_seed_menu', 'ssvc_handle_seed_request' );

function ssvc_seeded_result_notice() {
	if ( ! isset( $_GET['ssvc_seeded'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display of a redirect result.
		return;
	}

	$count = (int) $_GET['ssvc_seeded']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	echo '<div class="notice notice-success is-dismissible"><p>';
	if ( $count ) {
		printf(
			/* translators: %d: number of menu items added. */
			esc_html( _n( 'Sinclairs Services: added %d submenu item.', 'Sinclairs Services: added %d submenu items.', $count, 'sinclairs-services' ) ),
			$count
		);
	} else {
		esc_html_e( 'Sinclairs Services: the submenu items were already in place.', 'sinclairs-services' );
	}
	echo '</p></div>';
}
add_action( 'admin_notices', 'ssvc_seeded_result_notice' );
