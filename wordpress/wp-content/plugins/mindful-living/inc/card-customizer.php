<?php
/**
 * Customizer controls for post card layout (wp-admin editable).
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register card layout settings under Appearance → Customize.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function accelevate_register_card_customizer( $wp_customize ) {
	$wp_customize->add_section(
		'accelevate_cards',
		array(
			'title'       => __( 'Accelevate Post Cards', 'mindful-living' ),
			'description' => __( 'Control how titles and Read More buttons align in post grids. Content remains fully editable in Posts — these options only change presentation.', 'mindful-living' ),
			'priority'    => 40,
		)
	);

	$wp_customize->add_setting(
		'accelevate_card_title_lines',
		array(
			'default'           => 2,
			'sanitize_callback' => function ( $value ) {
				$value = absint( $value );
				return max( 1, min( 3, $value ) );
			},
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'accelevate_card_title_lines',
		array(
			'label'       => __( 'Title max lines', 'mindful-living' ),
			'description' => __( 'Longer titles truncate with an ellipsis. Full titles stay editable in Posts.', 'mindful-living' ),
			'section'     => 'accelevate_cards',
			'type'        => 'select',
			'choices'     => array(
				1 => __( '1 line', 'mindful-living' ),
				2 => __( '2 lines (recommended)', 'mindful-living' ),
				3 => __( '3 lines', 'mindful-living' ),
			),
		)
	);

	$wp_customize->add_setting(
		'accelevate_card_meta_gap',
		array(
			'default'           => 8,
			'sanitize_callback' => function ( $value ) {
				$value = absint( $value );
				return max( 0, min( 32, $value ) );
			},
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'accelevate_card_meta_gap',
		array(
			'label'       => __( 'Space under date (px)', 'mindful-living' ),
			'description' => __( 'Gap between the date and the Read More link when titles fill the title area.', 'mindful-living' ),
			'section'     => 'accelevate_cards',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 0,
				'max'  => 32,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'accelevate_card_align_readmore',
		array(
			'default'           => true,
			'sanitize_callback' => function ( $value ) {
				return (bool) $value;
			},
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'accelevate_card_align_readmore',
		array(
			'label'       => __( 'Align Read More across each row', 'mindful-living' ),
			'description' => __( 'Recommended: pins Read More to the bottom of equal-height cards so buttons share one line.', 'mindful-living' ),
			'section'     => 'accelevate_cards',
			'type'        => 'checkbox',
		)
	);
}
add_action( 'customize_register', 'accelevate_register_card_customizer' );

/**
 * Output CSS variables from Customizer settings.
 */
function accelevate_card_customizer_css() {
	$lines      = absint( get_theme_mod( 'accelevate_card_title_lines', 2 ) );
	$lines      = max( 1, min( 3, $lines ) );
	$gap        = absint( get_theme_mod( 'accelevate_card_meta_gap', 8 ) );
	$gap        = max( 0, min( 32, $gap ) );
	$align      = (bool) get_theme_mod( 'accelevate_card_align_readmore', true );
	$footer_mt  = $align ? 'auto' : '0';

	$css = ':root{';
	$css .= '--ml-card-title-lines:' . $lines . ';';
	$css .= '--ml-card-meta-gap:' . $gap . 'px;';
	$css .= '--ml-card-footer-mt:' . $footer_mt . ';';
	$css .= '}';

	wp_add_inline_style( 'mindful-living-custom', $css );
}
add_action( 'wp_enqueue_scripts', 'accelevate_card_customizer_css', 40 );
