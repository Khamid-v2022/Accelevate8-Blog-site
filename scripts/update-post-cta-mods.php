<?php
/**
 * Update post subscribe CTA theme mods.
 *
 * @package Accelevate
 */

$mods = array(
	'accelevate_post_cta_eyebrow'     => 'Before you go',
	'accelevate_post_cta_title'       => 'Let the next essay find you',
	'accelevate_post_cta_text'        => "If this one gave you something to sit with, there's more where it came from — calm, thoughtful reads delivered to your inbox. No noise, no pressure. Short enough for your morning coffee.",
	'accelevate_post_cta_placeholder' => 'Email address',
	'accelevate_post_cta_button'      => 'Join the journal',
);

foreach ( $mods as $key => $value ) {
	set_theme_mod( $key, $value );
	echo $key . '=' . get_theme_mod( $key ) . PHP_EOL;
}

echo "Done.\n";
