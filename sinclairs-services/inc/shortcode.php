<?php
/**
 * Shortcode renderers.
 *
 *   [sinclairs_services]            whole page
 *   [sinclairs_services_nutshell]   In a Nutshell index on its own
 *   [sinclairs_services_menu]       jump-to dropdown on its own
 *
 * Layout: each section puts the short "in a nutshell" wording in a sticky
 * side rail beside the detail prose, with the Q&As collapsed under one
 * "Information & FAQs" heading. Passing layout="accordion" instead folds
 * the detail prose into that accordion as a leading "Overview" panel, so
 * the two arrangements can be compared on the live page:
 *
 *   [sinclairs_services layout="accordion"]
 *
 * Class names keep the `sc-` prefix used by the plugin already installed
 * on the site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Body rows are ['p'|'h3'|'lede', text]. 'lede' is the opening sentence
 * the client wrote as a standalone hook in several documents — rendered
 * larger, but still a paragraph.
 */
function ssvc_render_body( array $body ) {
	$out = '';

	foreach ( $body as $row ) {
		list( $type, $text ) = $row;

		switch ( $type ) {
			case 'h3':
				$out .= '<h3 class="sc-section__subhead">' . esc_html( $text ) . '</h3>';
				break;
			case 'lede':
				$out .= '<p class="sc-section__lede">' . esc_html( $text ) . '</p>';
				break;
			default:
				$out .= '<p>' . esc_html( $text ) . '</p>';
		}
	}

	return $out;
}

/**
 * One "Information & FAQs" accordion. $overview, when passed, becomes the
 * first panel — that's the layout="accordion" variant.
 */
function ssvc_render_faqs( array $faqs, $section_id, $overview = '' ) {
	if ( ! $faqs && ! $overview ) {
		return '';
	}

	$out  = '<div class="sc-faqs">';
	$out .= '<h3 class="sc-faqs__heading">' . esc_html__( 'Information & FAQs', 'sinclairs-services' ) . '</h3>';
	$out .= '<div class="sc-faqs__list">';

	if ( $overview ) {
		$out .= '<details class="sc-faq sc-faq--overview">';
		$out .= '<summary><span>' . esc_html__( 'Overview', 'sinclairs-services' ) . '</span><span class="sc-faq__icon" aria-hidden="true"></span></summary>';
		$out .= '<div class="sc-faq__answer">' . $overview . '</div>';
		$out .= '</details>';
	}

	foreach ( $faqs as $i => $faq ) {
		$answer = '';
		foreach ( (array) $faq['a'] as $para ) {
			$answer .= '<p>' . esc_html( $para ) . '</p>';
		}

		$out .= '<details class="sc-faq" id="' . esc_attr( $section_id . '-faq-' . ( $i + 1 ) ) . '">';
		$out .= '<summary><span>' . esc_html( $faq['q'] ) . '</span><span class="sc-faq__icon" aria-hidden="true"></span></summary>';
		$out .= '<div class="sc-faq__answer">' . $answer . '</div>';
		$out .= '</details>';
	}

	$out .= '</div>';
	$out .= '<p class="sc-faqs__disclaimer">' . esc_html__( 'These answers are general information only and do not constitute legal advice.', 'sinclairs-services' ) . '</p>';
	$out .= '</div>';

	return $out;
}

/**
 * The In a Nutshell index — every service visible at once with its short
 * wording, each row jumping to the matching section.
 */
function ssvc_nutshell_markup() {
	$out = '<div class="sc-nutshell" id="sc-nutshell">';

	foreach ( ssvc_nutshell_groups() as $group ) {
		$out .= '<section class="sc-nutshell__group">';
		$out .= '<h3 class="sc-nutshell__group-label">' . esc_html( $group['label'] ) . '</h3>';
		$out .= '<ul class="sc-nutshell__rows">';

		foreach ( $group['rows'] as $row ) {
			$has_link = ! empty( $row['target'] );
			$tag      = $has_link ? 'a' : 'span';
			$attrs    = $has_link ? ' href="#' . esc_attr( $row['target'] ) . '"' : '';
			$classes  = 'sc-nutshell__row' . ( $has_link ? '' : ' is-unlinked' );

			$out .= '<li>';
			$out .= '<' . $tag . ' class="' . esc_attr( $classes ) . '"' . $attrs . '>';
			$out .= '<span class="sc-nutshell__label">' . esc_html( $row['label'] ) . '</span>';
			$out .= '<span class="sc-nutshell__text">' . esc_html( $row['text'] ) . '</span>';
			if ( $has_link ) {
				$out .= '<span class="sc-nutshell__arrow" aria-hidden="true">&rarr;</span>';
			}
			$out .= '</' . $tag . '>';
			$out .= '</li>';
		}

		$out .= '</ul></section>';
	}

	$out .= '</div>';

	return $out;
}

/**
 * The jump-to box. A real <select> so it uses the native picker on
 * mobile; frontend.js scrolls on change. Without JS the "Go" button is
 * hidden by the script's absence and the select still lists everything,
 * so nothing is lost — the nutshell index above it links to the same
 * anchors.
 */
function ssvc_menu_markup() {
	$sections = ssvc_section_index();

	if ( ! $sections ) {
		return '';
	}

	$out  = '<div class="sc-jump" data-sc-jump>';
	$out .= '<label class="sc-jump__label" for="sc-jump-select">' . esc_html__( 'Jump to a service', 'sinclairs-services' ) . '</label>';
	$out .= '<div class="sc-jump__control">';
	$out .= '<select class="sc-jump__select" id="sc-jump-select">';
	$out .= '<option value="">' . esc_html__( 'Select a service…', 'sinclairs-services' ) . '</option>';

	foreach ( $sections as $id => $title ) {
		$out .= '<option value="#' . esc_attr( $id ) . '">' . esc_html( $title ) . '</option>';
	}

	$out .= '</select>';
	$out .= '<span class="sc-jump__caret" aria-hidden="true"></span>';
	$out .= '<button type="button" class="sc-jump__go">' . esc_html__( 'Go', 'sinclairs-services' ) . '</button>';
	$out .= '</div></div>';

	return $out;
}

/**
 * [sinclairs_services]
 */
function ssvc_shortcode_services( $atts ) {
	$atts = shortcode_atts( array(
		'layout'   => 'rail',   // 'rail' (default) or 'accordion'
		'intro'    => 'yes',
		'nutshell' => 'yes',
		'jump'     => 'yes',
	), $atts, 'sinclairs_services' );

	ssvc_enqueue_assets();

	$accordion = ( 'accordion' === $atts['layout'] );
	$intro     = ssvc_page_intro();

	$out = '<div class="sc-services sc-services--' . esc_attr( $accordion ? 'accordion' : 'rail' ) . '">';

	if ( 'yes' === $atts['intro'] ) {
		$out .= '<header class="sc-intro">';
		$out .= '<p class="sc-intro__kicker">' . esc_html( $intro['kicker'] ) . '</p>';
		$out .= '<h2 class="sc-intro__title">' . esc_html( $intro['title'] ) . '</h2>';
		$out .= '<p class="sc-intro__dek">' . esc_html( $intro['dek'] ) . '</p>';
		$out .= '</header>';
	}

	if ( 'yes' === $atts['jump'] ) {
		$out .= ssvc_menu_markup();
	}

	if ( 'yes' === $atts['nutshell'] ) {
		$out .= ssvc_nutshell_markup();
	}

	$out .= '<div class="sc-sections">';

	foreach ( ssvc_sections() as $i => $section ) {
		$number = str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT );
		$body   = ssvc_render_body( $section['body'] );
		$icon   = ssvc_icon( $section['id'] );

		$out .= '<section class="sc-section" id="' . esc_attr( $section['id'] ) . '">';

		$out .= '<div class="sc-section__rail">';
		if ( $icon ) {
			// The SVG is plugin-authored markup from inc/icons.php, not user
			// input, so it is emitted as-is rather than escaped.
			$out .= '<span class="sc-section__icon">' . $icon . '</span>';
		}
		$out .= '<p class="sc-section__num">' . esc_html( $number ) . '</p>';
		$out .= '<h2 class="sc-section__title">' . esc_html( $section['title'] ) . '</h2>';
		if ( ! empty( $section['brief'] ) ) {
			$out .= '<div class="sc-section__rule" aria-hidden="true"></div>';
			$out .= '<p class="sc-section__brief">' . esc_html( $section['brief'] ) . '</p>';
		}
		$out .= '<a class="sc-section__top" href="#sc-nutshell">' . esc_html__( 'All services', 'sinclairs-services' ) . '</a>';
		$out .= '</div>';

		$out .= '<div class="sc-section__main">';
		if ( $accordion ) {
			$out .= ssvc_render_faqs( $section['faqs'], $section['id'], $body );
		} else {
			$out .= '<div class="sc-section__prose">' . $body . '</div>';
			$out .= ssvc_render_faqs( $section['faqs'], $section['id'] );
		}
		$out .= '</div>';

		$out .= '</section>';
	}

	$out .= '</div></div>';

	return $out;
}
add_shortcode( 'sinclairs_services', 'ssvc_shortcode_services' );

/**
 * [sinclairs_services_nutshell]
 */
function ssvc_shortcode_nutshell() {
	ssvc_enqueue_assets();
	return '<div class="sc-services">' . ssvc_nutshell_markup() . '</div>';
}
add_shortcode( 'sinclairs_services_nutshell', 'ssvc_shortcode_nutshell' );

/**
 * [sinclairs_services_menu]
 */
function ssvc_shortcode_menu() {
	ssvc_enqueue_assets();
	return '<div class="sc-services">' . ssvc_menu_markup() . '</div>';
}
add_shortcode( 'sinclairs_services_menu', 'ssvc_shortcode_menu' );
