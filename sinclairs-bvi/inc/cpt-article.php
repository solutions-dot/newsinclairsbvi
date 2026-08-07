<?php
/**
 * "article" CPT — Our Articles. Standfirst uses the native Excerpt field
 * (no custom meta needed); category uses a real taxonomy.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sbvi_register_article_cpt() {
	register_post_type( 'article', array(
		'labels' => array(
			'name'          => __( 'Articles', 'sinclairs-bvi' ),
			'singular_name' => __( 'Article', 'sinclairs-bvi' ),
			'add_new_item'  => __( 'Add Article', 'sinclairs-bvi' ),
			'edit_item'     => __( 'Edit Article', 'sinclairs-bvi' ),
			'all_items'     => __( 'Our Articles', 'sinclairs-bvi' ),
			'menu_name'     => __( 'Our Articles', 'sinclairs-bvi' ),
		),
		'public'       => true,
		'menu_icon'    => 'dashicons-media-document',
		'menu_position'=> 21,
		'has_archive'  => false,
		'rewrite'      => array( 'slug' => 'articles', 'with_front' => false ),
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
		'show_in_rest' => true,
	) );

	register_taxonomy( 'article_category', 'article', array(
		'labels' => array(
			'name'          => __( 'Article Categories', 'sinclairs-bvi' ),
			'singular_name' => __( 'Article Category', 'sinclairs-bvi' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'articles/topic' ),
	) );
}
add_action( 'init', 'sbvi_register_article_cpt' );
