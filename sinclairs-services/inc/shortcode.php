<?php
/**
 * Shortcode renderers.
 *
 * The page split
 * --------------
 * The index and the detail sections live on two pages:
 *
 *   /our-services/                  [sinclairs_services_index]
 *       jump box + In a Nutshell index. Where "Our Services" in the nav
 *       lands. Every row links across to the detail page's anchor.
 *
 *   /our-services/practice-areas/   [sinclairs_services_detail]
 *       the ten sections with their anchors and Information & FAQs.
 *       Where the nav dropdown items land.
 *
 *   [sinclairs_services]            everything on one page — kept for
 *       backwards compatibility and for a single-page arrangement.
 *
 * Anchor bases
 * ------------
 * Anything that links to a section takes a $base. It's '' when the links
 * and the sections are on the same page (plain in-page fragments), and
 * the detail page's URL when they are not. Getting this wrong is how you
 * end up with "#trade-marks" resolving against a page that has no such
 * section, so it is threaded through explicitly rather than guessed.
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
function ssvc_nutshell_markup( $base = '' ) {
	$out = '<div class="sc-nutshell" id="sc-nutshell">';

	foreach ( ssvc_nutshell_groups() as $group ) {
		$out .= '<section class="sc-nutshell__group">';
		$out .= '<h3 class="sc-nutshell__group-label">' . esc_html( $group['label'] ) . '</h3>';
		$out .= '<ul class="sc-nutshell__rows">';

		foreach ( $group['rows'] as $row ) {
			$has_link = ! empty( $row['target'] );
			$tag      = $has_link ? 'a' : 'span';
			$attrs    = $has_link ? ' href="' . esc_url( $base . '#' . $row['target'] ) . '"' : '';
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
 * mobile. frontend.js scrolls for a "#fragment" value and navigates for
 * a full URL, so the same control works on both pages.
 */
function ssvc_menu_markup( $base = '' ) {
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
		$out .= '<option value="' . esc_attr( $base . '#' . $id ) . '">' . esc_html( $title ) . '</option>';
	}

	$out .= '</select>';
	$out .= '<span class="sc-jump__caret" aria-hidden="true"></span>';
	$out .= '<button type="button" class="sc-jump__go">' . esc_html__( 'Go', 'sinclairs-services' ) . '</button>';
	$out .= '</div></div>';

	return $out;
}

/**
 * The ten detail sections.
 *
 * $back_url is where the "All services" link in each rail points — the
 * index page when the sections have a page of their own, or the in-page
 * "#sc-nutshell" fragment in the single-page arrangement. Empty hides
 * the link entirely, so it never points at something absent.
 */
function ssvc_sections_markup( $accordion = false, $back_url = '' ) {
	$out = '<div class="sc-sections">';

	foreach ( ssvc_sections() as $i => $section ) {
		$number = str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT );
		$body   = ssvc_render_body( $section['body'] );
		$icon   = ssvc_icon( $section['id'] );

		$out .= '<section class="sc-section" id="' . esc_attr( $section['id'] ) . '">';

		$out .= '<div class="sc-section__rail">';
		if ( $icon ) {
			// Plugin-authored markup from inc/icons.php, not user input, so
			// it is emitted as-is rather than escaped.
			$out .= '<span class="sc-section__icon">' . $icon . '</span>';
		}
		$out .= '<p class="sc-section__num">' . esc_html( $number ) . '</p>';
		$out .= '<h2 class="sc-section__title">' . esc_html( $section['title'] ) . '</h2>';
		if ( ! empty( $section['brief'] ) ) {
			$out .= '<div class="sc-section__rule" aria-hidden="true"></div>';
			$out .= '<p class="sc-section__brief">' . esc_html( $section['brief'] ) . '</p>';
		}
		if ( $back_url ) {
			$out .= '<a class="sc-section__top" href="' . esc_url( $back_url ) . '">' . esc_html__( 'All services', 'sinclairs-services' ) . '</a>';
		}
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

	$out .= '</div>';

	return $out;
}

/**
 * The ten services as a stack of collapsed bars — the arrangement the
 * client asked for: every service visible as a title, nothing expanded
 * until it is clicked.
 *
 * Built on native <details>/<summary> rather than divs and JS. With
 * JavaScript off the bars still open and close, the browser's own
 * find-in-page can reach inside a closed one, and keyboard and screen
 * reader behaviour is the platform's rather than something re-invented
 * here. frontend.js only adds what the element cannot do by itself:
 * closing the others, and honouring a deep link.
 *
 * The section id stays on the <details> so every existing link — the
 * nav dropdown, the home page, the jump box, [sinclairs_services_list]
 * — keeps resolving to the same anchor it always did.
 */
function ssvc_collapse_markup() {
	$out = '<div class="sc-collapses" data-sc-collapses>';

	foreach ( ssvc_sections() as $i => $section ) {
		$number = str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT );
		$body   = ssvc_render_body( $section['body'] );
		$icon   = ssvc_icon( $section['id'] );

		$out .= '<details class="sc-collapse" id="' . esc_attr( $section['id'] ) . '" data-sc-collapse>';

		$out .= '<summary class="sc-collapse__bar">';
		if ( $icon ) {
			// Plugin-authored markup from inc/icons.php, not user input.
			$out .= '<span class="sc-collapse__icon">' . $icon . '</span>';
		}
		$out .= '<span class="sc-collapse__num">' . esc_html( $number ) . '</span>';
		// A real heading, not a span: the rail layout used <h2> here, and
		// dropping to a span would take all ten practice areas out of
		// screen-reader heading navigation and orphan the <h3> subheads
		// and "Information & FAQs" inside them. A summary's content model
		// allows heading content, so this stays valid.
		$out .= '<h2 class="sc-collapse__title">' . esc_html( $section['title'] ) . '</h2>';
		$out .= '<span class="sc-collapse__chevron" aria-hidden="true"></span>';
		$out .= '</summary>';

		$out .= '<div class="sc-collapse__panel">';
		// No 'brief' here: the bar's title already says which service
		// this is, and the section's own opening line follows straight
		// after, so the summary only repeated what sat either side of it.
		$out .= '<div class="sc-section__prose">' . $body . '</div>';
		$out .= ssvc_render_faqs( $section['faqs'], $section['id'] );
		// No "All services" link here: the bars are the index. Every
		// other service is one click away up the page already.
		$out .= '</div>';

		$out .= '</details>';
	}

	$out .= '</div>';

	return $out;
}

function ssvc_open( $modifier = '' ) {
	return '<div class="sc-services' . ( $modifier ? ' sc-services--' . esc_attr( $modifier ) : '' ) . '">';
}

/**
 * [sinclairs_services_index] — the landing page: jump box + index.
 */
function ssvc_shortcode_index( $atts ) {
	$atts = shortcode_atts( array(
		'jump' => 'yes',
	), $atts, 'sinclairs_services_index' );

	ssvc_enqueue_assets();

	// Only link across to the detail page if it is really there. When it
	// isn't — the plugin's files were updated but activation never ran,
	// or the page was deleted — fall back to the single-page arrangement
	// and render the sections here. Linking to a page that doesn't exist
	// would turn every row into a 404.
	$has_detail = ssvc_detail_page_exists();
	$base       = $has_detail ? ssvc_detail_page_url() : '';

	$out = ssvc_open( 'index' );

	if ( 'yes' === $atts['jump'] ) {
		$out .= ssvc_menu_markup( $base );
	}

	$out .= ssvc_nutshell_markup( $base );

	if ( ! $has_detail ) {
		$out .= ssvc_sections_markup( false, '#sc-nutshell' );
	}

	$out .= '</div>';

	return $out;
}
add_shortcode( 'sinclairs_services_index', 'ssvc_shortcode_index' );

/**
 * [sinclairs_services_detail] — the ten sections on their own page.
 */
function ssvc_shortcode_detail( $atts ) {
	$atts = shortcode_atts( array(
		// 'collapse' (default) — every service a closed bar, one open at
		// a time. 'rail' and 'accordion' are the earlier layouts, kept
		// so an existing page can pin the arrangement it already has.
		'layout' => 'collapse',
		'jump'   => 'no',
	), $atts, 'sinclairs_services_detail' );

	ssvc_enqueue_assets();

	$layout = in_array( $atts['layout'], array( 'collapse', 'rail', 'accordion' ), true ) ? $atts['layout'] : 'collapse';

	$out = ssvc_open( $layout );

	if ( 'yes' === $atts['jump'] ) {
		$out .= ssvc_menu_markup();
	}

	if ( 'collapse' === $layout ) {
		$out .= ssvc_collapse_markup();
	} else {
		$out .= ssvc_sections_markup( 'accordion' === $layout, ssvc_services_page_url() );
	}

	$out .= '</div>';

	return $out;
}
add_shortcode( 'sinclairs_services_detail', 'ssvc_shortcode_detail' );

/**
 * [sinclairs_services] — everything on a single page.
 *
 * Kept for the single-page arrangement and for backwards compatibility
 * with the shortcode the old plugin's Elementor widget may still call.
 * Here the index and the sections share a page, so anchors stay local.
 */
function ssvc_shortcode_services( $atts ) {
	$atts = shortcode_atts( array(
		'layout'   => 'rail',   // 'rail' (default), 'collapse' or 'accordion'
		// Off by default: the page already carries its own "OUR EXPERTISE"
		// header above the shortcode, so rendering ours repeats it.
		'intro'    => 'no',
		'nutshell' => 'yes',
		'jump'     => 'yes',
	), $atts, 'sinclairs_services' );

	ssvc_enqueue_assets();

	$collapse  = ( 'collapse' === $atts['layout'] );
	$accordion = ( 'accordion' === $atts['layout'] );
	$nutshell  = ( 'yes' === $atts['nutshell'] );

	$out = ssvc_open( $collapse ? 'collapse' : ( $accordion ? 'accordion' : 'rail' ) );

	if ( 'yes' === $atts['intro'] ) {
		$intro = ssvc_page_intro();
		$out  .= '<header class="sc-intro">';
		$out  .= '<p class="sc-intro__kicker">' . esc_html( $intro['kicker'] ) . '</p>';
		$out  .= '<h2 class="sc-intro__title">' . esc_html( $intro['title'] ) . '</h2>';
		$out  .= '<p class="sc-intro__dek">' . esc_html( $intro['dek'] ) . '</p>';
		$out  .= '</header>';
	}

	if ( 'yes' === $atts['jump'] ) {
		$out .= ssvc_menu_markup();
	}

	if ( $nutshell ) {
		$out .= ssvc_nutshell_markup();
	}

	// Back link only where there is an index above to go back to.
	if ( $collapse ) {
		$out .= ssvc_collapse_markup();
	} else {
		$out .= ssvc_sections_markup( $accordion, $nutshell ? '#sc-nutshell' : '' );
	}
	$out .= '</div>';

	return $out;
}
add_shortcode( 'sinclairs_services', 'ssvc_shortcode_services' );

/**
 * [sinclairs_services_nutshell] — the index alone, for use anywhere.
 */
function ssvc_shortcode_nutshell() {
	ssvc_enqueue_assets();
	return ssvc_open() . ssvc_nutshell_markup( ssvc_detail_page_url() ) . '</div>';
}
add_shortcode( 'sinclairs_services_nutshell', 'ssvc_shortcode_nutshell' );

/**
 * [sinclairs_services_menu] — the jump box alone, for use anywhere.
 */
function ssvc_shortcode_menu() {
	ssvc_enqueue_assets();
	return ssvc_open() . ssvc_menu_markup( ssvc_detail_page_url() ) . '</div>';
}
add_shortcode( 'sinclairs_services_menu', 'ssvc_shortcode_menu' );

/**
 * [sinclairs_services_summary] — the tag the previous plugin registered.
 *
 * It is still called from a (currently hidden) container on the home
 * page. Without this alias, replacing the old plugin would leave the raw
 * shortcode text rendering there. Mapped to the index, which is the
 * closest equivalent to what it used to output.
 */
add_shortcode( 'sinclairs_services_summary', 'ssvc_shortcode_index' );

/**
 * [sinclairs_services_list] — just the service titles, each linking to
 * its section. For use away from the Services pages: the About page, a
 * footer column, a sidebar.
 *
 * Attributes:
 *   theme="light|dark"   dark = white text, for use on a navy/photo panel
 *   columns="1|2"        1 (default) suits a half-width column
 *   heading="…"          optional heading above the list
 *   pad="none|sm|md|lg|xl"
 *                        left inset, for a panel that butts up against
 *                        an image or the edge of its column. Also takes
 *                        an explicit length — pad="120px", pad="8%",
 *                        pad="6vw" — for when the keyword steps are not
 *                        the gap the design wants.
 *   size="normal|large|xlarge"
 *                        title size. normal (default) is what the list
 *                        was built at; the larger steps suit a full
 *                        panel like the home page's services block,
 *                        where 16px reads as small against the
 *                        surrounding type. Also takes an explicit
 *                        length — size="18px" — since the steps are
 *                        only a couple of pixels apart.
 *
 * Links resolve to wherever the sections actually live — the detail page
 * when it exists, the index page when the plugin is in its single-page
 * fallback — so this never points at a missing page.
 */
function ssvc_shortcode_list( $atts ) {
	$atts = shortcode_atts( array(
		'theme'   => 'light',
		'columns' => '1',
		'heading' => '',
		'size'    => 'normal',
		'pad'     => 'none',
	), $atts, 'sinclairs_services_list' );

	$sections = ssvc_section_index();

	if ( ! $sections ) {
		return '';
	}

	ssvc_enqueue_assets();

	$base    = ssvc_detail_page_exists() ? ssvc_detail_page_url() : ssvc_services_page_url();
	$dark    = ( 'dark' === $atts['theme'] );
	$columns = ( '2' === $atts['columns'] ) ? '2' : '1';
	// Same deal as pad: a keyword step, or an exact length for when the
	// steps are not the size wanted. Narrow pattern, since it ends up in
	// a style attribute.
	$size_raw   = trim( (string) $atts['size'] );
	$size_class = 'normal';
	$size_style = '';

	if ( in_array( $size_raw, array( 'normal', 'large', 'xlarge' ), true ) ) {
		$size_class = $size_raw;
	} elseif ( preg_match( '/^\d{1,3}(\.\d{1,2})?(px|rem|em)$/', $size_raw ) ) {
		$size_style = '--sc-list-size:' . $size_raw . ';';
	}
	// Either one of the keyword steps, or an explicit CSS length. The
	// pattern is deliberately narrow — a number and a unit, nothing
	// else — so the value can be dropped into a style attribute without
	// opening a hole for anything that is not a length.
	$pad_raw   = trim( (string) $atts['pad'] );
	$pad_class = '';
	$pad_style = '';

	if ( in_array( $pad_raw, array( 'sm', 'md', 'lg', 'xl' ), true ) ) {
		$pad_class = ' sc-list-pad--' . $pad_raw;
	} elseif ( preg_match( '/^\d{1,4}(\.\d{1,2})?(px|%|rem|em|vw)$/', $pad_raw ) ) {
		$pad_style = 'padding-left:' . $pad_raw . ';';
	}

	$classes = 'sc-services sc-services--list';
	if ( $dark ) {
		$classes .= ' sc-services--dark';
	}
	$classes .= $pad_class;

	$inline = $pad_style . $size_style;
	$out    = '<div class="' . esc_attr( $classes ) . '"' . ( $inline ? ' style="' . esc_attr( $inline ) . '"' : '' ) . '>';

	if ( '' !== $atts['heading'] ) {
		$out .= '<h3 class="sc-list__heading">' . esc_html( $atts['heading'] ) . '</h3>';
	}

	$out .= '<ul class="sc-list sc-list--cols-' . esc_attr( $columns ) . ' sc-list--size-' . esc_attr( $size_class ) . '">';

	foreach ( $sections as $id => $title ) {
		$out .= '<li class="sc-list__item">';
		$out .= '<a class="sc-list__link" href="' . esc_url( $base . '#' . $id ) . '">';
		$out .= '<span class="sc-list__text">' . esc_html( $title ) . '</span>';
		$out .= '<span class="sc-list__arrow" aria-hidden="true">&rarr;</span>';
		$out .= '</a></li>';
	}

	$out .= '</ul></div>';

	return $out;
}
add_shortcode( 'sinclairs_services_list', 'ssvc_shortcode_list' );
