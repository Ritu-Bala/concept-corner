<?php
/**
 * Pearson Single Sign On
 *
 * @package   Pearson_SSO
 * @author    Roger Los <roger@rogerlos.com>
 * @license   GPL-2.0+
 * @link      http://pearson.com
 * @copyright 2014 Pearson Education
 */
/**
 * Autoload helper classes
 *
 * @since 1.0.0
 */

/**
 * Pearson_SSO
 * This class instantates the plugin, defines defaults, and points to the helper
 * classes which provide the core functionality.
 *
 * @since     1.1.0
 * @package   Pearson_SSO
 * @author    Roger Los <roger@rogerlos.com>
 */
class Pearson_SSO {
	
	/**
	 * @since   1.0.0
	 * @var     string
	 */
	const VERSION = '1.9.3';
	
	/**
	 * @since   1.3.0
	 * @var     string
	 */
	const DB_VERSION = '3.1';
	
	/**
	 * @since   1.9.0
	 * @var     string
	 */
	protected static $FILE = PSSO_PATH;
	
	/**
	 * @since    1.0.0
	 * @var      string
	 */
	protected static $plugin_slug = 'pearson-sso';
	
	/**
	 * @since    1.0.0
	 * @var      null|Pearson_SSO
	 */
	protected static $instance = null;
	
	/**
	 * @since    1.2.0
	 * @var      null|Pearson_SSO_Auth
	 */
	public static $Auth = null;
	
	/**
	 * @since    1.0.0
	 * @var      string
	 */
	protected static $key = 'psso_options';
	
	/**
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $options = null;
	
	/**
	 * @since    1.5.0
	 * @var      array
	 */
	protected static $url = array();
	
	/**
	 * @since    1.5.0
	 * @var      array
	 */
	protected static $user = array();
	
	/**
	 * Errors output to login form
	 * @since    1.6.0
	 * @var      array
	 */
	protected static $errors = array();
	
	/**
	 * test flag
	 * @since    1.8
	 * @var int
	 */
	protected static $test = 0;
	
	/**
	 * Initialize the plugin
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		
		// initial logging
		Pearson_SSO_Debug::ds( 'Construct: Add actions and filters; early exit check.' );
		
		// Get options
		self::load_options();
		
		// get instance of the auth class
		add_action( 'init', array( $this, 'load_auth' ), 1 );
		
		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( 'Pearson_SSO_WP', 'activate_new_site' ) );
		
		// Load plugin text domain
		add_action( 'init', array( 'Pearson_SSO_WP', 'load_plugin_textdomain' ), 1 );
		
		// only add login stuff if login option is turned on
		if ( self::get_option('psso_auth_login') == 'on' ) {
			
			// add our custom fields to login form
			add_filter( 'login_form_middle', array( 'Pearson_SSO_Login', 'login_form_middle' ) );
			
			// add header to top of login form
			add_filter( 'login_form_top', array( 'Pearson_SSO_Login', 'add_login_top' ) );
			
			// return login errors to our login form
			add_action( 'wp_login_failed', array( 'Pearson_SSO_Login', 'login_fail' ), 10, 2 );
		}
		
		// enqueue scripts
		add_action( 'init', array( 'Pearson_SSO_WP', 'register_scripts' ) );
		add_action( 'init', array( 'Pearson_SSO_WP', 'localize_js' ) );
		add_action( 'wp_enqueue_scripts', array( 'Pearson_SSO_WP', 'enqueue_scripts' ) );
		
		// adds SSO to admin bar menu for quick access
		add_action( 'admin_bar_menu', array( 'Pearson_SSO_WP', 'add_sso_to_admin_menu' ), 999 );
		
		// get out early if conditions are not met
		if ( $this->no_early_exit() === true ) {
			
			// we run "pre_sso" to allow the parsing and loading of the URL or a feed in from test
			add_action( 'init', array( $this, 'pre_sso' ), 3 );
		}
		
		Pearson_SSO_Debug::de( 'Construct: complete.' );
	}
	
	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 * @return    string Plugin slug variable.
	 */
	public static function slug() {
		return self::$plugin_slug;
	}
	
	/**
	 * Return root plugin file
	 *
	 * @since    1.9
	 * @return    string Plugin slug variable.
	 */
	public static function file() {
		return self::$FILE;
	}
	
	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * Load Authorization Class
	 * @since    1.2.0
	 */
	public function load_auth() {
		self::$Auth = Pearson_SSO_Auth::get_instance();
	}
	
	/**
	 * Load SSO options
	 *
	 * @since    1.0.0
	 *           1.5.0 Check to see if options are in file first
	 *           1.9.1 Removed file check
	 */
	public static function load_options() {
		self::$options = stripslashes_deep( get_option( self::$key ) );
	}
	
	/**
	 * Setter, universal
	 *
	 * @since    1.8.0
	 *
	 * @param string $var
	 * @param string|array $keys
	 * @param mixed $value
	 * @param bool $log
	 */
	public static function setter( $value, $var, $keys = array(), $log = true ) {
		
		$keys = ! is_array( $keys ) ? (array) $keys : $keys;
		$rep = empty( $keys ) ? '' : '[&nbsp;' . implode( '&nbsp;][&nbsp;', $keys ) . '&nbsp;]';
		
		// $keys contains the steps to the key which needs saving in the property
		if ( ! empty( $keys ) ) {
			
			$lkey = array_pop( $keys );
			
			while ( $akey = array_shift( $keys ) ) {
				
				if ( ! array_key_exists( $akey, self::${$var} ) )
					self::${$var}[ $akey ] = array();
				
				self::${$var} = &self::${$var}[ $akey ];
			}
			
			self::${$var}[ $lkey ] = $value;
		}
		
		// no keys were sent, this is just a direct setting of a value
		else {
			self::${$var} = $value;
		}
		
		if ( $log ) {
			Pearson_SSO_Debug::dset( 'self::$' . $var . $rep, array( 'self::$' . $var . $rep => $value ) );
		}
	}
	
	/**
	 * Set public errors
	 *
	 * @since    1.6.0
	 *
	 * @param $error
	 */
	public static function set_error( $error ) {
		self::$errors[] = $error;
	}
	
	/**
	 * Called from admin test page, sets params needed to run SSO in test mode
	 * 
	 * @param $path
	 * @param $query
	 */
	public static function set_test( $path, $query ) {
		
		Pearson_SSO_Debug::ds('Admin Test Setup');
		
		self::$test = 1;
		self::setter( $path, 'url', 'path' );
		self::setter( $query, 'url', 'query' );
		
		Pearson_SSO_Debug::de('');
	}
	
	/**
	 * Return a particular option
	 *
	 * @since 1.2.0
	 *
	 * @param $x
	 * @param bool $log
	 *
	 * @return null
	 */
	public static function get_option( $x, $log = true ) {
		
		$ret = ( isset( self::$options[ $x ] ) && self::$options[ $x ] ) ? self::$options[ $x ] : null;
		
		if ( $log ) {
			Pearson_SSO_Debug::dopt(
				'self::$options[&nbsp;' . $x . '&nbsp;]',
				array( 'self::$options[&nbsp;' . $x . '&nbsp;]' => $ret )
			);
		}
		
		return $ret;
	}
	
	/**
	 * Generic getter function
	 *
	 * @param string $prop
	 * @param array|string $keys
	 * @param bool $log
	 *
	 * @return mixed
	 */
	public static function getter( $prop, $keys = array(), $log = true ) {
		
		$ret = null;
		$rep = '';
		
		if ( isset( self::${ $prop } ) && self::get_ok( $prop ) ) {
			
			$ret = self::${ $prop };
			$keys = ! is_array( $keys ) ? (array) $keys : $keys;
			
			if ( ! empty( $keys ) ) {
				$rep = '[&nbsp;' . implode('&nbsp;][&nbsp;', $keys ) . '&nbsp;]';
				while ( $akey = array_shift( $keys ) ) {
					$ret = array_key_exists( $akey, $ret ) ? $ret[ $akey ] : null;
					if ( $ret === null ) break;
				}
			}
		}
		
		if ( $log ) {
			Pearson_SSO_Debug::dopt( 'self::$' . $prop . $rep, array( 'self::$' . $prop . $rep => $ret ) );
		}
		
		return $ret;
	}
	
	/**
	 * Checks to be sure getting a property via the getter is OK
	 * 
	 * @param        $prop
	 * @param string $key
	 *
	 * @return bool
	 */
	private static function get_ok( $prop, $key = '' ) {
		
		$allowed_props = array(
			'options', 'url', 'user', 'test', 'FILE'
		);
		$prohibited_keys = array();
		
		return in_array( $prop, $allowed_props ) && ! in_array( $key, $prohibited_keys );
	}

	/**
	 * Returns class constant
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public static function get_const( $key ) {
		return constant( 'self::' . $key );
	}
	
	/**
	 * Return errors
	 *
	 * @since 1.6.0
	 */
	public static function get_errors() {
		
		$ret = empty( self::$errors ) ? false : self::$errors;
		Pearson_SSO_Debug::dopt( '', array( 'self::$errors'  => $ret ) );
		
		return $ret;
	}
	
	/**
	 * Early exit if user is logged in, or query string doesn't match
	 *
	 * @since    1.5.0
	 */
	public function no_early_exit() {
		
		Pearson_SSO_Debug::ds( 'Checks if SSO should continue loading' );
		
		// parse the URL
		$url     = parse_url( $_SERVER['REQUEST_URI'] );
		$ret     = null;
		$reason  = '';
		
		// if the query string contains "loggedout" we want to set an option to prevent logs from being written
		self::setter( false, 'options', 'writelog' );
		
		if ( isset( $url['query'] ) && $url['query'] && ! is_admin() ) {
			
			// make sure we're not logging out
			if ( strstr( $url['query'], 'loggedout' ) || strstr( $url['query'], 'logout' ) ) {
				$ret = false;
				$reason = 'Stop: User is logging out';
			}
			
			// save the path vars and allow SSO to continue
			else {
				$ret = true;
				self::setter( isset( $url['path'] ) && $url['path'] ? $url['path'] : '/', 'url', 'path' );
				self::setter( $url['query'], 'url', 'query' );
			}
		}
		
		// allow ajax from the test page
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_POST['action'] ) && $_POST['action'] == 'psso' ) {
			$ret = true;
			$reason = 'OK: SSO called via ajax';
		}
		
		// but deny it from everywhere else
		else if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$ret = false;
			$reason = 'Stop: Non-SSO ajax request';
		}
		
		// keep out wordpress functions like cron and anticaptcha
		if ( $ret === null && strstr( $url['path'], '/wp-' ) === true ) {
			$ret = false;
			$reason = 'Stop: Called via cron or other admin function';
		}
		
		// check if there is a query string
		if ( $ret === null && ! isset( $url['query'] ) ) {
			$ret = false;
			$reason = 'Stop: No query string';
		}
		
		// decipher the query string
		if ( $ret === true ) {
			
			self::setter( true, 'options', 'writelog' );
			
			$query_vars = Pearson_SSO_Url::sso_query_var_rules( $url['query'] );
			
			// if we didn't find our rules, return if we're not in admin
			if ( $query_vars === false && ! is_admin() ) {
				$ret = false;
				$reason = 'Stop: Query vars did not match SSO rules.';
			} 
			
			// place the found values into $url
			else {
				
				// put the host into the $url property, no matter what
				self::$url['site'] = $_SERVER['HTTP_HOST'];
				
				// place the query information into the $url property
				if ( ! is_admin() ) {
					
					foreach ( $query_vars as $key => $var ) {
						self::setter( $var, 'url', $key );
					}
				}
				
				$reason = 'OK: SSO key and payload in query string.';
			}
		}
		
		// set debug message
		Pearson_SSO_Debug::de( 
			$reason,
			array( 'url' => $_SERVER['REQUEST_URI'], ),
			( $ret === null || $ret === true ) ? 'ok' : 'fail'
		);
		
		return $ret === null ? true : $ret;
	}
	
	/**
	 * Public SSO function. Allows main SSO function to be run in test mode
	 * early_exit() has already prequalified the request
	 *
	 * @since    1.0.0
	 */
	public function pre_sso() {
		
		Pearson_SSO_Debug::ds( 'Checks query only for app payloads, allowing <code>sso</code> to run from admin.' );
		
		// we return admins here, and not in early_exit(), to allow SSO to load all classes for admin
		$ret = is_admin() ? 'Skipped: Admin test page' : '';
		
		// we'll just double-check there is a query
		if ( ! $ret && isset( self::$url['path'] ) && self::$url['query'] ) {
			
			// main SSO function: returns string if successful, array if an error
			$run = $this->sso();

			// if the result is not an array, forward people to that route, else bail
			if ( ! Pearson_SSO::get_errors() && $run ) {
				$this->sso_success( $run );
			}
			
			// oops, set the error and exit
			else {
				$this->sso_bail();
			}
		}

		// set debug message
		Pearson_SSO_Debug::de( $ret, array(), $ret || $ret === null ? 'ok' : 'fail' );
		
		return false;
	}
	
	/**
	 * Main SSO function
	 *
	 * @since    1.0.0
	 * @return bool|object|string
	 */
	public function sso() {
		
		Pearson_SSO_Debug::ds( 'Main SSO function' );
		
		// Is SSO turned on? (Does not fail from admin, to allow offline testing)
		if ( self::get_option('psso_master') == 'off' && self::$test != 1 ) {
			self::set_error( 'SSO turned off in options' );
			return false;
		}
		
		// Does the path pass the "request" rules? (ie, the payload was received at an OK URL?
		if ( ! Pearson_SSO_Url::sso_url_rules( self::$url['path'] ) ) {
			self::set_error( 'Request received at inappropriate URL' );
			return false;
		}
		
		// if this is not a test, we already have the query vars from early exit function
		$query_vars = Pearson_SSO_Url::sso_query_var_rules( self::$url['query'] );
		
		// this is a test, and we need to decipher the query
		if ( $query_vars === false ) {
			self::set_error( 'Query vars did not match SSO rules.' );
			return false;
		}
		
		// Add query vars to log
		Pearson_SSO_Log::log( 'sso_key', $query_vars['key'] );
		Pearson_SSO_Log::log( 'sso_payload', $query_vars['payload'] );
		
		// Decrypt payload
		$decrypted = Pearson_SSO_Decrypt::decrypt( $query_vars );
		if ( $decrypted === false || $decrypted === null ) {
			self::set_error( 'Payload decryption failed.' );
			return false;
		}
		
		// Check critical vars
		if ( ! Pearson_SSO_Payload::sso_critical_vars( $decrypted ) ) {
			self::set_error( 'Critical vars check failed.' );
			return false;
		}
		
		// Set the environment config
		self::setter( Pearson_SSO_Payload::sso_track( $decrypted ), 'user', 'env' );

		// try logging the user in via normal channels first
		$sso_user = Pearson_SSO_User::u( $decrypted );

		if ( ! $sso_user ) {
			
			// now try to log them in via a token if it was present
			if ( self::get_option('psso_auth_token') == 'on' ) {
				$sso_user = self::$Auth->sso_auth_token( $decrypted );
			}
			
			// if we still don't have a user, give up
			if ( ! $sso_user ) {
				self::set_error( 'Could not log in user' );
				return false;
			}
		}

		// how we got the user, if not a test run
		if ( self::$test != 1 )  Pearson_SSO_Log::log( 'sso_via', 'Payload user' );
		
		// update user meta
		$tracker = update_user_meta( $sso_user, 'track', self::$user['env'] );
		
		// debug note about the user meta
		Pearson_SSO_Debug::d( 
			$tracker ? 'Added/changed user meta' : 'Could not update/meta value already set',
			array( 'user' => $sso_user, 'track' => self::$user['env'], '$tracker' => $tracker ),
			$tracker ? 'ok' : 'fail'
		);
		
		// set cookies
		if ( ! Pearson_SSO_Payload::sso_cookies( $decrypted ) ) {
			self::set_error( 'Failed to set critical cookies' );
			return false;
		}
		
		// Set route
		$route = Pearson_SSO_Payload::sso_routing( $decrypted, self::$url['path'] );
		if ( $route === false ) {
			self::set_error( 'Failed to set route' );
			return false;
		}
		
		// if the route was a wildcard
		$route = $route == '*' ? self::$url['path'] : $route;

		// debug success
		Pearson_SSO_Debug::de( 'SSO Done! Route set.', array( 'route' => $route ), 'ok' );
		
		return $route;
	}

	/**
	 * SSO failed

	 * @since    1.0.0
	 * @since    1.9.3
	 */
	private function sso_bail() {

		$err = self::get_errors();

		Pearson_SSO_Debug::d( 'SSO is shutting down, failed with errors.', array( 'error' => $err, ), 'bail', '' );

		if ( self::get_option('psso_logs') == 'on' ) {
			
			Pearson_SSO_Log::log( 'sso_debug', Pearson_SSO_Debug::get_debug() );
			Pearson_SSO_Log::log( 'sso_success', 0 );
			Pearson_SSO_Log::write();
		}

		// cannot exit as WP will stop working
		return;
	}
	
	/**
	 * SSO succeeded
	 *
	 * @since    1.0.0
	 *
	 * @param $route
	 */
	private function sso_success( $route ) {
		
		Pearson_SSO_Debug::d(
			'SSO is shutting down, calling <code>wp_redirect( '. home_url( $route ) .' )</code>',
			array(),
			'success'
		);
		
		if ( self::get_option('psso_logs', false ) == 'on' ) {
			
			Pearson_SSO_Log::log( 'sso_via', 'Payload' );
			Pearson_SSO_Log::log( 'sso_debug', Pearson_SSO_Debug::get_debug() );
			Pearson_SSO_Log::log( 'sso_success', 1 );
			Pearson_SSO_Log::write();
		}

		wp_redirect( home_url( $route ) );
		exit();
	}
}