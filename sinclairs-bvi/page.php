<?php
/**
 * Fallback template for any Page not using one of the dedicated templates.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		$banner_id = get_post_thumbnail_id( get_the_ID() );
		?>

		<?php if ( $banner_id ) : ?>
			<section class="sbvi-banner">
				<?php sbvi_image( $banner_id, 'sbvi-banner', get_the_title(), 'sbvi-banner__img', true ); ?>
				<div class="sbvi-banner__scrim" aria-hidden="true"></div>
			</section>
		<?php endif; ?>

		<section class="sbvi-page-header sbvi-container">
			<h1><?php the_title(); ?></h1>
			<div class="sbvi-rule sbvi-rule--thick"></div>
		</section>

		<section class="sbvi-generic-page sbvi-container">
			<div class="entry-content" data-reveal>
				<?php the_content(); ?>
			</div>
		</section>

		<?php
	endwhile;
endif;

get_footer();
