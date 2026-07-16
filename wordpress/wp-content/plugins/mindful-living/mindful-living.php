<?php
/**
 * Plugin Name: Accelevate Site Styles
 * Description: Typography and layout enhancements for the Accelevate blog.
 * Version: 1.0.0
 * Author: Accelevate
 * Text Domain: mindful-living
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/inc/archive-layout.php';
require_once __DIR__ . '/inc/post-cards.php';
require_once __DIR__ . '/inc/homepage-hero.php';
require_once __DIR__ . '/inc/card-customizer.php';
require_once __DIR__ . '/inc/site-footer.php';
require_once __DIR__ . '/inc/related-posts.php';
require_once __DIR__ . '/inc/subscribe-form.php';
require_once __DIR__ . '/inc/seo-social.php';
require_once __DIR__ . '/inc/post-subscribe-cta.php';

/**
 * Get plugin logo URL if the file exists in assets/logos.
 *
 * @param string $variant white|color|dark
 */
function mindful_living_logo_url( $variant = 'white' ) {
	$allowed = array( 'white', 'color', 'dark' );
	if ( ! in_array( $variant, $allowed, true ) ) {
		return '';
	}

	$filename = "logo-{$variant}.png";
	$path     = plugin_dir_path( __FILE__ ) . 'assets/logos/' . $filename;
	if ( ! file_exists( $path ) ) {
		return '';
	}

	return plugins_url( 'assets/logos/' . $filename, __FILE__ );
}

/**
 * Build a logo <img> tag from plugin assets.
 *
 * @param string $variant white|color|dark
 * @param array  $attrs   Extra HTML attributes.
 */
function mindful_living_logo_img( $variant = 'white', $attrs = array() ) {
	$url = mindful_living_logo_url( $variant );
	if ( ! $url ) {
		return '';
	}

	$defaults = array(
		'src'      => $url,
		'alt'      => get_bloginfo( 'name', 'display' ),
		'decoding' => 'async',
		'loading'  => 'lazy',
	);
	$attrs    = array_merge( $defaults, $attrs );

	$html = '<img';
	foreach ( $attrs as $key => $value ) {
		if ( '' === $value ) {
			continue;
		}
		$html .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
	}
	$html .= ' />';

	return $html;
}

/**
 * Use clean archive/page titles without WordPress prefixes.
 *
 * @param string $title Archive title.
 */
function mindful_living_archive_title( $title ) {
	if ( is_category() ) {
		return single_cat_title( '', false );
	}
	if ( is_tag() ) {
		return single_tag_title( '', false );
	}
	if ( is_author() ) {
		return get_the_author();
	}
	if ( is_year() ) {
		return get_the_date( 'Y' );
	}
	if ( is_month() ) {
		return get_the_date( 'F Y' );
	}
	if ( is_day() ) {
		return get_the_date();
	}
	if ( is_post_type_archive() ) {
		return post_type_archive_title( '', false );
	}
	if ( is_home() && ! is_front_page() ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		if ( $posts_page ) {
			return get_the_title( $posts_page );
		}
	}
	return $title;
}
add_filter( 'get_the_archive_title', 'mindful_living_archive_title' );

/**
 * Render header logo from plugin assets instead of uploads.
 *
 * @param string $html    Logo HTML.
 * @param int    $blog_id Blog ID.
 */
function mindful_living_kadence_logo( $html, $blog_id ) {
	unset( $blog_id );

	if ( ! empty( $html ) ) {
		return $html;
	}

	$url = mindful_living_logo_url( 'white' );
	if ( ! $url ) {
		return $html;
	}

	return sprintf(
		'<img src="%s" class="custom-logo" alt="%s" decoding="async" />',
		esc_url( $url ),
		esc_attr( get_bloginfo( 'name', 'display' ) )
	);
}
add_filter( 'kadence_custom_logo', 'mindful_living_kadence_logo', 10, 2 );

/**
 * Register theme supports and editor styles.
 */
function mindful_living_setup() {
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
}
add_action( 'after_setup_theme', 'mindful_living_setup' );

/**
 * Load fonts and custom CSS.
 */
function mindful_living_enqueue_assets() {
	wp_enqueue_style(
		'mindful-living-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Inter:wght@400;500;600;700&display=swap',
		array(),
		'1.8.1'
	);

	wp_enqueue_style(
		'mindful-living-custom',
		plugins_url( 'assets/mindful-living.css', __FILE__ ),
		array( 'mindful-living-fonts' ),
		'1.8.1'
	);
}
add_action( 'wp_enqueue_scripts', 'mindful_living_enqueue_assets' );

/**
 * Add editor stylesheet for consistent post editing.
 */
function mindful_living_editor_styles() {
	add_editor_style( plugins_url( 'assets/mindful-living.css', __FILE__ ) );
}
add_action( 'admin_init', 'mindful_living_editor_styles' );

/**
 * Use logo-color.png as the site favicon.
 */
function mindful_living_favicon() {
	$url = mindful_living_logo_url( 'color' );
	if ( ! $url ) {
		return;
	}

	echo '<link rel="icon" href="' . esc_url( $url ) . '" sizes="32x32" />' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( $url ) . '" />' . "\n";
}
add_action( 'wp_head', 'mindful_living_favicon', 5 );
add_action( 'admin_head', 'mindful_living_favicon', 5 );
