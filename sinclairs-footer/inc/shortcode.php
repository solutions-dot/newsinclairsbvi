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
 *   headings="yes|no"    the column headings
 *   align="left|center"  desktop alignment. Columns centre on mobile
 *                        either way.
 *
 * The middle column is the short one, so it carries the hours and lets
 * the two long columns sit either side of it — that is what stops the
 * footer looking bottom-left heavy, which is the problem with the
 * current one.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'sinclairs_footer', 'sbvif_shortcode' );
function sbvif_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'theme'    => 'light',
		'headings' => 'yes',
		'align'    => 'left',
	), $atts, 'sinclairs_footer' );

	sbvif_enqueue_assets();

	$links    = sbvif_links();
	$hours    = sbvif_hours();
	$contact  = sbvif_contact();
	$headings = ( 'yes' === $atts['headings'] );

	$classes = array( 'sbvif' );
	$classes[] = 'dark' === $atts['theme'] ? 'sbvif--dark' : 'sbvif--light';
	$classes[] = 'center' === $atts['align'] ? 'sbvif--center' : 'sbvif--left';

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
		<div class="sbvif__grid">

			<?php if ( $links ) : ?>
				<nav class="sbvif__col sbvif__col--links" aria-label="<?php esc_attr_e( 'Footer', 'sinclairs-footer' ); ?>">
					<?php if ( $headings ) : ?>
						<h2 class="sbvif__heading"><?php esc_html_e( 'Explore', 'sinclairs-footer' ); ?></h2>
					<?php endif; ?>

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
				</nav>
			<?php endif; ?>

			<?php if ( $hours ) : ?>
				<div class="sbvif__col sbvif__col--hours">
					<?php if ( $headings ) : ?>
						<h2 class="sbvif__heading"><?php esc_html_e( 'Opening Hours', 'sinclairs-footer' ); ?></h2>
					<?php endif; ?>

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
				</div>
			<?php endif; ?>

			<?php if ( $contact ) : ?>
				<div class="sbvif__col sbvif__col--contact">
					<?php if ( $headings ) : ?>
						<h2 class="sbvif__heading"><?php esc_html_e( 'Get in Touch', 'sinclairs-footer' ); ?></h2>
					<?php endif; ?>

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
				</div>
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
