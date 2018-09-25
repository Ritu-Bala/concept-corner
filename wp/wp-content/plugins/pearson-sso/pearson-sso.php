<?php
/**
 * Pearson Single Sign On
 *
 * Allows a wordpress installation to use Single Sign On and the
 * Pearson Authorization system. Manages logins, decryptes encoded
 * access, and interfaces with the Pearson central authorization
 * process.
 *
 * @package   Pearson_SSO
 * @author    Roger Los <roger@rogerlos.oom>
 * @license   GPL-2.0+
 * @link      http://pearson.com
 * @copyright 2014 Pearson Education
 *
 * @wordpress-plugin
 * Plugin Name:       Pearson Single Sign-On
 * Plugin URI:        https://github.com/Pearson-Foundation/psoc-sso
 * Description:       Integrates Pearson's SSO into your wordpress installation
 * Version:           1.9.3
 * Author:            Roger Los <roger@rogerlos.oom>
 * Author URI:        http://rogerlos.com
 * Text Domain:       pearson-sso
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/Pearson-Foundation/psoc-sso
 */

if ( ! defined( 'WPINC' ) ) die;

require 'autoloader.php';
spl_autoload_register( 'sso_autoloader' );

// define plugin root path
define( 'PSSO_PATH', plugin_dir_path( __FILE__ ) );
define( 'PSSO_URL', plugins_url( '', __FILE__ ) . '/' );

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

//require_once( PSSO_PATH . 'public/class-pearson-sso.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'Pearson_SSO', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Pearson_SSO', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Pearson_SSO', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() ) {
	add_action( 'plugins_loaded', array( 'Pearson_SSO_Admin', 'get_instance' ) );
}

/*----------------------------------------------------------------------------*
 * Fire Up SSO and debugger
 *----------------------------------------------------------------------------*/

Pearson_SSO::get_instance();

/*----------------------------------------------------------------------------*
 * Pluggable function to abscond with the authentication process
 * ---------------------------------------------------------------------------*/

if ( ! function_exists( 'wp_authenticate' ) && Pearson_SSO::get_option( 'psso_auth_login', false ) == 'on' ) {

	function wp_authenticate( $username, $password ) {

		$return  = false;
		$wperror = '';

		// Debug
		Pearson_SSO_Debug::ds('Pluggable function captures login form.');

		// Authorization class
		$A = Pearson_SSO_Auth::get_instance();

		// this function ONLY returns pearson schoolnet credentials
		$credentials = $A->login_capture( $username, $password );
		$errors      = $A->get_errors();

		// the pearson auth process failed with errors
		if ( $errors !== false ) {
			Pearson_SSO_Debug::d(
				'Authorization failed',
				array( 'errors' => $errors ),
				'fail'
			);
		}

		// use our auth creds to login
		$user = apply_filters( 'authenticate', null, $credentials['user_login'], $credentials['user_password'] );

		// this is a catch for a null user getting through
		if ( $user === null ) {
			$user = new WP_Error( 
				'authentication_failed', 
				__( '<strong>ERROR</strong>: Invalid username or incorrect password.' ) 
			);
		}
		
		// wordpress login error
		if ( is_wp_error( $user ) ) {
			$wperror = $user->get_error_code();
			do_action( 'wp_login_failed', $user, $username );
		} 
		
		// we got a user
		else {
			$return = $user;
		}

		Pearson_SSO_Debug::de(
			$return ? 'Login capture successful' : 'Login failed',
			array( $return ? 'user' : 'error' => $return ? $user : $wperror, ),
			$return ? 'success' : 'bail'
		);
		
		$log  = Pearson_SSO::get_option( 'psso_logs', false );
		$log_form = Pearson_SSO::get_option( 'psso_log_form_visits', false );
		
		if ( $log && ( $log_form == 'on' || ( $log_form == 'success' && $return ) ) ) {
			Pearson_SSO_Log::log( 'sso_via', 'Login' );
			Pearson_SSO_Log::log( 'sso_user', $return ? $return->ID : 0 );
			Pearson_SSO_Log::log( 'sso_success', $return ? 1 : 0 );
			Pearson_SSO_Log::log( 'sso_debug', Pearson_SSO_Debug::get_debug() );
			Pearson_SSO_Log::write();
		}
		
		return $return;
	}
}