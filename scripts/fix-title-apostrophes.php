<?php
/**
 * Fix titles that contain literal "u2019" instead of a curly apostrophe.
 *
 * @package Accelevate
 */

$apostrophe = "\u{2019}";

$fixes = array(
	72 => "You Don{$apostrophe}t Need to Fix Everything at Once",
	36 => "Why It{$apostrophe}s Okay to Outgrow People, Places, and Versions of Yourself",
	30 => "Stillness Isn{$apostrophe}t Stagnation",
	21 => "How to Tell If You{$apostrophe}re Chasing the Wrong Goal",
);

foreach ( $fixes as $id => $title ) {
	$result = wp_update_post(
		array(
			'ID'         => $id,
			'post_title' => $title,
		),
		true
	);

	if ( is_wp_error( $result ) ) {
		WP_CLI::warning( "Failed #{$id}: " . $result->get_error_message() );
		continue;
	}

	WP_CLI::log( "Fixed #{$id}: {$title}" );
}

WP_CLI::success( 'Apostrophe titles updated.' );
