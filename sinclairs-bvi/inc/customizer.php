<?php
/**
 * Theme Options, exposed through the native Customizer (Appearance →
 * Customize → Theme Options) — no admin page of its own to maintain.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sbvi_customize_register( $wp_customize ) {
	$wp_customize->add_panel( 'sbvi_theme_options', array(
		'title'    => __( 'Theme Options', 'sinclairs-bvi' ),
		'priority' => 10,
	) );

	/** Brand **/
	$wp_customize->add_section( 'sbvi_brand', array(
		'title' => __( 'Brand', 'sinclairs-bvi' ),
		'panel' => 'sbvi_theme_options',
	) );

	$wp_customize->add_setting( 'sbvi_accent_color', array(
		'default'           => '#0e7c93',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'sbvi_accent_color', array(
		'label'   => __( 'Accent color', 'sinclairs-bvi' ),
		'section' => 'sbvi_brand',
	) ) );

	$wp_customize->add_setting( 'sbvi_default_banner_image', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'sbvi_default_banner_image', array(
		'label'       => __( 'Default photo', 'sinclairs-bvi' ),
		'description' => __( 'Used for any banner, hero or portrait slot that has not been given its own photo yet.', 'sinclairs-bvi' ),
		'section'     => 'sbvi_brand',
		'mime_type'   => 'image',
	) ) );

	/** Services **/
	$wp_customize->add_section( 'sbvi_services_options', array(
		'title' => __( 'Services', 'sinclairs-bvi' ),
		'panel' => 'sbvi_theme_options',
	) );

	$wp_customize->add_setting( 'sbvi_faq_heading_default', array(
		'default'           => 'merged',
		'sanitize_callback' => 'sbvi_sanitize_faq_heading',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'sbvi_faq_heading_default', array(
		'label'   => __( 'FAQ section heading (site default — each practice area can override this)', 'sinclairs-bvi' ),
		'section' => 'sbvi_services_options',
		'type'    => 'radio',
		'choices' => array(
			'merged'   => __( 'Merged: “Information & FAQs”', 'sinclairs-bvi' ),
			'separate' => __( 'Separate: “Frequently Asked Questions”', 'sinclairs-bvi' ),
		),
	) );

	$wp_customize->add_setting( 'sbvi_nutshell_in_menu', array(
		'default'           => true,
		'sanitize_callback' => 'rest_sanitize_boolean',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'sbvi_nutshell_in_menu', array(
		'label'   => __( 'Show the one-line summary under each service in the header mega-menu', 'sinclairs-bvi' ),
		'section' => 'sbvi_services_options',
		'type'    => 'checkbox',
	) );

	/** Contact **/
	$wp_customize->add_section( 'sbvi_contact_options', array(
		'title' => __( 'Contact Info', 'sinclairs-bvi' ),
		'panel' => 'sbvi_theme_options',
	) );

	$wp_customize->add_setting( 'sbvi_address', array(
		'default'           => "Mill Mall, 2nd Floor, Unit 20\nRoad Town, Tortola VG1110\nBritish Virgin Islands",
		'sanitize_callback' => 'sanitize_textarea_field',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'sbvi_address', array(
		'label'   => __( 'Office address (one line per line)', 'sinclairs-bvi' ),
		'section' => 'sbvi_contact_options',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'sbvi_phone_1', array(
		'default'           => '+1 (284) 542 2453',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'sbvi_phone_1', array(
		'label'   => __( 'Telephone 1', 'sinclairs-bvi' ),
		'section' => 'sbvi_contact_options',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'sbvi_phone_2', array(
		'default'           => '+1 (284) 545 2454',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'sbvi_phone_2', array(
		'label'   => __( 'Telephone 2 (optional — leave blank to hide)', 'sinclairs-bvi' ),
		'section' => 'sbvi_contact_options',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'sbvi_email', array(
		'default'           => 'bvi@sinclairsoffshore.com',
		'sanitize_callback' => 'sanitize_email',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'sbvi_email', array(
		'label'   => __( 'Email', 'sinclairs-bvi' ),
		'section' => 'sbvi_contact_options',
		'type'    => 'email',
	) );

	$wp_customize->add_setting( 'sbvi_contact_form_recipient', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_email',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'sbvi_contact_form_recipient', array(
		'label'       => __( 'Enquiry form recipient (optional — defaults to the email above)', 'sinclairs-bvi' ),
		'section'     => 'sbvi_contact_options',
		'type'        => 'email',
	) );

	/** Footer **/
	$wp_customize->add_section( 'sbvi_footer_options', array(
		'title' => __( 'Footer', 'sinclairs-bvi' ),
		'panel' => 'sbvi_theme_options',
	) );

	$wp_customize->add_setting( 'sbvi_footer_blurb', array(
		'default'           => 'Clear, practical and commercially focused legal services in the British Virgin Islands.',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'sbvi_footer_blurb', array(
		'label'   => __( 'Brand blurb', 'sinclairs-bvi' ),
		'section' => 'sbvi_footer_options',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'sbvi_copyright_text', array(
		'default'           => 'Sinclairs (BVI). All rights reserved.',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'sbvi_copyright_text', array(
		'label'       => __( 'Copyright line (the current year and © are added automatically)', 'sinclairs-bvi' ),
		'section'     => 'sbvi_footer_options',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'sbvi_disclaimer_text', array(
		'default'           => 'The information on this website, including answers to frequently asked questions, is general in nature and does not constitute legal advice.',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'sbvi_disclaimer_text', array(
		'label'   => __( 'Legal disclaimer (footer bottom bar)', 'sinclairs-bvi' ),
		'section' => 'sbvi_footer_options',
		'type'    => 'textarea',
	) );
}
add_action( 'customize_register', 'sbvi_customize_register' );

function sbvi_sanitize_faq_heading( $value ) {
	return in_array( $value, array( 'merged', 'separate' ), true ) ? $value : 'merged';
}

function sbvi_customizer_css() {
	$accent = get_theme_mod( 'sbvi_accent_color', '#0e7c93' );
	if ( ! $accent ) {
		return;
	}
	echo '<style id="sbvi-customizer-css">:root{--color-accent:' . esc_html( $accent ) . ';}</style>' . "\n";
}
add_action( 'wp_head', 'sbvi_customizer_css', 20 );
