<?php
/**
 * Single practice-area page: banner, dek, body, FAQ accordion (if any
 * FAQs are set), next-practice-area link.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();

		$post_id  = get_the_ID();
		$number   = sbvi_service_number( $post_id );
		$faqs     = sbvi_faq_items( $post_id );
		$ordered  = sbvi_get_services();
		$total    = count( $ordered );
		$is_last  = false;
		$next     = null;

		foreach ( $ordered as $index => $service ) {
			if ( (int) $service->ID === $post_id ) {
				$is_last = ( $index === $total - 1 );
				$next    = $ordered[ ( $index + 1 ) % $total ];
				break;
			}
		}
		?>

		<section class="sbvi-banner">
			<?php sbvi_image( get_post_thumbnail_id( $post_id ), 'sbvi-banner', get_the_title(), 'sbvi-banner__img', true ); ?>
			<div class="sbvi-banner__scrim" aria-hidden="true"></div>
		</section>

		<section class="sbvi-page-header sbvi-container sbvi-page-header--service">
			<a href="<?php echo esc_url( sbvi_services_url() ); ?>" class="sbvi-back-link">&larr; <?php esc_html_e( 'Our Services', 'sinclairs-bvi' ); ?></a>
			<h6 class="sbvi-kicker"><?php echo esc_html( sprintf( __( 'Practice area %s', 'sinclairs-bvi' ), $number ) ); ?></h6>
			<h1><?php the_title(); ?></h1>
			<?php if ( get_the_excerpt() ) : ?>
				<p class="sbvi-page-header__dek"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
			<div class="sbvi-rule sbvi-rule--thick"></div>
		</section>

		<section class="sbvi-single-service sbvi-container">
			<div class="sbvi-single-service__grid">
				<div class="entry-content" data-reveal>
					<?php the_content(); ?>
				</div>
				<figure class="sbvi-single-service__photo" data-reveal>
					<?php sbvi_image( get_post_thumbnail_id( $post_id ), 'sbvi-portrait', get_the_title(), 'sbvi-single-service__img' ); ?>
				</figure>
			</div>
		</section>

		<?php if ( $faqs ) : ?>
			<section class="sbvi-faq">
				<div class="sbvi-container">
					<h2><?php echo esc_html( sbvi_faq_heading( $post_id ) ); ?></h2>
					<div class="sbvi-faq__list">
						<?php foreach ( $faqs as $faq ) : ?>
							<details class="sbvi-faq__item">
								<summary>
									<span><?php echo esc_html( $faq['question'] ); ?></span>
									<span class="sbvi-faq__icon" aria-hidden="true"></span>
								</summary>
								<div class="sbvi-faq__answer"><?php echo wpautop( esc_html( $faq['answer'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped before wpautop. ?></div>
							</details>
						<?php endforeach; ?>
					</div>
					<p class="sbvi-faq__disclaimer"><?php esc_html_e( 'These answers are general information only and do not constitute legal advice.', 'sinclairs-bvi' ); ?></p>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $next ) : ?>
			<section class="sbvi-next-service sbvi-container">
				<a href="<?php echo esc_url( get_permalink( $next ) ); ?>" class="sbvi-next-service__link">
					<span>
						<span class="sbvi-next-service__label"><?php echo esc_html( $is_last ? __( 'Back to the start', 'sinclairs-bvi' ) : __( 'Next practice area', 'sinclairs-bvi' ) ); ?></span>
						<span class="sbvi-next-service__title"><?php echo esc_html( get_the_title( $next ) ); ?></span>
					</span>
					<span class="sbvi-next-service__arrow" aria-hidden="true">→</span>
				</a>
			</section>
		<?php endif; ?>

		<?php
	endwhile;
endif;

get_footer();
