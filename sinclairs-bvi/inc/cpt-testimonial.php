<?php
/**
 * "testimonial" CPT — rotator on the home page. Title = client name,
 * editor = the quote itself, one small meta field for "matter".
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sbvi_register_testimonial_cpt() {
	register_post_type( 'testimonial', array(
		'labels' => array(
			'name'          => __( 'Testimonials', 'sinclairs-bvi' ),
			'singular_name' => __( 'Testimonial', 'sinclairs-bvi' ),
			'add_new_item'  => __( 'Add Testimonial', 'sinclairs-bvi' ),
			'edit_item'     => __( 'Edit Testimonial', 'sinclairs-bvi' ),
			'all_items'     => __( 'Testimonials', 'sinclairs-bvi' ),
			'menu_name'     => __( 'Testimonials', 'sinclairs-bvi' ),
			'title_placeholder' => __( 'Client name', 'sinclairs-bvi' ),
		),
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_icon'           => 'dashicons-format-quote',
		'menu_position'       => 22,
		'has_archive'         => false,
		'supports'            => array( 'title', 'editor', 'page-attributes' ),
		'show_in_rest'        => true,
	) );
}
add_action( 'init', 'sbvi_register_testimonial_cpt' );

function sbvi_testimonial_meta_box() {
	add_meta_box( 'sbvi-testimonial-matter', __( 'Matter', 'sinclairs-bvi' ), 'sbvi_render_testimonial_meta_box', 'testimonial', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'sbvi_testimonial_meta_box' );

function sbvi_render_testimonial_meta_box( $post ) {
	wp_nonce_field( 'sbvi_testimonial_meta', 'sbvi_testimonial_meta_nonce' );
	$matter = get_post_meta( $post->ID, '_sbvi_matter', true );
	?>
	<p>
		<label for="sbvi_matter"><?php esc_html_e( 'e.g. "Fund formation" — shown after the client name.', 'sinclairs-bvi' ); ?></label><br>
		<input type="text" id="sbvi_matter" name="sbvi_matter" class="widefat" value="<?php echo esc_attr( $matter ); ?>">
	</p>
	<?php
}

function sbvi_save_testimonial_meta( $post_id ) {
	if ( ! isset( $_POST['sbvi_testimonial_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['sbvi_testimonial_meta_nonce'] ), 'sbvi_testimonial_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['sbvi_matter'] ) ) {
		update_post_meta( $post_id, '_sbvi_matter', sanitize_text_field( wp_unslash( $_POST['sbvi_matter'] ) ) );
	}
}
add_action( 'save_post_testimonial', 'sbvi_save_testimonial_meta' );
