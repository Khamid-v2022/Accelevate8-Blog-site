<?php
$content = file_get_contents( __DIR__ . '/homepage-content.html' );
if ( false === $content ) {
	WP_CLI::error( 'homepage-content.html not found.' );
}

$home = get_page_by_path( 'home' );
if ( ! $home ) {
	$home_id = wp_insert_post(
		array(
			'post_title'   => 'Home',
			'post_name'    => 'home',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => $content,
		)
	);
} else {
	$home_id = $home->ID;
	wp_update_post(
		array(
			'ID'           => $home_id,
			'post_content' => $content,
			'post_status'  => 'publish',
		)
	);
}

$blog = get_page_by_path( 'blog' );
if ( ! $blog ) {
	$blog_id = wp_insert_post(
		array(
			'post_title'  => 'Blog',
			'post_name'   => 'blog',
			'post_type'   => 'page',
			'post_status' => 'publish',
		)
	);
} else {
	$blog_id = $blog->ID;
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', (int) $home_id );
update_option( 'page_for_posts', (int) $blog_id );

WP_CLI::success( "Homepage set (ID {$home_id}), blog page ID {$blog_id}." );
