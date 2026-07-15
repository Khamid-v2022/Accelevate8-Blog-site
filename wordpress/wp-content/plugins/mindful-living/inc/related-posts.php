<?php
/**
 * Similar Posts section — blog cards inside Kadence Splide carousel.
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disable Kadence default related markup — replaced by Accelevate carousel.
 *
 * @param mixed $value Theme mod value.
 * @return bool
 */
function accelevate_disable_kadence_related( $value ) {
	unset( $value );
	return false;
}
add_filter( 'theme_mod_post_related', 'accelevate_disable_kadence_related' );

/**
 * Enqueue Splide assets for the related carousel.
 */
function accelevate_enqueue_related_carousel_assets() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	wp_enqueue_style( 'kad-splide' );
	wp_enqueue_script( 'kadence-slide-init' );
}
add_action( 'wp_enqueue_scripts', 'accelevate_enqueue_related_carousel_assets', 40 );

/**
 * Build related-posts query args for the current post.
 *
 * @param int $post_id Post ID.
 * @param int $count   Number of posts.
 * @return array
 */
function accelevate_related_posts_query_args( $post_id, $count = 6 ) {
	$post_id = absint( $post_id );
	$count   = max( 3, min( 12, absint( $count ) ) );

	$categories = get_the_terms( $post_id, 'category' );
	$cat_ids    = ( empty( $categories ) || is_wp_error( $categories ) ) ? array() : wp_list_pluck( $categories, 'term_id' );

	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $count,
		'post__not_in'        => array( $post_id ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
	);

	if ( ! empty( $cat_ids ) ) {
		$args['category__in'] = $cat_ids;
	}

	return apply_filters( 'accelevate_related_posts_args', $args, $post_id );
}

/**
 * Whether related posts should render.
 *
 * @return bool
 */
function accelevate_should_show_related_posts() {
	if ( ! is_singular( 'post' ) ) {
		return false;
	}
	return (bool) apply_filters( 'accelevate_show_related_posts', true );
}

/**
 * Render Similar Posts carousel with blog-matching cards.
 */
function accelevate_render_related_posts() {
	static $done = false;

	if ( $done || ! accelevate_should_show_related_posts() ) {
		return;
	}

	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return;
	}

	$count = absint( get_theme_mod( 'accelevate_related_count', 6 ) );
	$count = max( 3, min( 12, $count ) );

	$query = new WP_Query( accelevate_related_posts_query_args( $post_id, $count ) );
	if ( ! $query->have_posts() ) {
		return;
	}

	$done = true;

	$title = get_theme_mod( 'accelevate_related_title', '' );
	if ( '' === $title ) {
		$kadence_title = get_theme_mod( 'post_related_title', '' );
		$title         = $kadence_title ? $kadence_title : __( 'Similar Posts', 'mindful-living' );
	}

	$intro = get_theme_mod( 'accelevate_related_intro', '' );
	$dots  = (bool) get_theme_mod( 'accelevate_related_dots', true );
	$loop  = (bool) get_theme_mod( 'accelevate_related_loop', true );

	if ( function_exists( 'Kadence\kadence' ) ) {
		\Kadence\kadence()->print_styles( 'kad-splide' );
	}
	?>
	<section class="ml-related entry-related alignfull" aria-labelledby="ml-related-title">
		<div class="ml-related__inner content-container site-container">
			<header class="ml-related__header">
				<p class="ml-related__eyebrow"><?php esc_html_e( 'Keep reading', 'mindful-living' ); ?></p>
				<h2 class="ml-related__title entry-related-title" id="ml-related-title"><?php echo esc_html( $title ); ?></h2>
				<?php if ( $intro ) : ?>
					<p class="ml-related__intro"><?php echo esc_html( $intro ); ?></p>
				<?php endif; ?>
			</header>

			<div
				class="ml-related__carousel entry-related-carousel kadence-slide-init splide"
				aria-labelledby="ml-related-title"
				data-columns-xxl="3"
				data-columns-xl="3"
				data-columns-md="3"
				data-columns-sm="2"
				data-columns-xs="2"
				data-columns-ss="1"
				data-slider-anim-speed="400"
				data-slider-scroll="1"
				data-slider-dots="<?php echo $dots ? 'true' : 'false'; ?>"
				data-slider-arrows="true"
				data-slider-hover-pause="false"
				data-slider-auto="false"
				data-slider-speed="7000"
				data-slider-gutter="24"
				data-slider-loop="<?php echo $loop ? 'true' : 'false'; ?>"
				data-slider-next-label="<?php echo esc_attr__( 'Next', 'mindful-living' ); ?>"
				data-slider-prev-label="<?php echo esc_attr__( 'Previous', 'mindful-living' ); ?>"
				data-slider-slide-label="<?php echo esc_attr__( 'Posts', 'mindful-living' ); ?>"
			>
				<div class="splide__track">
					<ul class="splide__list">
						<?php
						while ( $query->have_posts() ) :
							$query->the_post();
							echo '<li class="entry-list-item carousel-item splide__slide">';
							accelevate_render_post_card( get_post() );
							echo '</li>';
						endwhile;
						wp_reset_postdata();
						?>
					</ul>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Render related posts just before the comments template (outside #comments).
 *
 * @param string $template Comments template path.
 * @return string
 */
function accelevate_related_before_comments_template( $template ) {
	accelevate_render_related_posts();
	return $template;
}
add_filter( 'comments_template', 'accelevate_related_before_comments_template', 5 );

/**
 * Fallback when comments are closed / not shown.
 */
add_action( 'kadence_after_main_content', 'accelevate_render_related_posts', 8 );

/**
 * Customizer controls for Similar Posts.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function accelevate_register_related_customizer( $wp_customize ) {
	$wp_customize->add_setting(
		'accelevate_related_title',
		array(
			'default'           => __( 'Similar Posts', 'mindful-living' ),
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'accelevate_related_title',
		array(
			'label'       => __( 'Similar Posts title', 'mindful-living' ),
			'description' => __( 'Heading above related cards on single posts.', 'mindful-living' ),
			'section'     => 'accelevate_single_post',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'accelevate_related_intro',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'accelevate_related_intro',
		array(
			'label'       => __( 'Similar Posts intro (optional)', 'mindful-living' ),
			'description' => __( 'Short line under the heading. Leave blank to hide.', 'mindful-living' ),
			'section'     => 'accelevate_single_post',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'accelevate_related_count',
		array(
			'default'           => 6,
			'sanitize_callback' => function ( $value ) {
				$value = absint( $value );
				return max( 3, min( 12, $value ) );
			},
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'accelevate_related_count',
		array(
			'label'       => __( 'Number of similar posts', 'mindful-living' ),
			'description' => __( 'Carousel shows 3 at a time on desktop; extra posts scroll.', 'mindful-living' ),
			'section'     => 'accelevate_single_post',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 3,
				'max'  => 12,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'accelevate_related_dots',
		array(
			'default'           => true,
			'sanitize_callback' => function ( $value ) {
				return (bool) $value;
			},
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'accelevate_related_dots',
		array(
			'label'   => __( 'Show carousel dots', 'mindful-living' ),
			'section' => 'accelevate_single_post',
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'accelevate_related_loop',
		array(
			'default'           => true,
			'sanitize_callback' => function ( $value ) {
				return (bool) $value;
			},
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'accelevate_related_loop',
		array(
			'label'   => __( 'Loop carousel', 'mindful-living' ),
			'section' => 'accelevate_single_post',
			'type'    => 'checkbox',
		)
	);
}
add_action( 'customize_register', 'accelevate_register_related_customizer', 20 );
