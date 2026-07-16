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

	wp_safe_redirect( add_query_arg( 'subscribe', 'sent', $redirect ) );
	exit;
}
add_action( 'admin_post_nopriv_accelevate_subscribe', 'accelevate_newsletter_handle_subscribe' );
add_action( 'admin_post_accelevate_subscribe', 'accelevate_newsletter_handle_subscribe' );
