<?php
/**
 * Required top-level fallback (WordPress needs index.php to consider a
 * theme valid). Handles anything not covered by a more specific template —
 * in normal use on this site, that's essentially just search results.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<section class="sbvi-page-header sbvi-container">
	<?php if ( is_search() ) : ?>
		<h6 class="sbvi-kicker"><?php esc_html_e( 'Search', 'sinclairs-bvi' ); ?></h6>
		<h1><?php echo esc_html( sprintf( __( 'Results for “%s”', 'sinclairs-bvi' ), get_search_query() ) ); ?></h1>
	<?php else : ?>
		<h1><?php bloginfo( 'name' ); ?></h1>
	<?php endif; ?>
	<div class="sbvi-rule sbvi-rule--thick"></div>
</section>

<section class="sbvi-generic-page sbvi-container">
	<?php if ( have_posts() ) : ?>
		<div class="sbvi-articles-list">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<a href="<?php the_permalink(); ?>" class="sbvi-article-row">
					<span class="sbvi-article-row__meta"><?php echo esc_html( get_the_date() ); ?></span>
					<span>
						<span class="sbvi-article-row__title"><?php the_title(); ?></span>
						<?php if ( get_the_excerpt() ) : ?>
							<span class="sbvi-article-row__standfirst"><?php echo esc_html( get_the_excerpt() ); ?></span>
						<?php endif; ?>
					</span>
				</a>
			<?php endwhile; ?>
		</div>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing found.', 'sinclairs-bvi' ); ?></p>
	<?php endif; ?>
</section>

<?php
get_footer();
