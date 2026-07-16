<?php
/**
 * Subscribe call-to-action at the end of single posts.
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render subscribe CTA after post content.
 */
function accelevate_render_post_subscribe_cta() {
	if ( ! is_singular( 'post' ) || ! (bool) get_theme_mod( 'accelevate_post_subscribe_cta', true ) ) {
		return;
	}

	$eyebrow = get_theme_mod( 'accelevate_post_cta_eyebrow', __( 'Stay with us', 'mindful-living' ) );
	$title   = get_theme_mod( 'accelevate_post_cta_title', __( 'Get calm updates in your inbox', 'mindful-living' ) );
	$text    = get_theme_mod(
		'accelevate_post_cta_text',
		__( 'New essays on intention, habits, and reflection — short enough for your morning coffee.', 'mindful-living' )
	);

	$notices = accelevate_render_subscribe_notices();
	$form    = accelevate_render_subscribe_form(
		array(
			'context'  => 'post',
			'field_id' => 'accelevate-post-subscribe-email',
		)
	);
	?>
	<aside class="ml-post-subscribe" aria-labelledby="ml-post-subscribe-title">
		<div class="ml-post-subscribe__inner">
			<?php if ( $eyebrow ) : ?>
				<p class="ml-post-subscribe__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<h2 class="ml-post-subscribe__title" id="ml-post-subscribe-title"><?php echo esc_html( $title ); ?></h2>
			<?php if ( $text ) : ?>
				<p class="ml-post-subscribe__text"><?php echo esc_html( $text ); ?></p>
			<?php endif; ?>
			<?php echo $notices; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p class="ml-post-subscribe__legal"><?php esc_html_e( 'By entering your email, you agree to our Privacy Policy. No spam — just thoughtful notes.', 'mindful-living' ); ?></p>
		</div>
	</aside>
	<?php
}
add_action( 'kadence_single_after_entry_content', 'accelevate_render_post_subscribe_cta', 12 );
