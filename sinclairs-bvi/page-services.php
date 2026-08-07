<?php
/**
 * Template Name: Our Services (hub)
 * 3 grouped sections, each made up of "nutshell" rows drawn from all 8
 * Service posts (see sbvi_nutshell_groups() / sbvi_nutshell_items()).
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
		$services  = sbvi_get_services();
		$groups    = sbvi_nutshell_groups();
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

		<section class="sbvi-services-hub sbvi-container">
			<?php foreach ( $groups as $group_slug => $group_label ) :
				$rows = array();
				foreach ( $services as $service ) {
					foreach ( sbvi_nutshell_items( $service->ID ) as $item ) {
						if ( isset( $item['group'] ) && $group_slug === $item['group'] ) {
							$rows[] = array( 'service' => $service, 'item' => $item );
						}
					}
				}
				if ( ! $rows ) {
					continue;
				}
				?>
				<div class="sbvi-service-group" data-reveal>
					<h2 class="sbvi-service-group__heading"><?php echo esc_html( $group_label ); ?></h2>
					<div class="sbvi-service-group__rows">
						<?php foreach ( $rows as $row ) : ?>
							<a href="<?php echo esc_url( get_permalink( $row['service'] ) ); ?>" class="sbvi-service-row">
								<span class="sbvi-service-row__title"><?php echo esc_html( $row['item']['label'] ); ?></span>
								<span class="sbvi-service-row__desc"><?php echo esc_html( $row['item']['description'] ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</section>

		<?php
	endwhile;
endif;

get_footer();
