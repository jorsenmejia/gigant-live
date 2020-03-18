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
define( 'AUTH_KEY',         'w[j5c-RuG#0Ugqt;6d@?]|mR$T9|-Yra?Oc zY:bUG[#Jd;,Y2->=py+0yIjE=Vw' );
define( 'SECURE_AUTH_KEY',  '(9Ykgt(NF.0:rAmI8p|Frs^#pz86B%Gt#%P+Bu8M3_W4pHm4+X3Y==-ghp;|-=Q;' );
define( 'LOGGED_IN_KEY',    'N# 2y~4WbQ`[K+#@9-94@5 QvLI5.u5p51 i]uKRVsFLG`(g|RC7Dr@&6KlN|VX8' );
define( 'NONCE_KEY',        '?a7]re^I>CP/d&HQqD*,nV4@>i%d%ha,pAuYkh~RHFc9(kh]v^hSvh(Dw9@>o7/c' );
define( 'AUTH_SALT',        'dqLQB?Pqvt#@BQUspKsaRJXD8o*m-@B4S(5,OiF!~>]>KfMsI~e(MFv,4P0#!7GT' );
define( 'SECURE_AUTH_SALT', 'M.fR5e2yAEWR#aP&]|;ls1_iBt|^@m#Z/A{C),c%.*CTqHai}a1|WA(pte}gnZpa' );
define( 'LOGGED_IN_SALT',   'qxV`;%V8 ^+zr:x#@A0mawUDzWzty}`4:&m}.*mC SeI!!7HWBfk_ )N1PHqa[[T' );
define( 'NONCE_SALT',       'M^+]7R/S9tA*L,,b54*u`r:5r#Kl}B?Zcu=2h3QN*bYs0m_LO5CXtH/]rg`qTZ`B' );

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
