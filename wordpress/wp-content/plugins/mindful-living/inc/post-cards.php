<?php
/**
 * Shared post card markup and homepage latest-posts shortcode.
 * Markup mirrors Kadence archive cards for visual parity.
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a single editorial post card matching the blog archive pattern.
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
	$title_attr = the_title_attribute(
		array(
			'echo' => false,
			'post' => $post,
		)
	);
	?>
	<article <?php post_class( 'entry content-bg loop-entry ml-post-card', $post ); ?>>
		<?php if ( has_post_thumbnail( $post ) ) : ?>
			<a
				class="ml-card-media"
				aria-hidden="true"
				tabindex="-1"
				href="<?php echo esc_url( $permalink ); ?>"
			>
				<?php
				echo get_the_post_thumbnail(
					$post,
					'medium_large',
					array(
						'alt' => $title_attr,
					)
				);
				?>
			</a>
		<?php endif; ?>

		<div class="entry-content-wrap">
			<?php if ( $category ) : ?>
				<div class="entry-taxonomies">
					<a class="category-style category-style-normal" href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
						<?php echo esc_html( $category->name ); ?>
					</a>
				</div>
			<?php endif; ?>

			<h2 class="entry-title">
				<a href="<?php echo esc_url( $permalink ); ?>" rel="bookmark" title="<?php echo esc_attr( get_the_title( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
			</h2>

			<div class="entry-meta">
				<span class="posted-on">
					<time class="entry-date published" datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post ) ); ?>">
						<?php echo esc_html( get_the_date( '', $post ) ); ?>
					</time>
				</span>
			</div>

			<footer class="entry-footer">
				<a class="post-more-link" href="<?php echo esc_url( $permalink ); ?>">
					<?php esc_html_e( 'Read More', 'mindful-living' ); ?>
					<span class="screen-reader-text"> <?php echo esc_html( get_the_title( $post ) ); ?></span>
				</a>
			</footer>
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
	echo '<ul class="ml-post-grid ml-dynamic-grid">';
	while ( $query->have_posts() ) {
		$query->the_post();
		echo '<li class="entry-list-item">';
		accelevate_render_post_card( get_post() );
		echo '</li>';
	}
	echo '</ul>';
	wp_reset_postdata();

	return (string) ob_get_clean();
}
add_shortcode( 'accelevate_latest_posts', 'accelevate_latest_posts_shortcode' );
