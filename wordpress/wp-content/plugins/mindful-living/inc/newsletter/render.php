<?php
/**
 * Newsletter form and block rendering.
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
function accelevate_newsletter_subscribe_status() {
	return isset( $_GET['subscribe'] ) ? sanitize_key( wp_unslash( $_GET['subscribe'] ) ) : '';
}

/**
 * Render subscribe success/error notices.
 *
 * @return string
 */
function accelevate_newsletter_render_notices() {
	$status = accelevate_newsletter_subscribe_status();
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

/**
 * Render the newsletter form only.
 *
 * @param array $args {
 *     @type string $layout   inline|card.
 *     @type string $context  footer|post|shortcode|...
 *     @type string $field_id Email input id.
 *     @type string $class    Extra classes.
 * }
 * @return string
 */
function accelevate_newsletter_render_form( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'layout'      => 'inline',
			'context'     => 'footer',
			'field_id'    => 'accelevate-subscribe-email',
			'class'       => '',
			'placeholder' => '',
			'button'      => '',
		)
	);

	$classes = trim( 'ml-subscribe-form ' . $args['class'] );
	if ( 'footer' === $args['context'] || ( 'inline' === $args['layout'] && 'shortcode' !== $args['context'] ) ) {
		$classes .= ' ml-footer__form';
	}
	if ( 'post' === $args['context'] || 'card' === $args['layout'] ) {
		$classes .= ' ml-post-subscribe__form';
	}

	$placeholder = $args['placeholder']
		? $args['placeholder']
		: get_theme_mod( 'accelevate_subscribe_placeholder', __( 'What is your email?', 'mindful-living' ) );
	$button      = $args['button']
		? $args['button']
		: get_theme_mod( 'accelevate_subscribe_button', __( 'Subscribe', 'mindful-living' ) );

	ob_start();
	?>
	<form class="<?php echo esc_attr( $classes ); ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php
		wp_nonce_field( 'accelevate_subscribe', 'accelevate_subscribe_nonce' );
		?><input type="hidden" name="action" value="accelevate_subscribe" /><input type="hidden" name="accelevate_subscribe_context" value="<?php echo esc_attr( $args['context'] ); ?>" /><label class="screen-reader-text" for="<?php echo esc_attr( $args['field_id'] ); ?>"><?php esc_html_e( 'Email address', 'mindful-living' ); ?></label><input id="<?php echo esc_attr( $args['field_id'] ); ?>" name="accelevate_email" type="email" required placeholder="<?php echo esc_attr( $placeholder ); ?>" autocomplete="email" /><button type="submit"><?php echo esc_html( $button ); ?></button></form>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render a reusable newsletter block (form + optional copy).
 *
 * @param array $args {
 *     @type string $layout        inline|card|minimal.
 *     @type string $context       footer|post|shortcode.
 *     @type string $eyebrow       Optional eyebrow.
 *     @type string $title         Optional title.
 *     @type string $text          Optional supporting text.
 *     @type string $legal         Optional legal note.
 *     @type bool   $show_notices  Show success/error notices.
 *     @type string $field_id      Email field id.
 * }
 * @return string
 */
function accelevate_newsletter_render_block( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'layout'       => 'inline',
			'context'      => 'footer',
			'eyebrow'      => '',
			'title'        => __( 'Get calm updates in your inbox', 'mindful-living' ),
			'text'         => '',
			'legal'        => __( 'By entering your email, you agree to our Privacy Policy. No spam — just thoughtful notes.', 'mindful-living' ),
			'show_notices' => true,
			'field_id'     => 'accelevate-subscribe-email',
		)
	);

	if ( 'post' === $args['context'] ) {
		$args['layout']   = 'card';
		$args['field_id'] = 'accelevate-post-subscribe-email';
	}

	$wrapper_class = 'ml-newsletter-block ml-newsletter-block--' . sanitize_html_class( $args['layout'] );
	if ( 'footer' === $args['context'] ) {
		$wrapper_class .= ' ml-footer__subscribe';
	}
	if ( 'card' === $args['layout'] ) {
		$wrapper_class = 'ml-post-subscribe';
	}

	ob_start();

	if ( 'card' === $args['layout'] ) {
		?>
		<aside class="<?php echo esc_attr( $wrapper_class ); ?>" aria-labelledby="ml-post-subscribe-title">
			<div class="ml-post-subscribe__inner">
				<?php if ( $args['eyebrow'] ) : ?>
					<p class="ml-post-subscribe__eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p>
				<?php endif; ?>
				<?php if ( $args['title'] ) : ?>
					<h2 class="ml-post-subscribe__title" id="ml-post-subscribe-title"><?php echo esc_html( $args['title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( $args['text'] ) : ?>
					<p class="ml-post-subscribe__text"><?php echo esc_html( $args['text'] ); ?></p>
				<?php endif; ?>
				<?php
				if ( $args['show_notices'] ) {
					echo accelevate_newsletter_render_notices(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				echo accelevate_newsletter_render_form( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				if ( $args['legal'] ) {
					echo '<p class="ml-post-subscribe__legal">' . esc_html( $args['legal'] ) . '</p>';
				}
				?>
			</div>
		</aside>
		<?php
		return (string) ob_get_clean();
	}

	?>
	<div class="<?php echo esc_attr( $wrapper_class ); ?>">
		<?php if ( $args['title'] ) : ?>
			<p class="ml-footer__subscribe-title"><?php echo esc_html( $args['title'] ); ?></p>
		<?php endif; ?>
		<?php
		if ( $args['show_notices'] ) {
			echo accelevate_newsletter_render_notices(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo accelevate_newsletter_render_form( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $args['legal'] ) {
			echo '<p class="ml-footer__legal-note">' . esc_html( $args['legal'] ) . '</p>';
		}
		?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/* Back-compat wrappers for older includes. */
function accelevate_subscribe_status() {
	return accelevate_newsletter_subscribe_status();
}
function accelevate_render_subscribe_notices() {
	return accelevate_newsletter_render_notices();
}
function accelevate_render_subscribe_form( $args = array() ) {
	return accelevate_newsletter_render_form( $args );
}
