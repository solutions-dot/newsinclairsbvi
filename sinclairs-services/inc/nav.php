<?php
/**
 * "Our Services" nav dropdown.
 *
 * A plain single-column list of the practice areas, each anchor-linking
 * to its section on the Services page — matching the reference the client
 * supplied (names only, no blurbs, no numerals, thin accent rule across
 * the top of the panel).
 *
 * This attaches to whichever WordPress menu item points at the Services
 * page, so it works with the site's existing "primary-menu" without any
 * theme edit. Hover/focus opening is CSS; frontend.js adds tap toggling.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Master switch for the *injected* dropdown.
 *
 * Once real submenu items have been seeded into the menu (see
 * inc/seed-menu.php) the theme renders its own submenu, so injecting a
 * second panel would show the list twice. Seeding therefore switches this
 * off automatically. Still filterable either way.
 */
function ssvc_nav_dropdown_enabled() {
	$enabled = ! get_option( SSVC_SEEDED_OPTION );

	return (bool) apply_filters( 'sinclairs_services_nav_dropdown', $enabled );
}

/**
 * ssvc_services_page_url() lives in inc/seed-page.php, alongside the page
 * lookup it depends on.
 */

/**
 * Is this menu item the "Our Services" entry?
 *
 * Matched on the URL path first (rename-proof), then on the title as a
 * fallback for menus built with a custom link.
 */
function ssvc_is_services_menu_item( $item ) {
	$target = untrailingslashit( wp_parse_url( ssvc_services_page_url(), PHP_URL_PATH ) );
	$url    = isset( $item->url ) ? untrailingslashit( wp_parse_url( $item->url, PHP_URL_PATH ) ) : '';

	if ( $target && $url && $target === $url ) {
		return true;
	}

	$title = isset( $item->title ) ? strtolower( wp_strip_all_tags( $item->title ) ) : '';

	return in_array( $title, array( 'our services', 'services' ), true );
}

/**
 * The dropdown panel itself.
 */
function ssvc_nav_dropdown_markup() {
	$sections = ssvc_section_index();

	if ( ! $sections ) {
		return '';
	}

	$base = ssvc_services_page_url();

	$out = '<ul class="sc-nav-dropdown">';

	foreach ( $sections as $id => $title ) {
		$out .= '<li><a href="' . esc_url( $base . '#' . $id ) . '">' . esc_html( $title ) . '</a></li>';
	}

	$out .= '</ul>';

	return $out;
}

/**
 * Mark the parent <li> so CSS can position the panel against it.
 */
function ssvc_nav_menu_css_class( $classes, $item, $args, $depth ) {
	if ( 0 !== (int) $depth ) {
		return $classes;
	}

	// NOT gated on ssvc_nav_dropdown_enabled(). That flag is false once
	// menu items have been seeded — which is precisely when this class is
	// needed, because it is what styles the theme's own seeded submenu.
	// Gating it here meant the hook never fired in the seeded case and the
	// submenu fell back to the theme's default hover colour.
	if ( ssvc_is_services_menu_item( $item ) ) {
		$classes[] = 'sc-nav-parent';
	}

	return $classes;
}
add_filter( 'nav_menu_css_class', 'ssvc_nav_menu_css_class', 10, 4 );

/**
 * Append the panel (and the caret button) directly after the menu item's
 * own <a>, so it lands inside the <li> that carries .sc-nav-parent.
 *
 * walker_nav_menu_start_el is the right hook for this: it returns just
 * the item's markup, before the walker appends any real children and
 * closes the </li>.
 */
function ssvc_nav_menu_start_el( $item_output, $item, $depth, $args ) {
	if ( 0 !== (int) $depth || ! ssvc_is_services_menu_item( $item ) ) {
		return $item_output;
	}

	// The caret is rendered whether or not the injected panel is — the
	// seeded submenu needs it too, because on mobile that submenu starts
	// collapsed and the parent link navigates rather than toggling.
	//
	// The link and the caret are wrapped in their own .sc-nav-row rather
	// than positioned against .sc-nav-parent (the <li>) directly: on
	// mobile the seeded submenu is a sibling inside that same <li>, and
	// once it opens the <li> grows to the height of all ten rows. A
	// toggle centred with top: 50% against the <li> would then centre
	// against that whole expanded height and land in the middle of the
	// submenu instead of beside the "Our Services" text. .sc-nav-row
	// sizes to the link alone, so the toggle's position never depends on
	// whether the submenu below it happens to be open.
	$row = '<span class="sc-nav-row">'
		. $item_output
		. '<button type="button" class="sc-nav-toggle" aria-expanded="false" aria-label="'
		. esc_attr__( 'Toggle the Our Services menu', 'sinclairs-services' )
		. '"><span aria-hidden="true"></span></button>'
		. '</span>';

	if ( ssvc_nav_dropdown_enabled() ) {
		$row .= ssvc_nav_dropdown_markup();
	}

	return $row;
}
add_filter( 'walker_nav_menu_start_el', 'ssvc_nav_menu_start_el', 10, 4 );
