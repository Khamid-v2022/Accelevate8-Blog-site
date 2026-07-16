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
 * Persist the latest newsletter delivery result for admin diagnostics.
 *
 * @param array $data Diagnostic payload.
 * @return void
 */
function accelevate_newsletter_record_status( $data ) {
	$payload = wp_parse_args(
		(array) $data,
		array(
			'time'              => gmdate( 'c' ),
			'provider'          => accelevate_newsletter_provider(),
			'email'             => '',
			'source'            => '',
			'status'            => 'unknown',
			'message'           => '',
			'detail'            => '',
			'used_backup'       => false,
			'refresh_attempted' => false,
			'refresh_status'    => '',
		)
	);

	update_option( 'accelevate_newsletter_last_status', $payload, false );
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
	$source   = isset( $context['source'] ) ? sanitize_key( $context['source'] ) : '';

	if ( 'aweber' === $provider ) {
		$result = accelevate_newsletter_aweber_subscribe( $email, $context );
		if ( is_wp_error( $result ) ) {
			$error_data = $result->get_error_data();
			if ( ! is_array( $error_data ) ) {
				$error_data = array();
			}

			$detail_parts = array( $result->get_error_message() );
			if ( ! empty( $error_data['refresh_status'] ) ) {
				$detail_parts[] = 'refresh=' . sanitize_text_field( (string) $error_data['refresh_status'] );
			}
			if ( ! empty( $error_data['oauth_response'] ) ) {
				$detail_parts[] = sanitize_text_field( (string) $error_data['oauth_response'] );
			} elseif ( ! empty( $error_data['aweber_response'] ) ) {
				$detail_parts[] = sanitize_text_field( (string) $error_data['aweber_response'] );
			}

			accelevate_newsletter_record_status(
				array(
					'provider'          => 'aweber',
					'email'             => $email,
					'source'            => $source,
					'status'            => 'failed',
					'message'           => $result->get_error_code(),
					'detail'            => implode( ' | ', array_filter( $detail_parts ) ),
					'refresh_attempted' => ! empty( $error_data['refresh_attempt'] ),
					'refresh_status'    => isset( $error_data['refresh_status'] ) ? (string) $error_data['refresh_status'] : '',
				)
			);

			if ( accelevate_aweber_is_configured() ) {
				return $result;
			}

			// Only fall back while credentials are still incomplete during initial setup.
			$local = accelevate_newsletter_local_subscribe( $email, $context );
			if ( is_wp_error( $local ) ) {
				return $result;
			}

			accelevate_newsletter_record_status(
				array(
					'provider'          => 'aweber',
					'email'             => $email,
					'source'            => $source,
					'status'            => 'fallback_local',
					'message'           => $result->get_error_code(),
					'detail'            => implode( ' | ', array_filter( $detail_parts ) ),
					'used_backup'       => true,
					'refresh_attempted' => ! empty( $error_data['refresh_attempt'] ),
					'refresh_status'    => isset( $error_data['refresh_status'] ) ? (string) $error_data['refresh_status'] : '',
				)
			);
			return $local;
		}
		accelevate_newsletter_record_status(
			array(
				'provider' => 'aweber',
				'email'    => $email,
				'source'   => $source,
				'status'   => 'success',
				'message'  => 'synced_to_aweber',
			)
		);
		return true;
	}

	$result = accelevate_newsletter_local_subscribe( $email, $context );
	if ( is_wp_error( $result ) ) {
		accelevate_newsletter_record_status(
			array(
				'provider' => 'local',
				'email'    => $email,
				'source'   => $source,
				'status'   => 'failed',
				'message'  => $result->get_error_code(),
				'detail'   => $result->get_error_message(),
			)
		);
		return $result;
	}

	accelevate_newsletter_record_status(
		array(
			'provider' => 'local',
			'email'    => $email,
			'source'   => $source,
			'status'   => 'success',
			'message'  => 'saved_locally',
		)
	);
	return $result;
}
