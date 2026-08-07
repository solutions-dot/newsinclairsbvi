<?php
/**
 * Site header: sticky logo + nav, header mega-menu, mobile drawer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sbvi_services = sbvi_get_services();
$sbvi_nutshell_in_menu = get_theme_mod( 'sbvi_nutshell_in_menu', true );
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>document.documentElement.classList.replace('no-js','js');</script>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="sbvi-skip-link" href="#sbvi-main"><?php esc_html_e( 'Skip to content', 'sinclairs-bvi' ); ?></a>

<div class="sbvi-page">

	<header class="sbvi-header">
		<div class="sbvi-header__bar sbvi-container">
			<a href="<?php echo esc_url( sbvi_home_url() ); ?>" class="sbvi-logo">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<span class="sbvi-logo__text"><?php bloginfo( 'name' ); ?></span>
				<?php endif; ?>
			</a>

			<nav class="sbvi-nav" aria-label="<?php esc_attr_e( 'Primary', 'sinclairs-bvi' ); ?>">
				<a href="<?php echo esc_url( sbvi_home_url() ); ?>" class="sbvi-nav__link"><?php esc_html_e( 'Home', 'sinclairs-bvi' ); ?></a>
				<a href="<?php echo esc_url( sbvi_about_url() ); ?>" class="sbvi-nav__link"><?php esc_html_e( 'About', 'sinclairs-bvi' ); ?></a>

				<div class="sbvi-nav__services" data-mega-trigger>
					<a href="<?php echo esc_url( sbvi_services_url() ); ?>" class="sbvi-nav__link"><?php esc_html_e( 'Our Services', 'sinclairs-bvi' ); ?></a>
					<button type="button" class="sbvi-nav__caret" aria-haspopup="true" aria-expanded="false" aria-controls="sbvi-mega-menu" aria-label="<?php esc_attr_e( 'Toggle Our Services menu', 'sinclairs-bvi' ); ?>">
						<span aria-hidden="true"></span>
					</button>
				</div>

				<a href="<?php echo esc_url( sbvi_articles_url() ); ?>" class="sbvi-nav__link"><?php esc_html_e( 'Our Articles', 'sinclairs-bvi' ); ?></a>
				<a href="<?php echo esc_url( sbvi_contact_url() ); ?>" class="btn btn-primary sbvi-nav__cta"><?php esc_html_e( 'Contact us', 'sinclairs-bvi' ); ?></a>
			</nav>

			<button type="button" class="sbvi-hamburger" aria-haspopup="true" aria-expanded="false" aria-controls="sbvi-mobile-drawer" aria-label="<?php esc_attr_e( 'Menu', 'sinclairs-bvi' ); ?>">
				<span></span><span></span><span></span>
			</button>
		</div>
		<div class="sbvi-header__rule"></div>

		<div class="sbvi-mega" id="sbvi-mega-menu" data-mega-panel hidden>
			<div class="sbvi-container sbvi-mega__inner">
				<div class="sbvi-mega__head">
					<h6><?php esc_html_e( 'Our Services', 'sinclairs-bvi' ); ?></h6>
					<a href="<?php echo esc_url( sbvi_services_url() ); ?>"><?php esc_html_e( 'All services, in a nutshell →', 'sinclairs-bvi' ); ?></a>
				</div>
				<div class="sbvi-mega__grid">
					<?php foreach ( $sbvi_services as $index => $service ) : ?>
						<a href="<?php echo esc_url( get_permalink( $service ) ); ?>" class="sbvi-mega__item">
							<span class="sbvi-mega__num"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<span>
								<span class="sbvi-mega__title"><?php echo esc_html( get_the_title( $service ) ); ?></span>
								<?php if ( $sbvi_nutshell_in_menu ) : $nutshell = get_post_meta( $service->ID, '_sbvi_nutshell', true ); ?>
									<?php if ( $nutshell ) : ?>
										<span class="sbvi-mega__blurb"><?php echo esc_html( $nutshell ); ?></span>
									<?php endif; ?>
								<?php endif; ?>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</header>

	<div class="sbvi-mobile-drawer" id="sbvi-mobile-drawer" hidden>
		<div class="sbvi-mobile-drawer__head sbvi-container">
			<?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
				<span class="sbvi-logo__text"><?php bloginfo( 'name' ); ?></span>
			<?php endif; ?>
			<button type="button" class="sbvi-mobile-drawer__close" aria-label="<?php esc_attr_e( 'Close menu', 'sinclairs-bvi' ); ?>">×</button>
		</div>
		<div class="sbvi-header__rule"></div>
		<nav class="sbvi-mobile-nav sbvi-container" aria-label="<?php esc_attr_e( 'Mobile', 'sinclairs-bvi' ); ?>">
			<a href="<?php echo esc_url( sbvi_home_url() ); ?>" class="sbvi-mobile-nav__link"><?php esc_html_e( 'Home', 'sinclairs-bvi' ); ?></a>
			<a href="<?php echo esc_url( sbvi_about_url() ); ?>" class="sbvi-mobile-nav__link"><?php esc_html_e( 'About', 'sinclairs-bvi' ); ?></a>
			<a href="<?php echo esc_url( sbvi_services_url() ); ?>" class="sbvi-mobile-nav__link"><?php esc_html_e( 'Our Services', 'sinclairs-bvi' ); ?></a>
			<div class="sbvi-mobile-nav__sub">
				<?php foreach ( $sbvi_services as $service ) : ?>
					<a href="<?php echo esc_url( get_permalink( $service ) ); ?>"><?php echo esc_html( get_the_title( $service ) ); ?></a>
				<?php endforeach; ?>
			</div>
			<a href="<?php echo esc_url( sbvi_articles_url() ); ?>" class="sbvi-mobile-nav__link"><?php esc_html_e( 'Our Articles', 'sinclairs-bvi' ); ?></a>
			<a href="<?php echo esc_url( sbvi_contact_url() ); ?>" class="sbvi-mobile-nav__link"><?php esc_html_e( 'Contact', 'sinclairs-bvi' ); ?></a>
		</nav>
	</div>

	<main id="sbvi-main" class="sbvi-main">
