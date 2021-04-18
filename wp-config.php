<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'nextgenwordpress' );

/** MySQL database username */
define( 'DB_USER', 'nextgen' );

/** MySQL database password */
define( 'DB_PASSWORD', 'sdf30ZD9Ax' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Kh$|W^N|Q:ZA;Pwpx!r}u?0=$`Oj,`~FmGt*-!OQ+,f+#$<#cLux%g}7OC!O~LRX' );
define( 'SECURE_AUTH_KEY',  '5zg.s;jf9jLfAx$a=22xJ$vt([CFG%SM15_1/*c!]kiVe0f}H{g3eY911NBw8np;' );
define( 'LOGGED_IN_KEY',    'PBI[+avA^#=oeCh,nO=L`w0<#ar*n_iq +fH/v*rc@uZUeV6)m7FNqi4h3; LM&F' );
define( 'NONCE_KEY',        '}c.c1H)m(qAVq7Lfbc7{lk$A@tF-a&XiX_fD*gC2qRF/hl6/etiD{H./)IYqOL~/' );
define( 'AUTH_SALT',        '#[${/I4JZW;M@t_@a)G!l1eV/]oSkpUd0,u)efwx:&dYD {VM@=wj<mNg)=]cs;#' );
define( 'SECURE_AUTH_SALT', '&eCCj8a=|x`q3S.SfN5mZVRV|5r|u!uGf%&DPx`q[*Ilc.m*v~/Mv,n4]xuF]3%w' );
define( 'LOGGED_IN_SALT',   'c[3WIc1-qZ_5lE>^$Ya(H:<dH 6!zzwTUgVD6@Sr@=8tujSr6/^%&/k-6~d28@r8' );
define( 'NONCE_SALT',       ')x6,-v@/oJp<6I~k.kI>UD*Py=EMYu62~DUmKVC+4;PZe)b;l6^0;.=+n$}%?Q^#' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
