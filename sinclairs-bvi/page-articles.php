<?php
/**
 * Template Name: Our Articles (hub)
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

		$articles = new WP_Query( array(
			'post_type'      => 'article',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		?>

		<section class="sbvi-banner">
			<?php sbvi_image( $banner_id, 'sbvi-banner', get_the_title(), 'sbvi-banner__img', true ); ?>
			<div class="sbvi-banner__scrim" aria-hidden="true"></div>
		</section>

		<section class="sbvi-page-header sbvi-container">
			<h6 class="sbvi-kicker"><?php echo esc_html( get_the_title() ); ?></h6>
			<h1><?php echo wp_kses_post( sbvi_headline( $post_id ) ); ?></h1>
			<?php if ( get_the_excerpt() ) : ?>
				<p class="sbvi-page-header__dek"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
			<div class="sbvi-rule sbvi-rule--thick"></div>
		</section>

		<section class="sbvi-articles-list sbvi-container">
			<?php if ( $articles->have_posts() ) : ?>
				<div data-reveal>
					<?php
					while ( $articles->have_posts() ) :
						$articles->the_post();
						$terms     = get_the_terms( get_the_ID(), 'article_category' );
						$cat_label = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
						?>
						<a href="<?php the_permalink(); ?>" class="sbvi-article-row">
							<span class="sbvi-article-row__meta"><?php echo esc_html( $cat_label ); ?></span>
							<span>
								<span class="sbvi-article-row__title"><?php the_title(); ?></span>
								<?php if ( get_the_excerpt() ) : ?>
									<span class="sbvi-article-row__standfirst"><?php echo esc_html( get_the_excerpt() ); ?></span>
								<?php endif; ?>
							</span>
						</a>
					<?php endwhile; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p><?php esc_html_e( 'No articles published yet — check back soon.', 'sinclairs-bvi' ); ?></p>
			<?php endif; ?>
		</section>

		<?php
	endwhile;
endif;

get_footer();
