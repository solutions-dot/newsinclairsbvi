<?php
/**
 * Site footer: 4-column chrome + bottom disclaimer bar.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sbvi_services      = sbvi_get_services();
$sbvi_footer_top    = array_slice( $sbvi_services, 0, 5 );
$sbvi_footer_rest   = array_slice( $sbvi_services, 5 );
$sbvi_contact       = sbvi_contact_info();
?>
	</main>

	<footer class="sbvi-footer">
		<div class="sbvi-container sbvi-footer__inner">
			<div class="sbvi-footer__grid">
				<div class="sbvi-footer__brand">
					<?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
						<span class="sbvi-logo__text"><?php bloginfo( 'name' ); ?></span>
					<?php endif; ?>
					<p><?php echo esc_html( get_theme_mod( 'sbvi_footer_blurb', 'Clear, practical and commercially focused legal services in the British Virgin Islands.' ) ); ?></p>
				</div>

				<details class="sbvi-footer__col">
					<summary><h6><?php esc_html_e( 'Services', 'sinclairs-bvi' ); ?></h6></summary>
					<div class="sbvi-footer__col-body">
						<?php foreach ( $sbvi_footer_top as $service ) : ?>
							<a href="<?php echo esc_url( get_permalink( $service ) ); ?>"><?php echo esc_html( get_the_title( $service ) ); ?></a>
						<?php endforeach; ?>
					</div>
				</details>

				<details class="sbvi-footer__col">
					<summary><h6><?php esc_html_e( 'More', 'sinclairs-bvi' ); ?></h6></summary>
					<div class="sbvi-footer__col-body">
						<?php foreach ( $sbvi_footer_rest as $service ) : ?>
							<a href="<?php echo esc_url( get_permalink( $service ) ); ?>"><?php echo esc_html( get_the_title( $service ) ); ?></a>
						<?php endforeach; ?>
						<a href="<?php echo esc_url( sbvi_about_url() ); ?>"><?php esc_html_e( 'About', 'sinclairs-bvi' ); ?></a>
						<a href="<?php echo esc_url( sbvi_articles_url() ); ?>"><?php esc_html_e( 'Our Articles', 'sinclairs-bvi' ); ?></a>
					</div>
				</details>

				<details class="sbvi-footer__col">
					<summary><h6><?php esc_html_e( 'Contact', 'sinclairs-bvi' ); ?></h6></summary>
					<div class="sbvi-footer__col-body">
						<p class="sbvi-footer__contact">
							<?php echo nl2br( esc_html( $sbvi_contact['address_lines'] ) ); ?><br>
							<?php if ( $sbvi_contact['phone_1'] ) : ?>
								<a href="<?php echo esc_attr( sbvi_tel_href( $sbvi_contact['phone_1'] ) ); ?>"><?php echo esc_html( $sbvi_contact['phone_1'] ); ?></a><br>
							<?php endif; ?>
							<?php if ( $sbvi_contact['email'] ) : ?>
								<a href="mailto:<?php echo esc_attr( $sbvi_contact['email'] ); ?>"><?php echo esc_html( $sbvi_contact['email'] ); ?></a>
							<?php endif; ?>
						</p>
					</div>
				</details>
			</div>

			<div class="sbvi-footer__rule"></div>

			<div class="sbvi-footer__bottom">
				<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_theme_mod( 'sbvi_copyright_text', 'Sinclairs (BVI). All rights reserved.' ) ); ?></span>
				<span><?php echo esc_html( get_theme_mod( 'sbvi_disclaimer_text', 'The information on this website, including answers to frequently asked questions, is general in nature and does not constitute legal advice.' ) ); ?></span>
			</div>
		</div>
	</footer>

</div><?php // .sbvi-page ?>

<?php wp_footer(); ?>
</body>
</html>
