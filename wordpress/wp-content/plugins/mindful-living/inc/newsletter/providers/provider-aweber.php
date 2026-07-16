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
 * @return string|WP_Error Access token.
 */
function accelevate_aweber_get_access_token() {
	$token = (string) get_option( 'accelevate_aweber_access_token', '' );
	if ( '' === $token ) {
		return new WP_Error( 'aweber_missing_token', __( 'AWeber access token is not configured.', 'mindful-living' ) );
	}
	return $token;
}

/**
 * Attempt token refresh using stored refresh token.
 *
 * @return true|WP_Error
 */
function accelevate_aweber_refresh_access_token() {
	$refresh = (string) get_option( 'accelevate_aweber_refresh_token', '' );
	$client  = (string) get_option( 'accelevate_aweber_client_id', '' );

	if ( '' === $refresh || '' === $client ) {
		return new WP_Error( 'aweber_refresh_unavailable', __( 'AWeber refresh token or client ID missing.', 'mindful-living' ) );
	}

	$response = wp_remote_post(
		'https://auth.aweber.com/oauth2/token',
		array(
			'timeout' => 20,
			'body'    => array(
				'grant_type'    => 'refresh_token',
				'refresh_token' => $refresh,
				'client_id'     => $client,
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 !== $code || empty( $data['access_token'] ) ) {
		return new WP_Error( 'aweber_refresh_failed', __( 'Could not refresh the AWeber access token.', 'mindful-living' ) );
	}

	update_option( 'accelevate_aweber_access_token', sanitize_text_field( $data['access_token'] ), false );
	if ( ! empty( $data['refresh_token'] ) ) {
		update_option( 'accelevate_aweber_refresh_token', sanitize_text_field( $data['refresh_token'] ), false );
	}

	return true;
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
	$token      = accelevate_aweber_get_access_token();
	if ( is_wp_error( $token ) ) {
		return $token;
	}

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
		$refresh = accelevate_aweber_refresh_access_token();
		if ( is_wp_error( $refresh ) ) {
			return $refresh;
		}
		$token    = accelevate_aweber_get_access_token();
		$response = accelevate_aweber_api_request( 'POST', $url, $body, $token );
	}

	if ( is_wp_error( $response ) ) {
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

	$code = wp_remote_retrieve_response_code( $response );

	if ( 401 === $code ) {
		return new WP_Error( 'aweber_unauthorized', __( 'AWeber access token expired.', 'mindful-living' ) );
	}

	if ( $code >= 200 && $code < 300 ) {
		return true;
	}

	$message = wp_remote_retrieve_body( $response );
	return new WP_Error(
		'aweber_subscribe_failed',
		sprintf(
			/* translators: %d: HTTP status code */
			__( 'AWeber returned HTTP %d while subscribing.', 'mindful-living' ),
			$code
		),
		$message
	);
}
