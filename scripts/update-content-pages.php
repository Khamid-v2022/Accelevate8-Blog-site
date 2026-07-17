<?php
/**
 * Update company page content (editable afterward in wp-admin).
 *
 * Usage: wp eval-file scripts/update-content-pages.php
 * Or: php -r "require 'wordpress/wp-load.php'; require 'scripts/update-content-pages.php';"
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$root = dirname( __DIR__ );

$updates = array(
	'about'          => array(
		'title' => 'About Us',
		'file'  => $root . '/scripts/about-page-content.html',
	),
	'contact'        => array(
		'title' => 'Contact Us',
		'file'  => $root . '/scripts/contact-page-content.html',
	),
	'faq'            => array(
		'title' => 'FAQs',
		'file'  => $root . '/scripts/faq-page-content.html',
	),
	'privacy-policy' => array(
		'title' => 'Privacy Policy',
		'file'  => $root . '/scripts/privacy-policy-content.html',
	),
	'terms-of-use'   => array(
		'title' => 'Terms of Use',
		'file'  => $root . '/scripts/terms-of-use-content.html',
	),
);

foreach ( $updates as $slug => $data ) {
	if ( ! file_exists( $data['file'] ) ) {
		echo "Missing file: {$data['file']}\n";
		continue;
	}

	$content  = file_get_contents( $data['file'] );
	$existing = get_page_by_path( $slug );

	if ( $existing ) {
		wp_update_post(
			array(
				'ID'           => $existing->ID,
				'post_title'   => $data['title'],
				'post_status'  => 'publish',
				'post_content' => $content,
			)
		);
		echo "Updated page: {$slug} (#{$existing->ID})\n";
	} else {
		$id = wp_insert_post(
			array(
				'post_title'   => $data['title'],
				'post_name'    => $slug,
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => $content,
			),
			true
		);
		echo is_wp_error( $id ) ? $id->get_error_message() . "\n" : "Created page: {$slug} (#{$id})\n";
	}
}

$privacy = get_page_by_path( 'privacy-policy' );
if ( $privacy ) {
	update_option( 'wp_page_for_privacy_policy', (int) $privacy->ID );
	echo "Privacy Policy page registered (#{$privacy->ID})\n";
}

$home_file = $root . '/scripts/homepage-content.html';
$home_id   = (int) get_option( 'page_on_front' );
if ( $home_id && file_exists( $home_file ) ) {
	wp_update_post(
		array(
			'ID'           => $home_id,
			'post_content' => file_get_contents( $home_file ),
		)
	);
	echo "Updated homepage (#{$home_id})\n";
} else {
	echo "Homepage not updated (page_on_front={$home_id})\n";
}

echo "Done.\n";
