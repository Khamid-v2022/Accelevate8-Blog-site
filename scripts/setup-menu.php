<?php
$menu_name = 'Main Menu';
$menu = wp_get_nav_menu_object( $menu_name );

if ( ! $menu ) {
	$menu_id = wp_create_nav_menu( $menu_name );
} else {
	$menu_id = $menu->term_id;
	$items   = wp_get_nav_menu_items( $menu_id );
	if ( $items ) {
		foreach ( $items as $item ) {
			wp_delete_post( $item->ID, true );
		}
	}
}

wp_update_nav_menu_item(
	$menu_id,
	0,
	array(
		'menu-item-title'  => 'Home',
		'menu-item-url'    => home_url( '/' ),
		'menu-item-status' => 'publish',
	)
);

wp_update_nav_menu_item(
	$menu_id,
	0,
	array(
		'menu-item-title'  => 'Blog',
		'menu-item-url'    => home_url( '/blog/' ),
		'menu-item-status' => 'publish',
	)
);

foreach ( array( 'about' => 'About Us', 'contact' => 'Contact Us' ) as $slug => $label ) {
	$page = get_page_by_path( $slug );
	if ( ! $page ) {
		continue;
	}
	wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'     => $label,
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $page->ID,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		)
	);
}

$locations            = get_theme_mod( 'nav_menu_locations', array() );
$locations['primary'] = (int) $menu_id;
$locations['mobile']  = (int) $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );

WP_CLI::success( "Navigation menu configured (ID {$menu_id})." );
