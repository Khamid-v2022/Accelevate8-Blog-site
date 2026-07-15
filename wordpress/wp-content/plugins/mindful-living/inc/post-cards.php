<?php
/**
 * Shared post card markup and homepage latest-posts shortcode.
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a single editorial post card (used on homepage and kept in sync with archive styling).
 *
 * @param WP_Post|null $post Post object.
 */
function accelevate_render_post_card( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return;
	}

	$permalink  = get_permalink( $post );
	$categories = get_the_category( $post->ID );
	$category   = $categories ? $categories[0] : null;
	$date       = get_the_date( '', $post );
	?>
	<article <?php post_class( 'entry content-bg loop-entry ml-post-card', $post ); ?>>
		<?php if ( has_post_thumbnail( $post ) ) : ?>
			<a class="ml-card-media" href="<?php echo esc_url( $permalink ); ?>" aria-hidden="true" tabindex="-1">
				<?php
				echo get_the_post_thumbnail(
					$post,
					'medium_large',
					array(
						'alt'     => the_title_attribute( array( 'echo' => false, 'post' => $post ) ),
						'loading' => 'lazy',
					)
				);
				?>
			</a>
		<?php endif; ?>

		<div class="entry-content-wrap ml-card-body">
			<?php if ( $category ) : ?>
				<div class="entry-taxonomies">
					<a class="category-style category-style-normal" href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
						<?php echo esc_html( $category->name ); ?>
					</a>
				</div>
			<?php endif; ?>

			<h3 class="entry-title">
				<a href="<?php echo esc_url( $permalink ); ?>" rel="bookmark"><?php echo esc_html( get_the_title( $post ) ); ?></a>
			</h3>

			<div class="entry-meta ml-card-meta">
				<span class="posted-on">
					<time class="entry-date published" datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post ) ); ?>">
						<?php echo esc_html( $date ); ?>
					</time>
				</span>
			</div>

			<div class="entry-footer ml-card-footer">
				<a class="post-more-link" href="<?php echo esc_url( $permalink ); ?>">
					<?php esc_html_e( 'Read More', 'mindful-living' ); ?>
					<span class="screen-reader-text"> <?php echo esc_html( get_the_title( $post ) ); ?></span>
				</a>
			</div>
		</div>
	</article>
	<?php
}

/**
 * Shortcode: [accelevate_latest_posts count="4"]
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function accelevate_latest_posts_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'count' => 4,
		),
		$atts,
		'accelevate_latest_posts'
	);

	$count = max( 1, min( 12, (int) $atts['count'] ) );

	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $count,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	if ( ! $query->have_posts() ) {
		return '';
	}

	ob_start();
	echo '<div class="ml-post-grid ml-dynamic-grid">';
	while ( $query->have_posts() ) {
		$query->the_post();
		accelevate_render_post_card( get_post() );
	}
	echo '</div>';
	wp_reset_postdata();

	return (string) ob_get_clean();
}
add_shortcode( 'accelevate_latest_posts', 'accelevate_latest_posts_shortcode' );
