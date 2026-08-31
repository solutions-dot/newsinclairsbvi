<?php
/**
 * Everything the client sets on a single slide: the image and its focal
 * point, the text block and where it sits, and up to two buttons that
 * are positioned independently of the text.
 *
 * Positions are stored as a 9-point anchor plus an X/Y nudge in
 * percent. The anchor gets you 95% of the way there in one click; the
 * nudge covers "just a bit lower than dead centre" without making the
 * client think in coordinates.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sbvis_anchors() {
	return array(
		'top-left'      => __( 'Top left', 'sinclairs-slider' ),
		'top-center'    => __( 'Top centre', 'sinclairs-slider' ),
		'top-right'     => __( 'Top right', 'sinclairs-slider' ),
		'middle-left'   => __( 'Middle left', 'sinclairs-slider' ),
		'middle-center' => __( 'Middle centre', 'sinclairs-slider' ),
		'middle-right'  => __( 'Middle right', 'sinclairs-slider' ),
		'bottom-left'   => __( 'Bottom left', 'sinclairs-slider' ),
		'bottom-center' => __( 'Bottom centre', 'sinclairs-slider' ),
		'bottom-right'  => __( 'Bottom right', 'sinclairs-slider' ),
	);
}

/**
 * Defaults are the layout in the client's existing hero: centred text,
 * a centred blue pill under it.
 */
function sbvis_slide_defaults() {
	return array(
		'image_id'      => 0,
		'focal_x'       => 50,
		'focal_y'       => 50,
		'alt'           => '',
		'overlay'       => 35,
		'overlay_color' => '#000000',
		'heading'       => '',
		'subheading'    => '',
		'text_size'     => 4.0,
		'text_size_m'   => 2.0,
		'sub_size'      => 1.25,
		'text_anchor'   => 'middle-center',
		'text_x'        => 0,
		'text_y'        => 0,
		'text_align'    => 'center',
		'text_color'    => '#ffffff',
		'text_width'    => 70,
		'mobile_stack'  => 1,
		'btn_on'        => 0,
		'btn_label'     => '',
		'btn_url'       => '',
		'btn_new'       => 0,
		'btn_style'     => 'solid',
		'btn_size'      => 'medium',
		'btn_color'     => '',
		'btn_custom'    => 0,
		'btn_text'      => '#ffffff',
		'btn_follow'    => 1,
		'btn_anchor'    => 'middle-center',
		'btn_x'         => 0,
		'btn_y'         => 18,
		'btn2_on'       => 0,
		'btn2_label'    => '',
		'btn2_url'      => '',
		'btn2_new'      => 0,
		'btn2_style'    => 'outline',
	);
}

function sbvis_slide( $post_id ) {
	$defaults = sbvis_slide_defaults();
	$slide    = array();

	foreach ( $defaults as $key => $default ) {
		$value = get_post_meta( $post_id, '_sbvis_' . $key, true );
		$slide[ $key ] = ( '' === $value || null === $value ) ? $default : $value;
	}

	return $slide;
}

add_action( 'add_meta_boxes', 'sbvis_add_meta_boxes' );
function sbvis_add_meta_boxes() {
	add_meta_box( 'sbvis-image', __( 'Image &amp; focal point', 'sinclairs-slider' ), 'sbvis_box_image', 'sbvi_slide', 'normal', 'high' );
	add_meta_box( 'sbvis-text', __( 'Text', 'sinclairs-slider' ), 'sbvis_box_text', 'sbvi_slide', 'normal', 'high' );
	add_meta_box( 'sbvis-buttons', __( 'Buttons', 'sinclairs-slider' ), 'sbvis_box_buttons', 'sbvi_slide', 'normal', 'default' );
}

function sbvis_field_name( $key ) {
	return 'sbvis[' . $key . ']';
}

function sbvis_anchor_grid( $key, $current ) {
	echo '<div class="sbvis-anchor-grid">';
	foreach ( sbvis_anchors() as $value => $label ) {
		printf(
			'<label class="sbvis-anchor%s" title="%s"><input type="radio" name="%s" value="%s"%s /><span aria-hidden="true"></span><span class="screen-reader-text">%s</span></label>',
			$current === $value ? ' is-active' : '',
			esc_attr( $label ),
			esc_attr( sbvis_field_name( $key ) ),
			esc_attr( $value ),
			checked( $current, $value, false ),
			esc_html( $label )
		);
	}
	echo '</div>';
}

function sbvis_box_image( $post ) {
	wp_nonce_field( 'sbvis_save_slide', 'sbvis_nonce' );
	$slide = sbvis_slide( $post->ID );
	$src   = $slide['image_id'] ? wp_get_attachment_image_url( (int) $slide['image_id'], 'sbvis-slide-md' ) : '';
	?>
	<div class="sbvis-box">
		<p class="sbvis-hint">
			<?php esc_html_e( 'Upload at 1920 × 1080 pixels (16:9). Then click anywhere on the image below to set the focal point — the spot that must stay visible when the image is cropped on narrower screens and phones.', 'sinclairs-slider' ); ?>
		</p>

		<div class="sbvis-image-field" data-sbvis-image>
			<div class="sbvis-focal" data-sbvis-focal>
				<img src="<?php echo esc_url( $src ); ?>" alt="" data-sbvis-focal-img<?php echo $src ? '' : ' hidden'; ?> />
				<span class="sbvis-focal__marker" data-sbvis-marker style="left:<?php echo esc_attr( $slide['focal_x'] ); ?>%;top:<?php echo esc_attr( $slide['focal_y'] ); ?>%;"<?php echo $src ? '' : ' hidden'; ?>></span>
				<p class="sbvis-focal__empty"<?php echo $src ? ' hidden' : ''; ?>><?php esc_html_e( 'No image selected yet.', 'sinclairs-slider' ); ?></p>
			</div>

			<p class="sbvis-image-field__actions">
				<button type="button" class="button button-primary" data-sbvis-select><?php echo $src ? esc_html__( 'Change image', 'sinclairs-slider' ) : esc_html__( 'Select image', 'sinclairs-slider' ); ?></button>
				<button type="button" class="button" data-sbvis-remove<?php echo $src ? '' : ' hidden'; ?>><?php esc_html_e( 'Remove', 'sinclairs-slider' ); ?></button>
				<button type="button" class="button" data-sbvis-recentre><?php esc_html_e( 'Re-centre focal point', 'sinclairs-slider' ); ?></button>
			</p>

			<input type="hidden" name="<?php echo esc_attr( sbvis_field_name( 'image_id' ) ); ?>" value="<?php echo esc_attr( $slide['image_id'] ); ?>" data-sbvis-image-id />
			<input type="hidden" name="<?php echo esc_attr( sbvis_field_name( 'focal_x' ) ); ?>" value="<?php echo esc_attr( $slide['focal_x'] ); ?>" data-sbvis-focal-x />
			<input type="hidden" name="<?php echo esc_attr( sbvis_field_name( 'focal_y' ) ); ?>" value="<?php echo esc_attr( $slide['focal_y'] ); ?>" data-sbvis-focal-y />

			<p class="sbvis-focal__readout">
				<?php esc_html_e( 'Focal point:', 'sinclairs-slider' ); ?>
				<strong><span data-sbvis-readout-x><?php echo esc_html( $slide['focal_x'] ); ?></span>% / <span data-sbvis-readout-y><?php echo esc_html( $slide['focal_y'] ); ?></span>%</strong>
			</p>
		</div>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="sbvis-alt"><?php esc_html_e( 'Alt text', 'sinclairs-slider' ); ?></label></th>
				<td>
					<input type="text" id="sbvis-alt" class="large-text" name="<?php echo esc_attr( sbvis_field_name( 'alt' ) ); ?>" value="<?php echo esc_attr( $slide['alt'] ); ?>" />
					<p class="description"><?php esc_html_e( 'Describes the image for screen readers and search engines. Leave blank to use the alt text set in the Media Library.', 'sinclairs-slider' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sbvis-overlay"><?php esc_html_e( 'Darken image', 'sinclairs-slider' ); ?></label></th>
				<td>
					<input type="range" id="sbvis-overlay" name="<?php echo esc_attr( sbvis_field_name( 'overlay' ) ); ?>" value="<?php echo esc_attr( $slide['overlay'] ); ?>" min="0" max="90" step="5" data-sbvis-range="sbvis-overlay-out" />
					<output id="sbvis-overlay-out"><?php echo esc_html( $slide['overlay'] ); ?>%</output>
					<input type="color" name="<?php echo esc_attr( sbvis_field_name( 'overlay_color' ) ); ?>" value="<?php echo esc_attr( $slide['overlay_color'] ); ?>" />
					<p class="description"><?php esc_html_e( 'A tint over the photo so white text stays readable. 30–40% suits most photographs.', 'sinclairs-slider' ); ?></p>
				</td>
			</tr>
		</table>
	</div>
	<?php
}

function sbvis_box_text( $post ) {
	$slide = sbvis_slide( $post->ID );
	?>
	<div class="sbvis-box">
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="sbvis-heading"><?php esc_html_e( 'Heading', 'sinclairs-slider' ); ?></label></th>
				<td>
					<textarea id="sbvis-heading" class="large-text" rows="2" name="<?php echo esc_attr( sbvis_field_name( 'heading' ) ); ?>"><?php echo esc_textarea( $slide['heading'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Press Enter for a deliberate line break, as in “Sinclairs (BVI)” / “Lawyers and Notaries Public”. Leave blank for an image-only slide.', 'sinclairs-slider' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sbvis-sub"><?php esc_html_e( 'Sub-heading', 'sinclairs-slider' ); ?></label></th>
				<td><textarea id="sbvis-sub" class="large-text" rows="2" name="<?php echo esc_attr( sbvis_field_name( 'subheading' ) ); ?>"><?php echo esc_textarea( $slide['subheading'] ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="sbvis-size"><?php esc_html_e( 'Heading size', 'sinclairs-slider' ); ?></label></th>
				<td>
					<input type="range" id="sbvis-size" name="<?php echo esc_attr( sbvis_field_name( 'text_size' ) ); ?>" value="<?php echo esc_attr( $slide['text_size'] ); ?>" min="1.5" max="8" step="0.1" data-sbvis-range="sbvis-size-out" data-sbvis-suffix="rem" />
					<output id="sbvis-size-out"><?php echo esc_html( $slide['text_size'] ); ?>rem</output>
					<span class="sbvis-inline-label"><?php esc_html_e( 'on desktop', 'sinclairs-slider' ); ?></span>
					<br />
					<input type="range" name="<?php echo esc_attr( sbvis_field_name( 'text_size_m' ) ); ?>" value="<?php echo esc_attr( $slide['text_size_m'] ); ?>" min="1" max="4" step="0.1" data-sbvis-range="sbvis-size-m-out" data-sbvis-suffix="rem" />
					<output id="sbvis-size-m-out"><?php echo esc_html( $slide['text_size_m'] ); ?>rem</output>
					<span class="sbvis-inline-label"><?php esc_html_e( 'on mobile', 'sinclairs-slider' ); ?></span>
					<p class="description"><?php esc_html_e( 'The size scales smoothly between the two, so there is no jump at the breakpoint. The typeface is inherited from the site theme.', 'sinclairs-slider' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sbvis-sub-size"><?php esc_html_e( 'Sub-heading size', 'sinclairs-slider' ); ?></label></th>
				<td>
					<input type="range" id="sbvis-sub-size" name="<?php echo esc_attr( sbvis_field_name( 'sub_size' ) ); ?>" value="<?php echo esc_attr( $slide['sub_size'] ); ?>" min="0.8" max="3" step="0.05" data-sbvis-range="sbvis-sub-size-out" data-sbvis-suffix="rem" />
					<output id="sbvis-sub-size-out"><?php echo esc_html( $slide['sub_size'] ); ?>rem</output>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Position on the image', 'sinclairs-slider' ); ?></th>
				<td>
					<?php sbvis_anchor_grid( 'text_anchor', $slide['text_anchor'] ); ?>
					<p class="sbvis-nudge">
						<label><?php esc_html_e( 'Nudge across', 'sinclairs-slider' ); ?>
							<input type="number" name="<?php echo esc_attr( sbvis_field_name( 'text_x' ) ); ?>" value="<?php echo esc_attr( $slide['text_x'] ); ?>" min="-50" max="50" step="1" class="small-text" />%</label>
						<label><?php esc_html_e( 'Nudge down', 'sinclairs-slider' ); ?>
							<input type="number" name="<?php echo esc_attr( sbvis_field_name( 'text_y' ) ); ?>" value="<?php echo esc_attr( $slide['text_y'] ); ?>" min="-50" max="50" step="1" class="small-text" />%</label>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Alignment &amp; colour', 'sinclairs-slider' ); ?></th>
				<td>
					<select name="<?php echo esc_attr( sbvis_field_name( 'text_align' ) ); ?>">
						<?php
						foreach ( array( 'left' => __( 'Left', 'sinclairs-slider' ), 'center' => __( 'Centre', 'sinclairs-slider' ), 'right' => __( 'Right', 'sinclairs-slider' ) ) as $value => $label ) {
							printf( '<option value="%s"%s>%s</option>', esc_attr( $value ), selected( $slide['text_align'], $value, false ), esc_html( $label ) );
						}
						?>
					</select>
					<input type="color" name="<?php echo esc_attr( sbvis_field_name( 'text_color' ) ); ?>" value="<?php echo esc_attr( $slide['text_color'] ); ?>" />
					<label class="sbvis-inline-label"><?php esc_html_e( 'Max width', 'sinclairs-slider' ); ?>
						<input type="number" name="<?php echo esc_attr( sbvis_field_name( 'text_width' ) ); ?>" value="<?php echo esc_attr( $slide['text_width'] ); ?>" min="20" max="100" step="5" class="small-text" />%</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'On phones', 'sinclairs-slider' ); ?></th>
				<td>
					<label><input type="checkbox" name="<?php echo esc_attr( sbvis_field_name( 'mobile_stack' ) ); ?>" value="1" <?php checked( $slide['mobile_stack'], 1 ); ?> /> <?php esc_html_e( 'Re-centre the text and buttons on narrow screens', 'sinclairs-slider' ); ?></label>
					<p class="description"><?php esc_html_e( 'Recommended. A position that works on a wide desktop image often falls off the edge of a phone. Untick to keep the desktop position exactly.', 'sinclairs-slider' ); ?></p>
				</td>
			</tr>
		</table>
	</div>
	<?php
}

function sbvis_box_buttons( $post ) {
	$slide = sbvis_slide( $post->ID );
	?>
	<div class="sbvis-box">
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Button', 'sinclairs-slider' ); ?></th>
				<td>
					<label><input type="checkbox" name="<?php echo esc_attr( sbvis_field_name( 'btn_on' ) ); ?>" value="1" <?php checked( $slide['btn_on'], 1 ); ?> /> <?php esc_html_e( 'Show a button on this slide', 'sinclairs-slider' ); ?></label>
					<p class="description"><?php esc_html_e( 'Buttons are per-slide — slide 1 can have one and slide 2 none.', 'sinclairs-slider' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sbvis-btn-label"><?php esc_html_e( 'Label &amp; link', 'sinclairs-slider' ); ?></label></th>
				<td>
					<input type="text" id="sbvis-btn-label" class="regular-text" name="<?php echo esc_attr( sbvis_field_name( 'btn_label' ) ); ?>" value="<?php echo esc_attr( $slide['btn_label'] ); ?>" placeholder="<?php esc_attr_e( 'View Our Services', 'sinclairs-slider' ); ?>" />
					<input type="url" class="regular-text" name="<?php echo esc_attr( sbvis_field_name( 'btn_url' ) ); ?>" value="<?php echo esc_attr( $slide['btn_url'] ); ?>" placeholder="https://" />
					<br />
					<label><input type="checkbox" name="<?php echo esc_attr( sbvis_field_name( 'btn_new' ) ); ?>" value="1" <?php checked( $slide['btn_new'], 1 ); ?> /> <?php esc_html_e( 'Open in a new tab', 'sinclairs-slider' ); ?></label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Style', 'sinclairs-slider' ); ?></th>
				<td>
					<select name="<?php echo esc_attr( sbvis_field_name( 'btn_style' ) ); ?>">
						<?php
						foreach ( array( 'solid' => __( 'Solid pill', 'sinclairs-slider' ), 'outline' => __( 'Outline', 'sinclairs-slider' ), 'link' => __( 'Text link', 'sinclairs-slider' ) ) as $value => $label ) {
							printf( '<option value="%s"%s>%s</option>', esc_attr( $value ), selected( $slide['btn_style'], $value, false ), esc_html( $label ) );
						}
						?>
					</select>
					<select name="<?php echo esc_attr( sbvis_field_name( 'btn_size' ) ); ?>">
						<?php
						foreach ( array( 'small' => __( 'Small', 'sinclairs-slider' ), 'medium' => __( 'Medium', 'sinclairs-slider' ), 'large' => __( 'Large', 'sinclairs-slider' ) ) as $value => $label ) {
							printf( '<option value="%s"%s>%s</option>', esc_attr( $value ), selected( $slide['btn_size'], $value, false ), esc_html( $label ) );
						}
						?>
					</select>
					<label class="sbvis-inline-label"><input type="checkbox" name="<?php echo esc_attr( sbvis_field_name( 'btn_custom' ) ); ?>" value="1" <?php checked( $slide['btn_custom'], 1 ); ?> /> <?php esc_html_e( 'Use a custom colour for this slide', 'sinclairs-slider' ); ?></label>
					<input type="color" name="<?php echo esc_attr( sbvis_field_name( 'btn_color' ) ); ?>" value="<?php echo esc_attr( $slide['btn_color'] ? $slide['btn_color'] : sbvis_setting( 'accent' ) ); ?>" />
					<input type="color" name="<?php echo esc_attr( sbvis_field_name( 'btn_text' ) ); ?>" value="<?php echo esc_attr( $slide['btn_text'] ); ?>" />
					<p class="description"><?php esc_html_e( 'Background colour, then label colour. Left unticked, the button follows the slider-wide button colour in Settings, so changing it there updates every slide at once.', 'sinclairs-slider' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Where it sits', 'sinclairs-slider' ); ?></th>
				<td>
					<label><input type="checkbox" name="<?php echo esc_attr( sbvis_field_name( 'btn_follow' ) ); ?>" value="1" <?php checked( $slide['btn_follow'], 1 ); ?> data-sbvis-toggle="sbvis-btn-free" /> <?php esc_html_e( 'Directly under the text (recommended)', 'sinclairs-slider' ); ?></label>
					<div id="sbvis-btn-free" class="sbvis-conditional"<?php echo $slide['btn_follow'] ? ' hidden' : ''; ?>>
						<p class="description"><?php esc_html_e( 'Position the button anywhere on the slide, independently of the heading:', 'sinclairs-slider' ); ?></p>
						<?php sbvis_anchor_grid( 'btn_anchor', $slide['btn_anchor'] ); ?>
						<p class="sbvis-nudge">
							<label><?php esc_html_e( 'Nudge across', 'sinclairs-slider' ); ?>
								<input type="number" name="<?php echo esc_attr( sbvis_field_name( 'btn_x' ) ); ?>" value="<?php echo esc_attr( $slide['btn_x'] ); ?>" min="-50" max="50" step="1" class="small-text" />%</label>
							<label><?php esc_html_e( 'Nudge down', 'sinclairs-slider' ); ?>
								<input type="number" name="<?php echo esc_attr( sbvis_field_name( 'btn_y' ) ); ?>" value="<?php echo esc_attr( $slide['btn_y'] ); ?>" min="-50" max="50" step="1" class="small-text" />%</label>
						</p>
					</div>
				</td>
			</tr>
		</table>

		<h3><?php esc_html_e( 'Second button (optional)', 'sinclairs-slider' ); ?></h3>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Show', 'sinclairs-slider' ); ?></th>
				<td><label><input type="checkbox" name="<?php echo esc_attr( sbvis_field_name( 'btn2_on' ) ); ?>" value="1" <?php checked( $slide['btn2_on'], 1 ); ?> /> <?php esc_html_e( 'Add a second button beside the first', 'sinclairs-slider' ); ?></label></td>
			</tr>
			<tr>
				<th scope="row"><label for="sbvis-btn2-label"><?php esc_html_e( 'Label &amp; link', 'sinclairs-slider' ); ?></label></th>
				<td>
					<input type="text" id="sbvis-btn2-label" class="regular-text" name="<?php echo esc_attr( sbvis_field_name( 'btn2_label' ) ); ?>" value="<?php echo esc_attr( $slide['btn2_label'] ); ?>" />
					<input type="url" class="regular-text" name="<?php echo esc_attr( sbvis_field_name( 'btn2_url' ) ); ?>" value="<?php echo esc_attr( $slide['btn2_url'] ); ?>" placeholder="https://" />
					<br />
					<label><input type="checkbox" name="<?php echo esc_attr( sbvis_field_name( 'btn2_new' ) ); ?>" value="1" <?php checked( $slide['btn2_new'], 1 ); ?> /> <?php esc_html_e( 'Open in a new tab', 'sinclairs-slider' ); ?></label>
					<select name="<?php echo esc_attr( sbvis_field_name( 'btn2_style' ) ); ?>">
						<?php
						foreach ( array( 'outline' => __( 'Outline', 'sinclairs-slider' ), 'solid' => __( 'Solid pill', 'sinclairs-slider' ), 'link' => __( 'Text link', 'sinclairs-slider' ) ) as $value => $label ) {
							printf( '<option value="%s"%s>%s</option>', esc_attr( $value ), selected( $slide['btn2_style'], $value, false ), esc_html( $label ) );
						}
						?>
					</select>
				</td>
			</tr>
		</table>
	</div>
	<?php
}

add_action( 'save_post_sbvi_slide', 'sbvis_save_slide', 10, 2 );
function sbvis_save_slide( $post_id, $post ) {
	if ( ! isset( $_POST['sbvis_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['sbvis_nonce'] ) ), 'sbvis_save_slide' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$raw = isset( $_POST['sbvis'] ) && is_array( $_POST['sbvis'] ) ? wp_unslash( $_POST['sbvis'] ) : array();

	// Checkboxes are absent when unticked, so they are handled outside
	// the main loop rather than falling back to their default.
	$checkboxes = array( 'mobile_stack', 'btn_on', 'btn_new', 'btn_follow', 'btn2_on', 'btn2_new', 'btn_custom' );

	foreach ( sbvis_slide_defaults() as $key => $default ) {
		if ( in_array( $key, $checkboxes, true ) ) {
			update_post_meta( $post_id, '_sbvis_' . $key, empty( $raw[ $key ] ) ? 0 : 1 );
			continue;
		}

		$value = isset( $raw[ $key ] ) ? $raw[ $key ] : $default;
		update_post_meta( $post_id, '_sbvis_' . $key, sbvis_sanitize_slide_field( $key, $value, $default ) );
	}

	// A colour input submits its value whether or not the client touched
	// it, so without this an untouched field would be stored as a
	// per-slide override and the slide would stop following the
	// slider-wide button colour.
	if ( empty( $raw['btn_custom'] ) ) {
		update_post_meta( $post_id, '_sbvis_btn_color', '' );
	}
}

function sbvis_sanitize_slide_field( $key, $value, $default ) {
	switch ( $key ) {
		case 'image_id':
			return absint( $value );

		case 'focal_x':
		case 'focal_y':
		case 'overlay':
		case 'text_width':
			return min( 100, max( 0, (float) $value ) );

		case 'text_x':
		case 'text_y':
		case 'btn_x':
		case 'btn_y':
			return min( 50, max( -50, (float) $value ) );

		case 'text_size':
		case 'text_size_m':
		case 'sub_size':
			return min( 8, max( 0.5, (float) $value ) );

		case 'overlay_color':
		case 'text_color':
		case 'btn_color':
		case 'btn_text':
			$hex = sanitize_hex_color( $value );
			return $hex ? $hex : $default;

		case 'text_anchor':
		case 'btn_anchor':
			return array_key_exists( $value, sbvis_anchors() ) ? $value : $default;

		case 'text_align':
			return in_array( $value, array( 'left', 'center', 'right' ), true ) ? $value : $default;

		case 'btn_style':
		case 'btn2_style':
			return in_array( $value, array( 'solid', 'outline', 'link' ), true ) ? $value : $default;

		case 'btn_size':
			return in_array( $value, array( 'small', 'medium', 'large' ), true ) ? $value : $default;

		case 'btn_url':
		case 'btn2_url':
			return esc_url_raw( $value );

		case 'heading':
		case 'subheading':
			return sanitize_textarea_field( $value );

		default:
			return sanitize_text_field( $value );
	}
}

add_action( 'admin_enqueue_scripts', 'sbvis_admin_assets' );
function sbvis_admin_assets( $hook ) {
	$screen = get_current_screen();

	if ( ! $screen || 'sbvi_slide' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style( 'sbvis-admin', SBVIS_URI . 'assets/css/admin.css', array(), sbvis_asset_version( 'assets/css/admin.css' ) );
	wp_enqueue_script( 'sbvis-admin', SBVIS_URI . 'assets/js/admin.js', array(), sbvis_asset_version( 'assets/js/admin.js' ), true );
	wp_localize_script( 'sbvis-admin', 'sbvisAdmin', array(
		'selectTitle'  => __( 'Choose a slide image', 'sinclairs-slider' ),
		'selectButton' => __( 'Use this image', 'sinclairs-slider' ),
	) );
}
