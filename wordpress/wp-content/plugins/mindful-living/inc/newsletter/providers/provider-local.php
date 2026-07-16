<?php
/**
 * Local newsletter provider (WP option + admin email).
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Store subscriber locally as a fallback.
 *
 * @param string $email   Email.
 * @param array  $context Context.
 * @return true|WP_Error
 */
function accelevate_newsletter_local_subscribe( $email, $context = array() ) {
	unset( $context );

	$list = get_option( 'accelevate_subscribers', array() );
	if ( ! is_array( $list ) ) {
		$list = array();
	}

	$list[ $email ] = gmdate( 'c' );
	update_option( 'accelevate_subscribers', $list, false );

	$subject = '[Accelevate] New newsletter subscriber';
	$body    = "A new subscriber joined the list:\n\n{$email}\n";
	wp_mail( get_option( 'admin_email' ), $subject, $body );

	return true;
}
