<?php
/**
 * Front-end output: the shortcode, the template tag, the block, and the
 * markup itself.
 *
 * Every positional value is emitted as a CSS custom property on the
 * element rather than as a bespoke stylesheet rule, so one static CSS
 * file covers any number of slides and the whole thing still works if
 * the page is cached.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'sinclairs_slider', 'sbvis_shortcode' );
function sbvis_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'ids'   => '',
		'class' => '',
		'width' => '',
	), $atts, 'sinclairs_slider' );

	$ids = array_filter( array_map( 'absint', array_map( 'trim', explode( ',', (string) $atts['ids'] ) ) ) );

	return sbvis_slider_markup( $ids, $atts['class'], $atts['width'] );
}

/**
 * Template tag, for dropping the slider straight into a theme file.
 */
function sinclairs_slider( $args = array() ) {
	$args = wp_parse_args( $args, array( 'ids' => array(), 'class' => '', 'width' => '', 'echo' => true ) );
	$html = sbvis_slider_markup( (array) $args['ids'], $args['class'], $args['width'] );

	if ( $args['echo'] ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapingOutput -- built and escaped in sbvis_slider_markup().
	}

	return $html;
}

/**
 * The 9-point anchors resolve to a percentage position within the
 * frame. The insets (8% / 14%) keep an edge-anchored layer clear of the
 * very edge of the image, where it would collide with the browser
 * chrome on small screens.
 */
function sbvis_anchor_position( $anchor ) {
	list( $y, $x ) = array_pad( explode( '-', $anchor ), 2, 'center' );

    $map_x = array( 'left' => 8, 'center' => 50, 'right' => 92 );
    $map_y = array( 'top' => 14, 'middle' => 50, 'bottom' => 86 );

	return array(
		isset( $map_x[ $x ] ) ? $map_x[ $x ] : 50,
		isset( $map_y[ $y ] ) ? $map_y[ $y ] : 50,
	);
}

function sbvis_style_string( $props ) {
	$out = array();

	foreach ( $props as $key => $value ) {
		if ( '' === $value || null === $value ) {
			continue;
		}
		$out[] = $key . ':' . $value;
	}

	return implode( ';', $out );
}

function sbvis_slider_markup( $ids = array(), $extra_class = '', $width = '' ) {
	$slides = sbvis_get_slides( $ids );

	if ( ! $slides ) {
		return '';
	}

	$settings = sbvis_settings();

	sbvis_enqueue_assets();

	$root_style = sbvis_style_string( array(
		'--sbvis-accent'    => $settings['accent'],
		'--sbvis-h-desktop' => (int) $settings['height_px'] . 'px',
		'--sbvis-h-mobile'  => (int) $settings['height_px_m'] . 'px',
		'--sbvis-speed'     => (int) $settings['speed'] . 'ms',
		'--sbvis-font'      => $settings['font_override'] ? $settings['font_override'] : 'inherit',
	) );

	// A width given on the shortcode wins over the global setting, so a
	// single page can box the slider without changing it everywhere.
	$width = in_array( $width, array( 'full', 'contained' ), true ) ? $width : $settings['width'];

	$classes = array(
		'sbvis',
		'sbvis--' . $width,
		'sbvis--effect-' . $settings['effect'],
		'sbvis--nav-' . $settings['nav'],
		'sbvis--hd-' . $settings['height_desktop'],
		'sbvis--hm-' . $settings['height_mobile'],
	);

	if ( $extra_class ) {
		$classes[] = sanitize_html_class( $extra_class );
	}

	$config = array(
		'effect'       => $settings['effect'],
		'speed'        => (int) $settings['speed'],
		'autoplay'     => (bool) $settings['autoplay'],
		'delay'        => (int) $settings['delay'],
		'loop'         => (bool) $settings['loop'],
		'pauseOnHover' => (bool) $settings['pause_on_hover'],
		'count'        => count( $slides ),
	);

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" style="<?php echo esc_attr( $root_style ); ?>" data-sbvis='<?php echo esc_attr( wp_json_encode( $config ) ); ?>' role="region" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Featured', 'sinclairs-slider' ); ?>">
		<div class="sbvis__stage" data-sbvis-stage>
			<?php foreach ( $slides as $index => $post ) : ?>
				<?php sbvis_render_slide( $post, $index, count( $slides ), $settings ); ?>
			<?php endforeach; ?>
		</div>

		<?php sbvis_render_nav( $slides, $settings ); ?>
	</div>
	<?php
	return ob_get_clean();
}

function sbvis_render_slide( $post, $index, $total, $settings ) {
	$slide = sbvis_slide( $post->ID );

	list( $text_x, $text_y ) = sbvis_anchor_position( $slide['text_anchor'] );
	$text_x += (float) $slide['text_x'];
	$text_y += (float) $slide['text_y'];

	$has_buttons = ( $slide['btn_on'] && $slide['btn_label'] ) || ( $slide['btn2_on'] && $slide['btn2_label'] );
	$free_button = $has_buttons && ! $slide['btn_follow'];

	if ( $free_button ) {
		list( $btn_x, $btn_y ) = sbvis_anchor_position( $slide['btn_anchor'] );
		$btn_x += (float) $slide['btn_x'];
		$btn_y += (float) $slide['btn_y'];
	}

	$slide_class = array( 'sbvis__slide' );
	if ( 0 === $index ) {
		$slide_class[] = 'is-active';
	}

	// One setting for the whole slider rather than a per-slide toggle:
	// a desktop position anchored to an edge almost always needs
	// somewhere different to go on a narrow screen, and repeating that
	// choice on every slide just for consistency was busywork.
	$mobile_position = $settings['mobile_position'];
	$mobile_x         = 0.0;
	$mobile_y         = 0.0;

	if ( 'custom' === $mobile_position ) {
		$slide_class[] = 'sbvis__slide--mobile-pos';
		list( $mobile_x, $mobile_y ) = sbvis_anchor_position( $settings['mobile_anchor'] );
		$mobile_x += (float) $settings['mobile_nudge_x'];
		$mobile_y += (float) $settings['mobile_nudge_y'];
	} elseif ( 'same' !== $mobile_position ) {
		// 'auto' (default), and anything unrecognised degrades to it.
		$slide_class[] = 'sbvis__slide--restack';
	}

	$image_id = (int) $slide['image_id'];
	$alt      = $slide['alt'] ? $slide['alt'] : get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	?>
	<div class="<?php echo esc_attr( implode( ' ', $slide_class ) ); ?>"
		role="group"
		aria-roledescription="slide"
		aria-label="<?php echo esc_attr( sprintf( __( '%1$d of %2$d', 'sinclairs-slider' ), $index + 1, $total ) ); ?>"
		<?php echo 0 === $index ? '' : 'aria-hidden="true"'; ?>
		data-sbvis-slide="<?php echo esc_attr( $index ); ?>">

		<div class="sbvis__media">
			<?php
			if ( $image_id ) {
				echo wp_get_attachment_image( $image_id, 'sbvis-slide', false, array(
					'class'         => 'sbvis__img',
					'alt'           => $alt,
					'style'         => 'object-position:' . (float) $slide['focal_x'] . '% ' . (float) $slide['focal_y'] . '%',
					'loading'       => 0 === $index ? 'eager' : 'lazy',
					'decoding'      => 'async',
					'fetchpriority' => 0 === $index ? 'high' : 'auto',
					'sizes'         => '100vw',
				) );
			}
			?>
			<span class="sbvis__scrim" aria-hidden="true" style="<?php echo esc_attr( sbvis_style_string( array(
				'background' => $slide['overlay_color'],
				'opacity'    => (float) $slide['overlay'] / 100,
			) ) ); ?>"></span>
		</div>

		<?php if ( $slide['heading'] || $slide['subheading'] || ( $has_buttons && $slide['btn_follow'] ) ) : ?>
			<div class="sbvis__layer sbvis__layer--text" style="<?php echo esc_attr( sbvis_style_string( array(
				'--x'          => $text_x . '%',
				'--y'          => $text_y . '%',
				// Only meaningful with .sbvis__slide--mobile-pos on the
				// slide (mobile_position = 'custom'); harmless custom
				// properties otherwise, since nothing reads them.
				'--x-m'        => $mobile_x . '%',
				'--y-m'        => $mobile_y . '%',
				// Unitless on purpose — slider.css multiplies these by
				// 1rem so the fluid-size calc() stays dimensionally valid.
				'--size'       => (float) $slide['text_size'],
				'--size-m'     => (float) $slide['text_size_m'],
				'--sub-size'   => (float) $slide['sub_size'],
				'--color'      => $slide['text_color'],
				'--align'      => $slide['text_align'],
				'--max-width'  => (float) $slide['text_width'] . '%',
			) ) ); ?>">
				<?php if ( $slide['heading'] ) : ?>
					<h2 class="sbvis__heading"><?php echo nl2br( esc_html( $slide['heading'] ) ); ?></h2>
				<?php endif; ?>

				<?php if ( $slide['subheading'] ) : ?>
					<p class="sbvis__sub"><?php echo nl2br( esc_html( $slide['subheading'] ) ); ?></p>
				<?php endif; ?>

				<?php
				if ( $has_buttons && $slide['btn_follow'] ) {
					sbvis_render_buttons( $slide );
				}
				?>
			</div>
		<?php endif; ?>

		<?php if ( $free_button ) : ?>
			<div class="sbvis__layer sbvis__layer--buttons" style="<?php echo esc_attr( sbvis_style_string( array(
				'--x' => $btn_x . '%',
				'--y' => $btn_y . '%',
			) ) ); ?>">
				<?php sbvis_render_buttons( $slide ); ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

function sbvis_render_buttons( $slide ) {
	$buttons = array();

	if ( $slide['btn_on'] && $slide['btn_label'] ) {
		$buttons[] = array(
			'label' => $slide['btn_label'],
			'url'   => $slide['btn_url'],
			'new'   => $slide['btn_new'],
			'style' => $slide['btn_style'],
			'bg'    => $slide['btn_color'],
			'fg'    => $slide['btn_text'],
		);
	}

	if ( $slide['btn2_on'] && $slide['btn2_label'] ) {
		$buttons[] = array(
			'label' => $slide['btn2_label'],
			'url'   => $slide['btn2_url'],
			'new'   => $slide['btn2_new'],
			'style' => $slide['btn2_style'],
			'bg'    => '',
			'fg'    => '',
		);
	}

	if ( ! $buttons ) {
		return;
	}

	echo '<div class="sbvis__buttons sbvis__buttons--' . esc_attr( $slide['btn_size'] ) . '">';

	foreach ( $buttons as $button ) {
		printf(
			'<a class="sbvis__btn sbvis__btn--%1$s" href="%2$s"%3$s style="%4$s">%5$s</a>',
			esc_attr( $button['style'] ),
			esc_url( $button['url'] ? $button['url'] : '#' ),
			$button['new'] ? ' target="_blank" rel="noopener noreferrer"' : '',
			esc_attr( sbvis_style_string( array(
				'--btn-bg' => $button['bg'],
				'--btn-fg' => $button['fg'],
			) ) ),
			esc_html( $button['label'] )
		);
	}

	echo '</div>';
}

/**
 * The navigation deliberately lives outside .sbvis__stage: it sits in
 * its own band under the images rather than floating over them.
 */
function sbvis_render_nav( $slides, $settings ) {
	if ( 'none' === $settings['nav'] && ! $settings['arrows'] ) {
		return;
	}
	if ( count( $slides ) < 2 ) {
		return;
	}
	?>
	<div class="sbvis__nav">
		<?php if ( $settings['arrows'] ) : ?>
			<button type="button" class="sbvis__arrow sbvis__arrow--prev" data-sbvis-prev aria-label="<?php esc_attr_e( 'Previous slide', 'sinclairs-slider' ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
		<?php endif; ?>

		<?php if ( 'none' !== $settings['nav'] ) : ?>
			<div class="sbvis__dots" role="tablist" aria-label="<?php esc_attr_e( 'Choose a slide', 'sinclairs-slider' ); ?>">
				<?php foreach ( $slides as $index => $post ) : ?>
					<?php
					$label = get_the_title( $post );
					$label = $label ? $label : sprintf( __( 'Slide %d', 'sinclairs-slider' ), $index + 1 );
					?>
					<button type="button"
						class="sbvis__dot<?php echo 0 === $index ? ' is-active' : ''; ?>"
						role="tab"
						aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
						aria-label="<?php echo esc_attr( $label ); ?>"
						data-sbvis-goto="<?php echo esc_attr( $index ); ?>">
						<?php
						if ( 'thumbs' === $settings['nav'] ) {
							$image_id = (int) get_post_meta( $post->ID, '_sbvis_image_id', true );
							$focal_x  = (float) get_post_meta( $post->ID, '_sbvis_focal_x', true );
							$focal_y  = (float) get_post_meta( $post->ID, '_sbvis_focal_y', true );

							if ( $image_id ) {
								echo wp_get_attachment_image( $image_id, 'sbvis-thumb', false, array(
									'alt'      => '',
									'loading'  => 'lazy',
									'decoding' => 'async',
									'style'    => 'object-position:' . $focal_x . '% ' . $focal_y . '%',
								) );
							}
						}
						?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $settings['arrows'] ) : ?>
			<button type="button" class="sbvis__arrow sbvis__arrow--next" data-sbvis-next aria-label="<?php esc_attr_e( 'Next slide', 'sinclairs-slider' ); ?>">
				<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
			</button>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * A block so the slider can be dropped in from the editor without the
 * client having to remember shortcode syntax. Server-rendered, so it
 * always reflects the current slides.
 */
add_action( 'init', 'sbvis_register_block' );
function sbvis_register_block() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	// The editor script is what actually puts the block in the
	// inserter; the PHP registration only supplies the render callback.
	wp_register_script(
		'sbvis-block',
		SBVIS_URI . 'assets/js/block.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
		sbvis_asset_version( 'assets/js/block.js' ),
		true
	);

	register_block_type( 'sinclairs/slider', array(
		'api_version'     => 2,
		'title'           => __( 'Sinclairs Slider', 'sinclairs-slider' ),
		'category'        => 'media',
		'icon'            => 'images-alt2',
		'editor_script'   => 'sbvis-block',
		'render_callback' => 'sbvis_render_block',
		'attributes'      => array(
			'ids' => array( 'type' => 'string', 'default' => '' ),
		),
	) );
}

function sbvis_render_block( $attributes ) {
	$ids = array_filter( array_map( 'absint', array_map( 'trim', explode( ',', (string) ( $attributes['ids'] ?? '' ) ) ) ) );

	return sbvis_slider_markup( $ids );
}

/**
 * Bring in the front-end assets, whichever route rendered the slider.
 *
 * The wp_enqueue_scripts pass below catches the shortcode and block
 * cases before wp_head(), but it cannot see the sinclairs_slider()
 * template tag, which a theme normally calls from a template *after*
 * get_header() has already run wp_head(). Enqueueing at that point
 * leaves the stylesheet to be printed in the footer, so the hero paints
 * unstyled first. When wp_head has been and gone we therefore print the
 * link tag immediately, inline at the slider, instead.
 */
function sbvis_enqueue_assets() {
	sbvis_register_front_assets();

	wp_enqueue_script( 'sbvis-slider' );

	if ( wp_style_is( 'sbvis-slider', 'done' ) ) {
		return;
	}

	if ( did_action( 'wp_head' ) && ! doing_action( 'wp_head' ) ) {
		wp_print_styles( 'sbvis-slider' );
		return;
	}

	wp_enqueue_style( 'sbvis-slider' );
}

function sbvis_register_front_assets() {
	if ( ! wp_style_is( 'sbvis-slider', 'registered' ) ) {
		wp_register_style( 'sbvis-slider', SBVIS_URI . 'assets/css/slider.css', array(), sbvis_asset_version( 'assets/css/slider.css' ) );
	}

	if ( ! wp_script_is( 'sbvis-slider', 'registered' ) ) {
		wp_register_script( 'sbvis-slider', SBVIS_URI . 'assets/js/slider.js', array(), sbvis_asset_version( 'assets/js/slider.js' ), true );
	}
}

/**
 * Registering up front and enqueueing early where we can avoids the
 * flash of unstyled hero you get when a stylesheet is only enqueued
 * once the shortcode runs inside the_content().
 */
add_action( 'wp_enqueue_scripts', 'sbvis_maybe_enqueue_front_assets' );
function sbvis_maybe_enqueue_front_assets() {
	sbvis_register_front_assets();

	if ( ! is_singular() ) {
		return;
	}

	$post = get_post();

	if ( ! $post ) {
		return;
	}

	if ( has_shortcode( $post->post_content, 'sinclairs_slider' ) || has_block( 'sinclairs/slider', $post ) ) {
		wp_enqueue_style( 'sbvis-slider' );
		wp_enqueue_script( 'sbvis-slider' );
	}
}

/**
 * The block preview inside the editor is the same server-rendered
 * markup, so it needs the same stylesheet.
 */
add_action( 'enqueue_block_editor_assets', 'sbvis_editor_assets' );
function sbvis_editor_assets() {
	wp_enqueue_style( 'sbvis-slider-editor', SBVIS_URI . 'assets/css/slider.css', array(), sbvis_asset_version( 'assets/css/slider.css' ) );
}
