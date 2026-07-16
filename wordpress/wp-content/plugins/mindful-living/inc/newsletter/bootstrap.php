<?php
/**
 * Newsletter module bootstrap.
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/render.php';
require_once __DIR__ . '/handler.php';
require_once __DIR__ . '/shortcode.php';
require_once __DIR__ . '/admin.php';
require_once __DIR__ . '/providers/provider-local.php';
require_once __DIR__ . '/providers/provider-aweber.php';

/**
 * Active newsletter provider slug.
 *
 * @return string aweber|local
 */
function accelevate_newsletter_provider() {
	$provider = get_option( 'accelevate_newsletter_provider', 'aweber' );
	return in_array( $provider, array( 'aweber', 'local' ), true ) ? $provider : 'aweber';
}

/**
 * Subscribe an email via the configured provider chain.
 *
 * @param string $email   Email address.
 * @param array  $context Context (source, tags, etc.).
 * @return true|WP_Error
 */
function accelevate_newsletter_subscribe_email( $email, $context = array() ) {
	$provider = accelevate_newsletter_provider();
	$result   = null;

	if ( 'aweber' === $provider ) {
		$result = accelevate_newsletter_aweber_subscribe( $email, $context );
		if ( is_wp_error( $result ) ) {
			// Graceful fallback so signups are not lost while AWeber is being configured.
			$local = accelevate_newsletter_local_subscribe( $email, $context );
			if ( is_wp_error( $local ) ) {
				return $result;
			}
			return $local;
		}
		return true;
	}

	return accelevate_newsletter_local_subscribe( $email, $context );
}
