<?php
/**
 * Template Name: About
 * Banner + WYSIWYG body + sticky portrait photo.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();

		$post_id     = get_the_ID();
		$banner_id   = get_post_thumbnail_id( $post_id );
		$portrait_id = sbvi_secondary_image_id( $post_id );
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

		<section class="sbvi-about sbvi-container">
			<div class="sbvi-about__grid">
				<div class="sbvi-about__body entry-content" data-reveal>
					<?php the_content(); ?>
				</div>
				<figure class="sbvi-about__portrait" data-reveal>
					<?php sbvi_image( $portrait_id, 'sbvi-portrait', get_the_title(), 'sbvi-about__portrait-img' ); ?>
				</figure>
			</div>
		</section>

		<?php
	endwhile;
endif;

get_footer();
