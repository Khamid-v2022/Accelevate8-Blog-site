<?php
/**
 * Create company pages used by the footer (editable in wp-admin).
 *
 * @package Accelevate
 */

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
		'lead'  => 'A few common questions about Accelevate and the journal.',
		'body'  => 'How often do you publish? We release thoughtful essays regularly — start on the Blog page. Can I subscribe? Yes — use the subscribe form in the footer. Edit this page in wp-admin to refine the answers.',
	),
	'privacy-policy' => array(
		'title' => 'Privacy Policy',
		'lead'  => 'How we handle information when you visit Accelevate or join the newsletter.',
		'body'  => 'We collect only what we need to run the site and newsletter (such as your email if you subscribe). We do not sell your information. Update this policy with your legal language before launch.',
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

WP_CLI::success( 'Company pages ready (About, Contact, FAQ, Privacy Policy).' );
