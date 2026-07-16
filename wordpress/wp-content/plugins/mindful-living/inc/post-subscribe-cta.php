<?php
/**
 * Subscribe call-to-action at the end of single posts.
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render subscribe CTA after post content.
 */
function accelevate_render_post_subscribe_cta() {
	if ( ! is_singular( 'post' ) || ! (bool) get_theme_mod( 'accelevate_post_subscribe_cta', true ) ) {
		return;
	}

	echo accelevate_newsletter_render_block( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		array(
			'context' => 'post',
			'layout'  => 'card',
			'eyebrow' => get_theme_mod( 'accelevate_post_cta_eyebrow', __( 'Stay with us', 'mindful-living' ) ),
			'title'   => get_theme_mod( 'accelevate_post_cta_title', __( 'Get calm updates in your inbox', 'mindful-living' ) ),
			'text'    => get_theme_mod(
				'accelevate_post_cta_text',
				__( 'New essays on intention, habits, and reflection — short enough for your morning coffee.', 'mindful-living' )
			),
		)
	);
}
add_action( 'kadence_single_after_entry_content', 'accelevate_render_post_subscribe_cta', 12 );
