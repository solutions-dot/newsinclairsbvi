<?php
/**
 * [sinclairs_footer] — the three-column footer.
 *
 *   Explore        Opening Hours        Get in Touch
 *   Home           Mon – Fri            phone
 *   About          8:30 – 5:00          phone
 *   Our Services                        email
 *   Legal Insights                      address
 *   Contact Us
 *
 * Attributes
 * ----------
 *   theme="light|dark"   light (default) = white text for the teal
 *                        panel; dark = ink text for a pale background
 *   headings="yes|no"    the column headings (and the rule under them).
 *                        Default no.
 *   align="left|center"  desktop alignment. Columns centre on mobile
 *                        either way.
 *   collapse="mobile|no"  fold each column under its heading on
 *                        narrow screens. Only possible with a heading
 *                        to tap, so this has no effect while
 *                        headings="no". Default mobile; the columns
 *                        are always open on desktop.
 *   padding="none|sm|md|lg"
 *                        space above and below. Also takes an exact
 *                        length — padding="12px" — for trimming the gap
 *                        against a section that carries its own padding.
 *
 * The middle column is the short one, so it carries the hours and lets
 * the two long columns sit either side of it — that is what stops the
 * footer looking bottom-left heavy, which is the problem with the
 * current one.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A column's opening tag and its heading.
 *
 * Collapsible columns are <details> carrying the `open` attribute in the
 * markup, not added by script: they arrive expanded, stay expanded if
 * JavaScript never runs, and footer.js only ever closes them once it has
 * confirmed the viewport is narrow. The other way round would leave a
 * no-JS visitor with a footer they could not open.
 *
 * The nav column keeps its <nav> wrapper either way — a <details> is not
 * a landmark, and dropping it would take the footer navigation out of a
 * screen reader's landmark list.
 */
function sbvif_col_open( $name, $collapse, $headings, $label ) {
	$classes = 'sbvif__col sbvif__col--' . $name;

	$out = ( 'links' === $name )
		? '<nav class="' . esc_attr( $classes ) . '" aria-label="' . esc_attr__( 'Footer', 'sinclairs-footer' ) . '">'
		: '<div class="' . esc_attr( $classes ) . '">';

	if ( $collapse ) {
		$out .= '<details class="sbvif__fold" open>';
		$out .= '<summary class="sbvif__heading">';
		$out .= '<span>' . esc_html( $label ) . '</span>';
		$out .= '<span class="sbvif__chevron" aria-hidden="true"></span>';
		$out .= '</summary>';
		$out .= '<div class="sbvif__foldbody">';

		return $out;
	}

	if ( $headings ) {
		$out .= '<h2 class="sbvif__heading">' . esc_html( $label ) . '</h2>';
	}

	return $out;
}

function sbvif_col_close( $name, $collapse ) {
	$close = ( 'links' === $name ) ? '</nav>' : '</div>';

	return $collapse ? '</div></details>' . $close : $close;
}

add_shortcode( 'sinclairs_footer', 'sbvif_shortcode' );
function sbvif_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'theme'    => 'light',
		'headings' => 'no',
		'align'    => 'left',
		'padding'  => 'md',
		'collapse' => 'mobile',
	), $atts, 'sinclairs_footer' );

	sbvif_enqueue_assets();

	// Collapsing needs a heading to fold under, so it is off whenever
	// the headings are.
	$collapse = ( 'mobile' === $atts['collapse'] && 'yes' === $atts['headings'] );

	$links    = sbvif_links();
	$hours    = sbvif_hours();
	$contact  = sbvif_contact();
	$headings = ( 'yes' === $atts['headings'] );

	$classes = array( 'sbvif' );
	$classes[] = 'dark' === $atts['theme'] ? 'sbvif--dark' : 'sbvif--light';
	$classes[] = 'center' === $atts['align'] ? 'sbvif--center' : 'sbvif--left';

	if ( $collapse ) {
		$classes[] = 'sbvif--collapsible';
	}

	// A keyword step, or an exact length. The pattern is deliberately
	// narrow — a number and a unit, nothing else — because the value
	// goes into a style attribute.
	$pad   = trim( (string) $atts['padding'] );
	$style = '';

	if ( in_array( $pad, array( 'none', 'sm', 'lg' ), true ) ) {
		$classes[] = 'sbvif--pad-' . $pad;
	} elseif ( preg_match( '/^\d{1,4}(\.\d{1,2})?(px|rem|em|vw|%)$/', $pad ) ) {
		$style = '--sbvif-pad-y:' . $pad . ';';
	}

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo $style ? ' style="' . esc_attr( $style ) . '"' : ''; ?>>
		<div class="sbvif__grid">

			<?php if ( $links ) : ?>
				<?php echo sbvif_col_open( 'links', $collapse, $headings, __( 'Explore', 'sinclairs-footer' ) ); // phpcs:ignore WordPress.Security.EscapingOutput -- escaped in sbvif_col_open(). ?>

					<ul class="sbvif__links">
						<?php foreach ( $links as $link ) : ?>
							<li>
								<a class="sbvif__link" href="<?php echo esc_url( $link['url'] ); ?>">
									<?php echo sbvif_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapingOutput -- plugin-authored SVG. ?>
									<span><?php echo esc_html( $link['label'] ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php echo sbvif_col_close( 'links', $collapse ); // phpcs:ignore WordPress.Security.EscapingOutput -- a closing tag. ?>
			<?php endif; ?>

			<?php if ( $hours ) : ?>
				<?php echo sbvif_col_open( 'hours', $collapse, $headings, __( 'Opening Hours', 'sinclairs-footer' ) ); // phpcs:ignore WordPress.Security.EscapingOutput -- escaped in sbvif_col_open(). ?>

					<ul class="sbvif__rows">
						<?php foreach ( $hours as $row ) : ?>
							<li class="sbvif__row">
								<?php echo sbvif_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapingOutput -- plugin-authored SVG. ?>
								<span class="sbvif__rowtext">
									<span class="screen-reader-text"><?php esc_html_e( 'Opening hours:', 'sinclairs-footer' ); ?> </span>
									<span class="sbvif__days"><?php echo esc_html( $row['days'] ); ?></span>
									<span class="sbvif__time"><?php echo esc_html( $row['time'] ); ?></span>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php echo sbvif_col_close( 'hours', $collapse ); // phpcs:ignore WordPress.Security.EscapingOutput -- a closing tag. ?>
			<?php endif; ?>

			<?php if ( $contact ) : ?>
				<?php echo sbvif_col_open( 'contact', $collapse, $headings, __( 'Get in Touch', 'sinclairs-footer' ) ); // phpcs:ignore WordPress.Security.EscapingOutput -- escaped in sbvif_col_open(). ?>

					<ul class="sbvif__rows">
						<?php foreach ( $contact as $row ) : ?>
							<li class="sbvif__row">
								<?php echo sbvif_icon( $row['icon'] ); // phpcs:ignore WordPress.Security.EscapingOutput -- plugin-authored SVG. ?>
								<?php
								// A row with an href is actionable — tapping a
								// number dials it, tapping the address opens a
								// map. That is most of what a footer is for on
								// a phone, so it is not left as plain text.
								if ( ! empty( $row['href'] ) ) :
									$external = 0 === strpos( $row['href'], 'http' );
									?>
									<a class="sbvif__rowtext" href="<?php echo esc_url( $row['href'] ); ?>"<?php echo $external ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
										<span class="screen-reader-text"><?php echo esc_html( $row['label'] ); ?>: </span>
										<?php echo esc_html( $row['text'] ); ?>
									</a>
								<?php else : ?>
									<span class="sbvif__rowtext">
										<span class="screen-reader-text"><?php echo esc_html( $row['label'] ); ?>: </span>
										<?php echo esc_html( $row['text'] ); ?>
									</span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php echo sbvif_col_close( 'contact', $collapse ); // phpcs:ignore WordPress.Security.EscapingOutput -- a closing tag. ?>
			<?php endif; ?>

		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Template tag, for dropping the footer straight into a theme file.
 */
function sinclairs_footer( $args = array() ) {
	$args = wp_parse_args( $args, array( 'echo' => true ) );
	$html = sbvif_shortcode( $args );

	if ( $args['echo'] ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapingOutput -- escaped in sbvif_shortcode().
	}

	return $html;
}
