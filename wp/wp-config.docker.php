<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */
// disable cache only for dev
define('WP_CACHE', false);

define('WP_REDIS_HOST', 'psocss-dev-redis.trauxb.ng.0001.usw2.cache.amazonaws.com');

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
//define('DB_NAME', 'conceptcornerwww');
define('DB_NAME', 'conceptcorner');

/** MySQL database username */
//define('DB_USER', 'a04c82de386131');
define('DB_USER', 'conceptcorner');

/** MySQL database password */
//define('DB_PASSWORD', '2d9e8d1f2ba126');
define('DB_PASSWORD', 'Ed5Gd2DJoNnVKRl');

/** MySQL hostname */
//define('DB_HOST', 'us-cdbr-azure-c-west-127-b.cloudapp.net');
define('DB_HOST', 'psocss-dev.cluster-cjhnb3whundc.us-west-2.rds.amazonaws.com');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '.P?@A*(]?a1! DeTc+EZbu%Pp4T{`*e6xkZ*uT}f$^]hYMcp)Q;*/,MFal~8EuCw');
define('SECURE_AUTH_KEY',  'kL)n}M,8r~Tge-*vvd;s_Vof+fR|n*gEUW0e&r$/U3lD/J bCNRPf2cT{3$xTIlS');
define('LOGGED_IN_KEY',    'Rw;2Daj+E*U+*kaKrYeJ@H8[`M0y)fBn$@l#C{Y$R>WkqP=ZU,rFhiN[=OD^($b^');
define('NONCE_KEY',        'hSqrR3U8GjToz`A>Zw)7$csYAO@,t_!kPJQXM*^{eJ]osBVo5}t|m6 hEx]9+-^+');
define('AUTH_SALT',        '}z[*wrU|L9SGpKI`x`6Y=a?+M.|i;N$9HaS=Xos6dwn$%8dfD@C]44eFS#6%`9*G');
define('SECURE_AUTH_SALT', 'FBaE8+ymK0j0s{$g-[Ry+#)Oq`*Ooj+T_4KkDEeTbSUwKn*5YF|^>|v_6{<:EYhv');
define('LOGGED_IN_SALT',   'Yw4|n M=]5gZ+A84Z) 8!A,/rO|LaLH+l-OX86f?T%+:@yKoR>X+*5}a6-(0sx*G');
define('NONCE_SALT',       ' -3yM9*lpW;~u^$HirzNJj#<esw-r~fT@jBQVcGb7yEi5J+8fzYt9?:7uw4-&]|F');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'ccs_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', true);

// have to re-define these since WP cannot seem to find the previous set.
if ( !defined('CC_FRAGMENT_CACHE') ) {
	define( 'CC_FRAGMENT_CACHE', false );
	define( 'CC_USE_FILES', false );
	define( 'CC_FLAT_DIR', 'wp-content/uploads/flat/' );
}

define('WP_HOME', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME']);
define('WP_SITEURL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/wp');

// give WP more memory
define( 'WP_MEMORY_LIMIT', '128M' );
define( 'WP_MAX_MEMORY_LIMIT', '256M' );

// pods
//define( 'PODS_LIGHT', true );
define( 'PODS_DISABLE_SHORTCODE', true );

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
