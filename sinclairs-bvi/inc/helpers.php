<?php
/**
 * Shared template + data helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The three Services-hub groups. Single source of truth: used both by the
 * admin "nutshell item" repeater (group picker) and the hub template loop.
 */
function sbvi_nutshell_groups() {
	return array(
		'corporate' => __( 'Corporate & Commercial Law', 'sinclairs-bvi' ),
		'funds'     => __( 'Investment Funds, Approved Managers & Investment Business', 'sinclairs-bvi' ),
		'private'   => __( 'Private Client Law', 'sinclairs-bvi' ),
	);
}

/**
 * Find the front-end URL of the Page currently assigned a given template
 * file, so header/footer/home links stay correct even if a client renames
 * a page's slug. Falls back to a guessed path if no page has claimed the
 * template yet (e.g. right after theme activation, before seeding runs).
 */
function sbvi_page_url_by_template( $template_file, $fallback_path = '/' ) {
	static $cache = array();

	if ( isset( $cache[ $template_file ] ) ) {
		return $cache[ $template_file ];
	}

	$pages = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'meta_key'       => '_wp_page_template',
		'meta_value'     => $template_file,
		'no_found_rows'  => true,
	) );

	$url = $pages ? get_permalink( $pages[0] ) : home_url( $fallback_path );

	$cache[ $template_file ] = $url;

	return $url;
}

function sbvi_home_url() {
	return home_url( '/' );
}

function sbvi_about_url() {
	return sbvi_page_url_by_template( 'page-about.php', '/about/' );
}

function sbvi_services_url() {
	return sbvi_page_url_by_template( 'page-services.php', '/services/' );
}

function sbvi_articles_url() {
	return sbvi_page_url_by_template( 'page-articles.php', '/articles/' );
}

function sbvi_contact_url() {
	return sbvi_page_url_by_template( 'page-contact.php', '/contact/' );
}

/**
 * All published Service posts, ordered the way the client arranges them in
 * wp-admin (drag-and-drop "Order" / Page Attributes box). Everything that
 * lists practice areas — mega-menu, home hover list, footer, services hub,
 * next-practice-area links — reads from this one query.
 */
function sbvi_get_services() {
	static $services = null;

	if ( null !== $services ) {
		return $services;
	}

	$query = new WP_Query( array(
		'post_type'      => 'service',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	) );

	$services = $query->posts;

	return $services;
}

/**
 * 1-based, zero-padded position of a service among its published siblings
 * — drives the "01" / "02" numerals and the "Practice area 0N" kicker.
 */
function sbvi_service_number( $service_id, $pad = 2 ) {
	$services = sbvi_get_services();
	foreach ( $services as $index => $service ) {
		if ( (int) $service->ID === (int) $service_id ) {
			return str_pad( (string) ( $index + 1 ), $pad, '0', STR_PAD_LEFT );
		}
	}
	return str_pad( '1', $pad, '0', STR_PAD_LEFT );
}

/**
 * The next service in display order, wrapping around to the first — powers
 * the "Next practice area" footer link on single service pages.
 */
function sbvi_next_service( $service_id ) {
	$services = sbvi_get_services();
	$count    = count( $services );
	if ( ! $count ) {
		return null;
	}
	foreach ( $services as $index => $service ) {
		if ( (int) $service->ID === (int) $service_id ) {
			return $services[ ( $index + 1 ) % $count ];
		}
	}
	return $services[0];
}

/**
 * FAQ heading text for a given service: per-service override if set,
 * otherwise the site-wide Customizer default.
 */
function sbvi_faq_heading( $service_id ) {
	$override = get_post_meta( $service_id, '_sbvi_faq_heading', true );

	if ( ! in_array( $override, array( 'merged', 'separate' ), true ) ) {
		$override = get_theme_mod( 'sbvi_faq_heading_default', 'merged' );
	}

	return 'separate' === $override
		? __( 'Frequently Asked Questions', 'sinclairs-bvi' )
		: __( 'Information & FAQs', 'sinclairs-bvi' );
}

function sbvi_faq_items( $service_id ) {
	return SBVI_Repeater_Field::get_rows( $service_id, '_sbvi_faqs' );
}

function sbvi_nutshell_items( $service_id ) {
	return SBVI_Repeater_Field::get_rows( $service_id, '_sbvi_nutshell_items' );
}

/**
 * Attachment ID of the site-wide fallback photo (seeded on activation, or
 * chosen by the client in Customizer > Theme Options). Used anywhere a
 * dedicated image hasn't been uploaded yet, so the site never shows a
 * broken image while photography is still being gathered.
 */
function sbvi_fallback_image_id() {
	$id = get_theme_mod( 'sbvi_default_banner_image' );
	return $id ? (int) $id : 0;
}

/**
 * Render an <img> for a given attachment, falling back to the site default
 * photo, and finally to a plain tone block if literally nothing is set.
 */
function sbvi_image( $attachment_id, $size, $alt, $class = '', $eager = false ) {
	$attachment_id = $attachment_id ? (int) $attachment_id : sbvi_fallback_image_id();

	if ( $attachment_id && wp_attachment_is_image( $attachment_id ) ) {
		$attrs = array(
			'alt'   => esc_attr( $alt ),
			'class' => esc_attr( $class ),
		);
		// Above-the-fold images (hero, banners, the practice-area preview
		// photo) should never carry loading="lazy" — deferring them hurts
		// LCP since they're visible without scrolling.
		if ( $eager ) {
			$attrs['loading']  = false;
			$attrs['fetchpriority'] = 'high';
		}
		echo wp_get_attachment_image( $attachment_id, $size, false, $attrs );
		return;
	}

	echo '<span class="sbvi-image-placeholder ' . esc_attr( $class ) . '" role="img" aria-label="' . esc_attr( $alt ) . '"></span>';
}

function sbvi_image_url( $attachment_id, $size ) {
	$attachment_id = $attachment_id ? (int) $attachment_id : sbvi_fallback_image_id();
	if ( ! $attachment_id ) {
		return '';
	}
	$src = wp_get_attachment_image_src( $attachment_id, $size );
	return $src ? $src[0] : '';
}

/**
 * Secondary image slot (Home: articles-teaser photo; About: sticky
 * portrait) — one reusable meta field, label changes per template in the
 * admin UI. See inc/page-meta-boxes.php.
 */
function sbvi_secondary_image_id( $post_id ) {
	$id = get_post_meta( $post_id, '_sbvi_secondary_image', true );
	return $id ? (int) $id : 0;
}

/**
 * "Choose a practice area" photo (Home only), set via the admin's
 * "Practice Area Photo" meta box. Returns 0 when unset — callers fall
 * back to the first practice area's featured image. See inc/page-meta-boxes.php.
 */
function sbvi_practice_image_id( $post_id ) {
	$id = get_post_meta( $post_id, '_sbvi_practice_image', true );
	return $id ? (int) $id : 0;
}

/**
 * Site-wide contact details, editable from Customizer > Theme Options.
 * Reused on the Contact page and in the footer.
 */
function sbvi_contact_info() {
	return array(
		'address_lines' => get_theme_mod( 'sbvi_address', "Mill Mall, 2nd Floor, Unit 20\nRoad Town, Tortola VG1110\nBritish Virgin Islands" ),
		'phone_1'       => get_theme_mod( 'sbvi_phone_1', '+1 (284) 542 2453' ),
		'phone_2'       => get_theme_mod( 'sbvi_phone_2', '+1 (284) 545 2454' ),
		'email'         => get_theme_mod( 'sbvi_email', 'bvi@sinclairsoffshore.com' ),
	);
}

function sbvi_tel_href( $display_number ) {
	return 'tel:' . preg_replace( '/[^\d+]/', '', $display_number );
}

/**
 * Split a "Firm · Role · Location" style kicker string into its pieces for
 * separate styling, without hard-coding the copy itself.
 */
function sbvi_split_kicker( $text ) {
	$parts = array_filter( array_map( 'trim', explode( '·', $text ) ) );
	return $parts ? $parts : array( $text );
}
