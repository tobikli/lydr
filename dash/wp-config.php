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
define( 'DB_NAME', 'u257622390_ssEtp' );

/** Database username */
define( 'DB_USER', 'u257622390_Fiusq' );

/** Database password */
define( 'DB_PASSWORD', '9mIEURAnY8' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

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
define( 'AUTH_KEY',          '79]0ifO4B,Ru%CqVs.t3=~0t$(3,$~O:bc)kM}}0U_$eMr]`}x#lkb(N5Uegfwon' );
define( 'SECURE_AUTH_KEY',   '9$NY?>CNVh%FDmMi@C%$P=;&4IyWImqyc/OAiua}%?H}wGcKXm|D:h1#W<U,iOh-' );
define( 'LOGGED_IN_KEY',     '9ENm%b<Tf6|ztE{Z;r;KECm`9|uGI6vKe[~LoIYj33,=ZRc`$sqn~iNZcA7#weGd' );
define( 'NONCE_KEY',         'vG-JG$*HztiN/UQC`qyI)FL~6;[(^u8uj$H3O:e3-2aYLrE:A$tzJOI34,+3JGC%' );
define( 'AUTH_SALT',         '.|HtR!{viSW`w|#16{]|{D9tM%s71G4{Z_g&O-lzr&6-t*(Av8ty8K/Z4f2c__!8' );
define( 'SECURE_AUTH_SALT',  '3cHKoE7Y(dtvI#|Od.:*WtLtu<uSn#S(cDV#0C^xh]..Z=r d~@3Ps@L<AL,|9{s' );
define( 'LOGGED_IN_SALT',    'yZF(&606*jPKlPSS9>J``lXfs:j..?($;f`xu`[6Y?1IlX{T.$p`dVi2Qmxi?[7n' );
define( 'NONCE_SALT',        '6?*Gc8jKbckop~AUQcd}xOm=`8B_EM`I(5D%>`Sb!Cod;sg+J:u1jzJ*{d7xRKkg' );
define( 'WP_CACHE_KEY_SALT', ' 4x?1_YR>+Svd/{JMZs}eI|hZgxUgCZ3gM%5oTKc{c;@|(q0X~Yxo}v>5f @&=AQ' );


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

define( 'FS_METHOD', 'direct' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
