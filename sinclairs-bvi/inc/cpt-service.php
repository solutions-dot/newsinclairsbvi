<?php
/**
 * "service" CPT — the 8 practice-area pages. One query (sbvi_get_services)
 * feeds the mega-menu, the home "Choose a practice area" list, the
 * Services hub and the footer, so editing a service here updates all of
 * them at once.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sbvi_register_service_cpt() {
	register_post_type( 'service', array(
		'labels' => array(
			'name'               => __( 'Services', 'sinclairs-bvi' ),
			'singular_name'      => __( 'Service', 'sinclairs-bvi' ),
			'add_new_item'       => __( 'Add Practice Area', 'sinclairs-bvi' ),
			'edit_item'          => __( 'Edit Practice Area', 'sinclairs-bvi' ),
			'all_items'          => __( 'Practice Areas', 'sinclairs-bvi' ),
			'menu_name'          => __( 'Practice Areas', 'sinclairs-bvi' ),
			'featured_image'     => __( 'Photo (used as the page banner and the sticky page photo)', 'sinclairs-bvi' ),
		),
		'public'        => true,
		'menu_icon'     => 'dashicons-portfolio',
		'menu_position' => 20,
		'has_archive'   => false,
		'rewrite'       => array( 'slug' => 'services', 'with_front' => false ),
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
		'show_in_rest'  => true,
	) );
}
add_action( 'init', 'sbvi_register_service_cpt' );

function sbvi_service_faq_heading_options() {
	return array(
		''         => __( 'Use site default', 'sinclairs-bvi' ),
		'merged'   => __( 'Merged: “Information & FAQs”', 'sinclairs-bvi' ),
		'separate' => __( 'Separate: “Frequently Asked Questions”', 'sinclairs-bvi' ),
	);
}

function sbvi_service_settings_box() {
	add_meta_box( 'sbvi-service-settings', __( 'Practice Area Settings', 'sinclairs-bvi' ), 'sbvi_render_service_settings_box', 'service', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'sbvi_service_settings_box' );

function sbvi_render_service_settings_box( $post ) {
	wp_nonce_field( 'sbvi_service_meta', 'sbvi_service_meta_nonce' );
	$nutshell    = get_post_meta( $post->ID, '_sbvi_nutshell', true );
	$faq_heading = get_post_meta( $post->ID, '_sbvi_faq_heading', true );
	?>
	<p>
		<label for="sbvi_nutshell"><strong><?php esc_html_e( 'Nutshell summary', 'sinclairs-bvi' ); ?></strong><br>
		<span class="description"><?php esc_html_e( 'One short paragraph. Shown in the header mega-menu and in the "Choose a practice area" panel on the home page.', 'sinclairs-bvi' ); ?></span></label><br>
		<textarea id="sbvi_nutshell" name="sbvi_nutshell" rows="3" style="width:100%;max-width:700px;"><?php echo esc_textarea( $nutshell ); ?></textarea>
	</p>
	<p>
		<label for="sbvi_faq_heading"><strong><?php esc_html_e( 'FAQ section heading', 'sinclairs-bvi' ); ?></strong></label><br>
		<select id="sbvi_faq_heading" name="sbvi_faq_heading">
			<?php foreach ( sbvi_service_faq_heading_options() as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $faq_heading, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select><br>
		<span class="description"><?php esc_html_e( 'The site default is set in Appearance → Customize → Theme Options.', 'sinclairs-bvi' ); ?></span>
	</p>
	<p class="description">
		<?php esc_html_e( 'Page title = H1. Excerpt (below) = the one-line dek under the H1. Body editor = full page copy. Featured image = banner + sticky photo. "Order" in Page Attributes controls the 01–08 numbering, menu/footer order and next-practice-area links.', 'sinclairs-bvi' ); ?>
	</p>
	<?php
}

function sbvi_save_service_meta( $post_id ) {
	if ( ! isset( $_POST['sbvi_service_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['sbvi_service_meta_nonce'] ), 'sbvi_service_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['sbvi_nutshell'] ) ) {
		update_post_meta( $post_id, '_sbvi_nutshell', sanitize_textarea_field( wp_unslash( $_POST['sbvi_nutshell'] ) ) );
	}

	if ( isset( $_POST['sbvi_faq_heading'] ) && array_key_exists( wp_unslash( $_POST['sbvi_faq_heading'] ), sbvi_service_faq_heading_options() ) ) {
		update_post_meta( $post_id, '_sbvi_faq_heading', sanitize_text_field( wp_unslash( $_POST['sbvi_faq_heading'] ) ) );
	}
}
add_action( 'save_post_service', 'sbvi_save_service_meta' );

/**
 * FAQ repeater: question / answer pairs, rendered as an accordion on the
 * single service template.
 */
function sbvi_register_service_faq_repeater() {
	new SBVI_Repeater_Field( array(
		'post_type' => 'service',
		'meta_key'  => '_sbvi_faqs',
		'box_title' => __( 'Information & FAQs', 'sinclairs-bvi' ),
		'row_label' => __( 'Question', 'sinclairs-bvi' ),
		'context'   => 'normal',
		'fields'    => array(
			'question' => array( 'label' => __( 'Question', 'sinclairs-bvi' ), 'type' => 'text' ),
			'answer'   => array( 'label' => __( 'Answer', 'sinclairs-bvi' ), 'type' => 'textarea' ),
		),
	) );
}
add_action( 'init', 'sbvi_register_service_faq_repeater' );

/**
 * Nutshell sub-service repeater: the granular rows shown on the Services
 * hub page. Each row belongs to one of the 3 hub groups (independent of
 * which group the parent service itself would intuitively sit in — e.g.
 * "Property & Private Client" contributes rows to both the "Corporate &
 * Commercial Law" group (Commercial Property) and the "Private Client Law"
 * group (Residential Conveyancing, Wills) — matching the approved design).
 * All rows link to this service's own page.
 */
function sbvi_register_service_nutshell_repeater() {
	$group_options = array();
	foreach ( sbvi_nutshell_groups() as $slug => $label ) {
		$group_options[ $slug ] = $label;
	}

	new SBVI_Repeater_Field( array(
		'post_type' => 'service',
		'meta_key'  => '_sbvi_nutshell_items',
		'box_title' => __( 'Services Hub — Sub-service Rows', 'sinclairs-bvi' ),
		'row_label' => __( 'Sub-service', 'sinclairs-bvi' ),
		'context'   => 'normal',
		'fields'    => array(
			'group'       => array( 'label' => __( 'Hub group', 'sinclairs-bvi' ), 'type' => 'select', 'options' => $group_options ),
			'label'       => array( 'label' => __( 'Sub-service name', 'sinclairs-bvi' ), 'type' => 'text' ),
			'description' => array( 'label' => __( 'One-line description', 'sinclairs-bvi' ), 'type' => 'textarea' ),
		),
	) );
}
add_action( 'init', 'sbvi_register_service_nutshell_repeater' );
