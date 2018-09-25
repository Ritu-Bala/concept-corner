<?php
/**
 * We're hijacking this before WP has a chance to load to see if a flat HTML file exists.
 * We're also going to peek at the cookies to see if the user is logged in
 */

/**
 * Concept Corner Fragment Cache
 */

// @todo: this will not work if different pages are shown to different roles, or different tracks

define( 'CC_FRAGMENT_CACHE', true );
define( 'CC_FRAGMENT_CACHE_IF_ADMIN', true );
define( 'CC_FRAGMENT_CACHE_FLAT_DIR', 'wp/wp-content/uploads/cc-fragment-cache' );
define( 'CC_FRAGMENT_CACHE_USE_FILES', false );

if (
	// if the cache is being used, and files are being used...
	CC_FRAGMENT_CACHE === true &&
	CC_FRAGMENT_CACHE_USE_FILES === true
) {

	// get path
	$path = $_SERVER[ 'REQUEST_URI' ];
	$file = dirname( __FILE__ ) . '/' . CC_FRAGMENT_CACHE_FLAT_DIR . $path . 'index.html';

	// if the file exists, serve the flat HTML
	if ( file_exists( $file ) ) {

		// load HTML file and exit
		echo file_get_contents( $file );
		exit();
	}
}

/**
 * Very crude debugging tool...
 */

class StopWatch {
	private static $start;
	private static $last;
	private static $events = array();

	public static function start() {
		self::$start = microtime(true);
		self::$last = self::$start;
	}
	public static function elapsed() {
		return microtime(true) - self::$start;
	}
	public static function record($event) {
		$t = microtime(true);
		self::$events[] = array( $t, $t - self::$start, $t - self::$last, $event );
		self::$last = $t;
		return true;
	}
	public static function results() {
		return self::$events;
	}
}
StopWatch::start();

/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */
 
/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
 
define('WP_USE_THEMES', true);

/** Loads the WordPress Environment and Template */
require( dirname( __FILE__ ) . '/wp/wp-blog-header.php' );
