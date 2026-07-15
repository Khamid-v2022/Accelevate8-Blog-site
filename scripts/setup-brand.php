<?php
/**
 * Apply Accelevate branding without uploading logos to the media library.
 *
 * @package Accelevate
 */

require_once WP_PLUGIN_DIR . '/mindful-living/mindful-living.php';

remove_theme_mod( 'custom_logo' );

update_option( 'blogname', 'Accelevate' );
update_option( 'blogdescription', 'Thoughtful ideas for a calmer, more intentional life' );

$home = get_page_by_path( 'home' );
if ( $home ) {
	$content = file_get_contents( __DIR__ . '/homepage-content.html' );
	if ( $content ) {
		wp_update_post(
			array(
				'ID'           => $home->ID,
				'post_content' => $content,
			)
		);
	}
}

// Footer logo is rendered dynamically by mindful_living_footer_html().
set_theme_mod( 'footer_html_content', 'managed-by-mindful-living-plugin' );

WP_CLI::success( 'Branding applied using plugin logo assets.' );
