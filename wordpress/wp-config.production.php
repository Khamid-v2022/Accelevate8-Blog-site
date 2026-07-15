<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u115517570_accelevate_db' );

/** Database username */
define( 'DB_USER', 'u115517570_khamid' );

/** Database password */
define( 'DB_PASSWORD', 'xTu^%63kTK' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'r}Oe^tlKFRAbm}>Sy.dOw[1RMo]}Q,TxRy?3z3)<]5(]GqW~HdY#>NHq_wMi1ET;' );
define( 'SECURE_AUTH_KEY',   '$4b-}25^;ZbY[Co*)uYx& UCyo46{.u8s,?c{83%D,A_/4fiILM$N$=MJY<+T5Zz' );
define( 'LOGGED_IN_KEY',     ';,RGyGnQv@?Q3#Ybk:(5F])~-kjS=N%lXGx:OX62@8I${P`>%L02f$&]sKY5NSkW' );
define( 'NONCE_KEY',         'Syc3w[7:u5dXZGIzpbNaQ3=;9:SJ,&-IJSj7=T[|lG*?;gz}_w91G6{Vx! b-Z^K' );
define( 'AUTH_SALT',         'vnZ8g1j1WCfKfAogT0o0dY&f_w!_y#`Q&)V|}=Z[ACombxH:@ZqaO-|C)]2%Y<oX' );
define( 'SECURE_AUTH_SALT',  'EUf6UKzawrEwzlWEzXUY^Id&CaQaASil+5-6T}q31kIcJ `ia?A&>Vl+a4~;@{dh' );
define( 'LOGGED_IN_SALT',    'Pj6A_n_*p#%b6~]{8-m<Lrl0*[EUEA+1xQJui?g+0~cheVr?^:i3s$@vf}a:6l $' );
define( 'NONCE_SALT',        'MMID[>2TV<Am*@Y~5|g`$oC:a.EcUQxtm1W|*4o_jN0c@,1 i=,zR`_Sl2:><Bm-' );
define( 'WP_CACHE_KEY_SALT', 'SLK@j|UIG38p_!T.{8C/(%g1hSFrUa)Ki^W Zp_YPIQJz5GMMbA:cGa~onNC!mvA' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
