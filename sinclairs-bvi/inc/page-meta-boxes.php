<?php
/**
 * Meta boxes for ordinary Pages: the Home page's structured text blocks
 * (only shown once a page is set as the site's front page) and the
 * reusable "secondary image" slot (About's sticky portrait / Home's
 * articles-teaser photo).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sbvi_home_fields() {
	return array(
		'hero'     => array(
			'label'  => __( 'Hero', 'sinclairs-bvi' ),
			'fields' => array(
				'sbvi_hero_kicker'     => array( 'label' => __( 'Kicker line', 'sinclairs-bvi' ), 'type' => 'text', 'default' => 'Sinclairs (BVI) · Attorneys-at-Law · Road Town · Tortola' ),
				'sbvi_hero_heading'    => array( 'label' => __( 'Headline (basic HTML like <em> allowed)', 'sinclairs-bvi' ), 'type' => 'text', 'default' => 'Your trusted legal partner in the <em>British Virgin Islands</em>' ),
				'sbvi_hero_subheading' => array( 'label' => __( 'Sub-headline', 'sinclairs-bvi' ), 'type' => 'textarea', 'default' => 'Clear, practical and commercially focused advice for BVI legal entities, financial institutions, trust and corporate services providers, and individuals — in the BVI and internationally.' ),
				'sbvi_hero_cta1'       => array( 'label' => __( 'Button 1 label (links to Our Services)', 'sinclairs-bvi' ), 'type' => 'text', 'default' => 'Our services' ),
				'sbvi_hero_cta2'       => array( 'label' => __( 'Button 2 label (links to Contact)', 'sinclairs-bvi' ), 'type' => 'text', 'default' => 'Speak to us' ),
			),
		),
		'intro'    => array(
			'label'  => __( 'Intro statement', 'sinclairs-bvi' ),
			'fields' => array(
				'sbvi_intro_kicker'  => array( 'label' => __( 'Kicker line', 'sinclairs-bvi' ), 'type' => 'text', 'default' => 'Every matter is different' ),
				'sbvi_intro_heading' => array( 'label' => __( 'Statement', 'sinclairs-bvi' ), 'type' => 'textarea', 'default' => "We take the time to understand our clients' objectives and give advice that answers their particular circumstances." ),
			),
		),
		'articles' => array(
			'label'  => __( 'Our Articles teaser', 'sinclairs-bvi' ),
			'fields' => array(
				'sbvi_articles_kicker'  => array( 'label' => __( 'Kicker line', 'sinclairs-bvi' ), 'type' => 'text', 'default' => 'Our Articles' ),
				'sbvi_articles_heading' => array( 'label' => __( 'Heading', 'sinclairs-bvi' ), 'type' => 'text', 'default' => 'Notes on BVI law, written plainly' ),
				'sbvi_articles_body'    => array( 'label' => __( 'Body', 'sinclairs-bvi' ), 'type' => 'textarea', 'default' => 'Our own posts on legislative change, regulatory practice and what it means for entities doing business through the British Virgin Islands.' ),
			),
		),
		'closing'  => array(
			'label'  => __( 'Closing call to action', 'sinclairs-bvi' ),
			'fields' => array(
				'sbvi_closing_heading' => array( 'label' => __( 'Heading (basic HTML like <em> allowed)', 'sinclairs-bvi' ), 'type' => 'text', 'default' => 'How may we <em>help you?</em>' ),
				'sbvi_closing_body'    => array( 'label' => __( 'Body', 'sinclairs-bvi' ), 'type' => 'textarea', 'default' => 'Tell us what you are working on. We will tell you plainly what is involved, what it will take and what it will cost.' ),
				'sbvi_closing_cta'     => array( 'label' => __( 'Button label (links to Contact)', 'sinclairs-bvi' ), 'type' => 'text', 'default' => 'Contact Sinclairs (BVI)' ),
			),
		),
	);
}

function sbvi_add_page_meta_boxes( $post ) {
	$is_front_page = ( (int) get_option( 'page_on_front' ) === (int) $post->ID );
	$template      = get_page_template_slug( $post->ID );

	if ( $is_front_page ) {
		add_meta_box( 'sbvi-home-content', __( 'Home Page Content', 'sinclairs-bvi' ), 'sbvi_render_home_meta_box', 'page', 'normal', 'high' );
		add_meta_box( 'sbvi-secondary-image', __( 'Articles Teaser Photo', 'sinclairs-bvi' ), 'sbvi_render_secondary_image_box', 'page', 'side', 'default' );
	} elseif ( 'page-about.php' === $template ) {
		add_meta_box( 'sbvi-secondary-image', __( 'Portrait Photo (sticky, beside the body text)', 'sinclairs-bvi' ), 'sbvi_render_secondary_image_box', 'page', 'side', 'default' );
	}

	if ( array_key_exists( $template, sbvi_page_headline_defaults() ) ) {
		add_meta_box( 'sbvi-page-headline', __( 'Page Header', 'sinclairs-bvi' ), 'sbvi_render_page_headline_box', 'page', 'normal', 'high' );
	}
}
add_action( 'add_meta_boxes_page', 'sbvi_add_page_meta_boxes' );

/**
 * About / Services hub / Articles hub / Contact all show a short kicker
 * (the page Title) above a larger, distinct H1 headline. Title stays the
 * plain admin/browser-tab label; this field carries the creative headline.
 */
function sbvi_page_headline_defaults() {
	return array(
		'page-about.php'    => 'Your trusted legal partner in the British Virgin Islands',
		'page-services.php' => 'Our services and expertise include',
		'page-articles.php' => 'Notes on BVI law, written plainly',
		'page-contact.php'  => 'How may we <em>help you?</em>',
	);
}

function sbvi_render_page_headline_box( $post ) {
	wp_nonce_field( 'sbvi_page_headline', 'sbvi_page_headline_nonce' );
	$template = get_page_template_slug( $post->ID );
	$defaults = sbvi_page_headline_defaults();
	$default  = isset( $defaults[ $template ] ) ? $defaults[ $template ] : '';
	$value    = get_post_meta( $post->ID, '_sbvi_headline', true );
	?>
	<p>
		<label for="sbvi_headline"><strong><?php esc_html_e( 'Headline (H1). Basic HTML like <em> allowed.', 'sinclairs-bvi' ); ?></strong></label><br>
		<input type="text" id="sbvi_headline" name="sbvi_headline" class="large-text" placeholder="<?php echo esc_attr( $default ); ?>" value="<?php echo esc_attr( $value ); ?>">
	</p>
	<p class="description"><?php esc_html_e( 'The page Title above is shown as the small label above this headline, and in the browser tab. The Excerpt box (enable it via Screen Options if hidden) is the one-line intro sentence under the headline, where this page uses one.', 'sinclairs-bvi' ); ?></p>
	<?php
}

function sbvi_save_page_headline( $post_id ) {
	if ( ! isset( $_POST['sbvi_page_headline_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['sbvi_page_headline_nonce'] ), 'sbvi_page_headline' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['sbvi_headline'] ) ) {
		update_post_meta( $post_id, '_sbvi_headline', wp_kses_post( wp_unslash( $_POST['sbvi_headline'] ) ) );
	}
}
add_action( 'save_post_page', 'sbvi_save_page_headline' );

function sbvi_headline( $post_id ) {
	$value = get_post_meta( $post_id, '_sbvi_headline', true );
	if ( '' !== trim( (string) $value ) ) {
		return $value;
	}
	$template = get_page_template_slug( $post_id );
	$defaults = sbvi_page_headline_defaults();
	return isset( $defaults[ $template ] ) ? $defaults[ $template ] : get_the_title( $post_id );
}

function sbvi_render_home_meta_box( $post ) {
	wp_nonce_field( 'sbvi_home_meta', 'sbvi_home_meta_nonce' );
	foreach ( sbvi_home_fields() as $group ) {
		echo '<h4 style="margin-bottom:6px">' . esc_html( $group['label'] ) . '</h4>';
		echo '<table class="form-table" role="presentation"><tbody>';
		foreach ( $group['fields'] as $key => $field ) {
			$value = get_post_meta( $post->ID, $key, true );
			echo '<tr><th scope="row"><label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';
			if ( 'textarea' === $field['type'] ) {
				echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" rows="2" class="large-text" placeholder="' . esc_attr( $field['default'] ) . '">' . esc_textarea( $value ) . '</textarea>';
			} else {
				echo '<input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" class="large-text" placeholder="' . esc_attr( $field['default'] ) . '" value="' . esc_attr( $value ) . '">';
			}
			echo '</td></tr>';
		}
		echo '</tbody></table>';
	}
	echo '<p class="description">' . esc_html__( 'Leave a field blank to use the approved design copy shown as its placeholder.', 'sinclairs-bvi' ) . '</p>';
}

function sbvi_save_home_meta( $post_id ) {
	if ( ! isset( $_POST['sbvi_home_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['sbvi_home_meta_nonce'] ), 'sbvi_home_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( sbvi_home_fields() as $group ) {
		foreach ( $group['fields'] as $key => $field ) {
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}
			$raw = wp_unslash( $_POST[ $key ] );
			$val = ( 'textarea' === $field['type'] ) ? sanitize_textarea_field( $raw ) : wp_kses_post( $raw );
			update_post_meta( $post_id, $key, $val );
		}
	}
}
add_action( 'save_post_page', 'sbvi_save_home_meta' );

/**
 * Front-end getter: stored value, or the approved design copy if blank.
 */
function sbvi_home_field( $post_id, $key ) {
	$value = get_post_meta( $post_id, $key, true );
	if ( '' !== trim( (string) $value ) ) {
		return $value;
	}
	foreach ( sbvi_home_fields() as $group ) {
		if ( isset( $group['fields'][ $key ] ) ) {
			return $group['fields'][ $key ]['default'];
		}
	}
	return '';
}

function sbvi_render_secondary_image_box( $post ) {
	wp_nonce_field( 'sbvi_secondary_image', 'sbvi_secondary_image_nonce' );
	$id = get_post_meta( $post->ID, '_sbvi_secondary_image', true );
	sbvi_render_image_picker( '_sbvi_secondary_image', $id );
}

function sbvi_save_secondary_image( $post_id ) {
	if ( ! isset( $_POST['sbvi_secondary_image_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['sbvi_secondary_image_nonce'] ), 'sbvi_secondary_image' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['_sbvi_secondary_image'] ) ) {
		update_post_meta( $post_id, '_sbvi_secondary_image', absint( $_POST['_sbvi_secondary_image'] ) );
	}
}
add_action( 'save_post_page', 'sbvi_save_secondary_image' );

/**
 * Generic media-library image picker: a hidden input holding the
 * attachment ID, a live preview, and Select/Remove buttons wired up by
 * assets/js/admin-repeater.js (wp.media). Reused wherever the theme needs
 * a single image field outside of the built-in Featured Image box.
 */
function sbvi_render_image_picker( $field_name, $attachment_id ) {
	$attachment_id = (int) $attachment_id;
	?>
	<div class="sbvi-image-picker" data-target="<?php echo esc_attr( $field_name ); ?>">
		<div class="sbvi-image-picker-preview">
			<?php if ( $attachment_id ) {
				echo wp_get_attachment_image( $attachment_id, 'medium' );
			} ?>
		</div>
		<input type="hidden" class="sbvi-image-picker-input" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $attachment_id ); ?>">
		<p>
			<button type="button" class="button sbvi-image-picker-select"><?php esc_html_e( 'Select Image', 'sinclairs-bvi' ); ?></button>
			<button type="button" class="button sbvi-image-picker-remove" <?php echo $attachment_id ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Remove', 'sinclairs-bvi' ); ?></button>
		</p>
	</div>
	<?php
}
