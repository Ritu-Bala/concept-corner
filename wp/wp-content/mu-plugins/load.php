<?php
/**
 * Must-Use Plugin Loader
 *
 * Automatically loads PHP and JS frameworks shared by Concept Corner plugins and themes.
 *
 * @package   cc-mu-loader
 * @author    Roger Los <roger@rogerlos.com>
 * @license   GPL-2.0+
 * @link      http://conceptcorner.com
 * @copyright 2014 Pearson
 *
 * @wordpress-plugin
 * Plugin Name:       Must-Use Plugin Loader
 * Description:       Automatically loads PHP and JS frameworks shared by Concept Corner plugins and themes.
 * Version:           1.0.0
 * Author:            Roger Los
 * Author URI:        http://rogerlos.com
 * Text Domain:       cc-mu-loader
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * Define Constants
 *
 * This feels a bit arbitrary going here, but insures we can use them everywhere.
 */

define( 'CC_LOCATION', get_template_directory_uri() );
define( 'CC_PATH', get_template_directory() );
define( 'CC_CDN_BASE', 's3.amazonaws.com/ccsoc.cc/');
define( 'CC_CDN', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/wp/wp-content/uploads/' );

/**
 * Register all of the scripts in the library
 */

function cc_scripts_register() {

	// bootstrap   http://getbootstrap.com
//	wp_register_script( 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js', array('jquery','cc-js-modernizr'), '3.2.0', false );
//	wp_register_style(  'bootstrap-style', '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css' );

    //WPMU_PLUGIN_URL = 
	wp_register_script( 'bootstrap', WPMU_PLUGIN_URL . '/libraries/bootstrap/js/bootstrap.min.js', array('jquery','cc-js-modernizr'), '3.2.0', false );
	wp_register_style(  'bootstrap-style', WPMU_PLUGIN_URL . '/libraries/bootstrap/css/bootstrap.css' );
	wp_register_style(  'bootstrap-style', WPMU_PLUGIN_URL . '/libraries/bootstrap/css/bootstrap-theme.css', array( 'bootstrap-main' ) );

	// fancybox    http://fancyapps.com
	wp_register_script( 'fancybox', WPMU_PLUGIN_URL . '/libraries/fancybox/jquery.fancybox.pack.js', array('jquery','cc-js-modernizr'), '2.1.5', false );
	wp_register_style(  'fancybox-style', WPMU_PLUGIN_URL . '/libraries/fancybox/jquery.fancybox.css' );

	// splitpage   http://tympanus.net/codrops/2013/10/25/split-layout/
	wp_register_script( 'classie', WPMU_PLUGIN_URL . '/libraries/splitpage/classie.js', array(), '1.0.0', true );
	wp_register_script( 'splitpage', WPMU_PLUGIN_URL . '/libraries/splitpage/splitpage.js', array('classie'), '1.0.0', true );

	// mathjax
	//wp_register_script( 'mathjax', 'http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML', array(), false, false );
	wp_register_script( 'mathjax', 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js?config=TeX-AMS-MML_HTMLorMML', array(), false, false );

}

/**
 * Enqueue scripts
 */

function cc_script_enqueue( $script ) {

	// see if script is registered, if so, enqueue
	if ( wp_script_is( $script, 'registered' ) ) {
		wp_enqueue_script( $script );
	}

	// see if there is a corresponding style registered
	if ( wp_style_is( $script . '-style', 'registered' ) ) {
		wp_enqueue_style( $script . '-style' );
	}

}


/**
 * Add Custom Metaboxes Library
 * https://github.com/WebDevStudios/Custom-Metaboxes-and-Fields-for-WordPress
 */

// optionally calls for metaboxes
if ( ! function_exists( 'cc_cmb' ) ) {
	function cc_cmb() {
		add_action( 'init', 'cc_initialize_cmb_meta_boxes', 9999 );
	}
}

// adds metabox class
if ( ! function_exists( 'cc_initialize_cmb_meta_boxes' ) ) {
	function cc_initialize_cmb_meta_boxes() {
		if ( !class_exists( 'cmb_Meta_Box' ) ) {
			require_once( WPMU_PLUGIN_DIR . '/libraries/Custom-Metaboxes-and-Fields-for-WordPress/init.php' );
		}
	}
}

/**
 * Load other plugins in this directory
 */

// concept corner core logic
require_once( WPMU_PLUGIN_DIR . '/concept-corner/concept-corner.php' );