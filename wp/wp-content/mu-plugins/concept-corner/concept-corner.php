<?php
/**
 * Concept Corner Core
 *
 * Functions tied to the program logic of the Concept Corner website which do not directly affect appearance.
 *
 * @package   Concept_Corner
 * @author    Roger Los <roger@rogerlos.com>
 * @license   GPL-2.0+
 * @link      http://conceptcorner.com
 * @copyright 2014 Pearson
 *
 * @wordpress-plugin
 * Plugin Name:       Concept Corner Core
 * Plugin URI:        @TODO
 * Description:       Provides core functions for Concept Corner site
 * Version:           1.0.0
 * Author:            Roger Los
 * Author URI:        http://rogerlos.com
 * Text Domain:       concept-corner
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/<owner>/<repo>
 * WordPress-Plugin-Boilerplate: v2.6.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-concept-corner.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'Concept_Corner', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Concept_Corner', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Concept_Corner', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-concept-corner-admin.php' );
	add_action( 'plugins_loaded', array( 'Concept_Corner_Admin', 'get_instance' ) );

}