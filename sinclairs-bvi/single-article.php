<?php
/**
 * Single article. Not part of the approved design (which only shows the
 * Our Articles index) — built to match the site's established page-header
 * + entry-content pattern used everywhere else.
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
		$terms     = get_the_terms( $post_id, 'article_category' );
		$cat_label = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
		?>

		<section class="sbvi-banner">
			<?php sbvi_image( $banner_id, 'sbvi-banner', get_the_title(), 'sbvi-banner__img', true ); ?>
			<div class="sbvi-banner__scrim" aria-hidden="true"></div>
		</section>

		<section class="sbvi-page-header sbvi-container sbvi-page-header--service">
			<a href="<?php echo esc_url( sbvi_articles_url() ); ?>" class="sbvi-back-link">&larr; <?php esc_html_e( 'Our Articles', 'sinclairs-bvi' ); ?></a>
			<h6 class="sbvi-kicker">
				<?php echo $cat_label ? esc_html( $cat_label ) . ' · ' : ''; ?>
				<?php echo esc_html( get_the_date() ); ?>
			</h6>
			<h1><?php the_title(); ?></h1>
			<?php if ( get_the_excerpt() ) : ?>
				<p class="sbvi-page-header__dek"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
			<div class="sbvi-rule sbvi-rule--thick"></div>
		</section>

		<section class="sbvi-single-article sbvi-container">
			<div class="entry-content" data-reveal>
				<?php the_content(); ?>
			</div>
		</section>

		<?php
	endwhile;
endif;

get_footer();
