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
			'context'     => 'post',
			'layout'      => 'card',
			'eyebrow'     => get_theme_mod( 'accelevate_post_cta_eyebrow', __( 'Before you go', 'mindful-living' ) ),
			'title'       => get_theme_mod( 'accelevate_post_cta_title', __( 'Let the next essay find you', 'mindful-living' ) ),
			'text'        => get_theme_mod(
				'accelevate_post_cta_text',
				__( 'If this one gave you something to sit with, there\'s more where it came from — calm, thoughtful reads delivered to your inbox. No noise, no pressure. Short enough for your morning coffee.', 'mindful-living' )
			),
			'placeholder' => get_theme_mod( 'accelevate_post_cta_placeholder', __( 'Email address', 'mindful-living' ) ),
			'button'      => get_theme_mod( 'accelevate_post_cta_button', __( 'Join the journal', 'mindful-living' ) ),
		)
	);
}
add_action( 'kadence_single_after_entry_content', 'accelevate_render_post_subscribe_cta', 12 );
