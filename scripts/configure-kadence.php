<?php
/**
 * Apply minimal, safe Kadence theme mods that do not override internal schemas.
 *
 * @package Accelevate
 */

$preserve = get_theme_mod( 'nav_menu_locations', array() );

set_theme_mod( 'nav_menu_locations', $preserve );
set_theme_mod( 'scroll_to_top', true );
set_theme_mod( 'post_content_style', 'unboxed' );
set_theme_mod( 'post_vertical_padding', 'medium' );
set_theme_mod( 'post_featured_image_display', true );
set_theme_mod( 'post_feature_image_placement', 'above' );
set_theme_mod( 'post_archive_item_image_placement', 'beside' );
set_theme_mod( 'post_archive_columns', '4' );
set_theme_mod(
	'post_archive_element_meta',
	array(
		'id'                     => 'meta',
		'enabled'                => true,
		'divider'                => 'dot',
		'author'                 => false,
		'authorLink'             => false,
		'authorImage'            => false,
		'authorImageSize'        => 25,
		'authorEnableLabel'      => false,
		'authorLabel'            => '',
		'date'                   => true,
		'dateTime'               => false,
		'dateEnableLabel'        => false,
		'dateLabel'              => '',
		'dateUpdated'            => false,
		'dateUpdatedTime'        => false,
		'dateUpdatedDifferent'   => false,
		'dateUpdatedEnableLabel' => false,
		'dateUpdatedLabel'       => '',
		'categories'             => false,
		'categoriesEnableLabel'  => false,
		'categoriesLabel'        => '',
		'comments'               => false,
		'commentsCondition'      => false,
	)
);
set_theme_mod(
	'footer_html_content',
	'<p style="text-align:center">© ' . gmdate( 'Y' ) . ' Accelevate. Thoughtful ideas for a calmer, more intentional life.</p>'
);

WP_CLI::success( 'Safe Kadence theme settings applied.' );
