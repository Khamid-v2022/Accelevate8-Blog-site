<?php
/**
 * Site footer markup and newsletter subscribe handling.
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve a page URL by slug, falling back to home when missing.
 *
 * @param string $slug Page slug.
 * @return string
 */
function accelevate_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	if ( $page && 'publish' === $page->post_status ) {
		return get_permalink( $page );
	}
	return home_url( '/' . trim( $slug, '/' ) . '/' );
}

/**
 * Render category link helper.
 *
 * @param string $slug Category slug.
 * @param string $label Label.
 * @return string
 */
function accelevate_footer_cat_link( $slug, $label ) {
	$term = get_term_by( 'slug', $slug, 'category' );
	$url  = $term && ! is_wp_error( $term ) ? get_category_link( $term ) : home_url( '/category/' . $slug . '/' );
	return '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
}

/**
 * Premium content-width footer HTML.
 *
 * @param string $content Theme footer HTML.
 * @return string
 */
function mindful_living_footer_html( $content ) {
	unset( $content );

	$logo = mindful_living_logo_img(
		'white',
		array(
			'class'   => 'ml-footer-logo',
			'width'   => '160',
			'loading' => 'lazy',
		)
	);

	$blog_url = get_permalink( (int) get_option( 'page_for_posts' ) );
	if ( ! $blog_url ) {
		$blog_url = home_url( '/blog/' );
	}

	$status = isset( $_GET['subscribe'] ) ? sanitize_key( wp_unslash( $_GET['subscribe'] ) ) : '';

	ob_start();
	?>
	<div class="ml-footer">
		<div class="ml-footer__grid">
			<div class="ml-footer__brand-col">
				<?php if ( $logo ) : ?>
					<div class="ml-footer__logo"><?php echo $logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php else : ?>
					<p class="ml-footer__sitename"><?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?></p>
				<?php endif; ?>

				<p class="ml-footer__tagline"><?php echo esc_html( get_bloginfo( 'description', 'display' ) ?: 'Thoughtful ideas for a calmer, more intentional life.' ); ?></p>

				<div class="ml-footer__subscribe">
					<p class="ml-footer__subscribe-title"><?php esc_html_e( 'Get calm updates in your inbox', 'mindful-living' ); ?></p>

					<?php if ( 'sent' === $status ) : ?>
						<p class="ml-footer__notice is-success" role="status"><?php esc_html_e( 'Thanks — you are on the list.', 'mindful-living' ); ?></p>
					<?php elseif ( 'error' === $status ) : ?>
						<p class="ml-footer__notice is-error" role="alert"><?php esc_html_e( 'Please enter a valid email and try again.', 'mindful-living' ); ?></p>
					<?php endif; ?>

					<form class="ml-footer__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php
						wp_nonce_field( 'accelevate_subscribe', 'accelevate_subscribe_nonce' );
						?><input type="hidden" name="action" value="accelevate_subscribe" /><label class="screen-reader-text" for="accelevate-subscribe-email"><?php esc_html_e( 'Email address', 'mindful-living' ); ?></label><input id="accelevate-subscribe-email" name="accelevate_email" type="email" required placeholder="<?php esc_attr_e( 'What is your email?', 'mindful-living' ); ?>" autocomplete="email" /><button type="submit"><?php esc_html_e( 'Subscribe', 'mindful-living' ); ?></button></form>

					<p class="ml-footer__legal-note"><?php esc_html_e( 'By entering your email, you agree to our Privacy Policy. No spam — just thoughtful notes.', 'mindful-living' ); ?></p>
				</div>
			</div>

			<div class="ml-footer__links">
				<nav class="ml-footer__nav" aria-label="<?php esc_attr_e( 'Journal', 'mindful-living' ); ?>">
					<p class="ml-footer__heading"><?php esc_html_e( 'Journal', 'mindful-living' ); ?></p>
					<ul>
						<li><a href="<?php echo esc_url( $blog_url ); ?>"><?php esc_html_e( 'All articles', 'mindful-living' ); ?></a></li>
						<?php
						echo accelevate_footer_cat_link( 'goals', 'Goals' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo accelevate_footer_cat_link( 'habits', 'Habits' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo accelevate_footer_cat_link( 'mindset', 'Mindset' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo accelevate_footer_cat_link( 'reflection', 'Reflection' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</ul>
				</nav>

				<nav class="ml-footer__nav" aria-label="<?php esc_attr_e( 'Company', 'mindful-living' ); ?>">
					<p class="ml-footer__heading"><?php esc_html_e( 'Company', 'mindful-living' ); ?></p>
					<ul>
						<li><a href="<?php echo esc_url( accelevate_page_url( 'about' ) ); ?>"><?php esc_html_e( 'About Us', 'mindful-living' ); ?></a></li>
						<li><a href="<?php echo esc_url( accelevate_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact Us', 'mindful-living' ); ?></a></li>
						<li><a href="<?php echo esc_url( accelevate_page_url( 'privacy-policy' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'mindful-living' ); ?></a></li>
						<li><a href="<?php echo esc_url( accelevate_page_url( 'faq' ) ); ?>"><?php esc_html_e( 'FAQs', 'mindful-living' ); ?></a></li>
					</ul>
				</nav>
			</div>
		</div>

		<div class="ml-footer__bottom">
			<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'mindful-living' ); ?></p>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_filter( 'theme_mod_footer_html_content', 'mindful_living_footer_html' );

/**
 * Keep footer HTML free of autop/br breakage around the subscribe form.
 *
 * @param string $content Content.
 * @return string
 */
function accelevate_footer_strip_form_breaks( $content ) {
	if ( false === strpos( $content, 'ml-footer__form' ) ) {
		return $content;
	}

	return (string) preg_replace_callback(
		'#<form class="ml-footer__form"[^>]*>.*?</form>#s',
		function ( $matches ) {
			$html = $matches[0];
			$html = preg_replace( '#<br\s*/?>#i', '', $html );
			$html = preg_replace( '#>\s+<#', '><', $html );
			return $html;
		},
		$content
	);
}
add_filter( 'theme_mod_footer_html_content', 'accelevate_footer_strip_form_breaks', 30 );

/**
 * Handle newsletter subscribe submissions.
 */
function accelevate_handle_subscribe() {
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

	$list   = get_option( 'accelevate_subscribers', array() );
	if ( ! is_array( $list ) ) {
		$list = array();
	}
	$list[ $email ] = gmdate( 'c' );
	update_option( 'accelevate_subscribers', $list, false );

	$subject = '[Accelevate] New newsletter subscriber';
	$body    = "A new subscriber joined the list:\n\n{$email}\n";
	wp_mail( get_option( 'admin_email' ), $subject, $body );

	wp_safe_redirect( add_query_arg( 'subscribe', 'sent', $redirect ) );
	exit;
}
add_action( 'admin_post_nopriv_accelevate_subscribe', 'accelevate_handle_subscribe' );
add_action( 'admin_post_accelevate_subscribe', 'accelevate_handle_subscribe' );
