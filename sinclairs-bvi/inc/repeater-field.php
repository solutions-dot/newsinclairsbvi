<?php
/**
 * Small reusable repeater meta box: powers the FAQ list and the "nutshell"
 * sub-service list on the Service CPT. No ACF dependency — plain post meta
 * (an indexed array of associative rows), plain vanilla-JS row cloning.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SBVI_Repeater_Field {

	public $post_type;
	public $meta_key;
	public $box_title;
	public $box_context;
	public $fields;
	public $row_label;

	/**
	 * @param array $args {
	 *   post_type, meta_key, box_title, row_label, context, fields => [
	 *     field_key => [ 'label' => '', 'type' => 'text|textarea|select', 'options' => [ value => label ] ]
	 *   ]
	 * }
	 */
	public function __construct( $args ) {
		$this->post_type   = $args['post_type'];
		$this->meta_key    = $args['meta_key'];
		$this->box_title   = $args['box_title'];
		$this->row_label   = isset( $args['row_label'] ) ? $args['row_label'] : __( 'Row', 'sinclairs-bvi' );
		$this->box_context = isset( $args['context'] ) ? $args['context'] : 'normal';
		$this->fields      = $args['fields'];

		add_action( 'add_meta_boxes', array( $this, 'add_box' ) );
		add_action( 'save_post_' . $this->post_type, array( $this, 'save' ) );
	}

	public function add_box() {
		add_meta_box(
			'sbvi-repeater-' . $this->meta_key,
			$this->box_title,
			array( $this, 'render' ),
			$this->post_type,
			$this->box_context,
			'default'
		);
	}

	public static function get_rows( $post_id, $meta_key ) {
		$rows = get_post_meta( $post_id, $meta_key, true );
		return is_array( $rows ) ? array_values( $rows ) : array();
	}

	protected function render_row_fields( $index_placeholder, $row ) {
		foreach ( $this->fields as $field_key => $field ) {
			$name  = sprintf( '%s[%s][%s]', $this->meta_key, $index_placeholder, $field_key );
			$value = isset( $row[ $field_key ] ) ? $row[ $field_key ] : '';
			echo '<div class="sbvi-repeater-field">';
			echo '<label>' . esc_html( $field['label'] ) . '</label>';
			if ( 'textarea' === $field['type'] ) {
				echo '<textarea name="' . esc_attr( $name ) . '" rows="3">' . esc_textarea( $value ) . '</textarea>';
			} elseif ( 'select' === $field['type'] ) {
				echo '<select name="' . esc_attr( $name ) . '">';
				foreach ( $field['options'] as $opt_value => $opt_label ) {
					echo '<option value="' . esc_attr( $opt_value ) . '" ' . selected( $value, $opt_value, false ) . '>' . esc_html( $opt_label ) . '</option>';
				}
				echo '</select>';
			} else {
				echo '<input type="text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
			}
			echo '</div>';
		}
	}

	public function render( $post ) {
		wp_nonce_field( 'sbvi_repeater_' . $this->meta_key, 'sbvi_repeater_nonce_' . $this->meta_key );
		$rows = self::get_rows( $post->ID, $this->meta_key );
		?>
		<div class="sbvi-repeater" data-meta-key="<?php echo esc_attr( $this->meta_key ); ?>" data-row-label="<?php echo esc_attr( $this->row_label ); ?>">
			<div class="sbvi-repeater-rows">
				<?php if ( $rows ) : ?>
					<?php foreach ( $rows as $i => $row ) : ?>
						<div class="sbvi-repeater-row">
							<div class="sbvi-repeater-row-head">
								<span class="sbvi-repeater-row-title"><?php echo esc_html( $this->row_label ); ?> <?php echo (int) $i + 1; ?></span>
								<span class="sbvi-repeater-row-actions">
									<button type="button" class="button sbvi-move-up" aria-label="<?php esc_attr_e( 'Move up', 'sinclairs-bvi' ); ?>">↑</button>
									<button type="button" class="button sbvi-move-down" aria-label="<?php esc_attr_e( 'Move down', 'sinclairs-bvi' ); ?>">↓</button>
									<button type="button" class="button sbvi-remove-row" aria-label="<?php esc_attr_e( 'Remove', 'sinclairs-bvi' ); ?>"><?php esc_html_e( 'Remove', 'sinclairs-bvi' ); ?></button>
								</span>
							</div>
							<?php $this->render_row_fields( $i, $row ); ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<p><button type="button" class="button button-secondary sbvi-add-row"><?php echo esc_html( sprintf( __( '+ Add %s', 'sinclairs-bvi' ), $this->row_label ) ); ?></button></p>
			<template class="sbvi-repeater-template">
				<div class="sbvi-repeater-row">
					<div class="sbvi-repeater-row-head">
						<span class="sbvi-repeater-row-title"><?php echo esc_html( $this->row_label ); ?></span>
						<span class="sbvi-repeater-row-actions">
							<button type="button" class="button sbvi-move-up" aria-label="<?php esc_attr_e( 'Move up', 'sinclairs-bvi' ); ?>">↑</button>
							<button type="button" class="button sbvi-move-down" aria-label="<?php esc_attr_e( 'Move down', 'sinclairs-bvi' ); ?>">↓</button>
							<button type="button" class="button sbvi-remove-row" aria-label="<?php esc_attr_e( 'Remove', 'sinclairs-bvi' ); ?>"><?php esc_html_e( 'Remove', 'sinclairs-bvi' ); ?></button>
						</span>
					</div>
					<?php $this->render_row_fields( '__INDEX__', array() ); ?>
				</div>
			</template>
		</div>
		<?php
	}

	public function save( $post_id ) {
		$nonce_name = 'sbvi_repeater_nonce_' . $this->meta_key;
		if ( ! isset( $_POST[ $nonce_name ] ) || ! wp_verify_nonce( wp_unslash( $_POST[ $nonce_name ] ), 'sbvi_repeater_' . $this->meta_key ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$posted = isset( $_POST[ $this->meta_key ] ) && is_array( $_POST[ $this->meta_key ] ) ? wp_unslash( $_POST[ $this->meta_key ] ) : array();
		$clean  = array();

		foreach ( $posted as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$clean_row  = array();
			$has_content = false;
			foreach ( $this->fields as $field_key => $field ) {
				$raw = isset( $row[ $field_key ] ) ? $row[ $field_key ] : '';
				if ( 'textarea' === $field['type'] ) {
					$val = sanitize_textarea_field( $raw );
				} elseif ( 'select' === $field['type'] ) {
					$val = array_key_exists( $raw, $field['options'] ) ? $raw : '';
				} else {
					$val = sanitize_text_field( $raw );
				}
				if ( '' !== trim( (string) $val ) ) {
					$has_content = true;
				}
				$clean_row[ $field_key ] = $val;
			}
			if ( $has_content ) {
				$clean[] = $clean_row;
			}
		}

		update_post_meta( $post_id, $this->meta_key, $clean );
	}
}
