<?php
/**
 * Newsletter settings (AWeber connection).
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register settings page.
 */
function accelevate_newsletter_admin_menu() {
	add_options_page(
		__( 'Accelevate Newsletter', 'mindful-living' ),
		__( 'Accelevate Newsletter', 'mindful-living' ),
		'manage_options',
		'accelevate-newsletter',
		'accelevate_newsletter_settings_page'
	);
}
add_action( 'admin_menu', 'accelevate_newsletter_admin_menu' );

/**
 * Register settings.
 */
function accelevate_newsletter_register_settings() {
	register_setting( 'accelevate_newsletter', 'accelevate_newsletter_provider', array(
		'type'              => 'string',
		'sanitize_callback' => function ( $value ) {
			return in_array( $value, array( 'aweber', 'local' ), true ) ? $value : 'aweber';
		},
		'default'           => 'aweber',
	) );

	$fields = array(
		'accelevate_aweber_account_id'   => 'sanitize_text_field',
		'accelevate_aweber_list_id'      => 'sanitize_text_field',
		'accelevate_aweber_client_id'    => 'sanitize_text_field',
		'accelevate_aweber_access_token' => 'sanitize_text_field',
		'accelevate_aweber_refresh_token'=> 'sanitize_text_field',
	);

	foreach ( $fields as $key => $callback ) {
		register_setting( 'accelevate_newsletter', $key, array(
			'type'              => 'string',
			'sanitize_callback' => $callback,
			'default'           => '',
		) );
	}
}
add_action( 'admin_init', 'accelevate_newsletter_register_settings' );

/**
 * Render settings page.
 */
function accelevate_newsletter_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$configured = accelevate_aweber_is_configured();
	$provider   = accelevate_newsletter_provider();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Accelevate Newsletter', 'mindful-living' ); ?></h1>
		<p><?php esc_html_e( 'Connect your branded subscribe forms (footer, posts, shortcode) to AWeber. Form copy is still edited under Appearance → Customize → Accelevate Sharing & Subscribe.', 'mindful-living' ); ?></p>

		<?php if ( $configured ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'AWeber credentials are saved. New signups will be sent to your configured list.', 'mindful-living' ); ?></p></div>
		<?php else : ?>
			<div class="notice notice-warning"><p><?php esc_html_e( 'AWeber is not fully configured yet. Signups will temporarily fall back to local storage + admin email.', 'mindful-living' ); ?></p></div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Setup steps', 'mindful-living' ); ?></h2>
		<ol>
			<li><?php esc_html_e( 'Install the official AWeber plugin (optional, for OAuth help) or create an app at AWeber Developer.', 'mindful-living' ); ?></li>
			<li><?php esc_html_e( 'Authorize the app and paste Account ID, List ID, Client ID, Access Token, and Refresh Token below.', 'mindful-living' ); ?></li>
			<li><?php esc_html_e( 'Set Provider to AWeber and save.', 'mindful-living' ); ?></li>
		</ol>

		<form method="post" action="options.php">
			<?php settings_fields( 'accelevate_newsletter' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="accelevate_newsletter_provider"><?php esc_html_e( 'Provider', 'mindful-living' ); ?></label></th>
					<td>
						<select name="accelevate_newsletter_provider" id="accelevate_newsletter_provider">
							<option value="aweber" <?php selected( $provider, 'aweber' ); ?>><?php esc_html_e( 'AWeber (recommended)', 'mindful-living' ); ?></option>
							<option value="local" <?php selected( $provider, 'local' ); ?>><?php esc_html_e( 'Local backup only', 'mindful-living' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'When AWeber is selected but not configured, signups still save locally so you do not lose leads.', 'mindful-living' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="accelevate_aweber_account_id"><?php esc_html_e( 'AWeber Account ID', 'mindful-living' ); ?></label></th>
					<td><input name="accelevate_aweber_account_id" id="accelevate_aweber_account_id" type="text" class="regular-text" value="<?php echo esc_attr( get_option( 'accelevate_aweber_account_id', '' ) ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="accelevate_aweber_list_id"><?php esc_html_e( 'AWeber List ID', 'mindful-living' ); ?></label></th>
					<td><input name="accelevate_aweber_list_id" id="accelevate_aweber_list_id" type="text" class="regular-text" value="<?php echo esc_attr( get_option( 'accelevate_aweber_list_id', '' ) ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="accelevate_aweber_client_id"><?php esc_html_e( 'AWeber Client ID', 'mindful-living' ); ?></label></th>
					<td><input name="accelevate_aweber_client_id" id="accelevate_aweber_client_id" type="text" class="regular-text" value="<?php echo esc_attr( get_option( 'accelevate_aweber_client_id', '' ) ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="accelevate_aweber_access_token"><?php esc_html_e( 'Access Token', 'mindful-living' ); ?></label></th>
					<td><textarea name="accelevate_aweber_access_token" id="accelevate_aweber_access_token" rows="3" class="large-text code"><?php echo esc_textarea( get_option( 'accelevate_aweber_access_token', '' ) ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="accelevate_aweber_refresh_token"><?php esc_html_e( 'Refresh Token', 'mindful-living' ); ?></label></th>
					<td><textarea name="accelevate_aweber_refresh_token" id="accelevate_aweber_refresh_token" rows="3" class="large-text code"><?php echo esc_textarea( get_option( 'accelevate_aweber_refresh_token', '' ) ); ?></textarea></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>

		<hr />
		<h2><?php esc_html_e( 'Reuse the form anywhere', 'mindful-living' ); ?></h2>
		<p><?php esc_html_e( 'Drop the same styled form into any page or post with the shortcode:', 'mindful-living' ); ?></p>
		<code>[accelevate_subscribe layout="inline" title="Get calm updates in your inbox"]</code>
		<p><?php esc_html_e( 'Card layout:', 'mindful-living' ); ?> <code>[accelevate_subscribe layout="card" eyebrow="Stay with us" title="Get calm updates in your inbox"]</code></p>
	</div>
	<?php
}
