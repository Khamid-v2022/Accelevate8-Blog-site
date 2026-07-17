<?php
/**
 * Diagnose block parse issues for a post.
 * Usage: wp eval-file scripts/diagnose-post-editor.php
 *
 * @package Accelevate
 */

$post_id = isset( $args[0] ) ? (int) $args[0] : 78;
$post    = get_post( $post_id );

if ( ! $post ) {
	WP_CLI::error( "Post #{$post_id} not found." );
}

WP_CLI::log( "Post #{$post->ID}: {$post->post_title}" );
WP_CLI::log( 'Status: ' . $post->post_status );
WP_CLI::log( 'Content length: ' . strlen( $post->post_content ) );

$blocks   = parse_blocks( $post->post_content );
$registry = WP_Block_Type_Registry::get_instance();
$unknown  = array();
$nullish  = 0;

foreach ( $blocks as $block ) {
	if ( empty( $block['blockName'] ) ) {
		if ( '' !== trim( (string) $block['innerHTML'] ) ) {
			++$nullish;
		}
		continue;
	}
	if ( ! $registry->is_registered( $block['blockName'] ) ) {
		$unknown[ $block['blockName'] ] = true;
	}
}

WP_CLI::log( 'Top-level blocks: ' . count( $blocks ) );
WP_CLI::log( 'Freeform chunks: ' . $nullish );
WP_CLI::log( 'Unknown block types: ' . ( $unknown ? implode( ', ', array_keys( $unknown ) ) : '(none)' ) );

if ( preg_match( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $post->post_content ) ) {
	WP_CLI::warning( 'Content contains control characters that can crash the editor.' );
}

if ( ! mb_check_encoding( $post->post_content, 'UTF-8' ) ) {
	WP_CLI::warning( 'Content is not valid UTF-8.' );
}

WP_CLI::log( 'siteurl=' . get_option( 'siteurl' ) );
WP_CLI::log( 'home=' . get_option( 'home' ) );
WP_CLI::log( 'permalink=' . get_permalink( $post ) );
WP_CLI::log( 'rest=' . rest_url( 'wp/v2/posts/' . $post_id ) );
WP_CLI::success( 'Local diagnosis complete.' );
