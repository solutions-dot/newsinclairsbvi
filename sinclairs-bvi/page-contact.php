<?php
/**
 * Template Name: Contact
 * Banner + office/phone/email block + a self-contained enquiry form
 * (see inc/contact-form.php).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();

		$post_id   = get_the_ID();
		$banner_id = get_post_thumbnail_id( $post_id );
		$contact   = sbvi_contact_info();
		$status    = isset( $_GET['sbvi_contact'] ) ? sanitize_key( wp_unslash( $_GET['sbvi_contact'] ) ) : '';
		$phones    = array_filter( array( $contact['phone_1'], $contact['phone_2'] ) );
		?>

		<section class="sbvi-banner">
			<?php sbvi_image( $banner_id, 'sbvi-banner', get_the_title(), 'sbvi-banner__img', true ); ?>
			<div class="sbvi-banner__scrim" aria-hidden="true"></div>
		</section>

		<section class="sbvi-page-header sbvi-container">
			<h6 class="sbvi-kicker"><?php echo esc_html( get_the_title() ); ?></h6>
			<h1><?php echo wp_kses_post( sbvi_headline( $post_id ) ); ?></h1>
			<div class="sbvi-rule sbvi-rule--thick"></div>
		</section>

		<section class="sbvi-contact sbvi-container">
			<div class="sbvi-contact__grid">
				<div data-reveal>
					<div class="entry-content sbvi-contact__intro"><?php the_content(); ?></div>
					<dl class="sbvi-contact__details">
						<div>
							<dt><?php esc_html_e( 'Office', 'sinclairs-bvi' ); ?></dt>
							<dd><?php echo nl2br( esc_html( $contact['address_lines'] ) ); ?></dd>
						</div>
						<?php if ( $phones ) : ?>
							<div>
								<dt><?php esc_html_e( 'Telephone', 'sinclairs-bvi' ); ?></dt>
								<dd>
									<?php
									$links = array();
									foreach ( $phones as $phone ) {
										$links[] = '<a href="' . esc_url( sbvi_tel_href( $phone ) ) . '">' . esc_html( $phone ) . '</a>';
									}
									echo implode( ' · ', $links ); // phpcs:ignore WordPress.Security.EscapeOutput -- each fragment already escaped above.
									?>
								</dd>
							</div>
						<?php endif; ?>
						<?php if ( $contact['email'] ) : ?>
							<div>
								<dt><?php esc_html_e( 'Email', 'sinclairs-bvi' ); ?></dt>
								<dd><a href="mailto:<?php echo esc_attr( $contact['email'] ); ?>"><?php echo esc_html( $contact['email'] ); ?></a></dd>
							</div>
						<?php endif; ?>
					</dl>
				</div>

				<div data-reveal>
					<?php if ( 'sent' === $status ) : ?>
						<div class="sbvi-form-notice sbvi-form-notice--success" role="status"><?php esc_html_e( 'Thank you — your enquiry has been sent. We will be in touch shortly.', 'sinclairs-bvi' ); ?></div>
					<?php elseif ( 'invalid' === $status ) : ?>
						<div class="sbvi-form-notice sbvi-form-notice--error" role="alert"><?php esc_html_e( 'Please fill in your name, a valid email address and a short message.', 'sinclairs-bvi' ); ?></div>
					<?php elseif ( 'error' === $status ) : ?>
						<div class="sbvi-form-notice sbvi-form-notice--error" role="alert"><?php esc_html_e( 'Sorry, something went wrong sending your enquiry. Please email us directly.', 'sinclairs-bvi' ); ?></div>
					<?php endif; ?>

					<form class="sbvi-contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="sbvi_contact">
						<?php wp_nonce_field( 'sbvi_contact_form', 'sbvi_contact_nonce' ); ?>
						<div class="sbvi-honeypot" aria-hidden="true">
							<label for="sbvi_contact_hp"><?php esc_html_e( 'Leave this field empty', 'sinclairs-bvi' ); ?></label>
							<input type="text" id="sbvi_contact_hp" name="sbvi_contact_hp" tabindex="-1" autocomplete="off">
						</div>
						<div class="sbvi-form-row">
							<div class="field">
								<label for="sbvi-name"><?php esc_html_e( 'Your name', 'sinclairs-bvi' ); ?></label>
								<input class="input" id="sbvi-name" name="sbvi_name" type="text" placeholder="<?php esc_attr_e( 'Full name', 'sinclairs-bvi' ); ?>" required>
							</div>
							<div class="field">
								<label for="sbvi-email"><?php esc_html_e( 'Email', 'sinclairs-bvi' ); ?></label>
								<input class="input" id="sbvi-email" name="sbvi_email" type="email" placeholder="you@company.com" required>
							</div>
						</div>
						<div class="field">
							<label for="sbvi-subject"><?php esc_html_e( 'Area of interest', 'sinclairs-bvi' ); ?></label>
							<input class="input" id="sbvi-subject" name="sbvi_subject" type="text" placeholder="<?php esc_attr_e( 'e.g. Investment funds, VASP registration', 'sinclairs-bvi' ); ?>">
						</div>
						<div class="field">
							<label for="sbvi-msg"><?php esc_html_e( 'How can we help?', 'sinclairs-bvi' ); ?></label>
							<textarea class="input" id="sbvi-msg" name="sbvi_message" rows="5" placeholder="<?php esc_attr_e( 'A short description of the matter', 'sinclairs-bvi' ); ?>" required></textarea>
						</div>
						<button type="submit" class="btn btn-primary sbvi-contact-form__submit"><?php esc_html_e( 'Send enquiry', 'sinclairs-bvi' ); ?></button>
						<p class="sbvi-form-disclaimer"><?php esc_html_e( 'Sending an enquiry does not create a lawyer–client relationship, and nothing on this website constitutes legal advice.', 'sinclairs-bvi' ); ?></p>
					</form>
				</div>
			</div>
		</section>

		<?php
	endwhile;
endif;

get_footer();
