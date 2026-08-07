<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<section class="sbvi-page-header sbvi-container">
	<h6 class="sbvi-kicker">404</h6>
	<h1><?php esc_html_e( 'Page not found', 'sinclairs-bvi' ); ?></h1>
	<div class="sbvi-rule sbvi-rule--thick"></div>
</section>

<section class="sbvi-generic-page sbvi-container">
	<p><?php esc_html_e( 'The page you are looking for may have been moved or no longer exists.', 'sinclairs-bvi' ); ?></p>
	<a href="<?php echo esc_url( sbvi_home_url() ); ?>" class="btn btn-primary"><?php esc_html_e( 'Back to home', 'sinclairs-bvi' ); ?></a>
</section>

<?php
get_footer();
