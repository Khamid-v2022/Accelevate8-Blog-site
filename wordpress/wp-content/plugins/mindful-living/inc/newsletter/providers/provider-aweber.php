<?php
/**
 * AWeber newsletter provider.
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether AWeber credentials are configured.
 *
 * @return bool
 */
function accelevate_aweber_is_configured() {
	$account_id = get_option( 'accelevate_aweber_account_id', '' );
	$list_id    = get_option( 'accelevate_aweber_list_id', '' );
	$token      = get_option( 'accelevate_aweber_access_token', '' );

	return '' !== $account_id && '' !== $list_id && '' !== $token;
}

/**
 * Refresh AWeber access token when expired.
 *
 * @return true|WP_Error
 */
function accelevate_aweber_refresh_access_token() {
	$refresh = (string) get_option( 'accelevate_aweber_refresh_token', '' );
	$client  = (string) get_option( 'accelevate_aweber_client_id', '' );
	$secret  = (string) get_option( 'accelevate_aweber_client_secret', '' );

	if ( '' === $refresh || '' === $client ) {
		return new WP_Error( 'aweber_refresh_unavailable', __( 'AWeber refresh token or client ID missing.', 'mindful-living' ) );
	}

	$body = array(
		'grant_type'    => 'refresh_token',
		'refresh_token' => $refresh,
	);

	$headers = array(
		'Content-Type' => 'application/x-www-form-urlencoded',
	);

	// PKCE/public apps: client_id in body only. Confidential apps: Basic auth (preferred by AWeber).
	if ( '' !== $secret ) {
		$headers['Authorization'] = 'Basic ' . base64_encode( $client . ':' . $secret ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	} else {
		$body['client_id'] = $client;
	}

	$response = wp_remote_post(
		'https://auth.aweber.com/oauth2/token',
		array(
			'timeout' => 20,
			'headers' => $headers,
			'body'    => $body,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code         = wp_remote_retrieve_response_code( $response );
	$raw_body     = wp_remote_retrieve_body( $response );
	$data         = json_decode( $raw_body, true );
	$error_detail = accelevate_aweber_format_oauth_error( $code, $raw_body, $data );

	if ( 200 !== $code || empty( $data['access_token'] ) ) {
		return new WP_Error(
			'aweber_refresh_failed',
			sprintf(
				/* translators: %s: OAuth error detail */
				__( 'Could not refresh the AWeber access token. %s', 'mindful-living' ),
				$error_detail
			),
			array(
				'http_code'       => $code,
				'oauth_response'  => $error_detail,
				'refresh_attempt' => true,
			)
		);
	}

	update_option( 'accelevate_aweber_access_token', sanitize_text_field( $data['access_token'] ), false );
	if ( ! empty( $data['refresh_token'] ) ) {
		update_option( 'accelevate_aweber_refresh_token', sanitize_text_field( $data['refresh_token'] ), false );
	}
	accelevate_aweber_mark_token_refreshed();

	return true;
}

/**
 * How long a stored access token should be considered fresh.
 *
 * AWeber access tokens expire after roughly two hours.
 *
 * @return int Seconds.
 */
function accelevate_aweber_access_token_ttl() {
	return (int) apply_filters( 'accelevate_aweber_access_token_ttl', 90 * MINUTE_IN_SECONDS );
}

/**
 * Record when the current access token was last issued or refreshed.
 *
 * @return void
 */
function accelevate_aweber_mark_token_refreshed() {
	update_option( 'accelevate_aweber_token_updated_at', time(), false );
}

/**
 * Whether the stored access token is old enough to refresh proactively.
 *
 * @return bool
 */
function accelevate_aweber_access_token_is_stale() {
	$updated_at = (int) get_option( 'accelevate_aweber_token_updated_at', 0 );

	if ( 0 === $updated_at ) {
		return true;
	}

	return ( time() - $updated_at ) >= accelevate_aweber_access_token_ttl();
}

/**
 * Return a usable access token, refreshing in the background when stale.
 *
 * @param array|null $refresh_meta Optional output for diagnostics.
 * @return string|WP_Error
 */
function accelevate_aweber_ensure_fresh_access_token( &$refresh_meta = null ) {
	$refresh_meta = array(
		'refresh_attempted' => false,
		'refresh_status'    => 'not_needed',
	);

	if ( ! accelevate_aweber_access_token_is_stale() ) {
		return accelevate_aweber_get_access_token();
	}

	$refresh_meta['refresh_attempted'] = true;
	$refresh_meta['refresh_status']    = 'proactive_attempted';

	$refresh = accelevate_aweber_refresh_access_token();
	if ( is_wp_error( $refresh ) ) {
		$refresh_data = $refresh->get_error_data();
		if ( ! is_array( $refresh_data ) ) {
			$refresh_data = array();
		}
		$refresh_data['refresh_attempt'] = true;
		$refresh_data['refresh_status']    = 'proactive_failed';
		$refresh->add_data( $refresh_data );

		$refresh_meta['refresh_status'] = 'proactive_failed';

		return $refresh;
	}

	$refresh_meta['refresh_status'] = 'proactive_success';

	return accelevate_aweber_get_access_token();
}

/**
 * Get the stored AWeber access token.
 *
 * @return string|WP_Error
 */
function accelevate_aweber_get_access_token() {
	$token = (string) get_option( 'accelevate_aweber_access_token', '' );
	if ( '' === $token ) {
		return new WP_Error( 'aweber_missing_token', __( 'AWeber access token is not configured.', 'mindful-living' ) );
	}

	return $token;
}

/**
 * Format an OAuth error response for admin diagnostics.
 *
 * @param int          $code     HTTP status code.
 * @param string       $raw_body Raw response body.
 * @param array|null   $data     Decoded JSON.
 * @return string
 */
function accelevate_aweber_format_oauth_error( $code, $raw_body, $data ) {
	if ( is_array( $data ) ) {
		$parts = array();
		if ( ! empty( $data['error'] ) ) {
			$parts[] = 'error=' . sanitize_text_field( (string) $data['error'] );
		}
		if ( ! empty( $data['error_description'] ) ) {
			$parts[] = sanitize_text_field( (string) $data['error_description'] );
		}
		if ( ! empty( $parts ) ) {
			return sprintf( 'HTTP %d (%s)', (int) $code, implode( ': ', $parts ) );
		}
	}

	$snippet = wp_strip_all_tags( (string) $raw_body );
	if ( strlen( $snippet ) > 240 ) {
		$snippet = substr( $snippet, 0, 240 ) . '...';
	}

	return sprintf( 'HTTP %d (%s)', (int) $code, $snippet ? $snippet : __( 'empty response', 'mindful-living' ) );
}

/**
 * Subscribe an email to the configured AWeber list.
 *
 * @param string $email   Email.
 * @param array  $context Context with optional tags.
 * @return true|WP_Error
 */
function accelevate_newsletter_aweber_subscribe( $email, $context = array() ) {
	if ( ! accelevate_aweber_is_configured() ) {
		return new WP_Error( 'aweber_not_configured', __( 'AWeber is not connected yet.', 'mindful-living' ) );
	}

	$account_id = sanitize_text_field( get_option( 'accelevate_aweber_account_id', '' ) );
	$list_id    = sanitize_text_field( get_option( 'accelevate_aweber_list_id', '' ) );

	$refresh_meta = array();
	$token        = accelevate_aweber_ensure_fresh_access_token( $refresh_meta );
	if ( is_wp_error( $token ) ) {
		return $token;
	}

	$refresh_attempted = ! empty( $refresh_meta['refresh_attempted'] );
	$refresh_status    = isset( $refresh_meta['refresh_status'] ) ? (string) $refresh_meta['refresh_status'] : 'not_needed';

	$tags = array( 'accelevate' );
	if ( ! empty( $context['tags'] ) && is_array( $context['tags'] ) ) {
		$tags = array_merge( $tags, array_map( 'sanitize_text_field', $context['tags'] ) );
	}
	$tags = array_values( array_unique( array_filter( $tags ) ) );

	$body = array(
		'email'           => $email,
		'update_existing' => true,
		'tags'            => $tags,
	);

	$url      = sprintf( 'https://api.aweber.com/1.0/accounts/%s/lists/%s/subscribers', rawurlencode( $account_id ), rawurlencode( $list_id ) );
	$response = accelevate_aweber_api_request( 'POST', $url, $body, $token );

	if ( is_wp_error( $response ) && 'aweber_unauthorized' === $response->get_error_code() ) {
		$refresh_attempted = true;
		$refresh_status    = 'reactive_attempted';
		$refresh           = accelevate_aweber_refresh_access_token();

		if ( is_wp_error( $refresh ) ) {
			$refresh_data = $refresh->get_error_data();
			if ( ! is_array( $refresh_data ) ) {
				$refresh_data = array();
			}
			$refresh_data['refresh_attempt'] = true;
			$refresh_data['refresh_status']  = 'reactive_failed';
			$refresh->add_data( $refresh_data );

			return $refresh;
		}

		$refresh_status = 'reactive_success';
		$token          = accelevate_aweber_get_access_token();
		if ( is_wp_error( $token ) ) {
			$token->add_data(
				array(
					'refresh_attempt' => true,
					'refresh_status'  => 'reactive_success_missing_token',
				)
			);
			return $token;
		}

		$response = accelevate_aweber_api_request( 'POST', $url, $body, $token );
	}

	if ( is_wp_error( $response ) ) {
		if ( $refresh_attempted && 'reactive_success' === $refresh_status && 'aweber_unauthorized' === $response->get_error_code() ) {
			$api_data = $response->get_error_data();
			if ( ! is_array( $api_data ) ) {
				$api_data = array();
			}

			return new WP_Error(
				'aweber_unauthorized_after_refresh',
				__( 'AWeber access token was refreshed, but the subscribe request was still rejected. Check Account ID, List ID, and that the app has subscriber.write scope.', 'mindful-living' ),
				array_merge(
					$api_data,
					array(
						'refresh_attempt' => true,
						'refresh_status'  => 'reactive_success',
					)
				)
			);
		}

		if ( $refresh_attempted ) {
			$response_data = $response->get_error_data();
			if ( ! is_array( $response_data ) ) {
				$response_data = array();
			}
			$response_data['refresh_attempt'] = true;
			$response_data['refresh_status']    = $refresh_status;
			$response->add_data( $response_data );
		}

		return $response;
	}

	return true;
}

/**
 * Make an authenticated AWeber API request.
 *
 * @param string $method HTTP method.
 * @param string $url    URL.
 * @param array  $body   JSON body.
 * @param string $token  Access token.
 * @return true|WP_Error
 */
function accelevate_aweber_api_request( $method, $url, $body, $token ) {
	$args = array(
		'method'  => $method,
		'timeout' => 20,
		'headers' => array(
			'Authorization' => 'Bearer ' . $token,
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
		),
	);

	if ( ! empty( $body ) ) {
		$args['body'] = wp_json_encode( $body );
	}

	$response = wp_remote_request( $url, $args );
	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code     = wp_remote_retrieve_response_code( $response );
	$raw_body = wp_remote_retrieve_body( $response );

	if ( 401 === $code ) {
		return new WP_Error(
			'aweber_unauthorized',
			__( 'AWeber access token expired.', 'mindful-living' ),
			array(
				'http_code'      => $code,
				'aweber_response'=> wp_strip_all_tags( (string) $raw_body ),
			)
		);
	}

	if ( $code >= 200 && $code < 300 ) {
		return true;
	}

	return new WP_Error(
		'aweber_subscribe_failed',
		sprintf(
			/* translators: %d: HTTP status code */
			__( 'AWeber returned HTTP %d while subscribing.', 'mindful-living' ),
			$code
		),
		array(
			'http_code'       => $code,
			'aweber_response' => wp_strip_all_tags( (string) $raw_body ),
		)
	);
}
