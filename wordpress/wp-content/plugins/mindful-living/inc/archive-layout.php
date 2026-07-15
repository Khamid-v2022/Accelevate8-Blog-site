<?php
/**
 * Archive layout enhancements: dynamic grid, category pills, spotlight inserts.
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configure archive listing layout.
 */
function mindful_living_archive_setup() {
	if ( ! is_admin() && ( is_home() || is_archive() || is_search() ) ) {
		remove_action( 'kadence_loop_entry_content', 'Kadence\loop_entry_summary', 20 );
	}
}
add_action( 'wp', 'mindful_living_archive_setup' );

/**
 * Force a compact 4-column archive grid with custom styling hooks.
 *
 * @param array $classes Archive container classes.
 */
function mindful_living_archive_container_classes( $classes ) {
	$replace = array(
		'grid-lg-col-1' => 'grid-lg-col-4',
		'grid-lg-col-2' => 'grid-lg-col-4',
		'grid-lg-col-3' => 'grid-lg-col-4',
	);

	foreach ( $replace as $from => $to ) {
		$key = array_search( $from, $classes, true );
		if ( false !== $key ) {
			$classes[ $key ] = $to;
		}
	}

	if ( ! in_array( 'grid-lg-col-4', $classes, true ) ) {
		$classes[] = 'grid-lg-col-4';
	}

	$classes[] = 'ml-dynamic-grid';
	$classes[] = 'item-image-style-above';

	return array_values( array_unique( $classes ) );
}
add_filter( 'kadence_archive_container_classes', 'mindful_living_archive_container_classes' );

/**
 * Reset card index at the start of each loop.
 *
 * @param WP_Query $query Query.
 */
function mindful_living_reset_card_index( $query ) {
	if ( $query->is_main_query() && ( is_home() || is_archive() || is_search() ) ) {
		$GLOBALS['ml_card_index'] = 0;
	}
}
add_action( 'loop_start', 'mindful_living_reset_card_index' );

/**
 * Add index-based card modifiers for asymmetric layout.
 *
 * @param array $classes Post classes.
 */
function mindful_living_post_card_classes( $classes ) {
	if ( ! in_the_loop() || ! ( is_home() || is_archive() || is_search() ) || 'post' !== get_post_type() ) {
		return $classes;
	}

	if ( ! isset( $GLOBALS['ml_card_index'] ) ) {
		$GLOBALS['ml_card_index'] = 0;
	}

	$GLOBALS['ml_card_index']++;
	$index = (int) $GLOBALS['ml_card_index'];

	$classes[] = 'ml-card-index-' . $index;
	$classes[] = 'ml-post-card';

	return $classes;
}
add_filter( 'post_class', 'mindful_living_post_card_classes' );

/**
 * Eyebrow label above archive/page titles.
 */
function mindful_living_page_eyebrow() {
	if ( is_category() ) {
		$label = 'Topic';
	} elseif ( is_home() && ! is_front_page() ) {
		$label = 'Journal';
	} elseif ( is_singular( 'post' ) ) {
		$label = 'Article';
	} elseif ( is_page() && ! is_front_page() ) {
		$label = 'Page';
	} else {
		$label = 'Explore';
	}

	echo '<p class="ml-page-eyebrow">' . esc_html( $label ) . '</p>';
}
add_action( 'kadence_entry_archive_hero', 'mindful_living_page_eyebrow', 5, 2 );
add_action( 'kadence_entry_hero', 'mindful_living_page_eyebrow', 5, 2 );

/**
 * Category filter pills above archive listings.
 */
function mindful_living_archive_category_pills() {
	if ( ! ( is_home() || is_category() ) ) {
		return;
	}

	$allowed_slugs = array( 'goals', 'habits', 'mindset', 'reflection' );
	$categories    = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'slug'       => $allowed_slugs,
			'orderby'    => 'name',
		)
	);

	if ( is_wp_error( $categories ) || empty( $categories ) ) {
		return;
	}

	$blog_url = get_permalink( (int) get_option( 'page_for_posts' ) );
	if ( ! $blog_url ) {
		$blog_url = home_url( '/blog/' );
	}

	$all_active = is_home() && ! is_category();

	echo '<nav class="ml-category-pills" aria-label="Browse topics">';
	echo '<a class="ml-pill' . ( $all_active ? ' is-active' : '' ) . '" href="' . esc_url( $blog_url ) . '">All</a>';

	foreach ( $categories as $category ) {
		if ( ! in_array( $category->slug, $allowed_slugs, true ) ) {
			continue;
		}
		$active = is_category( $category->term_id ) ? ' is-active' : '';
		echo '<a class="ml-pill' . esc_attr( $active ) . '" href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . esc_html( $category->name ) . '</a>';
	}

	echo '</nav>';
}
add_action( 'kadence_before_main_content', 'mindful_living_archive_category_pills', 8 );

/**
 * Keep archive listings newest-first in complete 4-column rows (8 posts = 2 rows).
 *
 * @param WP_Query $query Query.
 */
function mindful_living_archive_post_order( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_home() || $query->is_category() || $query->is_tag() || $query->is_archive() ) {
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
		$query->set( 'posts_per_page', 8 );
	}
}
add_action( 'pre_get_posts', 'mindful_living_archive_post_order' );

/**
 * Hide Kadence archive descriptions under page titles.
 */
function mindful_living_hide_archive_description() {
	return false;
}
add_filter( 'kadence_show_archive_description', 'mindful_living_hide_archive_description' );

/**
 * Insert a full-width spotlight block to break grid rhythm.
 */
function mindful_living_archive_spotlight() {
	if ( ! ( is_home() || is_archive() || is_search() ) ) {
		return;
	}

	static $count = 0;
	$count++;

	if ( 4 !== $count ) {
		return;
	}

	$blog_url = get_permalink( (int) get_option( 'page_for_posts' ) );
	if ( ! $blog_url ) {
		$blog_url = home_url( '/blog/' );
	}

	echo '<article class="entry loop-entry ml-archive-spotlight content-bg">';
	echo '<div class="ml-archive-spotlight-inner">';
	echo '<p class="ml-spotlight-eyebrow">Featured reading</p>';
	echo '<h2 class="ml-spotlight-title">Thoughtful ideas for a calmer, more intentional life</h2>';
	echo '<p class="ml-spotlight-text">Short, premium reads on goals, habits, mindset, and reflection — designed to be easy on the eyes and easy to return to.</p>';
	echo '<a class="ml-spotlight-link" href="' . esc_url( $blog_url ) . '">Browse all articles</a>';
	echo '</div>';
	echo '</article>';
}
add_action( 'kadence_loop_entry', 'mindful_living_archive_spotlight', 15 );
