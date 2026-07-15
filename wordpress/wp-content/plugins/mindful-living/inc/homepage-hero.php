<?php
/**
 * Front-page hero: full-bleed image with scroll motion.
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the homepage hero above page content.
 */
function accelevate_render_homepage_hero() {
	if ( ! is_front_page() ) {
		return;
	}

	$image = plugins_url( 'assets/images/hero-intention.jpg', dirname( __DIR__ ) . '/mindful-living.php' );
	$blog  = get_permalink( (int) get_option( 'page_for_posts' ) );
	if ( ! $blog ) {
		$blog = home_url( '/blog/' );
	}
	?>
	<section class="ml-home-hero" aria-label="Accelevate introduction">
		<div class="ml-home-hero__media" data-ml-hero-media>
			<img
				src="<?php echo esc_url( $image ); ?>"
				alt="A quiet path toward a calm horizon at first light"
				class="ml-home-hero__image"
				width="1600"
				height="900"
				decoding="async"
				fetchpriority="high"
			/>
			<div class="ml-home-hero__veil" aria-hidden="true"></div>
		</div>

		<div class="ml-home-hero__inner site-container">
			<p class="ml-home-hero__brand" data-ml-hero-anim="brand"><?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?></p>
			<h1 class="ml-home-hero__title" data-ml-hero-anim="title">Grow with intention, not urgency</h1>
			<p class="ml-home-hero__text" data-ml-hero-anim="text">A calm space for goals, habits, mindset, and reflection — written to help you return to what matters.</p>
			<div class="ml-home-hero__actions" data-ml-hero-anim="cta">
				<a class="ml-home-hero__cta" href="<?php echo esc_url( $blog ); ?>">Explore the journal</a>
			</div>
		</div>

		<div class="ml-home-hero__scroll" aria-hidden="true" data-ml-hero-anim="scroll">
			<span></span>
		</div>
	</section>
	<?php
}
add_action( 'kadence_before_content', 'accelevate_render_homepage_hero', 5 );

/**
 * Enqueue homepage hero motion script.
 */
function accelevate_homepage_hero_assets() {
	if ( ! is_front_page() ) {
		return;
	}

	$plugin_file = dirname( __DIR__ ) . '/mindful-living.php';

	wp_enqueue_script(
		'accelevate-hero',
		plugins_url( 'assets/hero.js', $plugin_file ),
		array(),
		'1.5.0',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'accelevate_homepage_hero_assets', 30 );
