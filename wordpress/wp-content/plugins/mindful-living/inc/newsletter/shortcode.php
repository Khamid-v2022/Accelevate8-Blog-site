<?php
/**
 * Newsletter shortcode.
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * [accelevate_subscribe layout="inline|card" title="" eyebrow="" text="" legal=""]
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function accelevate_subscribe_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'layout'  => 'inline',
			'title'   => __( 'Get calm updates in your inbox', 'mindful-living' ),
			'eyebrow' => '',
			'text'    => '',
			'legal'   => __( 'By entering your email, you agree to our Privacy Policy. No spam — just thoughtful notes.', 'mindful-living' ),
		),
		$atts,
		'accelevate_subscribe'
	);

	static $instance = 0;
	++$instance;

	return accelevate_newsletter_render_block(
		array(
			'layout'   => in_array( $atts['layout'], array( 'inline', 'card', 'minimal' ), true ) ? $atts['layout'] : 'inline',
			'context'  => 'shortcode',
			'eyebrow'  => $atts['eyebrow'],
			'title'    => $atts['title'],
			'text'     => $atts['text'],
			'legal'    => $atts['legal'],
			'field_id' => 'accelevate-subscribe-email-' . $instance,
		)
	);
}
add_shortcode( 'accelevate_subscribe', 'accelevate_subscribe_shortcode' );
