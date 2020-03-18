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
define( 'DB_NAME', 'gigant-live' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         '/62AzGcZ+33[OK^]-^TljA{RpUQ<Gv<:zv,WK`.QwYMcp:Gx(T,~PY7Uul0b.Ung' );
define( 'SECURE_AUTH_KEY',  'DGcxmxr6%qVWn?hV7fPha[%>+suz;:enr~xW;Q=XL7)_nWZ~r`/eVG+t8yy_(vP5' );
define( 'LOGGED_IN_KEY',    '%fPVzM]R3N<J_QDc:6Dv3hX`e$]Yjaf#VJw/**vp@_z0in|&Pbck)NC-8;97)4ju' );
define( 'NONCE_KEY',        '7-.Z}|:Y&E|>fTtqxd8*Yz5}B%whlbcJE^#-;$uny=ZU+gO:T[0AoxKnN{[ y$M{' );
define( 'AUTH_SALT',        'E8Wf*f{3UNST%Q;LC?C0ZHxE2S~,-Gli)#PYt_b#gDV[H,1(ymo4se&jGb#Q[+4?' );
define( 'SECURE_AUTH_SALT', '7fda5vq;`%Ikxr3t[CH7N4_fj]sYOmG[*QkZ2<D%3>p/J`$m-kxjjW;:U`Jz*9Cd' );
define( 'LOGGED_IN_SALT',   's3od;O/]On@@V7 T_5Od|N.F`a<$fSdXf>CJwC1*xozd$$^OvDi3L#cdIj7=qvs|' );
define( 'NONCE_SALT',       ',,MH?_P2-JhV{B9} aY[(=x[P/jq%>CRj>s/xIm(D(_o^3N?xBB%r1^h2t1Euw-!' );

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
define( 'WP_MEMORY_LIMIT', '256M' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
