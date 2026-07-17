<?php
/**
 * Newsletter submission handler.
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permalink for the subscribe thank-you page.
 *
 * @return string
 */
function accelevate_newsletter_thank_you_url() {
	$page_id = (int) get_option( 'accelevate_thank_you_page_id', 0 );
	if ( $page_id ) {
		$url = get_permalink( $page_id );
		if ( $url ) {
			return $url;
		}
	}

	$page = get_page_by_path( 'thank-you' );
	if ( $page && 'publish' === $page->post_status ) {
		return get_permalink( $page );
	}

	return '';
}

/**
 * Soft noindex for the thank-you page (conversion destination, not a content page).
 */
function accelevate_newsletter_thank_you_noindex() {
	if ( ! is_page() ) {
		return;
	}

	$page_id  = (int) get_queried_object_id();
	$thanks   = (int) get_option( 'accelevate_thank_you_page_id', 0 );
	$is_slug  = is_page( 'thank-you' );
	$is_opt   = $thanks && $page_id === $thanks;

	if ( ! $is_slug && ! $is_opt ) {
		return;
	}

	echo '<meta name="robots" content="noindex, follow" />' . "\n";
}
add_action( 'wp_head', 'accelevate_newsletter_thank_you_noindex', 1 );

/**
 * Handle newsletter subscribe submissions.
 */
function accelevate_newsletter_handle_subscribe() {
	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );

	if (
		! isset( $_POST['accelevate_subscribe_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['accelevate_subscribe_nonce'] ) ), 'accelevate_subscribe' )
	) {
		wp_safe_redirect( add_query_arg( 'subscribe', 'error', $redirect ) );
		exit;
	}

	$email = isset( $_POST['accelevate_email'] ) ? sanitize_email( wp_unslash( $_POST['accelevate_email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'subscribe', 'error', $redirect ) );
		exit;
	}

	$context = isset( $_POST['accelevate_subscribe_context'] )
		? sanitize_key( wp_unslash( $_POST['accelevate_subscribe_context'] ) )
		: 'unknown';

	$result = accelevate_newsletter_subscribe_email(
		$email,
		array(
			'source' => $context,
			'tags'   => array( 'accelevate-' . $context ),
		)
	);

	if ( is_wp_error( $result ) ) {
		wp_safe_redirect( add_query_arg( 'subscribe', 'error', $redirect ) );
		exit;
	}

	do_action( 'accelevate_newsletter_subscribed', $email, $context );

	$thanks = accelevate_newsletter_thank_you_url();
	if ( $thanks ) {
		wp_safe_redirect( $thanks );
		exit;
	}

	wp_safe_redirect( add_query_arg( 'subscribe', 'sent', $redirect ) );
	exit;
}
add_action( 'admin_post_nopriv_accelevate_subscribe', 'accelevate_newsletter_handle_subscribe' );
add_action( 'admin_post_accelevate_subscribe', 'accelevate_newsletter_handle_subscribe' );
