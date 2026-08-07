<?php
/**
 * Home. Text blocks come from the "Home Page Content" meta box (see
 * inc/page-meta-boxes.php); the practice-area list, hover panel and
 * mega-menu all read from the same sbvi_get_services() query.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();

		$home_id       = get_the_ID();
		$hero_image_id = get_post_thumbnail_id( $home_id );
		$services      = sbvi_get_services();

		$testimonials = new WP_Query( array(
			'post_type'      => 'testimonial',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		) );
		?>

		<section class="sbvi-hero">
			<div class="sbvi-hero__media">
				<?php sbvi_image( $hero_image_id, 'sbvi-hero', get_the_title(), 'sbvi-hero__img', true ); ?>
			</div>
			<div class="sbvi-hero__scrim" aria-hidden="true"></div>
			<div class="sbvi-hero__content sbvi-container">
				<div class="sbvi-hero__inner">
					<div class="sbvi-rule sbvi-rule--thick" data-reveal></div>
					<p class="sbvi-hero__kicker">
						<?php foreach ( sbvi_split_kicker( sbvi_home_field( $home_id, 'sbvi_hero_kicker' ) ) as $i => $part ) : ?>
							<span<?php echo 1 === $i ? ' class="is-accent"' : ''; ?>><?php echo esc_html( $part ); ?></span>
						<?php endforeach; ?>
					</p>
					<div class="sbvi-rule" data-reveal></div>
					<h1 class="sbvi-hero__heading"><?php echo wp_kses_post( sbvi_home_field( $home_id, 'sbvi_hero_heading' ) ); ?></h1>
					<p class="sbvi-hero__sub"><?php echo esc_html( sbvi_home_field( $home_id, 'sbvi_hero_subheading' ) ); ?></p>
					<div class="sbvi-hero__ctas">
						<a href="<?php echo esc_url( sbvi_services_url() ); ?>" class="btn btn-primary"><?php echo esc_html( sbvi_home_field( $home_id, 'sbvi_hero_cta1' ) ); ?></a>
						<a href="<?php echo esc_url( sbvi_contact_url() ); ?>" class="btn btn-secondary"><?php echo esc_html( sbvi_home_field( $home_id, 'sbvi_hero_cta2' ) ); ?></a>
					</div>
				</div>
			</div>
		</section>

		<section class="sbvi-intro sbvi-container">
			<div class="sbvi-intro__grid">
				<div data-reveal>
					<h6 class="sbvi-kicker"><?php echo esc_html( sbvi_home_field( $home_id, 'sbvi_intro_kicker' ) ); ?></h6>
					<p class="sbvi-intro__statement"><?php echo esc_html( sbvi_home_field( $home_id, 'sbvi_intro_heading' ) ); ?></p>
				</div>
				<div data-reveal class="sbvi-intro__body">
					<?php the_content(); ?>
					<a href="<?php echo esc_url( sbvi_about_url() ); ?>" class="sbvi-link-arrow"><?php esc_html_e( 'About the firm →', 'sinclairs-bvi' ); ?></a>
				</div>
			</div>
		</section>

		<?php if ( $services ) : ?>
		<section class="sbvi-practice sbvi-container">
			<div class="sbvi-practice__head">
				<h2><?php esc_html_e( 'Choose a practice area', 'sinclairs-bvi' ); ?></h2>
				<a href="<?php echo esc_url( sbvi_services_url() ); ?>"><?php esc_html_e( 'All services, in a nutshell →', 'sinclairs-bvi' ); ?></a>
			</div>
			<div class="sbvi-rule sbvi-rule--thick" data-reveal></div>

			<div class="sbvi-practice__grid" data-practice-picker>
				<div class="sbvi-practice__list">
					<?php foreach ( $services as $index => $service ) : ?>
						<a href="<?php echo esc_url( get_permalink( $service ) ); ?>" class="sbvi-practice__row<?php echo 0 === $index ? ' is-active' : ''; ?>" data-practice-index="<?php echo esc_attr( $index ); ?>">
							<span class="sbvi-practice__num"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<span class="sbvi-practice__title"><?php echo esc_html( get_the_title( $service ) ); ?></span>
							<span class="sbvi-practice__arrow" aria-hidden="true">→</span>
						</a>
					<?php endforeach; ?>
				</div>

				<div class="sbvi-practice__panel">
					<div class="sbvi-practice__photo">
						<?php foreach ( $services as $index => $service ) : ?>
							<div class="sbvi-practice__photo-layer<?php echo 0 === $index ? ' is-active' : ''; ?>" data-practice-panel="<?php echo esc_attr( $index ); ?>">
								<?php sbvi_image( get_post_thumbnail_id( $service ), 'sbvi-panel', get_the_title( $service ), 'sbvi-practice__img', true ); ?>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="sbvi-practice__text">
						<?php foreach ( $services as $index => $service ) : $nutshell = get_post_meta( $service->ID, '_sbvi_nutshell', true ); ?>
							<p class="sbvi-practice__desc<?php echo 0 === $index ? ' is-active' : ''; ?>" data-practice-desc="<?php echo esc_attr( $index ); ?>"><?php echo esc_html( $nutshell ); ?></p>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<section class="sbvi-articles-teaser">
			<div class="sbvi-container sbvi-articles-teaser__grid">
				<div data-reveal>
					<h6 class="sbvi-kicker"><?php echo esc_html( sbvi_home_field( $home_id, 'sbvi_articles_kicker' ) ); ?></h6>
					<h2><?php echo esc_html( sbvi_home_field( $home_id, 'sbvi_articles_heading' ) ); ?></h2>
					<p><?php echo esc_html( sbvi_home_field( $home_id, 'sbvi_articles_body' ) ); ?></p>
					<a href="<?php echo esc_url( sbvi_articles_url() ); ?>" class="sbvi-link-arrow"><?php esc_html_e( 'Read our articles →', 'sinclairs-bvi' ); ?></a>
				</div>
				<figure data-reveal class="sbvi-articles-teaser__figure">
					<?php sbvi_image( sbvi_secondary_image_id( $home_id ), 'sbvi-card', esc_attr__( 'Our Articles', 'sinclairs-bvi' ), 'sbvi-articles-teaser__img' ); ?>
				</figure>
			</div>
		</section>

		<?php if ( $testimonials->have_posts() ) : ?>
		<section class="sbvi-testimonials sbvi-container" data-reveal>
			<div class="sbvi-rule sbvi-rule--thick"></div>
			<h6 class="sbvi-kicker"><?php esc_html_e( 'In our clients’ words', 'sinclairs-bvi' ); ?></h6>
			<div class="sbvi-testimonials__stage" data-testimonial-stage>
				<?php
				$t_index = 0;
				while ( $testimonials->have_posts() ) :
					$testimonials->the_post();
					$matter = get_post_meta( get_the_ID(), '_sbvi_matter', true );
					?>
					<blockquote class="sbvi-testimonials__quote<?php echo 0 === $t_index ? ' is-active' : ''; ?>" data-testimonial-index="<?php echo esc_attr( $t_index ); ?>">
						<?php the_content(); ?>
						<footer><?php echo esc_html( get_the_title() ); ?><?php echo $matter ? ' · ' . esc_html( $matter ) : ''; ?></footer>
					</blockquote>
					<?php
					$t_index++;
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<?php if ( $t_index > 1 ) : ?>
				<div class="sbvi-testimonials__dots" data-testimonial-dots>
					<?php for ( $i = 0; $i < $t_index; $i++ ) : ?>
						<button type="button" class="sbvi-testimonials__dot<?php echo 0 === $i ? ' is-active' : ''; ?>" data-testimonial-goto="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Testimonial %d', 'sinclairs-bvi' ), $i + 1 ) ); ?>"><span></span></button>
					<?php endfor; ?>
				</div>
			<?php endif; ?>
		</section>
		<?php endif; ?>

		<section class="sbvi-cta-band sbvi-container">
			<div class="sbvi-rule sbvi-rule--thick"></div>
			<div class="sbvi-cta-band__grid">
				<h2><?php echo wp_kses_post( sbvi_home_field( $home_id, 'sbvi_closing_heading' ) ); ?></h2>
				<div>
					<p><?php echo esc_html( sbvi_home_field( $home_id, 'sbvi_closing_body' ) ); ?></p>
					<a href="<?php echo esc_url( sbvi_contact_url() ); ?>" class="btn btn-primary"><?php echo esc_html( sbvi_home_field( $home_id, 'sbvi_closing_cta' ) ); ?></a>
				</div>
			</div>
		</section>

		<?php
	endwhile;
endif;

get_footer();
