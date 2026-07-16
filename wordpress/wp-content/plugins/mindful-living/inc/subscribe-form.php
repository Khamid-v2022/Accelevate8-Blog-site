<?php
/**
 * Shared newsletter subscribe form markup.
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Subscribe status from query string.
 *
 * @return string sent|error|''
 */
function accelevate_subscribe_status() {
	return isset( $_GET['subscribe'] ) ? sanitize_key( wp_unslash( $_GET['subscribe'] ) ) : '';
}

/**
 * Render the Accelevate subscribe form.
 *
 * @param array $args {
 *     @type string $context   footer|post.
 *     @type string $field_id  Email input id.
 *     @type string $class     Extra form classes.
 * }
 * @return string
 */
function accelevate_render_subscribe_form( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'context'  => 'footer',
			'field_id' => 'accelevate-subscribe-email',
			'class'    => '',
		)
	);

	$classes = trim( 'ml-subscribe-form ' . $args['class'] );
	if ( 'footer' === $args['context'] ) {
		$classes .= ' ml-footer__form';
	}
	if ( 'post' === $args['context'] ) {
		$classes .= ' ml-post-subscribe__form';
	}

	ob_start();
	?>
	<form class="<?php echo esc_attr( $classes ); ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php
		wp_nonce_field( 'accelevate_subscribe', 'accelevate_subscribe_nonce' );
		?><input type="hidden" name="action" value="accelevate_subscribe" /><label class="screen-reader-text" for="<?php echo esc_attr( $args['field_id'] ); ?>"><?php esc_html_e( 'Email address', 'mindful-living' ); ?></label><input id="<?php echo esc_attr( $args['field_id'] ); ?>" name="accelevate_email" type="email" required placeholder="<?php echo esc_attr( get_theme_mod( 'accelevate_subscribe_placeholder', __( 'What is your email?', 'mindful-living' ) ) ); ?>" autocomplete="email" /><button type="submit"><?php echo esc_html( get_theme_mod( 'accelevate_subscribe_button', __( 'Subscribe', 'mindful-living' ) ) ); ?></button></form>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render subscribe success/error notices.
 *
 * @return string
 */
function accelevate_render_subscribe_notices() {
	$status = accelevate_subscribe_status();
	if ( ! in_array( $status, array( 'sent', 'error' ), true ) ) {
		return '';
	}

	ob_start();
	if ( 'sent' === $status ) {
		echo '<p class="ml-subscribe-notice is-success" role="status">' . esc_html__( 'Thanks — you are on the list.', 'mindful-living' ) . '</p>';
	} else {
		echo '<p class="ml-subscribe-notice is-error" role="alert">' . esc_html__( 'Please enter a valid email and try again.', 'mindful-living' ) . '</p>';
	}
	return (string) ob_get_clean();
}
