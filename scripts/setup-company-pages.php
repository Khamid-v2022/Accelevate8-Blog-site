<?php
/**
 * Create company pages used by the footer (editable in wp-admin).
 *
 * Usage: wp eval-file scripts/setup-company-pages.php
 *
 * @package Accelevate
 */

$pages = array(
	'about'          => array(
		'title' => 'About Us',
		'file'  => __DIR__ . '/about-page-content.html',
	),
	'contact'        => array(
		'title' => 'Contact Us',
		'file'  => __DIR__ . '/contact-page-content.html',
	),
	'faq'            => array(
		'title' => 'FAQs',
		'file'  => __DIR__ . '/faq-page-content.html',
	),
	'privacy-policy' => array(
		'title' => 'Privacy Policy',
		'file'  => __DIR__ . '/privacy-policy-content.html',
	),
	'terms-of-use'   => array(
		'title' => 'Terms of Use',
		'file'  => __DIR__ . '/terms-of-use-content.html',
	),
);

$template = file_get_contents( __DIR__ . '/simple-page-template.html' );

foreach ( $pages as $slug => $data ) {
	if ( ! empty( $data['file'] ) && file_exists( $data['file'] ) ) {
		$content = file_get_contents( $data['file'] );
	} else {
		$content = str_replace(
			array( 'PAGE_TITLE', 'PAGE_LEAD', 'PAGE_BODY' ),
			array( $data['title'], $data['lead'], $data['body'] ),
			$template
		);
	}

	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		wp_update_post(
			array(
				'ID'           => $existing->ID,
				'post_title'   => $data['title'],
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_content' => $content,
			)
		);
		WP_CLI::log( "Updated page: {$slug} (#{$existing->ID})" );
		continue;
	}

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

	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( $id->get_error_message() );
		continue;
	}

	WP_CLI::log( "Created page: {$slug} (#{$id})" );
}

// Register Privacy Policy page in WP settings when present.
$privacy = get_page_by_path( 'privacy-policy' );
if ( $privacy ) {
	update_option( 'wp_page_for_privacy_policy', (int) $privacy->ID );
}

WP_CLI::success( 'Company pages ready (About, Contact, FAQ, Privacy Policy, Terms of Use).' );
