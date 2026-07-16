<?php
/**
 * Social sharing meta (Open Graph, Twitter) and post preview helpers.
 *
 * @package Accelevate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a dedicated SEO plugin should own meta output.
 *
 * @return bool
 */
function accelevate_seo_plugin_active() {
	return defined( 'RANK_MATH_VERSION' )
		|| defined( 'WPSEO_VERSION' )
		|| class_exists( 'AIOSEO\\Plugin\\AIOSEO' )
		|| class_exists( 'The_SEO_Framework\\Load' );
}

/**
 * Register post meta for per-post social descriptions (block editor sidebar).
 */
function accelevate_register_social_meta() {
	register_post_meta(
		'post',
		'accelevate_social_description',
		array(
			'show_in_rest'      => true,
			'single'            => true,
			'type'              => 'string',
			'description'       => __( 'Short description for email previews and social sharing.', 'mindful-living' ),
			'sanitize_callback' => 'sanitize_textarea_field',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'accelevate_register_social_meta' );

/**
 * Classic editor meta box fallback.
 */
function accelevate_social_meta_box() {
	add_meta_box(
		'accelevate_social_preview',
		__( 'Email & social preview', 'mindful-living' ),
		'accelevate_social_meta_box_render',
		'post',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'accelevate_social_meta_box' );

/**
 * Render social preview meta box.
 *
 * @param WP_Post $post Post.
 */
function accelevate_social_meta_box_render( $post ) {
	wp_nonce_field( 'accelevate_social_meta', 'accelevate_social_meta_nonce' );
	$value = get_post_meta( $post->ID, 'accelevate_social_description', true );
	?>
	<p class="description" style="margin-top:0;">
		<?php esc_html_e( 'Used when sharing this post by email or on social platforms. Leave blank to auto-generate from the article. Set a Featured Image for the preview thumbnail.', 'mindful-living' ); ?>
	</p>
	<p>
		<label for="accelevate_social_description" class="screen-reader-text"><?php esc_html_e( 'Preview description', 'mindful-living' ); ?></label>
		<textarea id="accelevate_social_description" name="accelevate_social_description" rows="3" style="width:100%;" maxlength="320" placeholder="<?php esc_attr_e( 'A calm, compelling summary (about 155 characters works best).', 'mindful-living' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
	</p>
	<?php
}

/**
 * Save social preview meta.
 *
 * @param int $post_id Post ID.
 */
function accelevate_save_social_meta( $post_id ) {
	if ( ! isset( $_POST['accelevate_social_meta_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['accelevate_social_meta_nonce'] ) ), 'accelevate_social_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$desc = isset( $_POST['accelevate_social_description'] )
		? sanitize_textarea_field( wp_unslash( $_POST['accelevate_social_description'] ) )
		: '';
	update_post_meta( $post_id, 'accelevate_social_description', $desc );
}
add_action( 'save_post_post', 'accelevate_save_social_meta' );

/**
 * Build meta description for the current request.
 *
 * @return string
 */
function accelevate_get_meta_description() {
	if ( is_singular() ) {
		$post_id = get_queried_object_id();
		$custom  = get_post_meta( $post_id, 'accelevate_social_description', true );
		if ( is_string( $custom ) && '' !== trim( $custom ) ) {
			return accelevate_trim_description( $custom );
		}

		$post = get_post( $post_id );
		if ( $post && ! empty( $post->post_excerpt ) ) {
			return accelevate_trim_description( $post->post_excerpt );
		}

		if ( $post && ! empty( $post->post_content ) ) {
			return accelevate_trim_description( wp_strip_all_tags( $post->post_content ) );
		}
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term_desc = term_description();
		if ( $term_desc ) {
			return accelevate_trim_description( wp_strip_all_tags( $term_desc ) );
		}
	}

	$default = get_theme_mod( 'accelevate_default_meta_description', '' );
	if ( $default ) {
		return accelevate_trim_description( $default );
	}

	return accelevate_trim_description( get_bloginfo( 'description', 'display' ) );
}

/**
 * Trim text for meta descriptions.
 *
 * @param string $text Text.
 * @return string
 */
function accelevate_trim_description( $text ) {
	$text = wp_strip_all_tags( (string) $text );
	$text = preg_replace( '/\s+/u', ' ', $text );
	$text = trim( $text );

	if ( '' === $text ) {
		return '';
	}

	if ( function_exists( 'mb_strlen' ) && mb_strlen( $text ) > 160 ) {
		$text = mb_substr( $text, 0, 157 ) . '…';
	} elseif ( strlen( $text ) > 160 ) {
		$text = substr( $text, 0, 157 ) . '…';
	}

	return $text;
}

/**
 * Resolve OG image URL for the current request.
 *
 * @return string
 */
function accelevate_get_og_image_url() {
	if ( is_singular() && has_post_thumbnail() ) {
		$image = get_the_post_thumbnail_url( get_queried_object_id(), 'large' );
		if ( $image ) {
			return $image;
		}
	}

	$attachment_id = absint( get_theme_mod( 'accelevate_default_og_image', 0 ) );
	if ( $attachment_id ) {
		$url = wp_get_attachment_image_url( $attachment_id, 'large' );
		if ( $url ) {
			return $url;
		}
	}

	$logo = mindful_living_logo_url( 'color' );
	if ( $logo ) {
		return $logo;
	}

	return '';
}

/**
 * Resolve social sharing title.
 *
 * @return string
 */
function accelevate_get_social_title() {
	if ( is_singular() ) {
		return wp_get_document_title();
	}
	return wp_get_document_title();
}

/**
 * Output Open Graph and Twitter meta tags.
 */
function accelevate_output_social_meta() {
	if ( is_admin() || accelevate_seo_plugin_active() ) {
		return;
	}

	$description = accelevate_get_meta_description();
	$image       = accelevate_get_og_image_url();
	$title       = accelevate_get_social_title();
	$url         = is_singular() ? get_permalink() : home_url( '/' );
	$type        = is_singular( 'post' ) ? 'article' : 'website';

	echo "\n<!-- Accelevate social preview -->\n";
	if ( $description ) {
		echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n";
	}

	echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '" />' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" />' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
	if ( $description ) {
		echo '<meta property="og:description" content="' . esc_attr( $description ) . '" />' . "\n";
	}
	echo '<meta property="og:url" content="' . esc_url( $url ) . '" />' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $type ) . '" />' . "\n";

	if ( $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '" />' . "\n";
		echo '<meta property="og:image:secure_url" content="' . esc_url( $image ) . '" />' . "\n";
	}

	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\n";
	if ( $description ) {
		echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '" />' . "\n";
	}
	if ( $image ) {
		echo '<meta name="twitter:image" content="' . esc_url( $image ) . '" />' . "\n";
	}
}
add_action( 'wp_head', 'accelevate_output_social_meta', 5 );

/**
 * Customizer: sharing defaults.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function accelevate_register_sharing_customizer( $wp_customize ) {
	$wp_customize->add_section(
		'accelevate_sharing',
		array(
			'title'       => __( 'Accelevate Sharing & Subscribe', 'mindful-living' ),
			'description' => __( 'Email/social previews and the subscribe call-to-action at the end of posts. Per-post: set Featured Image + Email & social preview in the post editor.', 'mindful-living' ),
			'priority'    => 43,
		)
	);

	$wp_customize->add_setting(
		'accelevate_default_meta_description',
		array(
			'default'           => 'Thoughtful essays on goals, habits, mindset, and reflection — for a calmer, more intentional life.',
			'sanitize_callback' => 'sanitize_textarea_field',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'accelevate_default_meta_description',
		array(
			'label'       => __( 'Default preview description', 'mindful-living' ),
			'description' => __( 'Fallback when a post has no custom preview text. Individual posts can override this in the editor.', 'mindful-living' ),
			'section'     => 'accelevate_sharing',
			'type'        => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'accelevate_default_og_image',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'accelevate_default_og_image',
			array(
				'label'       => __( 'Default share image', 'mindful-living' ),
				'description' => __( 'Used when a post has no Featured Image. Recommended 1200×630px. Falls back to your color logo.', 'mindful-living' ),
				'section'     => 'accelevate_sharing',
				'mime_type'   => 'image',
			)
		)
	);

	$wp_customize->add_setting(
		'accelevate_post_subscribe_cta',
		array(
			'default'           => true,
			'sanitize_callback' => function ( $value ) {
				return (bool) $value;
			},
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'accelevate_post_subscribe_cta',
		array(
			'label'   => __( 'Show subscribe CTA at end of posts', 'mindful-living' ),
			'section' => 'accelevate_sharing',
			'type'    => 'checkbox',
		)
	);

	$cta_fields = array(
		'accelevate_post_cta_eyebrow' => array(
			'label'   => __( 'Post CTA eyebrow', 'mindful-living' ),
			'default' => 'Stay with us',
		),
		'accelevate_post_cta_title'   => array(
			'label'   => __( 'Post CTA title', 'mindful-living' ),
			'default' => 'Get calm updates in your inbox',
		),
		'accelevate_post_cta_text'    => array(
			'label'   => __( 'Post CTA supporting text', 'mindful-living' ),
			'default' => 'New essays on intention, habits, and reflection — short enough for your morning coffee.',
			'type'    => 'textarea',
		),
		'accelevate_subscribe_placeholder' => array(
			'label'   => __( 'Subscribe field placeholder', 'mindful-living' ),
			'default' => 'What is your email?',
		),
		'accelevate_subscribe_button' => array(
			'label'   => __( 'Subscribe button label', 'mindful-living' ),
			'default' => 'Subscribe',
		),
	);

	foreach ( $cta_fields as $id => $field ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $field['default'],
				'sanitize_callback' => ( isset( $field['type'] ) && 'textarea' === $field['type'] ) ? 'sanitize_textarea_field' : 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			$id,
			array(
				'label'   => $field['label'],
				'section' => 'accelevate_sharing',
				'type'    => isset( $field['type'] ) ? $field['type'] : 'text',
			)
		);
	}
}
add_action( 'customize_register', 'accelevate_register_sharing_customizer' );
