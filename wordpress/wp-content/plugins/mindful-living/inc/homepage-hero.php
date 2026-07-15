<?php
/**
 * Front-page hero: full-bleed image with scroll motion.
 * Copy is editable via Appearance → Customize → Accelevate Hero.
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default hero copy.
 *
 * @return array
 */
function accelevate_hero_defaults() {
	return array(
		'eyebrow'      => 'Journal',
		'title_before' => 'Grow with',
		'title_accent' => 'intention',
		'title_after'  => ', not urgency',
		'text'         => 'A calm space for goals, habits, mindset, and reflection — written to help you return to what matters.',
		'cta_label'    => 'Explore the journal',
	);
}

/**
 * Render the homepage hero above page content.
 */
function accelevate_render_homepage_hero() {
	if ( ! is_front_page() ) {
		return;
	}

	$defaults = accelevate_hero_defaults();
	$image    = plugins_url( 'assets/images/hero-intention.jpg', dirname( __DIR__ ) . '/mindful-living.php' );
	$blog     = get_permalink( (int) get_option( 'page_for_posts' ) );
	if ( ! $blog ) {
		$blog = home_url( '/blog/' );
	}

	$eyebrow      = get_theme_mod( 'accelevate_hero_eyebrow', $defaults['eyebrow'] );
	$title_before = get_theme_mod( 'accelevate_hero_title_before', $defaults['title_before'] );
	$title_accent = get_theme_mod( 'accelevate_hero_title_accent', $defaults['title_accent'] );
	$title_after  = get_theme_mod( 'accelevate_hero_title_after', $defaults['title_after'] );
	$text         = get_theme_mod( 'accelevate_hero_text', $defaults['text'] );
	$cta_label    = get_theme_mod( 'accelevate_hero_cta_label', $defaults['cta_label'] );
	$cta_url      = get_theme_mod( 'accelevate_hero_cta_url', $blog );
	$brand        = get_bloginfo( 'name', 'display' );
	?>
	<section class="ml-home-hero" aria-label="<?php echo esc_attr( $brand ); ?> introduction">
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

		<div class="ml-home-hero__inner">
			<div class="ml-home-hero__copy">
				<p class="ml-home-hero__brand" data-ml-hero-anim="brand">
					<span class="ml-home-hero__brand-mark" aria-hidden="true"></span>
					<span class="ml-home-hero__brand-name"><?php echo esc_html( $brand ); ?></span>
					<?php if ( $eyebrow ) : ?>
						<span class="ml-home-hero__eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
					<?php endif; ?>
				</p>

				<h1 class="ml-home-hero__title" data-ml-hero-anim="title">
					<span class="ml-home-hero__title-line"><?php echo esc_html( $title_before ); ?></span>
					<span class="ml-home-hero__title-accent"><?php echo esc_html( $title_accent ); ?></span><span class="ml-home-hero__title-rest"><?php echo esc_html( $title_after ); ?></span>
				</h1>

				<p class="ml-home-hero__text" data-ml-hero-anim="text"><?php echo esc_html( $text ); ?></p>

				<div class="ml-home-hero__actions" data-ml-hero-anim="cta">
					<a class="ml-home-hero__cta" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ); ?></a>
				</div>
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
		'1.6.0',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'accelevate_homepage_hero_assets', 30 );
