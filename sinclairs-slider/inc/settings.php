<?php
/**
 * Global slider settings — the things that belong to the slider as a
 * whole (transition, timing, navigation style) rather than to one
 * slide. Stored as a single option array so there is one row to read.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const SBVIS_OPTION = 'sbvis_settings';

function sbvis_default_settings() {
	return array(
		'effect'         => 'fade',
		'speed'          => 900,
		'autoplay'       => 1,
		'delay'          => 6000,
		'loop'           => 1,
		'pause_on_hover' => 1,
		'nav'            => 'thumbs',
		'width'          => 'full',
		'arrows'         => 1,
		'height_desktop' => 'ratio',
		'height_mobile'  => 'ratio',
		'height_px'      => 620,
		'height_px_m'    => 480,
		'font_override'  => '',
		'accent'         => '#0b5cab',
		// How every slide's text is positioned on phones. Per-slide
		// desktop positions are set individually, but a desktop layout
		// anchored to an edge usually falls off a narrow screen, so this
		// is one setting for the whole slider rather than something to
		// repeat on every slide.
		'mobile_position'   => 'auto',
		'mobile_anchor'     => 'middle-center',
		'mobile_nudge_x'    => 0,
		'mobile_nudge_y'    => 0,
	);
}

function sbvis_settings() {
	$saved = get_option( SBVIS_OPTION, array() );

	return wp_parse_args( is_array( $saved ) ? $saved : array(), sbvis_default_settings() );
}

function sbvis_setting( $key ) {
	$settings = sbvis_settings();

	return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
}

add_action( 'admin_menu', 'sbvis_settings_page' );
function sbvis_settings_page() {
	add_submenu_page(
		'edit.php?post_type=sbvi_slide',
		__( 'Slider Settings', 'sinclairs-slider' ),
		__( 'Settings', 'sinclairs-slider' ),
		'manage_options',
		'sbvis-settings',
		'sbvis_render_settings_page'
	);
}

add_action( 'admin_init', 'sbvis_register_settings' );
function sbvis_register_settings() {
	register_setting( 'sbvis_settings_group', SBVIS_OPTION, array(
		'sanitize_callback' => 'sbvis_sanitize_settings',
		'default'           => sbvis_default_settings(),
	) );
}

function sbvis_sanitize_settings( $input ) {
	$defaults = sbvis_default_settings();
	$out      = array();

	$effects = array( 'fade', 'slide', 'zoom', 'kenburns', 'none' );
	$out['effect'] = in_array( $input['effect'] ?? '', $effects, true ) ? $input['effect'] : $defaults['effect'];

	// Clamp rather than reject: a stray value should degrade to something
	// watchable, not break the slider.
	$out['speed'] = min( 3000, max( 0, absint( $input['speed'] ?? $defaults['speed'] ) ) );
	$out['delay'] = min( 30000, max( 1500, absint( $input['delay'] ?? $defaults['delay'] ) ) );

	foreach ( array( 'autoplay', 'loop', 'pause_on_hover', 'arrows' ) as $flag ) {
		$out[ $flag ] = empty( $input[ $flag ] ) ? 0 : 1;
	}

	$out['width'] = in_array( $input['width'] ?? '', array( 'full', 'contained' ), true ) ? $input['width'] : $defaults['width'];

	$navs = array( 'thumbs', 'dots', 'bars', 'none' );
	$out['nav'] = in_array( $input['nav'] ?? '', $navs, true ) ? $input['nav'] : $defaults['nav'];

	foreach ( array( 'height_desktop', 'height_mobile' ) as $key ) {
		$out[ $key ] = in_array( $input[ $key ] ?? '', array( 'ratio', 'fixed', 'viewport' ), true ) ? $input[ $key ] : $defaults[ $key ];
	}

	$out['height_px']   = min( 1200, max( 240, absint( $input['height_px'] ?? $defaults['height_px'] ) ) );
	$out['height_px_m'] = min( 1000, max( 200, absint( $input['height_px_m'] ?? $defaults['height_px_m'] ) ) );

	$out['font_override'] = sanitize_text_field( $input['font_override'] ?? '' );

	$accent = sanitize_hex_color( $input['accent'] ?? '' );
	$out['accent'] = $accent ? $accent : $defaults['accent'];

	$out['mobile_position'] = in_array( $input['mobile_position'] ?? '', array( 'auto', 'same', 'custom' ), true )
		? $input['mobile_position']
		: $defaults['mobile_position'];

	$out['mobile_anchor'] = array_key_exists( $input['mobile_anchor'] ?? '', sbvis_anchors() )
		? $input['mobile_anchor']
		: $defaults['mobile_anchor'];

	$out['mobile_nudge_x'] = min( 50, max( -50, (float) ( $input['mobile_nudge_x'] ?? $defaults['mobile_nudge_x'] ) ) );
	$out['mobile_nudge_y'] = min( 50, max( -50, (float) ( $input['mobile_nudge_y'] ?? $defaults['mobile_nudge_y'] ) ) );

	return $out;
}

function sbvis_render_settings_page() {
	$s = sbvis_settings();
	?>
	<div class="wrap sbvis-settings">
		<h1><?php esc_html_e( 'Slider Settings', 'sinclairs-slider' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'These apply to the slider as a whole. Images, headings, buttons and their positions are set on each individual slide.', 'sinclairs-slider' ); ?>
		</p>

		<form method="post" action="options.php">
			<?php settings_fields( 'sbvis_settings_group' ); ?>

			<h2 class="title"><?php esc_html_e( 'Transition', 'sinclairs-slider' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="sbvis-effect"><?php esc_html_e( 'Effect', 'sinclairs-slider' ); ?></label></th>
					<td>
						<select id="sbvis-effect" name="<?php echo esc_attr( SBVIS_OPTION ); ?>[effect]">
							<?php
							$effects = array(
								'fade'     => __( 'Fade (crossfade between slides)', 'sinclairs-slider' ),
								'slide'    => __( 'Slide (push horizontally)', 'sinclairs-slider' ),
								'zoom'     => __( 'Zoom fade (fade with a gentle scale)', 'sinclairs-slider' ),
								'kenburns' => __( 'Ken Burns (slow drift while the slide is on screen)', 'sinclairs-slider' ),
								'none'     => __( 'None (instant cut)', 'sinclairs-slider' ),
							);
							foreach ( $effects as $value => $label ) {
								printf( '<option value="%s"%s>%s</option>', esc_attr( $value ), selected( $s['effect'], $value, false ), esc_html( $label ) );
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sbvis-speed"><?php esc_html_e( 'Transition speed', 'sinclairs-slider' ); ?></label></th>
					<td>
						<input type="number" id="sbvis-speed" name="<?php echo esc_attr( SBVIS_OPTION ); ?>[speed]" value="<?php echo esc_attr( $s['speed'] ); ?>" min="0" max="3000" step="50" class="small-text" />
						<?php esc_html_e( 'milliseconds', 'sinclairs-slider' ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Autoplay', 'sinclairs-slider' ); ?></th>
					<td>
						<label><input type="checkbox" name="<?php echo esc_attr( SBVIS_OPTION ); ?>[autoplay]" value="1" <?php checked( $s['autoplay'], 1 ); ?> /> <?php esc_html_e( 'Advance slides automatically', 'sinclairs-slider' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( SBVIS_OPTION ); ?>[loop]" value="1" <?php checked( $s['loop'], 1 ); ?> /> <?php esc_html_e( 'Loop back to the first slide', 'sinclairs-slider' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( SBVIS_OPTION ); ?>[pause_on_hover]" value="1" <?php checked( $s['pause_on_hover'], 1 ); ?> /> <?php esc_html_e( 'Pause while the visitor hovers the slider', 'sinclairs-slider' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sbvis-delay"><?php esc_html_e( 'Time on each slide', 'sinclairs-slider' ); ?></label></th>
					<td>
						<input type="number" id="sbvis-delay" name="<?php echo esc_attr( SBVIS_OPTION ); ?>[delay]" value="<?php echo esc_attr( $s['delay'] ); ?>" min="1500" max="30000" step="500" class="small-text" />
						<?php esc_html_e( 'milliseconds (6000 = 6 seconds)', 'sinclairs-slider' ); ?>
					</td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Navigation', 'sinclairs-slider' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Style', 'sinclairs-slider' ); ?></th>
					<td>
						<?php
						$navs = array(
							'thumbs' => __( 'Thumbnails of each slide', 'sinclairs-slider' ),
							'dots'   => __( 'Dots', 'sinclairs-slider' ),
							'bars'   => __( 'Progress bars', 'sinclairs-slider' ),
							'none'   => __( 'No navigation', 'sinclairs-slider' ),
						);
						foreach ( $navs as $value => $label ) {
							printf(
								'<label style="margin-right:18px;"><input type="radio" name="%s[nav]" value="%s"%s /> %s</label>',
								esc_attr( SBVIS_OPTION ),
								esc_attr( $value ),
								checked( $s['nav'], $value, false ),
								esc_html( $label )
							);
						}
						?>
						<p class="description"><?php esc_html_e( 'The navigation always sits in a band below the images, never on top of them.', 'sinclairs-slider' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Arrows', 'sinclairs-slider' ); ?></th>
					<td>
						<label><input type="checkbox" name="<?php echo esc_attr( SBVIS_OPTION ); ?>[arrows]" value="1" <?php checked( $s['arrows'], 1 ); ?> /> <?php esc_html_e( 'Show previous / next arrows beside the navigation', 'sinclairs-slider' ); ?></label>
					</td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Size', 'sinclairs-slider' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Width', 'sinclairs-slider' ); ?></th>
					<td>
						<label style="margin-right:18px;"><input type="radio" name="<?php echo esc_attr( SBVIS_OPTION ); ?>[width]" value="full" <?php checked( $s['width'], 'full' ); ?> /> <?php esc_html_e( 'Full width — edge to edge, ignoring the page container', 'sinclairs-slider' ); ?></label>
						<label><input type="radio" name="<?php echo esc_attr( SBVIS_OPTION ); ?>[width]" value="contained" <?php checked( $s['width'], 'contained' ); ?> /> <?php esc_html_e( 'Contained — stay inside the page content width', 'sinclairs-slider' ); ?></label>
						<p class="description"><?php esc_html_e( 'A hero normally wants full width. Override per shortcode with width="contained" if one page needs it boxed.', 'sinclairs-slider' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sbvis-hd"><?php esc_html_e( 'Desktop height', 'sinclairs-slider' ); ?></label></th>
					<td>
						<select id="sbvis-hd" name="<?php echo esc_attr( SBVIS_OPTION ); ?>[height_desktop]">
							<option value="ratio" <?php selected( $s['height_desktop'], 'ratio' ); ?>><?php esc_html_e( 'Full 16:9 — show the whole 1920×1080 image', 'sinclairs-slider' ); ?></option>
							<option value="fixed" <?php selected( $s['height_desktop'], 'fixed' ); ?>><?php esc_html_e( 'Fixed height — crop to the focal point', 'sinclairs-slider' ); ?></option>
							<option value="viewport" <?php selected( $s['height_desktop'], 'viewport' ); ?>><?php esc_html_e( 'Full screen height', 'sinclairs-slider' ); ?></option>
						</select>
						<input type="number" name="<?php echo esc_attr( SBVIS_OPTION ); ?>[height_px]" value="<?php echo esc_attr( $s['height_px'] ); ?>" min="240" max="1200" step="10" class="small-text" /> px
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sbvis-hm"><?php esc_html_e( 'Mobile height', 'sinclairs-slider' ); ?></label></th>
					<td>
						<select id="sbvis-hm" name="<?php echo esc_attr( SBVIS_OPTION ); ?>[height_mobile]">
							<option value="ratio" <?php selected( $s['height_mobile'], 'ratio' ); ?>><?php esc_html_e( 'Full 16:9', 'sinclairs-slider' ); ?></option>
							<option value="fixed" <?php selected( $s['height_mobile'], 'fixed' ); ?>><?php esc_html_e( 'Fixed height — crop to the focal point', 'sinclairs-slider' ); ?></option>
							<option value="viewport" <?php selected( $s['height_mobile'], 'viewport' ); ?>><?php esc_html_e( 'Full screen height', 'sinclairs-slider' ); ?></option>
						</select>
						<input type="number" name="<?php echo esc_attr( SBVIS_OPTION ); ?>[height_px_m]" value="<?php echo esc_attr( $s['height_px_m'] ); ?>" min="200" max="1000" step="10" class="small-text" /> px
						<p class="description"><?php esc_html_e( 'A 16:9 image is very short on a phone. "Fixed height" plus a focal point usually reads far better.', 'sinclairs-slider' ); ?></p>
					</td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Text on phones', 'sinclairs-slider' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Position', 'sinclairs-slider' ); ?></th>
					<td>
						<?php
						$modes = array(
							'auto'   => __( 'Re-centre automatically (recommended)', 'sinclairs-slider' ),
							'same'   => __( 'Keep the exact desktop position', 'sinclairs-slider' ),
							'custom' => __( 'Use a different position on phones', 'sinclairs-slider' ),
						);
						foreach ( $modes as $value => $label ) {
							printf(
								'<label style="display:block;margin-bottom:6px;"><input type="radio" name="%s[mobile_position]" value="%s"%s data-sbvis-mobile-mode /> %s</label>',
								esc_attr( SBVIS_OPTION ),
								esc_attr( $value ),
								checked( $s['mobile_position'], $value, false ),
								esc_html( $label )
							);
						}
						?>
						<p class="description"><?php esc_html_e( 'Applies to every slide. A desktop position anchored to an edge often falls off a narrow screen, so text re-centres there by default.', 'sinclairs-slider' ); ?></p>

						<div id="sbvis-mobile-custom" class="sbvis-conditional"<?php echo 'custom' !== $s['mobile_position'] ? ' hidden' : ''; ?>>
							<?php sbvis_anchor_grid_field( SBVIS_OPTION . '[mobile_anchor]', $s['mobile_anchor'] ); ?>
							<p class="sbvis-nudge">
								<label><?php esc_html_e( 'Nudge across', 'sinclairs-slider' ); ?>
									<input type="number" name="<?php echo esc_attr( SBVIS_OPTION ); ?>[mobile_nudge_x]" value="<?php echo esc_attr( $s['mobile_nudge_x'] ); ?>" min="-50" max="50" step="1" class="small-text" />%</label>
								<label><?php esc_html_e( 'Nudge down', 'sinclairs-slider' ); ?>
									<input type="number" name="<?php echo esc_attr( SBVIS_OPTION ); ?>[mobile_nudge_y]" value="<?php echo esc_attr( $s['mobile_nudge_y'] ); ?>" min="-50" max="50" step="1" class="small-text" />%</label>
							</p>
						</div>
					</td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Appearance', 'sinclairs-slider' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="sbvis-accent"><?php esc_html_e( 'Button colour', 'sinclairs-slider' ); ?></label></th>
					<td>
						<input type="color" id="sbvis-accent" name="<?php echo esc_attr( SBVIS_OPTION ); ?>[accent]" value="<?php echo esc_attr( $s['accent'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Default for every slide. Any slide can override it.', 'sinclairs-slider' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sbvis-font"><?php esc_html_e( 'Font', 'sinclairs-slider' ); ?></label></th>
					<td>
						<input type="text" id="sbvis-font" class="regular-text" name="<?php echo esc_attr( SBVIS_OPTION ); ?>[font_override]" value="<?php echo esc_attr( $s['font_override'] ); ?>" placeholder="<?php esc_attr_e( 'Leave blank to use the theme font', 'sinclairs-slider' ); ?>" />
						<p class="description"><?php esc_html_e( 'Left blank the slider inherits whatever font the theme already loads, so it matches the rest of the site. Only fill this in with a font family the site already loads — the plugin will not fetch one for you.', 'sinclairs-slider' ); ?></p>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>

		<h2><?php esc_html_e( 'Putting the slider on a page', 'sinclairs-slider' ); ?></h2>
		<p><?php esc_html_e( 'Paste this shortcode wherever the old slider used to be:', 'sinclairs-slider' ); ?></p>
		<p><code>[sinclairs_slider]</code></p>
		<p><?php esc_html_e( 'Or, to show only certain slides in a set order:', 'sinclairs-slider' ); ?> <code>[sinclairs_slider ids="12,8,3"]</code></p>
	</div>
	<?php
}
