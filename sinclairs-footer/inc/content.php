<?php
/**
 * What the footer says, and where its links point.
 *
 * Page URLs are resolved rather than hard-coded: each entry lists the
 * slugs that page has plausibly been given, and the first one that
 * actually exists wins. The Services page has been at both
 * /our-services/ and /our-services2/ on this site, so a hard-coded path
 * is a 404 waiting to happen.
 *
 * Everything here runs through a filter, so the wording, the links and
 * the contact details can all be changed from a child theme without
 * editing the plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * First page that exists among $slugs, else the home URL with the first
 * slug appended so the link at least points somewhere sensible.
 */
function sbvif_page_url( array $slugs ) {
	foreach ( $slugs as $slug ) {
		$page = get_page_by_path( $slug );

		if ( $page ) {
			return trailingslashit( get_permalink( $page ) );
		}
	}

	return home_url( '/' . trim( $slugs[0], '/' ) . '/' );
}

function sbvif_links() {
	$links = array(
		array(
			'label' => __( 'Home', 'sinclairs-footer' ),
			'url'   => home_url( '/' ),
		),
		array(
			'label' => __( 'About', 'sinclairs-footer' ),
			'url'   => sbvif_page_url( array( 'about', 'about-us' ) ),
		),
		array(
			'label' => __( 'Our Services', 'sinclairs-footer' ),
			// The practice-areas page is where the services actually
			// are; get_page_by_path() takes the nested path, so this
			// resolves the real page rather than assuming the slug.
			'url'   => sbvif_page_url( array( 'our-services2/practice-areas', 'our-services/practice-areas', 'our-services2', 'our-services' ) ),
		),
		array(
			'label' => __( 'Legal Insights', 'sinclairs-footer' ),
			'url'   => sbvif_page_url( array( 'legal-insights', 'insights', 'articles' ) ),
		),
		array(
			'label' => __( 'Contact Us', 'sinclairs-footer' ),
			'url'   => sbvif_page_url( array( 'contact-us', 'contact' ) ),
		),
	);

	return apply_filters( 'sinclairs_footer_links', $links );
}

/**
 * Opening hours. 'days' and 'time' are separate so they can sit on two
 * lines beside one clock icon rather than reading as one long string.
 */
function sbvif_hours() {
	$hours = array(
		array(
			'days' => __( 'Monday – Friday', 'sinclairs-footer' ),
			'time' => __( '8:30 AM – 5:00 PM', 'sinclairs-footer' ),
		),
	);

	return apply_filters( 'sinclairs_footer_hours', $hours );
}

/**
 * Contact rows. 'href' is what makes each one actionable — a phone
 * number that dials on a mobile, an address that opens a map — which is
 * most of the point of a footer on a phone.
 */
function sbvif_contact() {
	$contact = array(
		array(
			'icon'  => 'phone',
			'text'  => '1 (284) 545-2454',
			'href'  => 'tel:+12845452454',
			'label' => __( 'Telephone', 'sinclairs-footer' ),
		),
		array(
			'icon'  => 'phone',
			'text'  => '1 (284) 542-2453',
			'href'  => 'tel:+12845422453',
			'label' => __( 'Telephone', 'sinclairs-footer' ),
		),
		array(
			'icon'  => 'mail',
			'text'  => 'Info@sinclairsbvi.com',
			'href'  => 'mailto:Info@sinclairsbvi.com',
			'label' => __( 'Email', 'sinclairs-footer' ),
		),
		array(
			'icon'  => 'pin',
			'text'  => '2nd Floor, Suite 20, Mill Mall Road Town, Tortola, VG1110 British Virgin Islands',
			'href'  => 'https://maps.google.com/?q=' . rawurlencode( '2nd Floor, Suite 20, Mill Mall, Road Town, Tortola, VG1110, British Virgin Islands' ),
			'label' => __( 'Address', 'sinclairs-footer' ),
		),
	);

	return apply_filters( 'sinclairs_footer_contact', $contact );
}
