<?php
/**
 * [sinclairs_testimonials] — the carousel.
 *
 * Attributes
 * ----------
 *   heading="What clients say"   optional heading above the card
 *   autoplay="yes|no"            default no. Reading speed varies far
 *                                more than a slideshow's timer can
 *                                allow for, so a testimonial that moves
 *                                on by itself is usually an annoyance.
 *   delay="9000"                 milliseconds per testimonial when
 *                                autoplay is on
 *   arrows="yes|no"              previous / next, one either side of
 *                                the card
 *   dots="yes|no"                position dots under the card. Off by
 *                                default: with the arrows flanking the
 *                                card there is nothing else to put in a
 *                                strip underneath it.
 *
 * Every testimonial is rendered into the page. Only the active one is
 * shown, but the others are real text in the document, so search
 * engines and find-in-page see all of them.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'sinclairs_testimonials', 'sbvit_shortcode' );
function sbvit_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'heading'  => '',
		'autoplay' => 'no',
		'delay'    => '9000',
		'arrows'   => 'yes',
		'dots'     => 'no',
	), $atts, 'sinclairs_testimonials' );

	$items = sbvit_testimonials();

	if ( ! $items ) {
		return '';
	}

	sbvit_enqueue_assets();

	$config = array(
		'autoplay' => ( 'yes' === $atts['autoplay'] ),
		'delay'    => max( 3000, absint( $atts['delay'] ) ),
		'count'    => count( $items ),
	);

	$total = count( $items );

	ob_start();
	?>
	<section class="sbvit" data-sbvit='<?php echo esc_attr( wp_json_encode( $config ) ); ?>'
		aria-roledescription="carousel"
		aria-label="<?php esc_attr_e( 'Client testimonials', 'sinclairs-testimonials' ); ?>">

		<?php if ( '' !== $atts['heading'] ) : ?>
			<h2 class="sbvit__heading"><?php echo esc_html( $atts['heading'] ); ?></h2>
		<?php endif; ?>

		<div class="sbvit__stage">
			<?php if ( $total > 1 && 'yes' === $atts['arrows'] ) : ?>
				<button type="button" class="sbvit__arrow" data-sbvit-prev aria-label="<?php esc_attr_e( 'Previous testimonial', 'sinclairs-testimonials' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
			<?php endif; ?>

			<div class="sbvit__card">
				<?php
				// The viewport's height is animated to the active slide
				// by the script. Without JS it has no height set at all,
				// so every testimonial simply stacks and stays readable.
				?>
				<div class="sbvit__viewport" data-sbvit-viewport>
					<?php foreach ( $items as $i => $item ) : ?>
						<figure class="sbvit__item<?php echo 0 === $i ? ' is-active' : ''; ?>"
							data-sbvit-item="<?php echo esc_attr( $i ); ?>"
							role="group"
							aria-roledescription="<?php esc_attr_e( 'testimonial', 'sinclairs-testimonials' ); ?>"
							aria-label="<?php echo esc_attr( sprintf( __( '%1$d of %2$d', 'sinclairs-testimonials' ), $i + 1, $total ) ); ?>">

							<figcaption class="sbvit__who">
								<p class="sbvit__name"><?php echo esc_html( $item['name'] ); ?></p>
								<?php if ( ! empty( $item['role'] ) ) : ?>
									<p class="sbvit__role"><?php echo esc_html( $item['role'] ); ?></p>
								<?php endif; ?>
							</figcaption>

							<blockquote class="sbvit__quote">
								<?php foreach ( (array) $item['quote'] as $para ) : ?>
									<p><?php echo esc_html( $para ); ?></p>
								<?php endforeach; ?>
							</blockquote>
						</figure>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( $total > 1 && 'yes' === $atts['arrows'] ) : ?>
				<button type="button" class="sbvit__arrow" data-sbvit-next aria-label="<?php esc_attr_e( 'Next testimonial', 'sinclairs-testimonials' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
			<?php endif; ?>
		</div>

		<?php if ( $total > 1 && 'yes' === $atts['dots'] ) : ?>
			<div class="sbvit__nav">
				<div class="sbvit__dots" role="tablist" aria-label="<?php esc_attr_e( 'Choose a testimonial', 'sinclairs-testimonials' ); ?>">
					<?php foreach ( $items as $i => $item ) : ?>
						<button type="button"
							class="sbvit__dot<?php echo 0 === $i ? ' is-active' : ''; ?>"
							role="tab"
							aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
							aria-label="<?php echo esc_attr( $item['name'] ); ?>"
							data-sbvit-goto="<?php echo esc_attr( $i ); ?>"></button>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Template tag, for dropping the carousel straight into a theme file.
 */
function sinclairs_testimonials( $args = array() ) {
	$args = wp_parse_args( $args, array( 'echo' => true ) );
	$html = sbvit_shortcode( $args );

	if ( $args['echo'] ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapingOutput -- escaped in sbvit_shortcode().
	}

	return $html;
}
