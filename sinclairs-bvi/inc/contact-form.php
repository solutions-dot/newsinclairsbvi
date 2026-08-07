<?php
/**
 * Self-contained enquiry form handler — no Contact Form 7 / WPForms
 * dependency. Posts to admin-post.php, verifies a nonce + honeypot, mails
 * the firm, then redirects back to the Contact page with a status flag.
 *
 * Swap-out note: if the client later prefers Contact Form 7 or WPForms,
 * replace the <form> markup in page-contact.php with the plugin's shortcode
 * / block and this file can be left unused.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sbvi_handle_contact_form() {
	if ( ! isset( $_POST['sbvi_contact_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['sbvi_contact_nonce'] ), 'sbvi_contact_form' ) ) {
		wp_safe_redirect( add_query_arg( 'sbvi_contact', 'error', sbvi_contact_url() ) );
		exit;
	}

	// Honeypot: real visitors never fill this hidden field.
	if ( ! empty( $_POST['sbvi_contact_hp'] ) ) {
		wp_safe_redirect( add_query_arg( 'sbvi_contact', 'sent', sbvi_contact_url() ) );
		exit;
	}

	$name    = isset( $_POST['sbvi_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sbvi_name'] ) ) : '';
	$email   = isset( $_POST['sbvi_email'] ) ? sanitize_email( wp_unslash( $_POST['sbvi_email'] ) ) : '';
	$subject = isset( $_POST['sbvi_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['sbvi_subject'] ) ) : '';
	$message = isset( $_POST['sbvi_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sbvi_message'] ) ) : '';

	if ( ! $name || ! $email || ! is_email( $email ) || ! $message ) {
		wp_safe_redirect( add_query_arg( 'sbvi_contact', 'invalid', sbvi_contact_url() ) );
		exit;
	}

	$recipient = get_theme_mod( 'sbvi_contact_form_recipient' );
	if ( ! $recipient ) {
		$recipient = get_theme_mod( 'sbvi_email', get_option( 'admin_email' ) );
	}

	$site_name    = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$mail_subject = sprintf( '[%s] New enquiry: %s', $site_name, $subject ? $subject : 'General enquiry' );

	$body  = "New enquiry from the website contact form.\n\n";
	$body .= "Name: {$name}\n";
	$body .= "Email: {$email}\n";
	$body .= 'Area of interest: ' . ( $subject ? $subject : '—' ) . "\n\n";
	$body .= "Message:\n{$message}\n";

	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );

	$sent = wp_mail( $recipient, $mail_subject, $body, $headers );

	wp_safe_redirect( add_query_arg( 'sbvi_contact', $sent ? 'sent' : 'error', sbvi_contact_url() ) );
	exit;
}
add_action( 'admin_post_nopriv_sbvi_contact', 'sbvi_handle_contact_form' );
add_action( 'admin_post_sbvi_contact', 'sbvi_handle_contact_form' );
