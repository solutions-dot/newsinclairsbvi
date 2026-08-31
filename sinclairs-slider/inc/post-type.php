<?php
/**
 * The `sbvi_slide` post type. Slides are ordered by menu_order so the
 * client can drag them around; the admin list is forced to that order
 * and given an image column, because a list of slide titles alone is
 * useless for spotting the slide you meant to edit.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'sbvis_register_post_type' );
function sbvis_register_post_type() {
	register_post_type( 'sbvi_slide', array(
		'labels'          => array(
			'name'               => __( 'Slider', 'sinclairs-slider' ),
			'singular_name'      => __( 'Slide', 'sinclairs-slider' ),
			'add_new'            => __( 'Add Slide', 'sinclairs-slider' ),
			'add_new_item'       => __( 'Add Slide', 'sinclairs-slider' ),
			'edit_item'          => __( 'Edit Slide', 'sinclairs-slider' ),
			'new_item'           => __( 'New Slide', 'sinclairs-slider' ),
			'view_item'          => __( 'View Slide', 'sinclairs-slider' ),
			'search_items'       => __( 'Search Slides', 'sinclairs-slider' ),
			'not_found'          => __( 'No slides yet — add one.', 'sinclairs-slider' ),
			'all_items'          => __( 'All Slides', 'sinclairs-slider' ),
			'menu_name'          => __( 'Slider', 'sinclairs-slider' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_rest'    => false,
		'menu_position'   => 21,
		'menu_icon'       => 'dashicons-images-alt2',
		'supports'        => array( 'title', 'page-attributes' ),
		'hierarchical'    => false,
		'has_archive'     => false,
		'rewrite'         => false,
		'query_var'       => false,
		'capability_type' => 'post',
	) );
}

/**
 * Slides only make sense in the order they play, so drop WordPress's
 * date ordering for this screen.
 */
add_action( 'pre_get_posts', 'sbvis_admin_order' );
function sbvis_admin_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'sbvi_slide' !== $query->get( 'post_type' ) ) {
		return;
	}

	$query->set( 'orderby', 'menu_order' );
	$query->set( 'order', 'ASC' );
}

add_filter( 'manage_sbvi_slide_posts_columns', 'sbvis_admin_columns' );
function sbvis_admin_columns( $columns ) {
	$new = array();

	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$new['sbvis_image'] = __( 'Image', 'sinclairs-slider' );
		}
		$new[ $key ] = $label;
	}

	$new['sbvis_order'] = __( 'Order', 'sinclairs-slider' );
	unset( $new['date'] );

	return $new;
}

add_action( 'manage_sbvi_slide_posts_custom_column', 'sbvis_admin_column_content', 10, 2 );
function sbvis_admin_column_content( $column, $post_id ) {
	if ( 'sbvis_image' === $column ) {
		$image_id = (int) get_post_meta( $post_id, '_sbvis_image_id', true );
		$focal_x  = get_post_meta( $post_id, '_sbvis_focal_x', true );
		$focal_y  = get_post_meta( $post_id, '_sbvis_focal_y', true );
		$src      = $image_id ? wp_get_attachment_image_url( $image_id, 'sbvis-slide-sm' ) : '';

		if ( $src ) {
			printf(
				'<img src="%s" alt="" style="width:120px;height:68px;object-fit:cover;object-position:%s%% %s%%;border-radius:4px;" />',
				esc_url( $src ),
				esc_attr( '' === $focal_x ? '50' : $focal_x ),
				esc_attr( '' === $focal_y ? '50' : $focal_y )
			);
		} else {
			echo '<span style="color:#b32d2e;">' . esc_html__( 'No image', 'sinclairs-slider' ) . '</span>';
		}
	}

	if ( 'sbvis_order' === $column ) {
		echo (int) get_post_field( 'menu_order', $post_id );
	}
}

/**
 * Fetch slides for the front end. Passing explicit IDs keeps their
 * given order rather than menu_order, so a shortcode can hand-pick a
 * sequence for one page.
 */
function sbvis_get_slides( $ids = array() ) {
	$args = array(
		'post_type'      => 'sbvi_slide',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	);

	if ( $ids ) {
		$args['post__in'] = array_map( 'absint', $ids );
		$args['orderby']  = 'post__in';
		unset( $args['order'] );
	}

	return get_posts( $args );
}
